<?php

namespace App\Services;

class DataPreprocessingService
{
    public function cleanNumber($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        // PhpSpreadsheet mengirim nilai numerik yg sebenarnya (bukan mengubah persen menjadi nilai biasa)
        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);

        // Nilai non-numerik seperti:
        // NO BD
        // #DIV/0!
        // -
        if (
            strtoupper($value) === 'NO BD' ||
            $value === '#DIV/0!' ||
            $value === '-'
        ) {
            return 0;
        }

        // Format Indonesia:
        // 1.234,56 -> 1234.56
        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        // Format desimal koma:
        // 6,00 -> 6.00
        elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        }

        // hapus spasi
        $value = str_replace(' ', '', $value);

        return is_numeric($value)
            ? (float) $value
            : 0;
    }
}