<?php

namespace App\Http\Controllers\Directivo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Adfinanciero\Pago;

class DirectivoController extends Controller
{

    //Funcion para la dashboard de financiera
    public function index(Request $request)
    {
        // 1. OBTENCIÓN DE DATOS PARA LOS SELECTS
        $ciclos = DB::table('ciclo_lectivo')->orderBy('ciclo_lectivo_id', 'desc')->get();
        $todasLasFacultades = DB::table('facultades')->orderBy('nombre')->get();
        $todasLasCarreras = DB::table('carreras')->orderBy('nombre')->get();

        $cicloActivo = $ciclos->whereIn('estado', ['ACTIVO', 'MATRICULA_ABIERTA', 'CONFIGURACION'])->first();
        $cicloId = $request->input('ciclo_id', $cicloActivo->ciclo_lectivo_id ?? null);
        $mesSeleccionado = $request->input('mes');

        // Nuevos filtros capturados de la URL
        $facultadSeleccionada = $request->input('facultad_id');
        $carreraSeleccionada = $request->input('carrera_id');

        $cicloSeleccionadoObj = $ciclos->firstWhere('ciclo_lectivo_id', $cicloId);
        $anioSeleccionado = $cicloSeleccionadoObj ? $cicloSeleccionadoObj->anio : date('Y');

        // =========================================================
        // 2. CONSTRUCCIÓN DE LA CONSULTA BASE CON FACULTADES
        // =========================================================
        $queryBase = DB::table('detalle_pago')
            ->join('pago', 'detalle_pago.id_pago', '=', 'pago.pago_id')
            ->join('concepto_pago', 'detalle_pago.id_concepto', '=', 'concepto_pago.concepto_pago_id')
            ->leftJoin('cargo_estudiante', 'detalle_pago.id_cargo', '=', 'cargo_estudiante.cargo_id')
            ->join('alumnos', 'pago.id_alumno', '=', 'alumnos.alumno_id')
            ->leftJoin('carreras', 'alumnos.id_carrera', '=', 'carreras.carrera_id')
            ->leftJoin('facultades', 'carreras.id_facultad', '=', 'facultades.facultad_id')

            ->where(function($q) use ($cicloId, $mesSeleccionado, $anioSeleccionado, $cicloSeleccionadoObj) {
                $q->where('cargo_estudiante.id_ciclo_lectivo', $cicloId);

                $q->orWhere(function($subQ) use ($mesSeleccionado, $anioSeleccionado, $cicloSeleccionadoObj) {
                    $subQ->whereNull('detalle_pago.id_cargo');

                    if ($mesSeleccionado) {
                        $subQ->whereMonth('pago.fecha_pago', $mesSeleccionado);
                    } else {
                        if ($cicloSeleccionadoObj) {
                            $nombreCiclo = strtoupper($cicloSeleccionadoObj->nombre_ciclo);
                            $esCiclo2 = str_contains($nombreCiclo, '02') || str_contains($nombreCiclo, ' 2') || str_contains($nombreCiclo, 'II');

                            $mesesPermitidos = $esCiclo2 ? [7, 8, 9, 10, 11, 12] : [1, 2, 3, 4, 5, 6];

                            $subQ->whereIn(DB::raw('EXTRACT(MONTH FROM pago.fecha_pago)'), $mesesPermitidos)
                                 ->whereYear('pago.fecha_pago', $anioSeleccionado);
                        }
                    }
                });
            });

        if ($anioSeleccionado) {
            $queryBase->whereYear('pago.fecha_pago', $anioSeleccionado);
        }

        if ($mesSeleccionado) {
            $queryBase->where(function($q) use ($mesSeleccionado) {
                 $q->whereMonth('pago.fecha_pago', $mesSeleccionado)
                   ->orWhereNull('detalle_pago.id_cargo');
            });
        }

        // =========================================================
        // NUEVO: APLICAR FILTROS DE FACULTAD Y CARRERA
        // =========================================================
        if ($facultadSeleccionada) {
            $queryBase->where('facultades.facultad_id', $facultadSeleccionada);
        }

        if ($carreraSeleccionada) {
            $queryBase->where('carreras.carrera_id', $carreraSeleccionada);
        }

        // =========================================================
        // 3. CÁLCULO DE KPIs
        // =========================================================
        $totalIngresos = (clone $queryBase)->sum('detalle_pago.subtotal');
        $totalTransacciones = (clone $queryBase)->distinct('pago.pago_id')->count('pago.pago_id');
        $ingresoPromedio = $totalTransacciones > 0 ? ($totalIngresos / $totalTransacciones) : 0;

        // =========================================================
        // NUEVO: CÁLCULO DE COMPARATIVAS (vs Ciclo Anterior)
        // =========================================================
        $cicloAnterior = DB::table('ciclo_lectivo')
            ->where('ciclo_lectivo_id', '<', $cicloId)
            ->orderBy('ciclo_lectivo_id', 'desc')
            ->first();

        $ingresosAnterior = 0;
        $transaccionesAnterior = 0;
        $promedioAnterior = 0;

        if ($cicloAnterior) {
            // Consulta rápida para obtener los totales del ciclo anterior
            $queryAnterior = DB::table('detalle_pago')
                ->join('pago', 'detalle_pago.id_pago', '=', 'pago.pago_id')
                ->leftJoin('cargo_estudiante', 'detalle_pago.id_cargo', '=', 'cargo_estudiante.cargo_id')
                ->where('cargo_estudiante.id_ciclo_lectivo', $cicloAnterior->ciclo_lectivo_id)
                ->whereYear('pago.fecha_pago', $cicloAnterior->anio);

            $ingresosAnterior = $queryAnterior->sum('detalle_pago.subtotal');
            $transaccionesAnterior = $queryAnterior->count('*');
            $promedioAnterior = $transaccionesAnterior > 0 ? ($ingresosAnterior / $transaccionesAnterior) : 0;
        }

        // Diferencias monetarias y absolutas
        $difIngresos = $totalIngresos - $ingresosAnterior;
        $difTransacciones = $totalTransacciones - $transaccionesAnterior;
        $difPromedio = $ingresoPromedio - $promedioAnterior;

        // Diferencias porcentuales
        $porcIngresos = $ingresosAnterior > 0 ? ($difIngresos / $ingresosAnterior) * 100 : ($totalIngresos > 0 ? 100 : 0);
        $porcTransacciones = $transaccionesAnterior > 0 ? ($difTransacciones / $transaccionesAnterior) * 100 : ($totalTransacciones > 0 ? 100 : 0);
        $porcPromedio = $promedioAnterior > 0 ? ($difPromedio / $promedioAnterior) * 100 : ($ingresoPromedio > 0 ? 100 : 0);

        // =========================================================
        // 4. ANÁLISIS POR CARRERA Y FACULTAD (CON ABREVIATURAS)
        // =========================================================
        $ingresosPorCarrera = (clone $queryBase)
            ->select(DB::raw("COALESCE(carreras.nombre, 'Otras Transacciones') as carrera"), DB::raw('SUM(detalle_pago.subtotal) as total'))
            ->groupBy('carreras.nombre')
            ->orderByDesc('total')
            ->get();

        // Arreglo de reemplazos para acortar los nombres
        $abreviaturas = [
            // 1. Abreviaturas de grado académico
            'Licenciatura en ' => 'Lic. ',
            'Técnico en '      => 'Téc. ',
            'Maestría en '     => 'Mtr. ',
            'Profesorado en '  => 'Prof. ',
            'Profesorado y Licenciatura en ' => 'Prof. y Lic. ',

            // 2. Recortes de las extensiones largas (basado en tu base de datos)
            ' para tercer ciclo de educacion basica y educacion media' => '', // Lo borramos para ahorrar espacio
            'Especialidad Idioma Inglés' => 'Inglés',
            'Especialidad Lenguaje y Literatura' => 'Lenguaje y Lit.',
            'Derecho Procesal, Civil y Mercantil' => 'Derecho Proc., Civil y Merc.'
        ];

        // Aplicamos el reemplazo a cada carrera
        foreach ($ingresosPorCarrera as $item) {
            $item->carrera = str_replace(array_keys($abreviaturas), array_values($abreviaturas), $item->carrera);
        }

        $ingresosPorFacultad = (clone $queryBase)
            ->select(DB::raw("COALESCE(facultades.nombre, 'Otras Transacciones') as facultad"), DB::raw('SUM(detalle_pago.subtotal) as total'))
            ->groupBy('facultades.nombre')
            ->orderByDesc('total')
            ->get();

        // NUEVO: Abreviar "Facultad de" a "Fac." para que el gráfico no se encoja
        foreach ($ingresosPorFacultad as $item) {
            $item->facultad = str_replace('Facultad de ', 'Fac. ', $item->facultad);
        }

        // =========================================================
        // 5. PREPARACIÓN PARA GRÁFICOS (JSON)
        // =========================================================
        $evolucion = (clone $queryBase)
            ->select(
                DB::raw("TO_CHAR(pago.fecha_pago, 'YYYY-MM') as mes_anio"),
                DB::raw('SUM(detalle_pago.subtotal) as total')
            )
            ->groupBy('mes_anio')
            ->orderBy('mes_anio')
            ->get();

        $labelsEvolucion = $evolucion->pluck('mes_anio')->toJson();
        $datosEvolucion = $evolucion->pluck('total')->toJson();

        $labelsCarrera = $ingresosPorCarrera->pluck('carrera')->toJson();
        $datosCarrera = $ingresosPorCarrera->pluck('total')->toJson();

        $labelsFacultad = $ingresosPorFacultad->pluck('facultad')->toJson();
        $datosFacultad = $ingresosPorFacultad->pluck('total')->toJson();

        return view('directivo.index', compact(
            'ciclos', 'cicloId', 'mesSeleccionado', 'anioSeleccionado',
            'todasLasFacultades', 'todasLasCarreras',
            'totalIngresos', 'totalTransacciones', 'ingresoPromedio',
            'difIngresos', 'porcIngresos',
            'difTransacciones', 'porcTransacciones',
            'difPromedio', 'porcPromedio',
            'ingresosPorCarrera', 'ingresosPorFacultad',
            'labelsEvolucion', 'datosEvolucion',
            'labelsCarrera', 'datosCarrera',
            'labelsFacultad', 'datosFacultad'
        ));
    }

    //Funcion para la dashboard de matricula
    public function matricula(Request $request)
    {
        // ---------------------------------------------------------
        // 1. OBTENCIÓN DE FILTROS
        // ---------------------------------------------------------
        $cicloId = $request->input('ciclo_id');

        // Si no selecciona ciclo, tomamos el activo por defecto
        if (!$cicloId) {
            $cicloId = DB::table('ciclo_lectivo')->where('estado', 'ACTIVO')->value('ciclo_lectivo_id')
                       ?? DB::table('ciclo_lectivo')->max('ciclo_lectivo_id');
        }

        $mesSeleccionado = $request->input('mes');
        $facultadId = $request->input('facultad_id');
        $carreraId = $request->input('carrera_id');

        // Catálogos
        $ciclos = DB::table('ciclo_lectivo')->orderByDesc('ciclo_lectivo_id')->get();
        $todasLasFacultades = DB::table('facultades')->orderBy('nombre')->get();
        $todasLasCarreras = DB::table('carreras')->orderBy('nombre')->get();

        // ---------------------------------------------------------
        // 2. CONSULTA BASE DE MATRÍCULA (CICLO SELECCIONADO)
        // ---------------------------------------------------------
        $queryBase = DB::table('inscripciones')
            ->join('alumnos', 'inscripciones.id_alumno', '=', 'alumnos.alumno_id')
            ->leftJoin('carreras', 'alumnos.id_carrera', '=', 'carreras.carrera_id')
            ->leftJoin('facultades', 'carreras.id_facultad', '=', 'facultades.facultad_id')
            ->where('inscripciones.id_ciclo_lectivo', $cicloId);

        if ($facultadId) {
            $queryBase->where('carreras.id_facultad', $facultadId);
        }
        if ($carreraId) {
            $queryBase->where('alumnos.id_carrera', $carreraId);
        }

        // Total Matriculados Únicos
        $totalMatriculados = (clone $queryBase)->distinct('inscripciones.id_alumno')->count('inscripciones.id_alumno');

        // ---------------------------------------------------------
        // 3. CÁLCULO DE ANÁLISIS DE CONTINUIDAD (VS CICLO ANTERIOR)
        // ---------------------------------------------------------
        $cicloAnterior = DB::table('ciclo_lectivo')
            ->where('ciclo_lectivo_id', '<', $cicloId)
            ->orderByDesc('ciclo_lectivo_id')
            ->first();

        $matriculaAnterior = 0;
        $continuidad = 0;
        $noReinscritos = 0;

        if ($cicloAnterior) {
            $queryAnterior = DB::table('inscripciones')
                ->join('alumnos', 'inscripciones.id_alumno', '=', 'alumnos.alumno_id')
                ->leftJoin('carreras', 'alumnos.id_carrera', '=', 'carreras.carrera_id')
                ->where('inscripciones.id_ciclo_lectivo', $cicloAnterior->ciclo_lectivo_id);

            if ($facultadId) $queryAnterior->where('carreras.id_facultad', $facultadId);
            if ($carreraId) $queryAnterior->where('alumnos.id_carrera', $carreraId);

            $matriculaAnterior = (clone $queryAnterior)->distinct('inscripciones.id_alumno')->count('inscripciones.id_alumno');

            // Cruce para detectar quiénes siguieron y quiénes no
            $alumnosAnterior = (clone $queryAnterior)->pluck('inscripciones.id_alumno')->toArray();
            $alumnosActuales = (clone $queryBase)->pluck('inscripciones.id_alumno')->toArray();

            $continuidad = count(array_intersect($alumnosAnterior, $alumnosActuales));
            $noReinscritos = count(array_diff($alumnosAnterior, $alumnosActuales));
        }

        // Tasas e Indicadores
        $tasaRetencion = $matriculaAnterior > 0 ? ($continuidad / $matriculaAnterior) * 100 : 0;
        $tasaNoReinscripcion = $matriculaAnterior > 0 ? ($noReinscritos / $matriculaAnterior) * 100 : 0;
        $variacionMatricula = $totalMatriculados - $matriculaAnterior;
        $porcVariacion = $matriculaAnterior > 0 ? ($variacionMatricula / $matriculaAnterior) * 100 : 0;

        // Estudiantes de Nuevo Ingreso (Primera inscripción histórica coincide con el ciclo actual)
        $nuevoIngreso = (clone $queryBase)
            ->whereNotExists(function ($query) use ($cicloId) {
                $query->select(DB::raw(1))
                    ->from('inscripciones as i2')
                    ->whereColumn('i2.id_alumno', 'inscripciones.id_alumno')
                    ->where('i2.id_ciclo_lectivo', '<', $cicloId);
            })
            ->distinct('inscripciones.id_alumno')
            ->count('inscripciones.id_alumno');

        // ---------------------------------------------------------
        // 4. AGRUPACIONES POR CARRERA Y FACULTAD (CON ABREVIATURAS)
        // ---------------------------------------------------------
        $abreviaturasCarrera = [
            'Licenciatura en ' => 'Lic. ',
            'Licenciatura '    => 'Lic. ',
            'Técnico en '      => 'Téc. ',
            'Ingeniería en '   => 'Ing. ',
            'Profesorado en '  => 'Prof. ',
            'Maestría en '     => 'Mtr. '
        ];

        $matriculaPorCarrera = (clone $queryBase)
            ->select(DB::raw("COALESCE(carreras.nombre, 'Sin Carrera') as carrera"), DB::raw('COUNT(DISTINCT inscripciones.id_alumno) as total'))
            ->groupBy('carreras.nombre')
            ->orderByDesc('total')
            ->get();

        foreach ($matriculaPorCarrera as $item) {
            $item->carrera = str_replace(array_keys($abreviaturasCarrera), array_values($abreviaturasCarrera), $item->carrera);
        }

        $matriculaPorFacultad = (clone $queryBase)
            ->select(DB::raw("COALESCE(facultades.nombre, 'Sin Facultad') as facultad"), DB::raw('COUNT(DISTINCT inscripciones.id_alumno) as total'))
            ->groupBy('facultades.nombre')
            ->orderByDesc('total')
            ->get();

        foreach ($matriculaPorFacultad as $item) {
            $item->facultad = str_replace('Facultad de ', 'Fac. ', $item->facultad);
        }

        // ---------------------------------------------------------
        // 5. EVOLUCIÓN HISTÓRICA POR CICLO
        // ---------------------------------------------------------
        $evolucionHistorica = DB::table('inscripciones')
            ->join('ciclo_lectivo', 'inscripciones.id_ciclo_lectivo', '=', 'ciclo_lectivo.ciclo_lectivo_id')
            ->select('ciclo_lectivo.nombre_ciclo', 'ciclo_lectivo.anio', DB::raw('COUNT(DISTINCT id_alumno) as total'))
            ->groupBy('ciclo_lectivo.ciclo_lectivo_id', 'ciclo_lectivo.nombre_ciclo', 'ciclo_lectivo.anio')
            ->orderBy('ciclo_lectivo.ciclo_lectivo_id')
            ->take(6)
            ->get();

        $labelsEvolucion = json_encode($evolucionHistorica->map(fn($e) => $e->nombre_ciclo . ' ' . $e->anio));
        $datosEvolucion = json_encode($evolucionHistorica->pluck('total'));

        $labelsCarrera = json_encode($matriculaPorCarrera->pluck('carrera'));
        $datosCarrera = json_encode($matriculaPorCarrera->pluck('total'));

        $labelsFacultad = json_encode($matriculaPorFacultad->pluck('facultad'));
        $datosFacultad = json_encode($matriculaPorFacultad->pluck('total'));

        return view('directivo.matricula.index', compact(
            'ciclos', 'cicloId', 'mesSeleccionado', 'todasLasFacultades', 'todasLasCarreras',
            'totalMatriculados', 'nuevoIngreso', 'continuidad', 'noReinscritos',
            'tasaRetencion', 'tasaNoReinscripcion', 'variacionMatricula', 'porcVariacion',
            'matriculaPorCarrera', 'matriculaPorFacultad',
            'labelsEvolucion', 'datosEvolucion', 'labelsCarrera', 'datosCarrera', 'labelsFacultad', 'datosFacultad'
        ));
    }

}
