<?php

namespace App\Imports;

use App\Actions\AdminAcademic\CreateMassiveStudentAction;
use App\Models\Users\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Mail\NewAccountStudentDirectMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class StudentsImport implements ToCollection, WithHeadingRow, WithChunkReading, SkipsEmptyRows
{
    public array $creados = [];
    public array $errores = [];

    public function __construct(
        protected User $admin,
        protected CreateMassiveStudentAction $creator,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $fila = $index + 2;

            $email = strtolower(trim((string) ($row['email'] ?? '')));

            // Fila vacía → skip
            if ($email === '' && empty($row['name'] ?? null)) {
                continue;
            }

            // Fila de ejemplo residual → skip
            if (str_ends_with($email, '@example.com')) {
                continue;
            }

            $data = [
                'name'                => trim((string) ($row['name'] ?? '')),
                'email'               => $email,
                'id_tipo_documento'   => (int) ($row['id_tipo_documento'] ?? 0),
                'documento_identidad' => trim((string) ($row['documento_identidad'] ?? '')),
                'fecha_nacimiento'    => $this->parseFecha($row['fecha_nacimiento'] ?? null),
                'genero'              => strtoupper(trim((string) ($row['genero'] ?? ''))),
                'estado_civil'        => $row['estado_civil'] ?? null,
                'celular'             => $row['celular'] ?? null,
                'direccion'           => $row['direccion'] ?? null,
                'id_regional_activo'  => (int) ($row['id_regional_activo'] ?? 0),
                'id_carrera'          => (int) ($row['id_carrera'] ?? 0),
            ];

            try {
                $validator = Validator::make(
                    $data,
                    \App\Rules\MassiveStudentRowRules::rules($data, $fila)
                );

                if ($validator->fails()) {
                    $this->errores[] = [
                        'fila'    => $fila,
                        'email'   => $data['email'] ?: '—',
                        'errores' => $validator->errors()->all(),
                    ];
                    continue;
                }

                [$user, $alumno] = $this->creator->execute($data, $this->admin);

                // envio de correos en cola.
                //Mail::to($user->email)->send(new NewAccountStudentDirectMail($alumno));
                Mail::to($user->email)->queue(new NewAccountStudentDirectMail($alumno));

                Log::info('Usuario nuevo creado de forma masiva por el admin-academico: ' . $this->admin->email . ' en la base de datos. '.' alumno: ' . $alumno->codigo_estudiante);

                $this->creados[] = [
                    'fila'   => $fila,
                    'nombre' => $user->name,
                    'email'  => $user->email,
                    'codigo' => $alumno->codigo_estudiante,
                ];
            } catch (\Throwable $e) {
                report($e);
                $this->errores[] = [
                    'fila'    => $fila,
                    'email'   => $data['email'] ?: '—',
                    'errores' => [$e->getMessage()],
                ];
            }
        }
    }

    private function parseFecha(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        // 1. Si ya es un objeto DateTime / Carbon, formateamos
        if ($valor instanceof \DateTimeInterface) {
            return $valor->format('Y-m-d');
        }

        // 2. Si es un número serial de Excel
        if (is_numeric($valor) && $valor > 1000 && $valor < 100000) {
            try {
                $fecha = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $valor);
                return $fecha->format('Y-m-d');
            } catch (\Throwable $e) {
                // seguimos con el resto
            }
        }

        $valor = trim((string) $valor);

        // 3. Formatos explícitos que puede devolver Excel
        $formatos = [
            'Y-m-d',       // 2000-10-10
            'd/m/Y',       // 10/10/2000
            'm/d/Y',       // 10/10/2000 (US)
            'd-m-Y',       // 10-10-2000
            'Y/m/d',       // 2000/10/10
        ];

        foreach ($formatos as $formato) {
            $dt = \DateTime::createFromFormat($formato, $valor);
            if ($dt && $dt->format($formato) === $valor) {
                return $dt->format('Y-m-d');
            }
        }

        // 4. Fallback a strtotime (último recurso)
        $ts = strtotime($valor);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        // 5. Devolvemos el valor crudo para que el validador lo rechace con un mensaje claro
        return $valor;
    }

    public function chunkSize(): int
    {
        return 50;
    }
}