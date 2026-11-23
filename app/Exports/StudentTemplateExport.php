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
                $tiposDoc = DB::table('tipo_documento_identidad')->orderBy('tipo_documento_id')->get(['tipo_documento_id', 'nombre']);
                $regionales = DB::table('regional_activo')->orderBy('regional_activo_id')->get(['regional_activo_id', 'sede']);
                $carreras = Carreras::activasConPensum()->sortBy('nombre')->values();

                // Formatear como "ID - Nombre"
                $tiposDocOpts = $tiposDoc->map(
                    fn ($t) => "{$t->tipo_documento_id} - {$t->nombre}"
                )->toArray();

                $regionalesOpts = $regionales->map(
                    fn ($r) => "{$r->regional_activo_id} - {$r->sede}"
                )->toArray();

                $carrerasOpts = $carreras->map(
                    fn ($c) => "{$c->carrera_id} - {$c->nombre}"
                )->toArray();

                // ---- Dropdowns ----

                // Género (columna F)
                $this->addDropdown($sheet, 'F2:F500', ['M', 'F']);

                // Estado civil (columna G)
                $this->addDropdown($sheet, 'G2:G500', [
                    'Soltero(a)', 'Casado(a)', 'Divorciado(a)', 'Viudo(a)',
                ]);

                $this->addDropdownByRange($sheet, 'C2:C500', "'Referencias'!\$H\$2:\$H\$" . (count($tiposDocOpts) + 1));
                $this->addDropdownByRange($sheet, 'J2:J500', "'Referencias'!\$I\$2:\$I\$" . (count($regionalesOpts) + 1));
                $this->addDropdownByRange($sheet, 'K2:K500', "'Referencias'!\$J\$2:\$J\$" . (count($carrerasOpts) + 1));

                // Fecha de nacimiento → formato de fecha
                $sheet->getStyle('E2:E500')
                      ->getNumberFormat()
                      ->setFormatCode('yyyy-mm-dd');

                // Comentarios de ayuda en el encabezado
                $sheet->getComment('A1')->getText()->createTextRun('Nombre completo. Ej: Juan Pérez López');
                $sheet->getComment('B1')->getText()->createTextRun('Correo único. Ej: juan.perez@correo.com');
                $sheet->getComment('C1')->getText()->createTextRun('Seleccioná un tipo de documento de la lista. El ID se extrae automáticamente.');
                $sheet->getComment('D1')->getText()->createTextRun('Número de documento. Ej: 01234567-8');
                $sheet->getComment('E1')->getText()->createTextRun('Formato YYYY-MM-DD. Mayor de 17 años. Ej: 2000-05-14');
                $sheet->getComment('F1')->getText()->createTextRun('M = Masculino, F = Femenino');
                $sheet->getComment('G1')->getText()->createTextRun('Soltero(a), Casado(a), Divorciado(a), Viudo(a)');
                $sheet->getComment('H1')->getText()->createTextRun('Ej: 7890-1234');
                $sheet->getComment('I1')->getText()->createTextRun('Ej: Col. Escalón, San Salvador');
                $sheet->getComment('J1')->getText()->createTextRun('Seleccioná una regional de la lista. El ID se extrae automáticamente.');
                $sheet->getComment('K1')->getText()->createTextRun('Seleccioná una carrera de la lista. El ID se extrae automáticamente.');
            },
        ];
    }

    /**
     * Agrega un dropdown que apunta a un rango en otra hoja.
     * Evita el límite de 255 caracteres de las listas inline.
     */
    private function addDropdownByRange(Worksheet $sheet, string $targetRange, string $sourceRange): void
    {
        // Parsear el rango destino: "C2:C500"
        [$start, $end] = explode(':', $targetRange);
        preg_match('/^([A-Z]+)(\d+)$/', $start, $mStart);
        preg_match('/^([A-Z]+)(\d+)$/', $end,   $mEnd);

        $col     = $mStart[1];
        $rowFrom = (int) $mStart[2];
        $rowTo   = (int) $mEnd[2];

        for ($row = $rowFrom; $row <= $rowTo; $row++) {
            $validation = new DataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Valor inválido');
            $validation->setError('Elegí un valor de la lista desplegable.');
            $validation->setFormula1($sourceRange); // ← referencia, no literal

            $sheet->getCell("{$col}{$row}")->setDataValidation($validation);
        }
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

        while (strlen($literal) > 255 && count($options) > 1) {
            array_pop($options);
            $literal = '"' . implode(',', $options) . '"';
        }

        if (strlen($literal) > 255) return;

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
        $carreras   = Carreras::activasConPensum()->sortBy('nombre')->values();

        $max = max($tiposDoc->count(), $regionales->count(), $carreras->count());

        $rows = [
            [
                'TIPOS DE DOCUMENTO', '', 'REGIONALES / SEDES', '', 'CARRERAS', '',
                '',
                'LISTA TIPO DOC', 'LISTA REGIONAL', 'LISTA CARRERA',
            ],
        ];

        for ($i = 0; $i < $max; $i++) {
            $rows[] = [
                $tiposDoc[$i]->tipo_documento_id ?? '',
                $tiposDoc[$i]->nombre ?? '',
                $regionales[$i]->regional_activo_id ?? '',
                $regionales[$i]->sede ?? '',
                $carreras[$i]->carrera_id ?? '',
                $carreras[$i]->nombre ?? '',
                '',
                isset($tiposDoc[$i]) ? "{$tiposDoc[$i]->tipo_documento_id} - {$tiposDoc[$i]->nombre}" : '',
                isset($regionales[$i]) ? "{$regionales[$i]->regional_activo_id} - {$regionales[$i]->sede}" : '',
                isset($carreras[$i]) ? "{$carreras[$i]->carrera_id} - {$carreras[$i]->nombre}" : '',
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Ocultar columnas técnicas (separador + listas)
                foreach (['G', 'H', 'I', 'J'] as $col) {
                    $sheet->getColumnDimension($col)->setVisible(false);
                }
            },
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 10, 'B' => 30, 'C' => 12, 'D' => 30, 'E' => 10, 'F' => 40, 'G' => 3, 'H' => 40, 'I' => 40, 'J' => 50,];
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