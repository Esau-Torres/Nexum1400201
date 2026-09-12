<?php

    namespace App\Http\Controllers\Estudiante;

    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\DB;

    class registroController extends Controller
    {
        // constructor cosulta de datos en para el formulario de registro
        public function index()
        {
            $tipo_documento_identidad = DB::table('tipo_documento_identidad')->get();
            $carreras = DB::table('carreras')->get();
            return view('auth.register', compact('tipo_documento_identidad', 'carreras'));
        }
    }