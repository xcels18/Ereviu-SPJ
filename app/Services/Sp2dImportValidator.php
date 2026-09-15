<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class Sp2dImportValidator
{
    public function amount($value, int $line): int
    {
        $value = trim((string) $value);
        if (! preg_match('/^\d{1,13}(?:\.\d{1,2})?$/D', $value)) {
            $this->fail($line, 'NILAI_SP2D harus angka non-negatif, tanpa pemisah ribuan, maksimal 13 digit dan 2 desimal bertitik.');
        }
        [$whole, $fraction] = array_pad(explode('.', $value), 2, '');

        return (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
    }

    public function decimal(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public function date($value, int $line): string
    {
        if (is_numeric($value)) {
            $value = Date::excelToDateTimeObject($value)->format('Y-m-d');
        }
        $value = trim((string) $value);
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat('!'.$format, $value);
            if ($date && $date->format($format) === $value && (int) $date->format('Y') >= 2020 && (int) $date->format('Y') <= 2100) {
                return $date->format('Y-m-d');
            }
        }
        $this->fail($line, 'TGL_SP2D tidak valid. Gunakan tanggal Excel, YYYY-MM-DD, DD/MM/YYYY, atau DD-MM-YYYY (2020–2100).');
    }

    public function groups(array $rows, array $columns): array
    {
        $groups = [];
        foreach ($rows as $index => $row) {
            $line = $index + 2;
            if (! array_filter($row, fn ($v) => trim((string) $v) !== '')) {
                continue;
            }
            $values = [];
            foreach (['KDUNIT' => 50, 'NMUNIT' => 255, 'KDPER' => 50, 'NMPER' => 255, 'NOSP2D' => 100] as $name => $max) {
                $values[$name] = trim((string) ($row[$columns[$name]] ?? ''));
                if ($values[$name] === '' || mb_strlen($values[$name]) > $max) {
                    $this->fail($line, "$name wajib diisi, maksimal $max karakter.");
                }
            }
            $date = $this->date($row[$columns['TGL_SP2D']] ?? null, $line);
            $cents = $this->amount($row[$columns['NILAI_SP2D']] ?? null, $line);
            $key = $values['NOSP2D'];
            $identity = [$values['KDUNIT'], $values['NMUNIT'], $date];
            if (isset($groups[$key]) && $groups[$key]['identity'] !== $identity) {
                $this->fail($line, 'Nomor SP2D yang sama memiliki OPD atau tanggal berbeda.');
            }
            $groups[$key] ??= ['identity' => $identity, 'kdunit' => $values['KDUNIT'], 'nmunit' => $values['NMUNIT'], 'tanggal_excel' => $date, 'details' => [], 'total_cents' => 0, 'line' => $line];
            $groups[$key]['total_cents'] += $cents;
            if ($groups[$key]['total_cents'] > 999999999999999) {
                $this->fail($line, 'Total SP2D melebihi kapasitas nominal database.');
            }
            $groups[$key]['details'][] = ['kode_belanja' => $values['KDPER'], 'uraian' => $values['NMPER'], 'jumlah' => $this->decimal($cents)];
        }
        if (! $groups) {
            $this->fail(2, 'Tidak ada rincian SP2D yang dapat diimpor.');
        }

        return $groups;
    }

    private function fail(int $line, string $message): never
    {
        throw ValidationException::withMessages(['file' => "Baris $line: $message Tidak ada data yang disimpan."]);
    }
}
