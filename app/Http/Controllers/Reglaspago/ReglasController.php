<?php

namespace App\Http\Controllers\Reglaspago;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adfinanciero\ReglaPago;
use App\Models\Adfinanciero\ConceptoPago; // Importante para cargar los aranceles

class ReglasController extends Controller
{
    public function index()
{
    $reglas = ReglaPago::with('conceptoRecargo')->get();

    // Traemos los conceptos para llenar los select de los modales
    $conceptos = \App\Models\Adfinanciero\ConceptoPago::all();

    return view('adfinanciero.reglas.index', compact('reglas', 'conceptos'));
}

    // Muestra el formulario de creación
    public function create()
    {
        $conceptos = ConceptoPago::all(); // Traemos los aranceles para el select
        return view('adfinanciero.reglas.create', compact('conceptos'));
    }

    // Guarda la nueva regla en la base de datos
    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string|max:100',
            'dia_inicio_ordinario' => 'required|integer|min:1|max:31',
            'dia_fin_ordinario' => 'required|integer|min:1|max:31',
            'dia_inicio_extra' => 'nullable|integer|min:1|max:31',
            'dia_fin_extra' => 'nullable|integer|min:1|max:31',
            'id_concepto_recargo' => 'nullable|exists:concepto_pago,concepto_pago_id',
            'estado' => 'required|in:ACTIVO,INACTIVO'
        ]);

        ReglaPago::create($request->all());
        return redirect()->route('reglas.index')->with('success', 'Regla de cobro creada exitosamente.');
    }

    // Muestra el formulario de edición
    public function edit($id)
    {
        $regla = ReglaPago::findOrFail($id);
        $conceptos = ConceptoPago::all();
        return view('adfinanciero.reglas.edit', compact('regla', 'conceptos'));
    }

    // Actualiza la regla existente
    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo' => 'required|string|max:100',
            'dia_inicio_ordinario' => 'required|integer|min:1|max:31',
            'dia_fin_ordinario' => 'required|integer|min:1|max:31',
            'dia_inicio_extra' => 'nullable|integer|min:1|max:31',
            'dia_fin_extra' => 'nullable|integer|min:1|max:31',
            'id_concepto_recargo' => 'nullable|exists:concepto_pago,concepto_pago_id',
            'estado' => 'required|in:ACTIVO,INACTIVO'
        ]);

        $regla = ReglaPago::findOrFail($id);
        $regla->update($request->all());
        return redirect()->route('reglas.index')->with('success', 'Regla de cobro actualizada exitosamente.');
    }
}
