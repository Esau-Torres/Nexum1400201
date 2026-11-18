<?php

    namespace App\Http\Controllers\Estudiante;

    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\DB;
    use App\Models\Estudiante\Carreras;
    use App\Models\Users\User;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;

    class RegistroController extends Controller
    {
        // constructor cosulta de datos en para el formulario de registro de usuario
        public function index()
        {
            $tipo_documento_identidad = DB::table('tipo_documento_identidad')->get();
            
            $carreras = Carreras::activasConPensum();

            return view('auth.register', compact('tipo_documento_identidad', 'carreras'));
        }

        public function checkEmail(Request $request): JsonResponse
        {
            $request->validate([
                'email' => ['required', 'string', 'email:rfc'],
            ]);

            $existe = User::where(
                'email', strtolower(trim($request->email))
            )->exists();

            return response()->json(['available' => !$existe]);
        }
    }
