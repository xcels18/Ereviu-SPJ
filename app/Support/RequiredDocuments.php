<?php

namespace App\Support;

use InvalidArgumentException;

class RequiredDocuments
{
    public static function decode(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        for ($i = 0; $i < 2 && is_string($value); $i++) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                break;
            }
            $value = $decoded;
        }
        if (is_string($value)) {
            $value = array_map('trim', explode(',', $value));
        }
        if (! is_array($value) || array_filter($value, fn ($item) => ! is_string($item))) {
            throw new InvalidArgumentException('Daftar dokumen wajib bukan daftar teks. Perbaiki sebelum migrasi.');
        }

        return array_values($value);
    }
}
