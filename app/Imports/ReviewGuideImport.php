<?php

namespace App\Imports;

use App\Models\ReviewGuide;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ReviewGuideImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $requiredDocs = [];
        if (!empty($row['dokumen_wajib'])) {
            $docs = explode(',', $row['dokumen_wajib']);
            foreach ($docs as $doc) {
                $requiredDocs[] = trim($doc);
            }
        }

        return new ReviewGuide([
            'kode_rekening' => $row['kode_rekening'] ?? '',
            'nama_belanja' => $row['nama_belanja'] ?? '',
            'required_documents' => json_encode($requiredDocs),
            'notes' => $row['catatan'] ?? null,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);
    }
}