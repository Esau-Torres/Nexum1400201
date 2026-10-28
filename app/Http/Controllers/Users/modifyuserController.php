<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Rules\UpdateUserPersonalDataRule;
use App\Actions\User\UpdateUserPersonalDataAction;
use App\Models\Users\User;
use App\Models\Users\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;
use Illuminate\Validation\ValidationException;


class ModifyUserController extends Controller
{
    public function index(): View
    {
        $tiposDocumento = DB::table('tipo_documento_identidad')->get();
        $regionales     = DB::table('regional_activo')->get();

        $roles = Role::query()
            ->where('estado', true)
            ->where('nombre', '!=', Role::ESTUDIANTE)
            ->orderBy('rol_id')
            ->get();

        // Regla 3: Obtener usuarios excluyendo a los que posean el rol ESTUDIANTE
       $usuarios = User::query()
            ->with(['roles', 'regionalactivo', 'tipodocumentoidentidad', 'docente'])
            ->whereDoesntHave('roles', function ($query) {
                $query->where('nombre', Role::ESTUDIANTE);
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('superadmin.panel-administrativo', compact(
            'usuarios',
            'tiposDocumento',
            'regionales',
            'roles'
        ));
    }

    public function update(UpdateUserPersonalDataRule $request, int $id, UpdateUserPersonalDataAction $action): RedirectResponse
    {
        try{
            $action->execute($id, $request->validated());
            return redirect()->route('superadmin.panel-administrativo')->with('success', 'Información actualizada correctamente');

        } catch (ValidationException $e) {
            // Obtener todos los errores formateados
            $errors = $e->validator->errors()->all();
            
            // Si hay múltiples errores, mostrar el primero como principal 
            $mainError = $errors[0] ?? 'Error de validación';
            
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->validator)
                ->with('error', $mainError)
                ->with('validation_errors', $errors); // Pasar todos los errores
                
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}