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
            // 3. INDICADORES: CICLO ACTIVO VS CICLO CERRADO
            // ---------------------------------------------------------

            // A) Estudiantes de Nuevo Ingreso (Tarjeta 2) - Se calcula para TODOS los ciclos
            $nuevoIngreso = (clone $queryBase)
                ->whereNotExists(function ($query) use ($cicloId) {
                    $query->select(DB::raw(1))
                        ->from('inscripciones as i2')
                        ->whereColumn('i2.id_alumno', 'inscripciones.id_alumno')
                        ->where('i2.id_ciclo_lectivo', '<', $cicloId);
                })
                ->distinct('inscripciones.id_alumno')
                ->count('inscripciones.id_alumno');

            // B) Alumnos que continúan / Antiguo Ingreso (Tarjeta 3) - Se calcula para TODOS los ciclos
            $alumnosRetenidos = $totalMatriculados - $nuevoIngreso;

            // C) Variación vs ciclo anterior (Tarjeta 1) - Se calcula para TODOS los ciclos
            $varMatricula = '0.0%';
            $cicloAnterior = DB::table('ciclo_lectivo')
                ->where('ciclo_lectivo_id', '<', $cicloId)
                ->orderByDesc('ciclo_lectivo_id')
                ->first();

            if ($cicloAnterior) {
                $queryAnterior = DB::table('inscripciones')
                    ->join('alumnos', 'inscripciones.id_alumno', '=', 'alumnos.alumno_id')
                    ->leftJoin('carreras', 'alumnos.id_carrera', '=', 'carreras.carrera_id')
                    ->where('inscripciones.id_ciclo_lectivo', $cicloAnterior->ciclo_lectivo_id);

                if ($facultadId) $queryAnterior->where('carreras.id_facultad', $facultadId);
                if ($carreraId) $queryAnterior->where('alumnos.id_carrera', $carreraId);

                $matriculaAnterior = (clone $queryAnterior)->distinct('inscripciones.id_alumno')->count('inscripciones.id_alumno');

                if ($matriculaAnterior > 0) {
                    $porcentaje = (($totalMatriculados - $matriculaAnterior) / $matriculaAnterior) * 100;
                    $signo = $porcentaje > 0 ? '+' : '';
                    $varMatricula = $signo . number_format($porcentaje, 1) . '%';
                }
            }

            // D) Estudiantes Activos e Inactivos (Tarjetas 4 y 5) Cambio
            $cicloActualObj = DB::table('ciclo_lectivo')->where('ciclo_lectivo_id', $cicloId)->first();

            // Verificamos si el ciclo está en cualquiera de los estados "vivos" (no cerrado)
            $estadosVivos = ['CONFIGURACION', 'MATRICULA_ABIERTA', 'ACTIVO'];
            $esCicloVivo = ($cicloActualObj && in_array($cicloActualObj->estado, $estadosVivos));

            $estudiantesActivos = (clone $queryBase)
                ->where('alumnos.estado_carrera', 'ACTIVO')
                ->distinct('inscripciones.id_alumno')
                ->count('inscripciones.id_alumno');

            $estudiantesInactivos = (clone $queryBase)
                ->where('alumnos.estado_carrera', 'INACTIVO')
                ->distinct('inscripciones.id_alumno')
                ->count('inscripciones.id_alumno');

            if ($esCicloVivo) {
                $queryAlumnos = DB::table('alumnos')
                    ->leftJoin('carreras', 'alumnos.id_carrera', '=', 'carreras.carrera_id');

                if ($facultadId) {
                    $queryAlumnos->where('carreras.id_facultad', $facultadId);
                }
                if ($carreraId) {
                    $queryAlumnos->where('alumnos.id_carrera', $carreraId);
                }

                $estudiantesActivos = (clone $queryAlumnos)->where('alumnos.estado_carrera', 'ACTIVO')->count();
                $estudiantesInactivos = (clone $queryAlumnos)->where('alumnos.estado_carrera', 'INACTIVO')->count();
            }


        // ---------------------------------------------------------
        // 4. AGRUPACIONES POR CARRERA Y FACULTAD (CON ABREVIATURAS)
        // ---------------------------------------------------------
        $abreviaturasCarrera = [
            'Licenciatura en ' => 'Lic. ',
            'Licenciatura '    => 'Lic. ',
            'Técnico en '      => 'Téc. ',
            'Ingeniería en '   => 'Ing. ',
            'Profesorado en '  => 'Prof. ',
            'Maestría en '     => 'Mtr. ',
            'Maestria en '     => 'Mtr. '
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
        // 5. EVOLUCIÓN HISTÓRICA Y DATOS PARA GRÁFICOS
        // ---------------------------------------------------------
        $evolucionHistorica = DB::table('inscripciones')
            ->join('ciclo_lectivo', 'inscripciones.id_ciclo_lectivo', '=', 'ciclo_lectivo.ciclo_lectivo_id')
            ->select('ciclo_lectivo.nombre_ciclo', 'ciclo_lectivo.anio', DB::raw('COUNT(DISTINCT id_alumno) as total'))
            ->groupBy('ciclo_lectivo.ciclo_lectivo_id', 'ciclo_lectivo.nombre_ciclo', 'ciclo_lectivo.anio')
            ->orderBy('ciclo_lectivo.ciclo_lectivo_id')
            ->take(6)
            ->get();

        $labelsEvolucion = json_encode($evolucionHistorica->map(fn($e) => $e->nombre_ciclo)); // <-- Aquí ya no se repite el año
        $datosEvolucion = json_encode($evolucionHistorica->pluck('total'));

        $labelsCarrera = json_encode($matriculaPorCarrera->pluck('carrera'));
        $datosCarrera = json_encode($matriculaPorCarrera->pluck('total'));

        $labelsFacultad = json_encode($matriculaPorFacultad->pluck('facultad'));
        $datosFacultad = json_encode($matriculaPorFacultad->pluck('total'));

        // Variables preparadas para el nuevo gráfico de Composición Poblacional (Izquierda)
        $labelsComposicionJson = json_encode(['Nuevo Ingreso', 'Estudiantes Continuos']);
        $datosComposicionJson = json_encode([$nuevoIngreso, $alumnosRetenidos]);

        // ---------------------------------------------------------
        // 6. ENVÍO DE VARIABLES A LA VISTA
        // ---------------------------------------------------------
        return view('directivo.matricula.index', compact(
            'ciclos', 'cicloId', 'mesSeleccionado', 'todasLasFacultades', 'todasLasCarreras',
            'totalMatriculados', 'varMatricula', 'nuevoIngreso', 'alumnosRetenidos', 'estudiantesActivos', 'estudiantesInactivos',
            'matriculaPorCarrera', 'matriculaPorFacultad',
            'labelsEvolucion', 'datosEvolucion', 'labelsCarrera', 'datosCarrera', 'labelsFacultad', 'datosFacultad',
            'labelsComposicionJson', 'datosComposicionJson'
        ));
    }

    //Funcion para la dashboard de morosidad
    public function morosidad(Request $request)
    {
        // ---------------------------------------------------------
        // 1. OBTENCIÓN DE FILTROS Y CATÁLOGOS
        // ---------------------------------------------------------
        $ciclos = DB::table('ciclo_lectivo')->orderByDesc('ciclo_lectivo_id')->get();
        $todasLasFacultades = DB::table('facultades')->orderBy('nombre')->get();
        $todasLasCarreras = DB::table('carreras')->orderBy('nombre')->get();

        $cicloActivo = $ciclos->whereIn('estado', ['ACTIVO', 'MATRICULA_ABIERTA', 'CONFIGURACION'])->first();
        $cicloId = $request->input('ciclo_id', $cicloActivo->ciclo_lectivo_id ?? $ciclos->first()->ciclo_lectivo_id);

        $facultadId = $request->input('facultad_id');
        $carreraId = $request->input('carrera_id');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $hoy = Carbon::now()->format('Y-m-d');

        // ---------------------------------------------------------
        // 2. CONSULTA BASE DE DEUDAS (CARGOS vs PAGOS REALIZADOS + MORA DINÁMICA)
        // ---------------------------------------------------------
        // Usamos una subconsulta para sumar todos los abonos hechos a un cargo
        $abonos = DB::table('detalle_pago')
            ->select('id_cargo', DB::raw('SUM(subtotal) as total_abonado'))
            ->groupBy('id_cargo');

        // Consulta principal cruzando el cargo con lo abonado
        $queryBase = DB::table('cargo_estudiante')
            ->join('alumnos', 'cargo_estudiante.id_alumno', '=', 'alumnos.alumno_id')
            ->join('users', 'alumnos.id_usuario', '=', 'users.id')
            ->leftJoin('carreras', 'alumnos.id_carrera', '=', 'carreras.carrera_id')
            ->leftJoin('facultades', 'carreras.id_facultad', '=', 'facultades.facultad_id')
            ->leftJoinSub($abonos, 'abonos', function ($join) {
                $join->on('cargo_estudiante.cargo_id', '=', 'abonos.id_cargo');
            })
            ->where('cargo_estudiante.id_ciclo_lectivo', $cicloId)
            ->where('cargo_estudiante.estado_cargo', '=', 'PENDIENTE')
            ->whereRaw('(COALESCE(cargo_estudiante.monto_neto_estudiante, 0) - COALESCE(abonos.total_abonado, 0)) > 0');

        if ($facultadId) {
            $queryBase->where('carreras.id_facultad', $facultadId);
        }
        if ($carreraId) {
            $queryBase->where('alumnos.id_carrera', $carreraId);
        }
        // <-- SE AGREGA EL FILTRO DE FECHA A LA CONSULTA BASE
        if ($fechaInicio && $fechaFin) {
            $queryBase->whereBetween('cargo_estudiante.fecha_vencimiento', [$fechaInicio, $fechaFin]);
        } elseif ($fechaInicio) {
            $queryBase->where('cargo_estudiante.fecha_vencimiento', '>=', $fechaInicio);
        } elseif ($fechaFin) {
            $queryBase->where('cargo_estudiante.fecha_vencimiento', '<=', $fechaFin);
        }

        // Definimos el cálculo exacto del saldo: Monto neto - Abonos + Mora (si aplica)
        $calcSaldo = "(COALESCE(cargo_estudiante.monto_neto_estudiante, 0) - COALESCE(abonos.total_abonado, 0)) +
            (CASE
                WHEN cargo_estudiante.fecha_vencimiento < '{$hoy}' THEN
                    CASE
                        WHEN EXTRACT(DAY FROM cargo_estudiante.fecha_vencimiento) = 18 THEN 10
                        ELSE 5
                    END
                ELSE 0
            END)";

        // ---------------------------------------------------------
        // 3. CÁLCULO DE KPIs PRINCIPALES
        // ---------------------------------------------------------

        // Deuda Total (Suma directa por SQL)
        $deudaTotal = (clone $queryBase)->sum(DB::raw($calcSaldo));

        // Cuotas Pendientes
        $cuotasPendientes = (clone $queryBase)->count('cargo_estudiante.cargo_id');

        // Estudiantes Morosos (Solo contamos a los alumnos con al menos 1 cuota VENCIDA)
        $estudiantesMorosos = (clone $queryBase)
            ->where('cargo_estudiante.fecha_vencimiento', '<', $hoy)
            ->count(DB::raw('DISTINCT cargo_estudiante.id_alumno'));

        // Cuotas Vencidas
        $cuotasVencidas = (clone $queryBase)
            ->where('cargo_estudiante.fecha_vencimiento', '<', $hoy)
            ->sum(DB::raw($calcSaldo));

        // Índice de morosidad
        $totalCargosGenerados = DB::table('cargo_estudiante')
            ->where('id_ciclo_lectivo', $cicloId)
            ->where('estado_cargo', '!=', 'ANULADO');

        if ($fechaInicio && $fechaFin) {
            $totalCargosGenerados->whereBetween('fecha_vencimiento', [$fechaInicio, $fechaFin]);
        } elseif ($fechaInicio) {
            $totalCargosGenerados->where('fecha_vencimiento', '>=', $fechaInicio);
        } elseif ($fechaFin) {
            $totalCargosGenerados->where('fecha_vencimiento', '<=', $fechaFin);
        }

        $totalCargosGenerados = $totalCargosGenerados->sum('monto_neto_estudiante');

        $indiceMorosidad = $totalCargosGenerados > 0 ? round(($cuotasVencidas / $totalCargosGenerados) * 100, 1) : 0;

        // ---------------------------------------------------------
        // 3.5 CÁLCULO DE VARIACIONES (VS CICLO ANTERIOR)
        // ---------------------------------------------------------
        $varDeuda = 0; $varEstudiantes = 0; $varCuotas = 0; $varVencidas = 0; $varIndice = 0;

        // 1. Buscamos el ID del ciclo anterior
        $cicloAnterior = DB::table('ciclo_lectivo')
            ->where('ciclo_lectivo_id', '<', $cicloId)
            ->orderByDesc('ciclo_lectivo_id')
            ->first();

        if ($cicloAnterior) {
            $cicloAnteriorId = $cicloAnterior->ciclo_lectivo_id;

            // 2. Construimos la consulta base pero apuntando al ciclo anterior
            $queryAnterior = DB::table('cargo_estudiante')
                ->join('alumnos', 'cargo_estudiante.id_alumno', '=', 'alumnos.alumno_id')
                ->leftJoin('carreras', 'alumnos.id_carrera', '=', 'carreras.carrera_id')
                ->leftJoinSub($abonos, 'abonos', function ($join) {
                    $join->on('cargo_estudiante.cargo_id', '=', 'abonos.id_cargo');
                })
                ->where('cargo_estudiante.id_ciclo_lectivo', $cicloAnteriorId)
                ->where('cargo_estudiante.estado_cargo', '=', 'PENDIENTE')
                ->whereRaw('(COALESCE(cargo_estudiante.monto_neto_estudiante, 0) - COALESCE(abonos.total_abonado, 0)) > 0');

            if ($facultadId) $queryAnterior->where('carreras.id_facultad', $facultadId);
            if ($carreraId) $queryAnterior->where('alumnos.id_carrera', $carreraId);
            // <-- SE AGREGA EL FILTRO DE FECHA A LA CONSULTA DEL CICLO ANTERIOR
            if ($fechaInicio && $fechaFin) {
                $queryAnterior->whereBetween('cargo_estudiante.fecha_vencimiento', [$fechaInicio, $fechaFin]);
            } elseif ($fechaInicio) {
                $queryAnterior->where('cargo_estudiante.fecha_vencimiento', '>=', $fechaInicio);
            } elseif ($fechaFin) {
                $queryAnterior->where('cargo_estudiante.fecha_vencimiento', '<=', $fechaFin);
            }

            // 3. Calculamos los totales que hubo en ese ciclo anterior
            $deudaAnterior = (clone $queryAnterior)->sum(DB::raw($calcSaldo));

            $cuotasAnterior = (clone $queryAnterior)->count('cargo_estudiante.cargo_id');

            $estudiantesAnterior = (clone $queryAnterior)
                ->where('cargo_estudiante.fecha_vencimiento', '<', $hoy)
                ->count(DB::raw('DISTINCT cargo_estudiante.id_alumno'));

            $vencidasAnterior = (clone $queryAnterior)
                ->where('cargo_estudiante.fecha_vencimiento', '<', $hoy)
                ->sum(DB::raw($calcSaldo));

            $totalCargosAnterior = DB::table('cargo_estudiante')
                ->where('id_ciclo_lectivo', $cicloAnteriorId)
                ->where('estado_cargo', '!=', 'ANULADO');

            if ($fechaInicio && $fechaFin) {
                $totalCargosAnterior->whereBetween('fecha_vencimiento', [$fechaInicio, $fechaFin]);
            } elseif ($fechaInicio) {
                $totalCargosAnterior->where('fecha_vencimiento', '>=', $fechaInicio);
            } elseif ($fechaFin) {
                $totalCargosAnterior->where('fecha_vencimiento', '<=', $fechaFin);
            }

            $totalCargosAnterior = $totalCargosAnterior->sum('monto_neto_estudiante');

            $indiceAnterior = $totalCargosAnterior > 0 ? round(($vencidasAnterior / $totalCargosAnterior) * 100, 1) : 0;

            // 4. Aplicamos la fórmula con los signos matemáticos correctos
            $varDeuda = $deudaAnterior > 0 ? round((($deudaTotal - $deudaAnterior) / $deudaAnterior) * 100, 1) : ($deudaTotal > 0 ? 100 : 0);

            $varCuotas = $cuotasAnterior > 0 ? round((($cuotasPendientes - $cuotasAnterior) / $cuotasAnterior) * 100, 1) : ($cuotasPendientes > 0 ? 100 : 0);

            $varEstudiantes = $estudiantesAnterior > 0 ? round((($estudiantesMorosos - $estudiantesAnterior) / $estudiantesAnterior) * 100, 1) : ($estudiantesMorosos > 0 ? 100 : 0);

            $varVencidas = $vencidasAnterior > 0 ? round((($cuotasVencidas - $vencidasAnterior) / $vencidasAnterior) * 100, 1) : ($cuotasVencidas > 0 ? 100 : 0);

            // El índice de morosidad se mide restando los puntos porcentuales
            $varIndice = round($indiceMorosidad - $indiceAnterior, 1);
        }

        // ---------------------------------------------------------
        // 4. AGRUPACIONES PARA GRÁFICOS
        // ---------------------------------------------------------

        // A. Evolución de la deuda
        $evolucion = (clone $queryBase)
            ->select(
                DB::raw("TO_CHAR(cargo_estudiante.fecha_vencimiento, 'YYYY-MM') as mes_anio"),

                // Línea Roja: Cuota Principal (Deuda neta sin meter recargos)
                DB::raw("SUM(COALESCE(cargo_estudiante.monto_neto_estudiante, 0) - COALESCE(abonos.total_abonado, 0)) as deuda_neta"),

                // Línea Gris: Recargos puros (Los $5 o $10 extra de las cuotas ya vencidas)
                DB::raw("SUM(CASE
                    WHEN cargo_estudiante.fecha_vencimiento < '$hoy' THEN
                        CASE WHEN EXTRACT(DAY FROM cargo_estudiante.fecha_vencimiento) = 18 THEN 10 ELSE 5 END
                    ELSE 0
                END) as recargos")
            )
            ->groupBy('mes_anio')
            ->orderBy('mes_anio', 'ASC')
            ->get();

        $labelsEvolucion = json_encode($evolucion->pluck('mes_anio'));
        $datosDeudaNeta = json_encode($evolucion->pluck('deuda_neta'));
        $datosRecargos = json_encode($evolucion->pluck('recargos'));

        // B. Deuda por Facultad
        $deudaPorFacultad = (clone $queryBase)
            ->select(
                DB::raw("COALESCE(facultades.nombre, 'Sin Facultad') as facultad"),
                DB::raw("SUM($calcSaldo) as total")
            )
            ->groupBy('facultades.nombre')
            ->orderByDesc('total')
            ->get();

        foreach ($deudaPorFacultad as $item) {
            $item->facultad = str_replace('Facultad de ', 'Fac. ', $item->facultad);
        }
        $labelsFacultad = json_encode($deudaPorFacultad->pluck('facultad'));
        $datosFacultad = json_encode($deudaPorFacultad->pluck('total'));

        // C. Deuda por Carrera (Top 5)
        $abreviaturasCarrera = [
            'Licenciatura en ' => 'Lic. ', 'Técnico en ' => 'Téc. ', 'Ingeniería en ' => 'Ing. ', 'Maestría en ' => 'Mtr. ',
            ' para tercer ciclo de educacion basica y educacion media' => ''
        ];

        $deudaPorCarrera = (clone $queryBase)
            ->select(
                DB::raw("COALESCE(carreras.nombre, 'Sin Carrera') as carrera"),
                DB::raw("SUM($calcSaldo) as total")
            )
            ->groupBy('carreras.nombre')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        foreach ($deudaPorCarrera as $item) {
            $item->carrera = str_replace(array_keys($abreviaturasCarrera), array_values($abreviaturasCarrera), $item->carrera);
        }
        $labelsCarrera = json_encode($deudaPorCarrera->pluck('carrera'));
        $datosCarrera = json_encode($deudaPorCarrera->pluck('total'));

        // ---------------------------------------------------------
        // 5. LISTA DE ESTUDIANTES MOROSOS (TABLA)
        // ---------------------------------------------------------
        $estudiantesMorososLista = (clone $queryBase)
            ->select(
                'users.name as nombre',
                'alumnos.codigo_estudiante as codigo',
                DB::raw("COALESCE(facultades.nombre, 'N/A') as facultad"),
                DB::raw("COALESCE(carreras.nombre, 'N/A') as carrera"),
                DB::raw('COUNT(cargo_estudiante.cargo_id) as cuotas'),
                DB::raw("SUM($calcSaldo) as monto"),
                DB::raw('MIN(cargo_estudiante.fecha_vencimiento) as cuota_mas_antigua')
            )
            ->groupBy('alumnos.alumno_id', 'users.name', 'alumnos.codigo_estudiante', 'facultades.nombre', 'carreras.nombre')
            ->orderByDesc('monto')
            ->take(10)
            ->get()
            ->map(function ($est) {
                // Calcular estado
                $diasVencidos = Carbon::now()->diffInDays(Carbon::parse($est->cuota_mas_antigua), false) * -1;

                $est->estado = 'No Vencida';
                $est->color = 'secondary';

                if ($diasVencidos > 90) { $est->estado = '> 90 días'; $est->color = 'danger'; }
                elseif ($diasVencidos > 60) { $est->estado = '61 - 90 días'; $est->color = 'warning'; }
                elseif ($diasVencidos > 30) { $est->estado = '31 - 60 días'; $est->color = 'warning'; }
                elseif ($diasVencidos > 0) { $est->estado = '1 - 30 días'; $est->color = 'info'; }

                $est->facultad = str_replace('Facultad de ', '', $est->facultad);
                $est->carrera = substr(str_replace(['Licenciatura en ', 'Ingeniería en ', 'Profesorado en '], '', $est->carrera), 0, 25);

                return $est;
            });


        // ---------------------------------------------------------
        // MOROSIDAD POR FACULTAD (SOLO CARGOS VENCIDOS / CON MORA)
        // ---------------------------------------------------------
        $facultadesData = (clone $queryBase)
            // Filtro estricto: solo recibos cuya fecha de vencimiento ya pasó
            ->where('cargo_estudiante.fecha_vencimiento', '<', $hoy)
            ->select(
                DB::raw("COALESCE(facultades.nombre, 'Sin Facultad') as nombre_facultad"),
                DB::raw("SUM($calcSaldo) as total_deuda")
            )
            ->groupBy('facultades.nombre', 'facultades.facultad_id')
            ->orderByDesc('total_deuda')
            ->get();

        $datosFacultad = json_encode($facultadesData->pluck('total_deuda'));
        $labelsFacultad = json_encode($facultadesData->pluck('nombre_facultad'));

        return view('directivo.morosidad.index', compact(
            'ciclos', 'cicloId', 'todasLasFacultades', 'todasLasCarreras',
            'deudaTotal', 'estudiantesMorosos', 'cuotasPendientes', 'cuotasVencidas', 'indiceMorosidad',
            'varDeuda', 'varEstudiantes', 'varCuotas', 'varVencidas', 'varIndice',
            'labelsEvolucion',
            'datosDeudaNeta', 'datosRecargos', // <-- Solo las variables nuevas de la Opción 3
            'labelsFacultad', 'datosFacultad',
            'labelsCarrera', 'datosCarrera',
            'estudiantesMorososLista', 'deudaPorCarrera'
        ));
    }


}
