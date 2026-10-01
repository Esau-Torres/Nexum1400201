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

git clone https://github.com/tu-organizacion/nexum.git
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
## Arquitectura del proyecto

```
└── 📁nexum
    └── 📁app
        └── 📁Actions
            └── 📁AdminAcademic
                ├── ApproveStudentApplicationAction.php
                ├── AssignStudentBenefitAction.php
                ├── CreateActiveStudentAction.php
                ├── ReassignStudentBenefitAction.php
                ├── RejectStudentApplicationAction.php
                ├── RevokeStudentBenefitAction.php
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
                ├── CreateStudentProfileAction.php
                ├── UploadStudentDocumentsAction.php
            └── 📁Teacher
                ├── CreateDocenteProfileAction.php
                ├── SyncDocenteProfileAction.php
            └── 📁User
                ├── AssignUserRoleAction.php
                ├── CreateUserAccountAction.php
                ├── UpdateUserPersonalDataAction.php
        └── 📁Enums
            ├── EstadoBeneficio.php
            ├── TipoBeneficio.php
        └── 📁Http
            └── 📁Controllers
                └── 📁Adfinanciero
                    ├── AdminfinancieroController.php
                └── 📁AdminAcademico
                    ├── ActiveStudentController.php
                    ├── StudentApplicationController.php
                    ├── StudentBenefitController.php
                └── 📁Cajero
                    ├── CajeroController.php
                └── 📁Docente
                    ├── PruebaNotasController.php
                └── 📁Estudiante
                    ├── EstudianteController.php
                    ├── registroController.php
                └── 📁Reglaspago
                    ├── ReglasController.php
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
        └── 📁Mail
            ├── AssingRoleUsersMail.php
            ├── BenefitReassignedMail.php
            ├── BenefitRevokedMail.php
            ├── NewAccountCredentialsMail.php
            ├── NewAccountStudentCredentialsMail.php
            ├── ReciboPagoMail.php
            ├── StudentApplicationRejectedMail.php
        └── 📁Models
            └── 📁Academico
                ├── BeneficioEstudiante.php
                ├── CargoEstudiante.php
                ├── CicloLectivo.php
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
            └── 📁Docente
                ├── Docentes.php
            └── 📁Estudiante
                ├── AlumnoDocumentos.php
                ├── Alumnos.php
                ├── Carreras.php
                ├── Facultades.php
            └── 📁Users
                ├── RegionalActivo.php
                ├── Role.php
                ├── TipoDocumentoIdentidad.php
                ├── User.php
        └── 📁Providers
            ├── AppServiceProvider.php
            ├── FortifyServiceProvider.php
        └── 📁Rules
            ├── ApproveStudentRules.php
            ├── AssignRolesToUserRule.php
            ├── codigoEstudiante.php
            ├── DocenteSyncRule.php
            ├── GeneradorCodigoDocente.php
            ├── ReassignBenefitRules.php
            ├── RejectStudentRules.php
            ├── RevokeBenefitRules.php
            ├── StoreActiveStudentRules.php
            ├── StoreUserRule.php
            ├── UpdateRoleStatusRule.php
            ├── UpdateUserPersonalDataRule.php
            ├── ValidarDocumentoIdentidad.php
        └── 📁Services
            └── 📁Beneficios
                ├── EvaluarReglasBeneficioService.php
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
            ├── DatabaseSeeder.php
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
                └── 📁imagenes
                    └── 📁12
                        ├── rSJP22UnOghV0kINJh30EhvS5eRdzeHm7IyltTFn.jpg
                    └── 📁13
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
                └── 📁solicitudes
                    ├── approve-student.blade.php
                    ├── show.blade.php
                ├── create-student.blade.php
                ├── modify-benefit-student.blade.php
                ├── modify-student.blade.php
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
                    ├── reassigned.blade.php
                    ├── revoked.blade.php
                └── 📁cajero
                    ├── recibopago.blade.php
                └── 📁student
                    ├── application-rejected.blade.php
                    ├── new-account-student-credentials.blade.php
                └── 📁users
                    ├── asignacionroles.blade.php
                    ├── credentials.blade.php
            └── 📁recorfinanciero
                ├── record_financiero.blade.php
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
            ├── docentes.php
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
                            ├── hCnr2EMxzL3FztwskMsFDM4hpd9RGQqX169ZI1w4.jpg
                            ├── s01EpvPCWDyc5i1zpgXxaKXqkMHl0dhrA7G1JUuk.jpg
                        └── 📁13
                        └── 📁8
                            ├── b3Y5yb8vHpyWuDPMZSps7oPfKc2SMuhuNT7EZo60.pdf
                            ├── pXKaUtyk7b4Wg5rcziMu3vBQ0yfG0vn3F4hPhIa1.jpg
                            ├── XwjvOO2k1hzu5GR4qYoXsdhLLvnaQ58oJv0IbR5B.jpg
                ├── .gitignore
            └── 📁public
                └── 📁documents
                └── 📁estudiantes
                    └── 📁imagenes
                        └── 📁12
                            ├── rSJP22UnOghV0kINJh30EhvS5eRdzeHm7IyltTFn.jpg
                        └── 📁13
                        └── 📁8
                            ├── vq3dKo1uRHIJ3HFu20c4RB5atYIxUknzKPAH7iLY.jpg
                ├── .gitignore
            ├── .gitignore
        └── 📁logs
            ├── .gitignore
            ├── laravel-2026-09-26.log
            ├── laravel-2026-09-27.log
            ├── laravel-2026-09-28.log
            ├── laravel.log
  
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