<?php

namespace App\Http\Controllers\AdminAcademico;

use App\Http\Controllers\Controller;
use App\Models\Academico\ReglaBeneficio;
use App\Rules\UpdateReglaBeneficioRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ReglaBeneficioController extends Controller
{
    public function index(): View
    {
        $reglas = ReglaBeneficio::query()
            ->with('creadoPor')
            ->orderBy('tipo_beneficio')
            ->orderByDesc('vigente_desde')
            ->get();

        return view('adminacademico.rule_becados', compact('reglas'));
    }

    public function update(UpdateReglaBeneficioRules $request, ReglaBeneficio $regla): RedirectResponse
    {
        try {
            $regla->update($request->validated());

            return redirect()
                ->route('admin-academico.reglas-beneficio.index')
                ->with('success', 'Regla de beneficio actualizada correctamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo actualizar: ' . $e->getMessage());
        }
    }
}