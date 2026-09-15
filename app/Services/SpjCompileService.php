<?php

namespace App\Services;

use App\Exceptions\SpjExportException;
use App\Models\Sp2d;
use App\Models\Sp2dBudgetCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;

class SpjCompileService
{
    /**
     * Pilih scan SP2D dan bukti sesuai akun, tanpa menggandakan bukti gabungan.
     */
    public function selectedDocuments(Sp2d $sp2d, array $selectedBudgetCodeIds): Collection
    {
        $spj = $sp2d->spj()->with('documents')->first();

        if (! $spj) {
            return collect();
        }

        $documents = $spj->documents;

        $filesToMerge = $documents->where('document_type', 'sp2d_scan')->values();

        $gabunganDocs = $documents->where('document_type', 'bukti_belanja')
            ->whereNull('sp2d_budget_code_id')->values();
        $gabunganDitambahkan = false;

        // Id "0" adalah item virtual untuk SP2D tanpa akun belanja terdaftar
        // yang dipilih langsung agar bukti belanja gabungannya ikut dikompilasi.
        $normalizedIds = array_map('intval', $selectedBudgetCodeIds);
        $gabunganDipilihLangsung = in_array(0, $normalizedIds, true);
        $realBudgetCodeIds = array_filter($normalizedIds, fn ($id) => $id !== 0);

        $budgetCodes = Sp2dBudgetCode::whereIn('id', $realBudgetCodeIds)
            ->where('sp2d_id', $sp2d->id)
            ->get();

        foreach ($budgetCodes as $code) {
            $perKode = $documents->where('document_type', 'bukti_belanja')
                ->where('sp2d_budget_code_id', $code->id)->values();

            if ($perKode->isNotEmpty()) {
                $filesToMerge = $filesToMerge->merge($perKode);
            } elseif ($gabunganDocs->isNotEmpty() && ! $gabunganDitambahkan) {
                $filesToMerge = $filesToMerge->merge($gabunganDocs);
                $gabunganDitambahkan = true;
            }
        }

        if ($gabunganDipilihLangsung && $gabunganDocs->isNotEmpty() && ! $gabunganDitambahkan) {
            $filesToMerge = $filesToMerge->merge($gabunganDocs);
            $gabunganDitambahkan = true;
        }

        if ($filesToMerge->isEmpty()) {
            return collect();
        }

        return $filesToMerge->unique('id')->values();
    }

    public function compileSp2d(Sp2d $sp2d, array $selectedBudgetCodeIds, string $outputDir): ?string
    {
        $filesToMerge = $this->selectedDocuments($sp2d, $selectedBudgetCodeIds);
        if ($filesToMerge->isEmpty()) {
            return null;
        }
        $bytes = 0;
        foreach ($filesToMerge as $doc) {
            $bytes += filesize(app(SpjExportService::class)->sourcePath($doc->file_path));
        }
        if ($bytes > config('spj_compile.merge_max_bytes')) {
            throw new SpjExportException('PDF gabungan terlalu besar. Gunakan mode arsip asli.');
        }
        $pdf = new Fpdi;
        $adaKonten = false;

        foreach ($filesToMerge as $doc) {
            $disk = Storage::disk('local')->exists($doc->file_path) ? 'local' : 'public';

            if (! Storage::disk($disk)->exists($doc->file_path)) {
                throw new SpjExportException('Dokumen sumber tidak tersedia.');
            }

            $absolutePath = Storage::disk($disk)->path($doc->file_path);
            $extension = strtolower(pathinfo($doc->file_name, PATHINFO_EXTENSION));

            if ($extension === 'pdf') {
                if ($this->appendPdf($pdf, $absolutePath)) {
                    $adaKonten = true;
                }
            } elseif (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
                if ($this->appendImage($pdf, $absolutePath)) {
                    $adaKonten = true;
                }
            } else {
                throw new SpjExportException('Jenis dokumen tidak didukung PDF gabungan. Gunakan arsip asli.');
            }
        }

        if (! $adaKonten) {
            return null;
        }

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $outputPath = $outputDir.'/'.Str::random(12).'_'.Str::slug($sp2d->nomor_sp2d).'.pdf';
        $pdf->Output($outputPath, 'F');

        return $outputPath;
    }

    protected function appendPdf(Fpdi $pdf, string $path): bool
    {
        try {
            $pageCount = $pdf->setSourceFile($path);
        } catch (\Throwable $e) {
            throw new SpjExportException('PDF tidak dapat dibaca. Gunakan arsip asli.', 0, $e);
        }
        if ($pdf->PageNo() + $pageCount > config('spj_compile.merge_max_pages')) {
            throw new SpjExportException('Jumlah halaman melampaui batas. Gunakan arsip asli.');
        }

        for ($i = 1; $i <= $pageCount; $i++) {
            $tplId = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tplId);
            $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);
        }

        return $pageCount > 0;
    }

    protected function appendImage(Fpdi $pdf, string $path): bool
    {
        $info = @getimagesize($path);

        if (! $info || $info[0] * $info[1] > 25000000 || $pdf->PageNo() >= config('spj_compile.merge_max_pages')) {
            throw new SpjExportException('Gambar terlalu besar atau tidak valid. Gunakan arsip asli.');
        }

        [$pxWidth, $pxHeight] = $info;

        // Muat gambar ke halaman A4, menjaga rasio, dengan margin kecil.
        $pageWidth = 210;
        $pageHeight = 297;
        $margin = 10;
        $maxWidth = $pageWidth - ($margin * 2);
        $maxHeight = $pageHeight - ($margin * 2);

        $ratio = min($maxWidth / $pxWidth, $maxHeight / $pxHeight);
        $imgWidth = $pxWidth * $ratio;
        $imgHeight = $pxHeight * $ratio;

        $x = ($pageWidth - $imgWidth) / 2;
        $y = ($pageHeight - $imgHeight) / 2;

        $pdf->AddPage('P', [$pageWidth, $pageHeight]);
        $pdf->Image($path, $x, $y, $imgWidth, $imgHeight);

        return true;
    }
}
