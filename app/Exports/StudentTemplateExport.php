<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use App\Models\Estudiante\Carreras;

class StudentTemplateExport implements Export, WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Estudiantes'   => new StudentsSheet(),
            'Instrucciones' => new InstructionsSheet(),
            'Referencias'   => new ReferencesSheet(),
        ];
    }
}

/* ============================================================
 * HOJA 1: Estudiantes (con datos de ejemplo y dropdowns)
 * ============================================================ */
class StudentsSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    public function title(): string
    {
        return 'Estudiantes';
    }

    public function headings(): array
    {
        return [
            'name',
            'email',
            'id_tipo_documento',
            'documento_identidad',
            'fecha_nacimiento',
            'genero',
            'estado_civil',
            'celular',
            'direccion',
            'id_regional_activo',
            'id_carrera',
        ];
    }

    public function array(): array
    {
        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28, 'B' => 32, 'C' => 18, 'D' => 22, 'E' => 18,
            'F' => 10, 'G' => 15, 'H' => 15, 'I' => 35, 'J' => 20, 'K' => 15,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC3545']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Congelar fila de encabezado
                $sheet->freezePane('A2');

                // Alto de la fila de encabezado
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Filas 2 y 3 (ejemplos) → gris claro itálico
                foreach ([2, 3] as $row) {
                    $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FA']],
                        'font' => ['italic' => true, 'color' => ['rgb' => '6C757D']],
                    ]);
                }

                // ---- Traer IDs reales de la BD para las listas ----
                $tiposDocIds   = DB::table('tipo_documento_identidad')
                                    ->orderBy('tipo_documento_id')
                                    ->pluck('tipo_documento_id')
                                    ->map(fn ($id) => (string) $id)
                                    ->toArray();

                $regionalesIds = DB::table('regional_activo')
                                    ->orderBy('regional_activo_id')
                                    ->pluck('regional_activo_id')
                                    ->map(fn ($id) => (string) $id)
                                    ->toArray();

                $carrerasIds   = Carreras::activasConPensum()
                                    ->pluck('carrera_id')
                                    ->map(fn ($id) => (string) $id)
                                    ->toArray();

                // ---- Dropdowns ----

                // Género (columna F)
                $this->addDropdown($sheet, 'F2:F500', ['M', 'F']);

                // Estado civil (columna G)
                $this->addDropdown($sheet, 'G2:G500', [
                    'Soltero(a)', 'Casado(a)', 'Divorciado(a)', 'Viudo(a)',
                ]);

                // Tipo de documento (C) — IDs reales
                $this->addDropdown($sheet, 'C2:C500', $tiposDocIds);

                // Regional (J) — IDs reales
                $this->addDropdown($sheet, 'J2:J500', $regionalesIds);

                // Carrera (K) — IDs reales
                $this->addDropdown($sheet, 'K2:K500', $carrerasIds);

                // Fecha de nacimiento → formato de fecha
                $sheet->getStyle('E2:E500')
                      ->getNumberFormat()
                      ->setFormatCode('yyyy-mm-dd');

                // Comentarios de ayuda en el encabezado
                $sheet->getComment('A1')->getText()->createTextRun('Nombre completo. Ej: Juan Pérez López');
                $sheet->getComment('B1')->getText()->createTextRun('Correo único. Ej: juan.perez@correo.com');
                $sheet->getComment('C1')->getText()->createTextRun('ID numérico (ver hoja Referencias). Ej: 1');
                $sheet->getComment('D1')->getText()->createTextRun('Número de documento. Ej: 01234567-8');
                $sheet->getComment('E1')->getText()->createTextRun('Formato YYYY-MM-DD. Mayor de 17 años. Ej: 2000-05-14');
                $sheet->getComment('F1')->getText()->createTextRun('M = Masculino, F = Femenino');
                $sheet->getComment('G1')->getText()->createTextRun('Soltero(a), Casado(a), Divorciado(a), Viudo(a)');
                $sheet->getComment('H1')->getText()->createTextRun('Ej: 7890-1234');
                $sheet->getComment('I1')->getText()->createTextRun('Ej: Col. Escalón, San Salvador');
                $sheet->getComment('J1')->getText()->createTextRun('ID de la regional (ver hoja Referencias). Ej: 1');
                $sheet->getComment('K1')->getText()->createTextRun('ID de la carrera (ver hoja Referencias). Ej: 14');
            },
        ];
    }

    /**
     * Agrega una lista desplegable aplicándola celda por celda.
     * PhpSpreadsheet NO aplica correctamente un DataValidation a un rango
     * completo en Excel 365; hay que recorrer las celdas.
     */
    private function addDropdown(Worksheet $sheet, string $range, array $options): void
    {
        if (empty($options)) {
            return;
        }

        $options = array_values(array_filter($options, fn ($v) => $v !== '' && $v !== null));

        if (empty($options)) {
            return;
        }

        $literal = '"' . implode(',', $options) . '"';

        // Guardamos contra el límite de 255 chars de Excel
        if (strlen($literal) > 255) {
            $literal = '"' . implode(',', array_slice($options, 0, 50)) . '"';
        }

        // Parsear el rango: "F4:F500" → startCol=6, startRow=4, endRow=500
        [$start, $end] = explode(':', $range);
        preg_match('/^([A-Z]+)(\d+)$/', $start, $mStart);
        preg_match('/^([A-Z]+)(\d+)$/', $end,   $mEnd);

        $col     = $mStart[1];          // "F"
        $rowFrom = (int) $mStart[2];    // 4
        $rowTo   = (int) $mEnd[2];      // 500

        // Recorremos cada celda del rango y le asignamos su propia
        // instancia de DataValidation (no compartida).
        for ($row = $rowFrom; $row <= $rowTo; $row++) {
            $validation = new DataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Valor inválido');
            $validation->setError('Elegí un valor de la lista desplegable.');
            $validation->setFormula1($literal);

            $sheet->getCell("{$col}{$row}")->setDataValidation($validation);
        }
    }
}

/* ============================================================
 * HOJA 2: Instrucciones (sin emojis, texto plano)
 * ============================================================ */
class InstructionsSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function title(): string
    {
        return 'Instrucciones';
    }

    public function array(): array
    {
        return [
            ['INSTRUCCIONES PARA CARGA MASIVA DE ESTUDIANTES'],
            [''],
            ['1.', 'Completá la hoja "Estudiantes" desde la fila 2. La fila 1 son los encabezados.'],
            ['2.', 'No modifiques los encabezados de la fila 1 (deben ser exactamente los mismos).'],
            ['3.', 'Todos los campos son obligatorios'],
            ['4.', 'Los campos con dropdown (C, F, G, J, K) solo aceptan valores de la lista.'],
            ['5.', 'Para los IDs (tipo de documento, regional, carrera) consultá la hoja "Referencias".'],
            ['6.', 'La fecha de nacimiento debe estar en formato YYYY-MM-DD y ser mayor de 17 años.'],
            ['7.', 'El correo electrónico debe ser único en el sistema.'],
            ['8.', 'El número de documento debe ser único por tipo de documento.'],
            [''],
            ['SOBRE LOS DOCUMENTOS'],
            [''],
            ['Los estudiantes creados por esta vía quedarán con expediente PENDIENTE.'],
            ['Cada estudiante subirá su fotografía, título de bachillerato y partida de nacimiento'],
            ['al iniciar sesión por primera vez en el sistema.'],
            [''],
            ['CONTRASEÑA TEMPORAL'],
            [''],
            ['La contraseña inicial de cada estudiante será su CÓDIGO DE ESTUDIANTE'],
            ['(se genera automáticamente a partir de sus apellidos).'],
            [''],
            ['SOPORTE'],
            [''],
            ['Si tenés problemas con la carga, contactá al área de Registro Académico.'],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 100];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'DC3545']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            12 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'DC3545']]],
            17 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'DC3545']]],
            22 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'DC3545']]],
        ];
    }
}

/* ============================================================
 * HOJA 3: Referencias (IDs para dropdowns)
 * ============================================================ */
class ReferencesSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function title(): string
    {
        return 'Referencias';
    }

    public function array(): array
    {
        $tiposDoc   = DB::table('tipo_documento_identidad')->orderBy('tipo_documento_id')->get();
        $regionales = DB::table('regional_activo')->orderBy('regional_activo_id')->get();
        $carreras   = Carreras::activasConPensum();

        $max = max($tiposDoc->count(), $regionales->count(), $carreras->count());

        $rows = [
            ['TIPOS DE DOCUMENTO', '', 'REGIONALES / SEDES', '', 'CARRERAS', ''],
        ];

        for ($i = 0; $i < $max; $i++) {
            $rows[] = [
                $tiposDoc[$i]->tipo_documento_id ?? '',
                $tiposDoc[$i]->nombre ?? '',
                $regionales[$i]->regional_activo_id ?? '',
                $regionales[$i]->sede ?? '',
                $carreras[$i]->carrera_id ?? '',
                $carreras[$i]->nombre ?? '',
            ];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return ['A' => 10, 'B' => 30, 'C' => 12, 'D' => 30, 'E' => 10, 'F' => 40];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6C3545']],
            ],
        ];
    }
}