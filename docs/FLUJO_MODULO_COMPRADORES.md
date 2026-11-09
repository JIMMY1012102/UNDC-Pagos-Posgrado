# Compradores: flujo de frontend, backend y base de datos

Fecha: 9 de noviembre de 2026.

El módulo permite registrar compradores, iniciar sesión con contraseña o Google y completar su perfil antes de continuar al carrito.

**Validación reportada por el responsable del proyecto:** registro, login y perfil probados como usuario; se comprobó la creación de cuentas en `users`. Esto no constituye una validación de pagos, beneficiarios ni de todos los escenarios de error. Esta documentación describe el código revisado; no implica una ejecución nueva de pruebas ni una inspección directa del servidor.

## Cómo leer los flujos

Las rutas de archivos parten de la raíz del repositorio. Azul representa interfaz; morado, controladores; verde, servicios; amarillo, tablas.

El navegador envía solicitudes HTTP (HTTPS cuando el sitio está configurado para ello). Los formularios web de registro, login y perfil usan POST con protección CSRF y reciben redirecciones; no todas las solicitudes son JSON. Las API del perfil sí responden JSON. Dentro de Laravel, controlador y servicio se comunican mediante llamadas PHP.

Laravel recibe las solicitudes mediante `public/index.php`, carga su configuración y aplica los middleware correspondientes. `routes/web.php` incorpora `app/Modules/Compradores/routes.php`; Laravel busca allí la ruta que debe atender la solicitud.

## 1. Registro con contraseña

```mermaid
flowchart TD
    A["Formulario de registro: resources/views/modules/acceso/register.blade.php"]
    --> B["POST /tienda/registro: app/Modules/Compradores/routes.php"]
    B --> C["AccountController::register(): app/Modules/Compradores/Http/Controllers/AccountController.php"]
    C --> D{"¿Datos válidos?"}
    D -->|No| E["Volver al formulario con errores"]
    D -->|Sí| F["Crear cuenta con User: app/Models/User.php"]
    F --> U[("users")]
    F --> G["BuyerProfileService::save(): app/Modules/Compradores/Services/BuyerProfileService.php"]
    G --> P[("tienda_perfiles_compradores")]
    G --> H["AccountController: iniciar sesión y regenerarla"]
    H --> Z["Carrito: resources/views/modules/tienda/cart.blade.php"]
    classDef screen fill:#e0f2fe,stroke:#0284c7,color:#0c4a6e
    classDef controller fill:#ede9fe,stroke:#7c3aed,color:#4c1d95
    classDef service fill:#dcfce7,stroke:#16a34a,color:#14532d
    classDef database fill:#fef3c7,stroke:#d97706,color:#78350f
    class A,E,Z screen
    class B,C,D,F,H controller
    class G service
    class U,P database
```

1. El controlador normaliza el correo y valida datos personales, correo único y contraseña confirmada de al menos 12 caracteres.
2. `AccountController::register()` crea directamente el usuario mediante el modelo `User`, que almacena la contraseña como hash.
3. Llama a `BuyerProfileService::save()` para guardar el perfil.
4. Cuenta y perfil se crean dentro de una transacción: un fallo revierte las escrituras de esa operación.
5. El controlador inicia sesión, regenera la sesión y redirige a `/tienda/carrito`.

Si ya existe una sesión, el registro redirige a `/tienda/perfil`.

## 2. Login con contraseña

| Paso | Archivo y función | Resultado |
|---|---|---|
| Formulario | `resources/views/modules/acceso/login.blade.php` | POST a `/tienda/login` con CSRF. |
| Ruta | `app/Modules/Compradores/routes.php` | Ejecuta `AccountController::login()`. |
| Autenticación | `app/Modules/Compradores/Http/Controllers/AccountController.php` | Valida los campos y usa `Auth::guard('web')->attempt()` para autenticar la cuenta de `users`. |
| Error | Mismo controlador | Regresa al formulario con error de credenciales. |
| Éxito | Mismo controlador | Regenera la sesión y consulta `BuyerProfileService::isComplete()`. |
| Destino | Perfil completo: carrito; incompleto: perfil | Redirige a `/tienda/carrito` o `/tienda/perfil`. |

## 3. Acceso con Google y perfil

```mermaid
flowchart TD
    A["Login: resources/views/modules/acceso/login.blade.php"]
    --> B["GET /auth/google/redirect: app/Modules/Compradores/routes.php"]
    B --> C["GoogleAuthController::redirect(): app/Modules/Compradores/Http/Controllers/GoogleAuthController.php"]
    C --> D["Google autentica al usuario"]
    D --> E["GET /auth/google/callback: app/Modules/Compradores/routes.php"]
    E --> F["GoogleAuthController::callback(): app/Modules/Compradores/Http/Controllers/GoogleAuthController.php"]
    F --> G["GoogleIdentityService::resolve(): app/Modules/Compradores/Services/GoogleIdentityService.php"]
    G --> U[("users")]
    G --> O[("tienda_identidades_oauth")]
    G --> H["Controlador inicia sesión y regenera la sesión"]
    H --> I["BuyerProfileService::isComplete(): app/Modules/Compradores/Services/BuyerProfileService.php"]
    I --> J{"¿Perfil completo?"}
    J -->|Sí| K["Carrito: resources/views/modules/tienda/cart.blade.php"]
    J -->|No| L["Perfil: resources/views/modules/acceso/profile.blade.php"]
    L --> M["POST /tienda/perfil: AccountController::saveProfile()"]
    M --> N["BuyerProfileService::save(): app/Modules/Compradores/Services/BuyerProfileService.php"]
    N --> P[("tienda_perfiles_compradores")]
    N --> K
    classDef screen fill:#e0f2fe,stroke:#0284c7,color:#0c4a6e
    classDef controller fill:#ede9fe,stroke:#7c3aed,color:#4c1d95
    classDef service fill:#dcfce7,stroke:#16a34a,color:#14532d
    classDef database fill:#fef3c7,stroke:#d97706,color:#78350f
    class A,D,K,L screen
    class B,C,E,F,H,J,M controller
    class G,I,N service
    class U,O,P database
```

- Socialite gestiona la redirección y el callback OAuth con estado. Google debe estar habilitado y configurado; el controlador exige HTTPS.
- `resolve()` verifica el identificador del proveedor y el correo verificado. Busca por proveedor e identificador de Google.
- Si la identidad existe, recupera el usuario y comprueba coherencia del correo.
- Si es nueva y no hay conflicto, crea `users` y `tienda_identidades_oauth` dentro de una transacción.
- No vincula automáticamente una identidad nueva a una cuenta existente solo por coincidencia de correo. Los errores vuelven al login con un mensaje.
- El controlador conserva nombres de Google en sesión para completar el formulario y comprueba el perfil.
- `saveProfile()` valida datos, llama al servicio, limpia el prellenado de sesión y redirige al carrito.
- Google no acredita DNI, matrícula, condición de estudiante ni permisos internos. El registro no concede roles.

## 4. Perfil mediante API

| Método y dirección | Función en BuyerProfileController | Servicio |
|---|---|---|
| GET `/api/v1/acceso/estado` | `state()` | `isComplete()` y `find()`. |
| GET `/api/v1/acceso/perfil` | `show()` | `find()`. |
| PUT `/api/v1/acceso/perfil` | `update()` | Valida y llama a `save()`. |

Controlador: `app/Modules/Compradores/Http/Controllers/BuyerProfileController.php`.

Servicio: `app/Modules/Compradores/Services/BuyerProfileService.php`.

Estas rutas requieren sesión autenticada y tienen límite de solicitudes. La identidad del comprador proviene de la sesión, no de un `user_id` enviado por el navegador. El frontend puede consumirlas desde `public/js/undc-tienda.js`. El controlador convierte los resultados PHP en JSON y JavaScript actualiza la interfaz.

## 5. Tablas del módulo

```mermaid
erDiagram
    users ||--o| tienda_perfiles_compradores : "tiene perfil"
    users ||--o{ tienda_identidades_oauth : "tiene identidades"
    tienda_perfiles_compradores ||--o{ tienda_beneficiarios : "tiene beneficiarios"
```

| Tabla | Responsabilidad | Alcance del código documentado |
|---|---|---|
| `users` | Cuenta del comprador y credenciales. | Registro y autenticación. |
| `tienda_perfiles_compradores` | Datos personales y contacto. | Consulta, guardado y comprobación de completitud. |
| `tienda_identidades_oauth` | Vínculo entre usuario y proveedor Google. | Resolución de identidades. |
| `tienda_beneficiarios` | Beneficiarios asociados al perfil. | Estructura preparada; no se documenta como flujo funcional implementado. |

Las tablas viven en la base de datos configurada del servidor, no en carpetas PHP. Sus definiciones están en `database/migrations/`: migración inicial de usuarios, `2026_10_25_000001_create_undc_identity_and_catalog_tables.php` y renombrado incremental `2026_11_09_000001_rename_store_business_tables_to_spanish.php`.

Los compradores de `users` son distintos del personal histórico de `tm_usuario`.

## 6. Límite del módulo

Compradores deja al usuario autenticado y con su perfil guardado. No crea pedidos ni confirma pagos. La redirección al carrito continúa en Pedidos.

[Volver al módulo Compradores](../app/Modules/Compradores/README.md) · [Modelo de datos de Compradores](./modelo-datos/03_COMPRADORES.md)
