<?php
namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use App\Models\Users\Role;
use App\Models\Users\User;

class CreateRolController extends Controller
{
    public function index(): View
        {
            // 1. Roles del catálogo institucional UMA
            $roles = Role::query()
                ->orderBy('rol_id', 'asc')
                ->get();

            // 2. Usuarios del sistema excluyendo estudiantes, con carga ávida (Eager Loading)
            $usuarios = User::query()
                ->with(['roles', 'regionalactivo'])
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('nombre', Role::ESTUDIANTE);
                })
                ->orderBy('id', 'desc')
                ->get();

            return view('superadmin.create-rol', compact('roles', 'usuarios'));
        }
}