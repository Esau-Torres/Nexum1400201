<?php

namespace App\Actions\Fortify;

use App\Models\Users\User;
use App\Rules\ValidarDocumentoIdentidad;
use App\Actions\User\CreateUserAccountAction;
use App\Actions\User\AssignUserRoleAction;
use App\Actions\Student\CreateStudentProfileAction;
use App\Actions\Student\UploadStudentDocumentsAction;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    public function __construct(
        protected CreateUserAccountAction $createUserAction,
        protected AssignUserRoleAction $assignRoleAction,
        protected CreateStudentProfileAction $createStudentAction,
        protected UploadStudentDocumentsAction $uploadDocsAction
    ) {}

    public function create(array $input): User
    {
        Validator::make($input, [
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'id_tipo_documento'   => ['required', 'exists:tipo_documento_identidad,tipo_documento_id'],
            'documento_identidad' => ['required', 'string', 'max:30', 
            new ValidarDocumentoIdentidad($input['id_tipo_documento'] ?? null),
            Rule::unique('users', 'documento_identidad')->where(function ($query) use ($input) {
            return $query->where('id_tipo_documento', $input['id_tipo_documento'] ?? null);}),],
            'fecha_nacimiento'    => ['required', 'date', 'before:-17 years'],
            'genero'              => ['required', 'in:M,F'],
            'id_carrera'          => ['required', 'exists:carreras,carrera_id'],
            'titulo_bachillerato' => ['required', 'file', 'mimes:pdf,jpg,png', 'max:2048'],
            'partida_nacimiento'  => ['required', 'file', 'mimes:pdf,jpg,png', 'max:2048'],
            'fotografia_personal' => ['required', 'file', 'mimes:jpg,png', 'max:2048'],
            'constancia_paes'     => ['nullable', 'file', 'mimes:pdf,jpg,png', 'max:2048'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            try {
                $user = $this->createUserAction->execute($input);
                $this->assignRoleAction->execute($user, 8); // 8 = ESTUDIANTE
                $alumno = $this->createStudentAction->execute($user, $input['id_carrera']);
                $this->uploadDocsAction->execute($alumno, request());

                session()->flash('info', 'Solicitud recibida. Tu expediente está en revisión por Administración Académica.');
                
                return $user;
            } catch (\Exception $e) {
                throw ValidationException::withMessages([
                    'error_general' => 'Ocurrió un error de integridad al procesar el expediente. Contacte a soporte técnico.'
                ]);
            }
        });
    }
}
