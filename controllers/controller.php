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

    define(
        'CONTROLLER_CORE',
        true
    );

}


/* ==========================================================
   INICIALIZAR CONTROLADOR
========================================================== */

require_once __DIR__ . '/../config/bootstrap.php';


function controllerInit(): void
{
    /*
     * Función de compatibilidad.
     *
     * El bootstrap ya inicializa
     * el entorno principal.
     */
}


/* ==========================================================
   VALIDAR REQUEST
========================================================== */

function controllerRequireMethod(
    string $method = 'POST'
): void {

    $requestMethod = strtoupper(
        $_SERVER['REQUEST_METHOD']
        ?? ''
    );


    if (
        $requestMethod !==
        strtoupper($method)
    ) {

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

            (string)(

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
    $action =
        controllerAction();


    if (
        $action === ''
    ) {

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

    if (
        trim($permission) === ''
    ) {

        throw new InvalidArgumentException(
            'Permiso inválido.'
        );

    }


    if (
        !tienePermiso($permission)
    ) {

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


    if (
        $connection instanceof PDO
    ) {

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


    $connection =
        $pdo;


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
    string $message =
        'Operación realizada correctamente.'
): array {

    return [

        'type' =>
            'success',

        'message' =>
            $message

    ];
}


/* ==========================================================
   RESPUESTA DE ERROR
========================================================== */

function controllerError(
    string $message
): array {

    return [

        'type' =>
            'error',

        'message' =>
            $message

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

        'redirect' =>
            $redirect,

        'type' =>
            $type,

        'message' =>
            $message

    ];
}


/* ==========================================================
   DETECTAR PETICIÓN JSON / AJAX
========================================================== */

function controllerIsJsonRequest(): bool
{
    $requestedWith =
        strtolower(

            trim(

                $_SERVER[
                    'HTTP_X_REQUESTED_WITH'
                ]
                ?? ''

            )

        );


    $accept =
        strtolower(

            $_SERVER['HTTP_ACCEPT']
            ?? ''

        );


    return (

        $requestedWith ===
        'xmlhttprequest'

    )

    ||

    str_contains(

        $accept,

        'application/json'

    );
}


/* ==========================================================
   RESPUESTA JSON
========================================================== */

function controllerJson(

    bool $success,

    string $message = '',

    array $data = []

): never {

    if (
        !headers_sent()
    ) {

        http_response_code(

            $success
                ? 200
                : 400

        );


        header(
            'Content-Type: application/json; charset=utf-8'
        );

    }


    echo json_encode(

        array_merge(

            [

                'success' =>
                    $success,

                'message' =>
                    $message,

                // El token CSRF ya fue regenerado por
                // validarCsrf() antes de llegar aquí. Se
                // reenvía para que el JavaScript actualice
                // window.CSRF_TOKEN y la siguiente petición
                // AJAX de la misma página no falle por token
                // desactualizado.
                'csrf_token' =>
                    function_exists('generarCsrf')
                        ? generarCsrf()
                        : ($_SESSION['csrf_token'] ?? null),

            ],

            $data

        ),

        JSON_UNESCAPED_UNICODE
        |
        JSON_UNESCAPED_SLASHES

    );


    exit;
}


/* ==========================================================
   EJECUTAR CONTROLLER
========================================================== */

function controllerRun(

    array $actions,

    array $options = []

): void {

    if (
        empty($actions)
    ) {

        throw new LogicException(
            'No existen acciones registradas.'
        );

    }


    $redirect =
        $options['redirect']
        ?? '../index.php';


    $method =
        strtoupper(

            $options['method']
            ?? 'POST'

        );


    $csrf =
        (bool)(

            $options['csrf']
            ?? true

        );


    try {

        /* ======================================
           VALIDAR REQUEST
        ====================================== */

        controllerRequireMethod(
            $method
        );


        /* ======================================
           VALIDAR CSRF
        ====================================== */

        if (
            $csrf
        ) {

            controllerValidateCsrf();

        }


        /* ======================================
           OBTENER ACTION
        ====================================== */

        $action =
            controllerRequireAction();


        /* ======================================
           VALIDAR ACTION
        ====================================== */

        if (
            !isset(
                $actions[$action]
            )
        ) {

            throw new Exception(

                "La acción '{$action}' no existe."

            );

        }


        /* ======================================
           EJECUTAR ACCIÓN
        ====================================== */

        $response =
            controllerExecute(

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


    /* ======================================================
       ERROR PDO
    ====================================================== */

catch (PDOException $e) {

    controllerRollback();

    controllerLog($e);

    if (controllerIsJsonRequest()) {

        controllerJson(
            false,
            $e->getMessage()
        );

    }

    redirect(
        $redirect,
        'error',
        $e->getMessage()
    );
}

    /* ======================================================
       ERROR GENERAL
    ====================================================== */

    catch (Throwable $e) {

        controllerRollback();

        controllerLog($e);


        if (
            controllerIsJsonRequest()
        ) {

            controllerJson(

                false,

                $e->getMessage()

            );

        }


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


    /* ======================================================
       RESPUESTA JSON / AJAX
    ====================================================== */

    if (
        controllerIsJsonRequest()
    ) {


        /* --------------------------------------------------
           ARRAY
        -------------------------------------------------- */

        if (
            is_array($response)
        ) {

            controllerJson(

                true,

                $response['message']
                    ??
                    'Operación realizada correctamente.',

                $response['data']
                    ??
                    []

            );

        }


        /* --------------------------------------------------
           TRUE
        -------------------------------------------------- */

        if (
            $response === true
        ) {

            controllerJson(

                true,

                'Operación realizada correctamente.'

            );

        }


        /* --------------------------------------------------
           FALSE
        -------------------------------------------------- */

        if (
            $response === false
        ) {

            controllerJson(

                false,

                'La operación no pudo completarse.'

            );

        }


        /* --------------------------------------------------
           STRING
        -------------------------------------------------- */

        if (
            is_string($response)
        ) {

            controllerJson(

                true,

                $response

            );

        }


        /* --------------------------------------------------
           NULL / DEFAULT
        -------------------------------------------------- */

        controllerJson(

            true,

            'Operación realizada correctamente.'

        );

    }


    /* ======================================================
       RESPUESTA NORMAL DEL SISTEMA
    ====================================================== */


    /* ------------------------------------------------------
       NULL
    ------------------------------------------------------ */

    if (
        $response === null
    ) {

        redirect(

            $defaultRedirect,

            'success',

            'Operación realizada correctamente.'

        );

        return;

    }


    /* ------------------------------------------------------
       TRUE
    ------------------------------------------------------ */

    if (
        $response === true
    ) {

        redirect(

            $defaultRedirect,

            'success',

            'Operación realizada correctamente.'

        );

        return;

    }


    /* ------------------------------------------------------
       FALSE
    ------------------------------------------------------ */

    if (
        $response === false
    ) {

        throw new Exception(

            'La operación no pudo completarse.'

        );

    }


    /* ------------------------------------------------------
       STRING
    ------------------------------------------------------ */

    if (
        is_string($response)
    ) {

        redirect(

            $defaultRedirect,

            'success',

            $response

        );

        return;

    }


    /* ------------------------------------------------------
       ARRAY
    ------------------------------------------------------ */

    if (
        is_array($response)
    ) {

        redirect(

            $response['redirect']
                ??
                $defaultRedirect,

            $response['type']
                ??
                'success',

            $response['message']
                ??
                'Operación realizada correctamente.'

        );

        return;

    }


    /* ------------------------------------------------------
       RESPUESTA INVÁLIDA
    ------------------------------------------------------ */

    throw new Exception(

        'El Controller devolvió una respuesta no válida.'

    );

}


/* ==========================================================
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


    if (
        $pdo->inTransaction()
    ) {

        $pdo->rollBack();

    }

}


/* ==========================================================
   LOG DE ERRORES
========================================================== */

function controllerLog(
    Throwable $e
): void {

    $usuario =
        $_SESSION['usuario']['id']
        ?? 'Invitado';


    $action =
        $_POST['action']
        ??
        $_GET['action']
        ??
        '-';


    $ip =
        $_SERVER['REMOTE_ADDR']
        ??
        'Desconocida';


    error_log(

        sprintf(

            '[%s] %s | Usuario: %s | Acción: %s | IP: %s | Archivo: %s | Línea: %d | %s',

            date(
                'Y-m-d H:i:s'
            ),

            get_class($e),

            $usuario,

            $action,

            $ip,

            basename(
                $e->getFile()
            ),

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