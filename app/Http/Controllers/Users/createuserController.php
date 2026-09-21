<?php
namespace App\Http\Controllers\Users;

    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\DB;
    use App\Models\Users\Role;
    use App\Actions\User\AssignUserRoleAction;
    use App\Actions\Teacher\CreateDocenteProfileAction;
    use App\Actions\User\CreateUserAccountAction;
    use App\Rules\StoreUserRule;
    use App\Mail\NewAccountCredentialsMail;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Support\Facades\Mail;
    use Illuminate\Support\Str;
    use Illuminate\View\View;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Log;

class CreateUserController extends Controller
{
    public function index()
    {
        $tiposDocumento = DB::table('tipo_documento_identidad')->get();
        $regionales     = DB::table('regional_activo')->get();

        // Roles activos, excluyendo ESTUDIANTE
        $roles = Role::query()
            ->where('estado', true)
            ->where('nombre', '!=', Role::ESTUDIANTE)
            ->orderBy('rol_id')
            ->get();

        return view('superadmin.createuser', compact(
            'tiposDocumento',
            'regionales',
            'roles'
        ));
    }

    public function store(
        StoreUserRule $request,
        CreateUserAccountAction $createUserAction,
        AssignUserRoleAction $assignRoleAction,
        CreateDocenteProfileAction $createDocenteAction
    ): RedirectResponse {

        $validated = $request->validated();
        // Contraseña provisional para el primer acceso
        $plainPassword = Str::password(12, true, true, false, false);

        $user = null;
        $nombresRoles = [];

        DB::beginTransaction();
        try {
            // 1. Ejecución del Action global reutilizable
            $user = $createUserAction->execute($validated);

            // 2. Establecer la contraseña temporal generada
            $user->password = Hash::make($plainPassword);
            $user->save();

            // 3. Asignación de roles en la tabla pivote usuario_rol
            $rolesModelos = Role::whereIn('rol_id', $validated['roles'])->get();
            $nombresRoles = $rolesModelos->pluck('nombre')->toArray();

            foreach ($validated['roles'] as $roleId) {
                $assignRoleAction->execute($user, (int) $roleId, $request->user()->id);
            }

            // 4. Lógica de Docente (si el rol DOCENTE fue asignado)
            if (in_array(Role::DOCENTE, $nombresRoles, true)) {
                $createDocenteAction->execute($user);
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Fallo de transacción al crear usuario en NEXUM: ' . $e->getMessage(), [
                'exception' => $e,
                'input'     => $request->except(['password']),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Ocurrió un error transaccional al registrar la cuenta en NEXUM. Verifique los logs: ' . $e->getMessage());
        }

        // 5. El correo se envía SI Y SOLO SI la base de datos confirmó el COMMIT
        try {
            Mail::to($user->email)->send(new NewAccountCredentialsMail($user, $plainPassword, $nombresRoles));
        } catch (\Throwable $mailEx) {
            Log::warning('Usuario creado pero falló el envío de credenciales por correo: ' . $mailEx->getMessage());
            return redirect()
                ->route('superadmin.createuser')
                ->with('success', "Usuario {$user->name} creado, pero el correo no pudo enviarse. Notifique manualmente.");
        }

        Log::info('Usuario nuevo creado por el super usuario en la base de datos. '.' usuario: ' .$user->email );
        return redirect()
            ->route('superadmin.createuser')
            ->with('success', "Usuario {$user->name} creado exitosamente. Credenciales despachadas a {$user->email}.");
        
    }

}