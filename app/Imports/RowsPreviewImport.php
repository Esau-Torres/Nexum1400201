<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import "vacío" cuyo único propósito es servir como argumento
 * válido para Excel::toArray() con WithHeadingRow.
 *
 */
class RowsPreviewImport implements ToArray, WithHeadingRow
{
    public function array(array $array): void
    {
        // Intencionalmente vacío.
    }
}