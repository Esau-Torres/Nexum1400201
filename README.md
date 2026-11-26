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
| 1  | Super Administrador          | davidesau140\@gmail.com          | `User1234`  |
| 2  | Administrador académico      | rrf2kp8eqk\@olipii.com           | `User1234`  |
| 3  | Coordinador de facultad      | ev9u65lhlp\@bwmyga.com           | `User1234`  |
| 4  | Docente                      | powvu6ilbs\@ozsaip.com           | `User1234`  |
| 5  | Directivo                    | kvrs1dql22\@bwmyga.com           | `User1234`  |
| 6  | Administrador Financiero     | prsj5367\@gmail.com              | `User1234`  |
| 7  | Cajero                       | 552pko38u8\@ruutukf.com          | `User1234`  |
| 8  | Alumno Egresado Maestría     | cg224100282\@uma.edu.sv          | `User1234`  |
| 9  | Alumno Egresado Licenciatura | mr224100122\@uma.edu.sv          | `User1234`  |
| 10 | Alumno Becado                | angelamarcelatorres30\@gmail.com | `User1234`  |

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
                ├── ReactivarExpedienteAction.php
                ├── ReassignStudentBenefitAction.php
                ├── RegistrarCambioCarreraAction.php
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
                ├── CorregirCalificacionAction.php
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
            ├── EstadoEstudiante.php
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
                    ├── CambioCarreraController.php
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
                    ├── CorreccionNotasController.php
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
                    ├── EstadoCuentaController.php
                    ├── EstudianteController.php
                    ├── FotoPerfilController.php
                    ├── InscripcionController.php
                    ├── MisNotasController.php
                    ├── PensumController.php
                    ├── RecordAcademicoController.php
                    ├── RegistroController.php
                    ├── RetirarMateriaController.php
                    ├── SituacionController.php
                └── 📁Reglaspago
                    ├── ReglasController.php
                └── 📁SuperAdmin
                    ├── ConfiguracionAcademicaController.php
                └── 📁Users
                    ├── CreateUserController.php
                    ├── HomeController.php
                    ├── ModifyUserController.php
                    ├── RoleManagementController.php
                ├── Controller.php
            └── 📁Middleware
                ├── CheckRole.php
            └── 📁Responses
                ├── LoginResponse.php
        └── 📁Imports
            ├── RowsPreviewImport.php
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
                ├── CargoEstudiante.php
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
                ├── PuedeCursarMateriaRule.php
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
            ├── CodigoEstudiante.php
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
                ├── app-CJd4w3xS.js
                ├── app-D9fYEWhC.css
                ├── bootstrap-icons-BeopsB42.woff
                ├── bootstrap-icons-mSm7cUeB.woff2
                ├── cambiocarrera-C4-D1ua3.css
                ├── evaluaciones-docentes-DK8TskPo.css
                ├── fonts-C9MNnjVw.css
                ├── instrument-sans-400-normal-D1W7dsQl.woff
                ├── instrument-sans-400-normal-DRC__1Mx.woff2
                ├── instrument-sans-500-normal-Dk9ku72i.woff2
                ├── instrument-sans-500-normal-Z6ESRlEs.woff
                ├── instrument-sans-600-normal-B7fBEWYG.woff2
                ├── instrument-sans-600-normal-B9e8oLYv.woff
                ├── notas-estudiante-DakdG8hy.css
                ├── pensum-88cf8k3S.css
                ├── profile-vufZ8yE1.css
                ├── reglas-academicas-DBY9nFMK.css
                ├── roles-permisos-Bdy-WrZH.css
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
                    └── 📁34
                        ├── D81cDEcvQnUwZVPSh5SsOczTqbNxOmC8QXdyWUIV.jpg
                    └── 📁35
                        ├── czkTW0uQOgU42sZxunBTBS3o3oF77B1dQjlaziJ6.jpg
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
            ├── cambiocarrera.css
            ├── correccion-notas.css
            ├── evaluaciones-docentes.css
            ├── notas-estudiante.css
            ├── pensum.css
            ├── profile.css
            ├── reglas-academicas.css
            ├── roles-permisos.css
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
                ├── cambio-carrera.blade.php
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
                ├── correccion-notas.blade.php
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
                ├── solicitud_cambio_nota.blade.php
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
                └── 📁cargosestudiante
                    ├── index.blade.php
                ├── desempeño_docente.blade.php
                ├── inscripcion.blade.php
                ├── notas_student.blade.php
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
                        └── 📁34
                            ├── C5RHMIlPZVWjiirFWalmupEuxuuAX5vcAJumEdWs.pdf
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
                        └── 📁34
                            ├── D81cDEcvQnUwZVPSh5SsOczTqbNxOmC8QXdyWUIV.jpg
                        └── 📁35
                            ├── czkTW0uQOgU42sZxunBTBS3o3oF77B1dQjlaziJ6.jpg
                        └── 📁8
                            ├── vq3dKo1uRHIJ3HFu20c4RB5atYIxUknzKPAH7iLY.jpg
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

## COLABORADORES

ESAU-TORRES: Scrum Master, desarrollador DEVOPS, Tech Lead. 
LEANDRO-ANTONIO: QA, desarrollador.
FERNANDO-MANUEL: desarrollador DEVOPS, Administrador de base de datos.