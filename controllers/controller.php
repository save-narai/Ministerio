<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CONTROLLER CORE
|--------------------------------------------------------------------------
|
| Núcleo compartido por todos los controllers del sistema.
|
| Responsabilidades:
|
| • Inicializar el entorno.
| • Validar Request.
| • Validar CSRF.
| • Despachar Actions.
| • Gestionar respuestas.
| • Gestionar excepciones.
| • Gestionar Rollback.
|
| No contiene lógica de negocio.
|
*/

if (!defined('CONTROLLER_CORE')) {

    define('CONTROLLER_CORE', true);

}

/* ==========================================================
   INICIALIZAR CONTROLADOR
========================================================== */

require_once __DIR__ . '/../config/bootstrap.php';

function controllerInit(): void
{
    // Compatibilidad.
}

/* ==========================================================
   VALIDAR REQUEST
========================================================== */

function controllerRequireMethod(
    string $method = 'POST'
): void {

    $requestMethod = strtoupper(
        $_SERVER['REQUEST_METHOD'] ?? ''
    );

    if ($requestMethod !== strtoupper($method)) {

        throw new Exception(
            'Acceso inválido.'
        );

    }

}

/* ==========================================================
   VALIDAR CSRF
========================================================== */
function controllerValidateCsrf(): void
{
    validarCsrf();
}

/* ==========================================================
   OBTENER ACTION
========================================================== */

function controllerAction(): string
{
    return strtolower(

        trim(

            (string) (

                $_POST['action']

                ??

                $_GET['action']

                ??

                ''

            )

        )

    );
}

/* ==========================================================
   VALIDAR ACTION
========================================================== */

function controllerRequireAction(): string
{
    $action = controllerAction();

    if ($action === '') {

        throw new Exception(
            'Acción no especificada.'
        );

    }

    return $action;
}

/* ==========================================================
   EXIGIR PERMISO
========================================================== */

function controllerRequirePermission(
    string $permission
): void {

    if (trim($permission) === '') {

        throw new InvalidArgumentException(
            'Permiso inválido.'
        );

    }

    if (!tienePermiso($permission)) {

        throw new RuntimeException(
            'Acceso denegado.'
        );

    }

}

/* ==========================================================
   OBTENER CONEXIÓN PDO
========================================================== */

function controllerPdo(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {

        return $connection;

    }

    global $pdo;

    if (

        !isset($pdo)

        ||

        !($pdo instanceof PDO)

    ) {

        throw new Exception(
            'No existe conexión con la base de datos.'
        );

    }

    $connection = $pdo;

    return $connection;
}

/* ==========================================================
   EJECUTAR CALLBACK
========================================================== */

function controllerExecute(
    callable $callback
): mixed {

    return $callback();

}

/* ==========================================================
   RESPUESTA EXITOSA
========================================================== */

function controllerSuccess(
    string $message = 'Operación realizada correctamente.'
): array {

    return [

        'type' => 'success',

        'message' => $message

    ];

}

/* ==========================================================
   RESPUESTA DE ERROR
========================================================== */

function controllerError(
    string $message
): array {

    return [

        'type' => 'error',

        'message' => $message

    ];

}

/* ==========================================================
   RESPUESTA CON REDIRECCIÓN
========================================================== */

function controllerRedirect(

    string $redirect,

    string $message,

    string $type = 'success'

): array {

    return [

        'redirect' => $redirect,

        'type' => $type,

        'message' => $message

    ];

}                                                                                                                         /* ==========================================================
   EJECUTAR CONTROLLER
========================================================== */

function controllerRun(
    array $actions,
    array $options = []
): void {

    if (empty($actions)) {

        throw new LogicException(
            'No existen acciones registradas.'
        );

    }

    $redirect = $options['redirect']
        ?? '../index.php';

    $method = strtoupper(

        $options['method']
        ?? 'POST'

    );

    $csrf = (bool) (

        $options['csrf']
        ?? true

    );

    try {

        /* ======================================
           VALIDAR REQUEST
        ====================================== */

        controllerRequireMethod($method);

        /* ======================================
           VALIDAR CSRF
        ====================================== */

        if ($csrf) {

            controllerValidateCsrf();

        }

        /* ======================================
           OBTENER ACTION
        ====================================== */

        $action = controllerRequireAction();

        /* ======================================
           VALIDAR ACTION
        ====================================== */

        if (!isset($actions[$action])) {

            throw new Exception(
                "La acción '{$action}' no existe."
            );

        }

        /* ======================================
           EJECUTAR ACCIÓN
        ====================================== */

        $response = controllerExecute(
            $actions[$action]
        );

        /* ======================================
           PROCESAR RESPUESTA
        ====================================== */

        controllerResponse(
            $response,
            $redirect
        );

    }

    catch (PDOException $e) {

        controllerRollback();

        controllerLog($e);

        redirect(

            $redirect,

            'error',

            'Ocurrió un error interno del sistema.'

        );

    }

    catch (Throwable $e) {

        controllerRollback();

        controllerLog($e);

        redirect(

            $redirect,

            'error',

            $e->getMessage()

        );

    }

}

/* ==========================================================
   RESPUESTA DEL CONTROLLER
========================================================== */

function controllerResponse(

    mixed $response,

    string $defaultRedirect

): void {

    if ($response === null) {

        redirect(

            $defaultRedirect,

            'success',

            'Operación realizada correctamente.'

        );

        return;

    }

    if ($response === true) {

        redirect(

            $defaultRedirect,

            'success',

            'Operación realizada correctamente.'

        );

        return;

    }

    if ($response === false) {

        throw new Exception(
            'La operación no pudo completarse.'
        );

    }

    if (is_string($response)) {

        redirect(

            $defaultRedirect,

            'success',

            $response

        );

        return;

    }

    if (is_array($response)) {

        redirect(

            $response['redirect']
                ?? $defaultRedirect,

            $response['type']
                ?? 'success',

            $response['message']
                ?? 'Operación realizada correctamente.'

        );

        return;

    }

    throw new Exception(
        'El Controller devolvió una respuesta no válida.'
    );

}                                                                                                                                    /* ==========================================================
   ROLLBACK AUTOMÁTICO
========================================================== */

function controllerRollback(): void
{
    global $pdo;

    if (

        !isset($pdo)

        ||

        !($pdo instanceof PDO)

    ) {

        return;

    }

    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }

}

/* ==========================================================
   LOG DE ERRORES
========================================================== */

function controllerLog(
    Throwable $e
): void {

    $usuario = $_SESSION['usuario']['id']
        ?? 'Invitado';

    $action = $_POST['action']
        ?? $_GET['action']
        ?? '-';

    $ip = $_SERVER['REMOTE_ADDR']
        ?? 'Desconocida';

    error_log(

        sprintf(

            '[%s] %s | Usuario: %s | Acción: %s | IP: %s | Archivo: %s | Línea: %d | %s',

            date('Y-m-d H:i:s'),

            get_class($e),

            $usuario,

            $action,

            $ip,

            basename($e->getFile()),

            $e->getLine(),

            $e->getMessage()

        )

    );

    if (

        defined('APP_DEBUG')

        &&

        APP_DEBUG

    ) {

        error_log(

            $e->getTraceAsString()

        );

    }

}