# NEXUM - Sistema Integral de Gestión Académico-Administrativa

## Acerca del Proyecto
**NEXUM** es una solución de infraestructura de software propia desarrollada específicamente para la Universidad Modular Abierta[cite: 2]. El proyecto nace con una finalidad clara y crítica: mitigar las vulnerabilidades asociadas al uso de software obsoleto, eliminar de raíz la dependencia de proveedores externos y garantizar los más altos estándares de seguridad de la información institucional.

El sistema sirve como un núcleo centralizado que administra dos grandes áreas operativas:
1. **Gestión Académica:** Control absoluto sobre docentes, estudiantes, mallas curriculares, facultades, registro de asistencia y calificaciones.
2. **Gestión Administrativa y Financiera:** Manejo de aranceles, tesorería, directivas, consejo académico, promociones, reglas de pago y emisión de comprobantes.

El sistema implementa un Control de Acceso Basado en Roles (RBAC) altamente granular, estructurado para los siguientes perfiles: `SUPER_ADMIN`, `DIRECTIVO`, `ADMIN_ACADEMICO`, `COORDINADOR_FACULTAD`, `CAJERO / COLECTURIA`, `ADMIN_FINANCIERO`, `DOCENTE` y `ESTUDIANTE`[cite: 3]. 

##  Stack Tecnológico y Herramientas Utilizadas

NEXUM (v0.1) está construido sobre una arquitectura robusta, priorizando la seguridad (Backend), la integridad ACID (Base de datos) y la accesibilidad (Frontend):

### Entorno y Backend (PHP / Laravel)
* **PHP 8.2+**: Lenguaje base, utilizando tipado estricto y características modernas.
* **Laravel Framework**: Estructura principal aplicando el patrón de diseño Action y principios SOLID.
* **Laravel Herd**: Entorno de desarrollo local de ultra alto rendimiento, utilizado para aislar y agilizar el despliegue del servidor web y PHP.
* **Laravel Fortify**: Núcleo del sistema de autenticación. Implementa seguridad paranoica: Autenticación de Dos Factores (2FA), protección contra fuerza bruta, confirmación y recuperación segura de contraseñas.
* **Laravel Reverb & Echo**: Infraestructura WebSockets propia para notificaciones y mensajería en tiempo real sin depender de servicios externos como Pusher.

### Base de Datos (PostgreSQL)
* **PostgreSQL**: Motor relacional principal. Garantiza integridad transaccional (ACID) rigurosa para historiales académicos y financieros. Utiliza características avanzadas como campos `JSONB` para atributos dinámicos, `CTEs` para reportes de directivos y Row-Level Security (RLS) para aislar la información.

### Frontend (UI/UX)
* **Bootstrap 5 + CSS Personalizado (Blade)**: Sistema de diseño basado en cuadrícula de tarjetas con estilo **Neumórfico (Soft UI)**. Se hace uso de sombras extruidas/intruidas y transiciones sutiles.
* **Accesibilidad (WCAG 2.1 AA)**: Micro-interacciones claras, estados `focus/active` definidos y contraste riguroso en la paleta de colores para garantizar una navegación inclusiva.
* **Vite**: Empaquetador de assets (JS/CSS) para compilación ultrarrápida.

## Requisitos de Instalación

Para levantar el entorno de desarrollo de la versión 0.1, necesitas tener instalado lo siguiente en tu máquina:

1. **Laravel Herd** (Incluye PHP 8.2+, Nginx/Valet y Composer).
2. **PostgreSQL** (Versión 14 o superior).
3. **Node.js y NPM** (Para la compilación de assets de Bootstrap 5 y configuración de Echo).
4. **Git** (Para control de versiones).

## Pasos para Desplegar el Proyecto Localmente

git clone https://github.com/Esau-Torres/Nexum1400201.git

- cd nexum
```
composer install
```
```
npm install
```  
## Configuración del Entorno y Backend (Laravel Herd)
- cp .env.example .env
- php artisan key:generate

Ajustes de la db
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nexum
DB_USERNAME=postgres
DB_PASSWORD=tu_password_seguro
```

## Compilación del Soft UI y WebSockets

consola 1: Inicia el servidor de WebSockets propio de la infraestructura
```
php artisan reverb:start
```

Consola 2: Compila los assets interactivos y estilos WCAG 2.1 AA
```
npm run dev
```
## Roles y Cuentas de Acceso Al proyecto NEXUM

| N° | ROL                          | CORREO                           | CONTRASEÑA       |
| -- | ---------------------------- | -------------------------------- | ---------------- |
| 1  | Super Administrador          | davidesau140\@gmail.com          | `Admin12345`     |
| 2  | Administrador académico      | rrf2kp8eqk\@olipii.com           | `Academico1234`  |
| 3  | Coordinador de facultad      | ev9u65lhlp\@bwmyga.com           | `Facultad1234`   |
| 4  | Docente                      | powvu6ilbs\@ozsaip.com           | `Docente1234`    |
| 5  | Directivo                    | kvrs1dql22\@bwmyga.com           | `Directivo1234`  |
| 6  | Administrador Financiero     | prsj5367\@gmail.com              | `Financiero1234` |
| 7  | Cajero                       | 552pko38u8\@ruutukf.com          | `Cajero1234`     |
| 8  | Alumno Egresado Maestría     | cg224100282\@uma.edu.sv          | `User1234`       |
| 9  | Alumno Egresado Licenciatura | mr224100122\@uma.edu.sv          | `User1234`       |
| 10 | Alumno Becado                | angelamarcelatorres30\@gmail.com | `User1234`       |

## Arquitectura del proyecto

```
└── 📁nexum
    └── 📁app
        └── 📁Actions
            └── 📁AdminAcademic
                ├── ActualizarEstadoProcesoGraduacionAction.php
                ├── ActualizarTituloTemaAction.php
                ├── AgregarMateriaAPensumAction.php
                ├── AgregarPrerequisitoAction.php
                ├── ApproveStudentApplicationAction.php
                ├── AssignStudentBenefitAction.php
                ├── CambiarEstadoSituacionAction.php
                ├── ChangeCicloStatusAction.php
                ├── ChangeEstadoPensumAction.php
                ├── CreateActiveStudentAction.php
                ├── CreateCarreraAction.php
                ├── CreateCicloLectivoAction.php
                ├── CreateFacultadAction.php
                ├── CreateMassiveStudentAction.php
                ├── CreateMateriaAction.php
                ├── CreatePensumAction.php
                ├── DeleteCarreraAction.php
                ├── DeleteFacultadAction.php
                ├── DeleteStudentDocumentsAction.php
                ├── EliminarMateriaDePensumAction.php
                ├── EliminarPrerequisitoAction.php
                ├── GestionarHorasSocialesAction.php
                ├── GestionarPasantiaAction.php
                ├── MarcarSolvenciaFinancieraAction.php
                ├── ReassignStudentBenefitAction.php
                ├── RegistrarEgresoAction.php
                ├── RegistrarProcesoGraduacionAction.php
                ├── RejectStudentApplicationAction.php
                ├── RevokeStudentBenefitAction.php
                ├── UpdateCarreraAction.php
                ├── UpdateCicloLectivoAction.php
                ├── UpdateFacultadAction.php
                ├── UpdateMateriaAction.php
                ├── UpdateStudentAction.php
                ├── UploadStudentDocumentsAction.php
                ├── ValidarEgresoAction.php
            └── 📁CoordinadorFacultad
                ├── CreateAulaAction.php
                ├── CreateEdificioAction.php
                ├── CreateGrupoAction.php
                ├── CreateHorarioAction.php
                ├── CreateSeccionAction.php
                ├── DeleteAulaAction.php
                ├── DeleteEdificioAction.php
                ├── DeleteGrupoAction.php
                ├── DeleteHorarioAction.php
                ├── DeleteSeccionAction.php
                ├── UpdateAulaAction.php
                ├── UpdateEdificioAction.php
                ├── UpdateGrupoAction.php
                ├── UpdateHorarioAction.php
                ├── UpdateSeccionAction.php
            └── 📁Fortify
                ├── CreateNewUser.php
                ├── LoginUser.php
                ├── PasswordValidationRules.php
                ├── ResetUserPassword.php
                ├── UpdateUserPassword.php
                ├── UpdateUserProfileInformation.php
            └── 📁Role
                ├── AssignRolesToUserAction.php
                ├── GetRoleImpactAction.php
                ├── UpdateRoleStatusAction.php
            └── 📁Student
                ├── CrearSituacionAction.php
                ├── CreateStudentProfileAction.php
                ├── EliminarSituacionAction.php
                ├── InscribirMateriaAction.php
                ├── ObtenerPensumAlumnoAction.php
                ├── ObtenerRecordAcademicoAction.php
                ├── ObtenerSeccionesDisponiblesAction.php
                ├── RetirarMateriaAction.php
                ├── UploadStudentDocumentsAction.php
                ├── ValidarPuedeInscribirseAction.php
                ├── ValidarPuedeRetirarMateriaAction.php
                ├── ValidarSolvenciaAction.php
            └── 📁SuperAdmin
                ├── ActualizarConfiguracionAcademicaAction.php
            └── 📁Teacher
                ├── CreateDocenteProfileAction.php
                ├── StoreCalificacionesAction.php
                ├── SyncDocenteProfileAction.php
            └── 📁User
                ├── AssignUserRoleAction.php
                ├── CreateUserAccountAction.php
                ├── UpdateUserPersonalDataAction.php
        └── 📁Console
            └── 📁Commands
                ├── CerrarCicloLectivoCommand.php
        └── 📁Enums
            ├── ArancelesGraduacion.php
            ├── ConceptosPagoPorCarrera.php
            ├── DiaSemana.php
            ├── EstadoBeneficio.php
            ├── EstadoCiclo.php
            ├── EstadoEgreso.php
            ├── EstadoInscripcion.php
            ├── EstadoMateria.php
            ├── EstadoPensum.php
            ├── EstadoProcesoGraduacion.php
            ├── EstadoSeccion.php
            ├── EstadoSituacion.php
            ├── ModalidadGraduacion.php
            ├── TamanoGrupo.php
            ├── TipoAula.php
            ├── TipoBeneficio.php
            ├── TipoCiclo.php
            ├── TipoJornada.php
            ├── TipoRepeticion.php
            ├── TipoSituacion.php
        └── 📁Exports
            ├── StudentTemplateExport.php
        └── 📁Http
            └── 📁Controllers
                └── 📁Adfinanciero
                    ├── AdminfinancieroController.php
                └── 📁AdminAcademico
                    ├── ActiveStudentController.php
                    ├── CicloLectivoController.php
                    ├── EvaluacionDocenteController.php
                    ├── FacultadCarreraController.php
                    ├── MateriaController.php
                    ├── PensumController.php
                    ├── ProcesoGraduacionController.php
                    ├── ReglaBeneficioController.php
                    ├── RequisitosEgresoController.php
                    ├── SituacionGestionController.php
                    ├── StudentApplicationController.php
                    ├── StudentBenefitController.php
                    ├── StudentModifyController.php
                └── 📁Cajero
                    ├── CajeroController.php
                └── 📁CoordinadorFacultad
                    ├── DashboardController.php
                    ├── HorariosController.php
                    ├── InfraestructuraHorarioController.php
                    ├── SeccionesController.php
                └── 📁Directivo
                    ├── DirectivoController.php
                └── 📁Docente
                    ├── AsistenciaMateriaController.php
                    ├── DashboardDocenteController.php
                    ├── MateriasDocenteController.php
                    ├── NotasMateriaController.php
                    ├── PruebaNotasController.php
                └── 📁Estudiante
                    ├── CarnetController.php
                    ├── DesempenoDocenteController.php
                    ├── EstudianteController.php
                    ├── FotoPerfilController.php
                    ├── InscripcionController.php
                    ├── PensumController.php
                    ├── RecordAcademicoController.php
                    ├── registroController.php
                    ├── RetirarMateriaController.php
                    ├── SituacionController.php
                └── 📁Reglaspago
                    ├── ReglasController.php
                └── 📁SuperAdmin
                    ├── ConfiguracionAcademicaController.php
                └── 📁Users
                    ├── createuserController.php
                    ├── homeController.php
                    ├── modifyuserController.php
                    ├── RoleManagementController.php
                ├── Controller.php
            └── 📁Middleware
                ├── CheckRole.php
            └── 📁Responses
                ├── LoginResponse.php
        └── 📁Imports
            ├── StudentsImport.php
        └── 📁Jobs
            ├── CerrarCicloLectivoJob.php
        └── 📁Mail
            ├── AssingBenefitStudentMail.php
            ├── AssingRoleUsersMail.php
            ├── BenefitReassignedMail.php
            ├── BenefitRevokedMail.php
            ├── DarDeBajaMail.php
            ├── NewAccountCredentialsMail.php
            ├── NewAccountStudentCredentialsMail.php
            ├── NewAccountStudentDirectMail.php
            ├── ReciboPagoMail.php
            ├── StudentApplicationRejectedMail.php
        └── 📁Models
            └── 📁Academico
                ├── Asistencia.php
                ├── BeneficioEstudiante.php
                ├── Calificacion.php
                ├── CargoEstudiante.php
                ├── CategoriaEvaluacion.php
                ├── CicloLectivo.php
                ├── ClaseSesion.php
                ├── ConfiguracionAcademica.php
                ├── EvaluacionDocente.php
                ├── Evaluaciones.php
                ├── GrupoGraduacion.php
                ├── Materias.php
                ├── NivelAcademico.php
                ├── Pensum.php
                ├── PensumMateria.php
                ├── PeriodoEvaluacion.php
                ├── Prerequisito.php
                ├── ProcesoGraduacion.php
                ├── ReglaBeneficio.php
            └── 📁Adfinanciero
                ├── CargoEstudiante.php
                ├── CicloArancelPeriodo.php
                ├── CicloLectivo.php
                ├── ConceptoPago.php
                ├── DetallePago.php
                ├── Pago.php
                ├── Promocion.php
                ├── ReglaPago.php
            └── 📁CoordinadorFacultad
                ├── Aula.php
                ├── ClaseCompartida.php
                ├── ClaseSesion.php
                ├── Edificio.php
                ├── Grupo.php
                ├── GrupoHorario.php
                ├── Horarios.php
                ├── Seccion.php
                ├── SeccionHorario.php
            └── 📁Docente
                ├── Docentes.php
            └── 📁Estudiante
                ├── AlumnoDocumentos.php
                ├── Alumnos.php
                ├── Carreras.php
                ├── DetalleInscripcion.php
                ├── ExamenDiferido.php
                ├── Facultades.php
                ├── Inscripcion.php
                ├── RequisitosEgreso.php
                ├── SituacionUsuario.php
            └── 📁Users
                ├── RegionalActivo.php
                ├── Role.php
                ├── TipoDocumentoIdentidad.php
                ├── User.php
        └── 📁Providers
            ├── AppServiceProvider.php
            ├── FortifyServiceProvider.php
        └── 📁Rules
            └── 📁AdminAcademico
            └── 📁CoordinadorFacultad
                ├── StoreAulaRules.php
                ├── StoreEdificioRules.php
                ├── StoreGrupoRules.php
                ├── StoreHorarioRules.php
                ├── StoreSeccionRules.php
            └── 📁Egreso
                ├── ArancelServicioSocialRule.php
                ├── ConstanciaCumRule.php
                ├── ConstanciaNotasRule.php
                ├── CumMinimoRule.php
                ├── HorasSocialesDentroDelLimiteRule.php
                ├── HorasSocialesRequest.php
                ├── HorasSocialesRule.php
                ├── MateriasAprobadasRule.php
                ├── PasantiaAprobadaRule.php
                ├── PasantiaRequest.php
                ├── SolvenciaFinancieraRule.php
                ├── TieneArancelServicioSocialPagadoRule.php
                ├── UvMinimasHorasSocialesRule.php
                ├── UvMinimasPasantiaRule.php
            └── 📁Graduacion
                ├── ArancelAperturaPagadoRule.php
                ├── ArancelDefensaPagadoRule.php
                ├── ArancelMatriculaPreEspecializacionPagadoRule.php
                ├── ArancelRevisionAnteproyectoPagadoRule.php
                ├── ArancelRevisionTesisPagadoRule.php
                ├── CuotasAsesoriaCompletasRule.php
                ├── CuotasPreEspecializacionCompletasRule.php
                ├── EgresadoRule.php
                ├── ModalidadPermitidaRule.php
                ├── PagoExamenSuficienciaRule.php
                ├── ProcesoNoAgotadoRule.php
                ├── PuedeDefinirTituloRule.php
                ├── PuedeReprogramarDefensaRule.php
                ├── RegistrarProcesoGraduacionRequest.php
                ├── SinProcesoActivoRule.php
                ├── TienePagoDerechosGradoRule.php
            └── 📁Student
                ├── CumplePrerequisitosRule.php
                ├── MaxReprobacionesRule.php
                ├── Pensum.php
                ├── StoreEvaluacionDocenteRules.php
                ├── StoreSituacionRule.php
            └── 📁SuperAdmin
                ├── ConfiguracionAcademicaRule.php
            ├── ActualizarCarreraRules.php
            ├── ActualizarCicloLectivoRules.php
            ├── ActualizarFacultadRules.php
            ├── ActualizarMateriaRules.php
            ├── AgregarMateriaAPensumRules.php
            ├── AgregarPrerequisitoRules.php
            ├── ApproveStudentRules.php
            ├── AssignBenefitRules.php
            ├── AssignRolesToUserRule.php
            ├── CambiarEstadoCicloRules.php
            ├── CambiarEstadoPensumRules.php
            ├── CarreraInscribibleRules.php
            ├── codigoEstudiante.php
            ├── CrearCarreraRules.php
            ├── CrearCicloLectivoRules.php
            ├── CrearFacultadRules.php
            ├── CrearMateriaRules.php
            ├── CrearPensumRules.php
            ├── DocenteSyncRule.php
            ├── EliminarCarreraRules.php
            ├── EliminarFacultadRules.php
            ├── GeneradorCodigoDocente.php
            ├── MassiveStudentRowRules.php
            ├── ReassignBenefitRules.php
            ├── RejectStudentRules.php
            ├── RevokeBenefitRules.php
            ├── StoreActiveStudentRules.php
            ├── StoreCalificacionesRules.php
            ├── StoreUserRule.php
            ├── UpdateReglaBeneficioRules.php
            ├── UpdateRoleStatusRule.php
            ├── UpdateStudentRules.php
            ├── UpdateUserPersonalDataRule.php
            ├── UploadStudentDocumentsRules.php
            ├── ValidarDocumentoIdentidad.php
        └── 📁Services
            └── 📁Beneficios
                ├── EvaluarReglasBeneficioService.php
            └── 📁CicloLectivo
                ├── CicloLectivoService.php
            └── 📁CoordinadorFacultad
                ├── ProyeccionSeccionesService.php
            └── 📁SuperAdmin
                ├── CalculadoraEstadoMateriaService.php
    └── 📁bootstrap
        └── 📁cache
            ├── .gitignore
            ├── packages.php
            ├── services.php
        ├── app.php
        ├── providers.php
    └── 📁config
        ├── app.php
        ├── auth.php
        ├── broadcasting.php
        ├── cache.php
        ├── database.php
        ├── excel.php
        ├── filesystems.php
        ├── fortify.php
        ├── logging.php
        ├── mail.php
        ├── queue.php
        ├── reverb.php
        ├── services.php
        ├── session.php
    └── 📁database
        └── 📁factories
            ├── UserFactory.php
        └── 📁migrations
            ├── 0001_01_01_000000_create_users_table.php
            ├── 0001_01_01_000001_create_cache_table.php
            ├── 0001_01_01_000002_create_jobs_table.php
            ├── 2026_08_23_061522_add_two_factor_columns_to_users_table.php
            ├── 2026_08_23_061523_create_passkeys_table.php
            ├── 2026_09_17_000001_create_docentes_table.php
        └── 📁seeders
            ├── ClasesSesionSeeder.php
            ├── DatabaseSeeder.php
            ├── NotasDocenteSeeder.php
            ├── SeccionesDocenteSeeder.php
        ├── .gitignore
        ├── database.sqlite
    └── 📁public
        └── 📁assets
            └── 📁images
                ├── logo-uma-santa-ana.png
        └── 📁build
            └── 📁assets
                ├── app-C-SdqaK1.css
                ├── app-Cd4bo840.js
                ├── bootstrap-icons-BeopsB42.woff
                ├── bootstrap-icons-mSm7cUeB.woff2
                ├── fonts-C9MNnjVw.css
                ├── instrument-sans-400-normal-D1W7dsQl.woff
                ├── instrument-sans-400-normal-DRC__1Mx.woff2
                ├── instrument-sans-500-normal-Dk9ku72i.woff2
                ├── instrument-sans-500-normal-Z6ESRlEs.woff
                ├── instrument-sans-600-normal-B7fBEWYG.woff2
                ├── instrument-sans-600-normal-B9e8oLYv.woff
                ├── toast-Ckrmdojc.css
            ├── fonts-manifest.json
            ├── manifest.json
        └── 📁images
            ├── uma_logo.png
            ├── uma_santa_ana.png
        └── 📁storage
            └── 📁documents
            └── 📁estudiantes
                └── 📁carnet
                    └── 📁32
                        ├── zxHE9ZMxcTCuHmaoPuXjUixIIXWAUmQAHKoFthDk.jpg
                └── 📁imagenes
                    └── 📁12
                        ├── rSJP22UnOghV0kINJh30EhvS5eRdzeHm7IyltTFn.jpg
                    └── 📁15
                        ├── oYZeB1MAA7luBwuI0oTdsKC2WiHhn1YQjwCRWc9O.png
                    └── 📁16
                        ├── DxH8zDjEVbnUf9Ugg8KrvNRZPjqPGRN5mvoWooBJ.png
                    └── 📁32
                        ├── XgNDOiYvIt73C3YTj8gowNdLVQ00YAIKgfm2RlX1.png
                    └── 📁8
                        ├── vq3dKo1uRHIJ3HFu20c4RB5atYIxUknzKPAH7iLY.jpg
            ├── .gitignore
        ├── .htaccess
        ├── favicon.ico
        ├── fonts-manifest.dev.json
        ├── hot
        ├── index.php
        ├── robots.txt
    └── 📁resources
        └── 📁css
            ├── app.css
            ├── pensum.css
            ├── profile.css
            ├── toast.css
        └── 📁js
            ├── app.js
            ├── bootstrap.js
            ├── datatable.js
            ├── echo.js
            ├── roles-modal.js
            ├── toast.js
            ├── validacion-documento.js
        └── 📁views
            └── 📁adfinanciero
                └── 📁cargos
                    ├── create.blade.php
                └── 📁cargosestudiante
                    ├── index.blade.php
                └── 📁ciclosarancel
                    ├── create.blade.php
                    ├── edit.blade.php
                    ├── index.blade.php
                └── 📁conceptospagos
                    ├── create.blade.php
                    ├── edit.blade.php
                    ├── index.blade.php
                └── 📁promociones
                    ├── create.blade.php
                    ├── edit.blade.php
                    ├── index.blade.php
                └── 📁reglas
                    ├── index.blade.php
                └── 📁reportes
                    ├── index.blade.php
            └── 📁adminacademico
                └── 📁pensum
                    ├── edit.blade.php
                    ├── index.blade.php
                    ├── show.blade.php
                └── 📁solicitudes
                    ├── approve-student.blade.php
                    ├── show.blade.php
                ├── ciclolectivo.blade.php
                ├── create-student-massive.blade.php
                ├── create-student.blade.php
                ├── facultad.blade.php
                ├── materias.blade.php
                ├── modify-benefit-student.blade.php
                ├── modify-student.blade.php
                ├── proceso_graduacion.blade.php
                ├── registro_evaluacion_docente.blade.php
                ├── requsitos_egreso.blade.php
                ├── rule_becados.blade.php
                ├── situaciones_reponse.blade.php
            └── 📁auth
                ├── confirm-password.blade.php
                ├── forgot-password.blade.php
                ├── register.blade.php
                ├── reset-password.blade.php
                ├── two-factor-challenge.blade.php
                ├── verify-email.blade.php
            └── 📁cajero
                └── 📁deuda
                    ├── index.blade.php
                └── 📁pagos
                    ├── create.blade.php
                └── 📁promociones
                    ├── index.blade.php
                └── 📁ventanilla
                    ├── index.blade.php
            └── 📁components
                ├── toast-container.blade.php
            └── 📁coordinador-facultad
                └── 📁partials
                    ├── seccion-form-fields.blade.php
                ├── dashboard-pdf.blade.php
                ├── dashboard.blade.php
                ├── horarios-grupohorario.blade.php
                ├── infraestructura-horarios.blade.php
                ├── secciones.blade.php
            └── 📁directivo
                └── 📁matricula
                    ├── index.blade.php
                └── 📁morosidad
                    ├── index.blade.php
                ├── index.blade.php
            └── 📁docente
                ├── asistencia_materia.blade.php
                ├── home_docente.blade.php
                ├── materias_docente.blade.php
                ├── notas_materia.blade.php
                ├── prueba-notas.blade.php
            └── 📁layouts
                ├── app.blade.php
            └── 📁mails
                └── 📁benefits
                    ├── assingbenefit.blade.php
                    ├── reassigned.blade.php
                    ├── revoked.blade.php
                └── 📁cajero
                    ├── recibopago.blade.php
                └── 📁student
                    ├── application-rejected.blade.php
                    ├── new-account-student-credentials.blade.php
                    ├── new-account-student-direct.blade.php
                    ├── retiro_materia.blade.php
                └── 📁users
                    ├── asignacionroles.blade.php
                    ├── credentials.blade.php
            └── 📁recorfinanciero
                ├── record_financiero.blade.php
            └── 📁student
                ├── desempeño_docente.blade.php
                ├── inscripcion.blade.php
                ├── pdf.blade.php
                ├── pensum.blade.php
                ├── record_academico.blade.php
                ├── recursos_bibliograficos.blade.php
                ├── situacion_estudiante.blade.php
            └── 📁superadmin
                ├── createuser.blade.php
                ├── manage-ruler.blade.php
                ├── manageroles.blade.php
                ├── panel-administrativo.blade.php
            ├── about.blade.php
            ├── dashboard.blade.php
            ├── home.blade.php
            ├── profile.blade.php
            ├── welcome.blade.php
    └── 📁routes
        └── 📁modules
            ├── admin-academico.php
            ├── coordinador-facultad.php
            ├── directivo.php
            ├── docente.php
            ├── docentes.php
            ├── student.php
            ├── superadmin.php
        ├── channels.php
        ├── console.php
        ├── web.php
    └── 📁storage
        └── 📁app
            └── 📁private
                └── 📁documentos
                    └── 📁alumnos-ingreso
                        └── 📁12
                            ├── 5SwYNrXF79t8PW0iJz3Xw0bD5WLHVRwNAFyDmeGr.pdf
                            ├── jMQ8YbCdvY5WC5oKDxYou3ib3xzhCZW8owHn9OYT.jpg
                            ├── WdZENvU9sgx8dUrrEOVJ54djea7j9P3ThNGyihQn.jpg
                        └── 📁15
                            ├── nPkN9PbGzRKuCSUhYUPNsEoaJhzZ01P1aixYVYO1.jpg
                            ├── umvJssbPpc8IRu5EGJsDjJXIr5Ai6AyjYEi9NLIS.jpg
                            ├── UVHjMxuL7KMLR71u2sXjlexT6pKCpPbYXOQ3J76G.pdf
                        └── 📁16
                            ├── haX5iPa041J3JKWXkQ1FcSVGpOp8yMHsluuRYgHt.pdf
                        └── 📁32
                            ├── DW3yV5fJdxsdb2VrfHcy9Khue7VJ1iqlsoFTFTod.png
                        └── 📁8
                            ├── b3Y5yb8vHpyWuDPMZSps7oPfKc2SMuhuNT7EZo60.pdf
                            ├── pXKaUtyk7b4Wg5rcziMu3vBQ0yfG0vn3F4hPhIa1.jpg
                            ├── QbtJd7wFqSTJLZusdC6p9lRhEeBaHaCfLMdU23ao.jpg
                    └── 📁proceso-egreso
                        └── 📁16
                            ├── reyMJDBC5DzuLBmuaZZYOVfjkoYEcIPVByrdhbAG.pdf
                        └── 📁30
                            ├── xrNTaDMq7q9WVFUOzUheQMm035RNDmhDHZ41HSL8.pdf
                ├── .gitignore
            └── 📁public
                └── 📁documents
                └── 📁estudiantes
                    └── 📁carnet
                        └── 📁32
                            ├── zxHE9ZMxcTCuHmaoPuXjUixIIXWAUmQAHKoFthDk.jpg
                    └── 📁imagenes
                        └── 📁12
                            ├── rSJP22UnOghV0kINJh30EhvS5eRdzeHm7IyltTFn.jpg
                        └── 📁15
                            ├── oYZeB1MAA7luBwuI0oTdsKC2WiHhn1YQjwCRWc9O.png
                        └── 📁16
                            ├── DxH8zDjEVbnUf9Ugg8KrvNRZPjqPGRN5mvoWooBJ.png
                        └── 📁32
                            ├── XgNDOiYvIt73C3YTj8gowNdLVQ00YAIKgfm2RlX1.png
                        └── 📁8
                            ├── vq3dKo1uRHIJ3HFu20c4RB5atYIxUknzKPAH7iLY.jpg
                ├── .gitignore
            ├── .gitignore
        └── 📁framework
            └── 📁cache
                └── 📁data
                    ├── .gitignore
                └── 📁laravel-excel
                ├── .gitignore
            └── 📁sessions
                ├── .gitignore
            └── 📁testing
                ├── .gitignore
            └── 📁views
                ├── .gitignore
                ├── 01526ec735d8852939d87633523faef1.php
                ├── 0d362cb95bbc53d32e7b2da02d28c165.php
                ├── 10b8872e601ed99200a9f0e25d92b27c.php
                ├── 13480cad0562582f266f1f309349465b.php
                ├── 13f353606a940177abc0596d12f0246a.php
                ├── 14c8a1a8d324efd899931ccc00c5294f.php
                ├── 15a1d79b13624b0abf95601c291f6530.php
                ├── 19ed3c435a87845e8626dcb7e542bae6.php
                ├── 1a783eee82734040217e056e86d3e2a2.php
                ├── 1b7f07a530b77784c203766ca301273c.php
                ├── 1bc7e73f73bcc85c30813978692048b0.php
                ├── 1d300f6cc9791ae7a129e0a3a8515b31.php
                ├── 1f42f8ac3ff3478570071e9d1d080df6.php
                ├── 1f5833386e433e2035b38de480bf561e.php
                ├── 1f883e75848804b462cf0acc67373a67.php
                ├── 20e28198ff4844a5a9bfae62ac824f34.php
                ├── 213de332a81606a37b3acee81c56f127.php
                ├── 21bccb02307f1934af3c61e284640872.php
                ├── 22ca96f90bf3181b16b8d2565ddc08d7.php
                ├── 230e246ce801099e9bb44f91de0f8663.php
                ├── 28a7a111be047650dabfb981875a8965.php
                ├── 2bacde1d417b6cf75b4b27bd628ec1d8.php
                ├── 2d6f3f7234c09e6f69ce49d0d6ca2534.php
                ├── 2fb3773af6104cfd392135e89ac5b621.php
                ├── 31941e685c5a442b04c408ee081f6d2b.php
                ├── 35a3ed8890bf153b37dfaf6fd8b2c0ae.php
                ├── 389ae866fd7969ffc63ba675ce350eb8.php
                ├── 3c8eea0412e5210062fd06e638f63ae3.php
                ├── 3efc6de7074e60d17ceccbffb6ccf7a0.php
                ├── 4042645e9ae4eef5f7544939fec2f33f.php
                ├── 412d5bf1bb26631c1468d85ea9841ae9.php
                ├── 45da28f327774bd05a61ac6493196012.php
                ├── 461ac245edcee37184d7f1f62b927547.php
                ├── 46f990fce4453bd3e2f39f9f96a553e8.php
                ├── 47c385122e9c132d96b89c1dd8ff3b3c.php
                ├── 4cfd834c63bb90c507c61202a553e2b4.php
                ├── 4e30a038b10e8374be122d71ba41bce6.php
                ├── 4f12c4cd085b0d05074f4a06f9605bf5.php
                ├── 4fc41f59f8daa6b19f2c15d86e58f59a.php
                ├── 527ad721160df41f706112a87588fa4e.php
                ├── 53c8072e74b796cfb9bcc5832084c308.php
                ├── 54231454065866a62617bfacf88b2b52.php
                ├── 555da14495043b3a4cee9cee5b1d082c.php
                ├── 5b3117ceb65974d6f77a243380bb6f78.php
                ├── 5e198541ef4c320f6e9232a86372b78b.php
                ├── 5f102be91e66990231d9a8ef5f0afa58.php
                ├── 60be9b1c50732b17533d458833cd0fc6.php
                ├── 637e15e2d47bedbef9acce5ce2c39701.php
                ├── 665980d4dd74c9c4701503697998f8f0.php
                ├── 66f557de76034e553c21372214795614.php
                ├── 67a6948e2ef9d01b2c267ea44292665b.php
                ├── 67dbcc50588c7c95d8bd8afdc1ada722.php
                ├── 6a86428712a9cc614b58597cba06fb63.php
                ├── 6ac8ef5508f0fb362be729f3d922e5df.php
                ├── 7094d91002020e1ecef1445ee2fe6176.php
                ├── 7167D2.tmp
                ├── 716d0cb93a480627af52637c6c930b81.php
                ├── 78fa68c7e57ee21a005146a219288860.php
                ├── 7aad3830910450c31529ac5f1a90a470.php
                ├── 7bd4e04691cde2e7c2a22134d0e4ee87.php
                ├── 7c0e8f81ae32020421a67fc82b6b926f.php
                ├── 7f136cc3c21766128ae4309f8c202a19.php
                ├── 846a8f273e2e12a59864132997bc4fa9.php
                ├── 876eeba7c4f07eb5eef5f89bd009b5bf.php
                ├── 8b0f21c7fb20f2778d73404dc6500e85.php
                ├── 901db82e3a565a95862e76e944e82395.php
                ├── 912f3f8fd8b8c903411c0725906db01a.php
                ├── 93bc5c5e7cdf3cd372d7effb78528bb0.php
                ├── 93f8a3329bbf661676dff46db0dfe608.php
                ├── 93f9a9abf164417104e87cff93577636.php
                ├── 962f83602b10fdd1c3f698f532c9f9e7.php
                ├── 97ad6745d5872c100515567aadcf1ba2.php
                ├── 99da68207a481b2877ffd63541a15275.php
                ├── 9aeb756bb448e56e697f1120de95c1e4.php
                ├── 9af483d1c647601a7ec8a9d50abf3a3d.php
                ├── a0e93789e7b3bba74deef2723805aa89.php
                ├── aa7c13ab9225079bca183fcb064371a0.php
                ├── aa828a88ef6b9800f4c54e219e1fd93d.php
                ├── b1e04190dcf017e9aba5658b05190211.php
                ├── b45d273a6df0e71e4df6305d685f3751.php
                ├── bbf8377d1728c3b44b5787c5781b3ca3.php
                ├── c43902d562b5cb9ece60ddc01d6c8d26.php
                ├── c4c1fd74af55684e24f921ad53d88e3f.php
                ├── c52235ea0fe1ba819737c442f34d7dc9.php
                ├── c6aabac2fede1db5780706281d688757.php
                ├── c8f16efde83176d0631a088ebed99177.php
                ├── cd1ae09dd55976446a639802d96f0334.php
                ├── d2b12860491b0be2304c5b6026bca3ff.php
                ├── d380c157f1ccdaa006f480e3f6ce746d.php
                ├── d4e8ef5ef83102ef301d19b38e5b2f19.php
                ├── d87b23ce642ab9ce5e4f7fb25c97e06a.php
                ├── d94022fad86e7f1079895f88b8893a3c.php
                ├── dbbc49ca1094fdd5725c39a50eb633b4.php
                ├── dbd9cea641b31997b10db8d9ddfb8183.php
                ├── dc4917b800e39c7a2dc70a3f7b58177a.php
                ├── dc9b8ae5b85303fe95f8e44d693a8889.php
                ├── df965d12694e3c2f0a0b692e91a5260a.php
                ├── df99fa4d735d31a1d54e727a3bc4ef3c.php
                ├── e20c95d2a11c64e0fad9a06be87c8eb7.php
                ├── ec596c58df7b7593aa2ce05aad0bd79b.php
                ├── efef83d273fa6e252e857aaa1d63c87f.php
                ├── f520bad616f7f59271bf351f582a0da2.php
                ├── f700f24fe06c88e136dbc2870c1a66e4.php
                ├── f86b8fa0e5733a2c4dbeafbf797c6c50.php
                ├── fabd86a0987d5118619effe4c39262c9.php
                ├── fc364313f1451d8963cb31704678daa6.php
                ├── fc8d6e5e2bd31f2a46349376120105af.php
            ├── .gitignore
        └── 📁logs
            ├── .gitignore
            ├── laravel-2026-09-26.log
            ├── laravel-2026-09-27.log
            ├── laravel-2026-09-28.log
            ├── laravel-2026-10-07.log
            ├── laravel.log
    └── 📁tests
        └── 📁Feature
            ├── ExampleTest.php
        └── 📁Unit
            ├── ExampleTest.php
        ├── TestCase.php
    └── 📁vendor
    ├── .editorconfig
    ├── .env
    ├── .gitattributes
    ├── .gitignore
    ├── .npmrc
    ├── artisan
    ├── composer.json
    ├── composer.lock
    ├── package-lock.json
    ├── package.json
    ├── phpunit.xml
    ├── README.md
    └── vite.config.js
```
