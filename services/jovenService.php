<?php

declare(strict_types=1);

require_once __DIR__ . '/actividadService.php';
require_once __DIR__ . '/../middleware/permiso.php';

/*
|--------------------------------------------------------------------------
| Joven Service
|--------------------------------------------------------------------------
|
| Servicio encargado de la gestión de jóvenes.
|
*/

/* ==========================================================
   CONSTANTES
========================================================== */

const GENEROS = [

    'M',

    'F'

];

const ESTADOS_ESPIRITUALES = [

    'NUEVO',

    'CONGREGANTE',

    'DISCIPULADO',

    'SERVIDOR',

    'LIDER'

];

const ESTADOS_ACTIVIDAD = [

    'ACTIVO',

    'INACTIVO',

    'ELIMINADO'

];

/* ==========================================================
   OBTENER JOVEN POR ID
========================================================== */

function obtenerJovenPorId(
    PDO $pdo,
    int $id
): ?array
{
    $stmt = $pdo->prepare("
        SELECT *
        FROM jovenes
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([

        ':id' => $id

    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/* ==========================================================
   OBTENER POR TELÉFONO
========================================================== */

function obtenerJovenPorTelefono(
    PDO $pdo,
    string $telefono
): ?array
{
    $stmt = $pdo->prepare("
        SELECT *
        FROM jovenes
        WHERE telefono = :telefono
        LIMIT 1
    ");

    $stmt->execute([

        ':telefono' => $telefono

    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/* ==========================================================
   EXISTE JOVEN
========================================================== */

function existeJoven(
    PDO $pdo,
    int $id
): bool
{
    $stmt = $pdo->prepare("
        SELECT id
        FROM jovenes
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([

        ':id' => $id

    ]);

    return (bool) $stmt->fetch();
}

/* ==========================================================
   EXISTE DUPLICADO
========================================================== */

function existeJovenDuplicado(
    PDO $pdo,
    string $nombre,
    ?string $telefono,
    int $ignorarId = 0
): bool
{
    $sql = "
        SELECT id
        FROM jovenes
        WHERE nombre_completo = :nombre
        AND telefono <=> :telefono
    ";

    if ($ignorarId > 0) {

        $sql .= "
            AND id != :id
        ";

    }

    $sql .= "
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);

    $params = [

        ':nombre' => trim($nombre),

        ':telefono' => $telefono

    ];

    if ($ignorarId > 0) {

        $params[':id'] = $ignorarId;

    }

    $stmt->execute($params);

    return (bool) $stmt->fetch();
}

/* ==========================================================
   OBTENER TODOS LOS JÓVENES
========================================================== */

function obtenerJovenes(
    PDO $pdo,
    bool $incluirEliminados = false
): array
{
    $sql = "
        SELECT *
        FROM jovenes
    ";

    if (!$incluirEliminados) {

        $sql .= "
            WHERE estado_actividad != 'ELIMINADO'
        ";

    }

    $sql .= "
        ORDER BY nombre_completo ASC
    ";

    return $pdo
        ->query($sql)
        ->fetchAll(PDO::FETCH_ASSOC);
}

/* ==========================================================
   OBTENER JÓVENES ACTIVOS
========================================================== */

function obtenerJovenesActivos(
    PDO $pdo
): array
{
    $stmt = $pdo->query("
        SELECT

            id,

            nombre_completo,

            telefono,

            genero,

            estado_espiritual

        FROM jovenes

        WHERE estado_actividad = 'ACTIVO'

        ORDER BY nombre_completo ASC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ==========================================================
   CONTAR JÓVENES ACTIVOS
========================================================== */

function contarJovenesActivos(
    PDO $pdo
): int
{
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM jovenes
        WHERE estado_actividad = 'ACTIVO'
    ");

    return (int) $stmt->fetchColumn();
}

/* ==========================================================
   VALIDAR JOVEN
========================================================== */

function validarJoven(
    PDO $pdo,
    int $id
): void
{
    if ($id <= 0) {

        throw new Exception(
            'Joven inválido.'
        );

    }

    if (!existeJoven($pdo, $id)) {

        throw new Exception(
            'El joven no existe.'
        );

    }
}

/* ==========================================================
   VALIDAR NOMBRE
========================================================== */

function validarNombreJoven(
    string $nombre
): string
{
    $nombre = normalizarNombrePersona($nombre);

    if ($nombre === '') {

        throw new Exception(
            'Debe ingresar el nombre del joven.'
        );

    }

    if (mb_strlen($nombre) > 150) {

        throw new Exception(
            'El nombre es demasiado largo.'
        );

    }

    return $nombre;
}

/* ==========================================================
   VALIDAR GÉNERO
========================================================== */

function validarGeneroJoven(
    string $genero
): string
{
    $genero = strtoupper(
        trim($genero)
    );

    if (!in_array(
        $genero,
        GENEROS,
        true
    )) {

        throw new Exception(
            'Debe seleccionar un género válido.'
        );

    }

    return $genero;
}

/* ==========================================================
   VALIDAR ESTADO ESPIRITUAL
========================================================== */

function validarEstadoEspiritualJoven(
    string $estado
): string
{
    $estado = strtoupper(
        trim($estado)
    );

    if (!in_array(
        $estado,
        ESTADOS_ESPIRITUALES,
        true
    )) {

        throw new Exception(
            'Estado espiritual inválido.'
        );

    }

    return $estado;
}

/* ==========================================================
   VALIDAR SERVIDOR
========================================================== */

function validarServidorJoven(
    int $servidor
): int
{
    if (!in_array(
        $servidor,
        [0, 1],
        true
    )) {

        throw new Exception(
            'Valor de servidor inválido.'
        );

    }

    return $servidor;
}

/* ==========================================================
   VALIDAR FECHA INGRESO
========================================================== */

function validarFechaIngresoJoven(
    ?string $fecha
): ?string
{
    if (
        $fecha === null ||
        $fecha === ''
    ) {

        return null;

    }

    if (!strtotime($fecha)) {

        throw new Exception(
            'Fecha de ingreso inválida.'
        );

    }

    return $fecha;
}

/* ==========================================================
   VALIDAR EDAD
========================================================== */

function validarEdadJoven(
    ?string $fechaNacimiento,
    ?int $edadManual
): array
{
    if ($fechaNacimiento) {

        if (!strtotime($fechaNacimiento)) {

            throw new Exception(
                'Fecha de nacimiento inválida.'
            );

        }

        return [

            $fechaNacimiento,

            null,

            null

        ];
    }

    if ($edadManual === null) {

        throw new Exception(
            'Debe ingresar la edad o la fecha de nacimiento.'
        );

    }

    if (
        $edadManual < 0 ||
        $edadManual > 120
    ) {

        throw new Exception(
            'Edad inválida.'
        );

    }

    return [

        null,

        $edadManual,

        date('Y-m-d')

    ];
}

/* ==========================================================
   VALIDAR TELÉFONO
========================================================== */

function validarTelefonoJoven(
    ?string $telefono,
    bool $sinTelefono
): ?string
{
    if ($sinTelefono) {

        return null;

    }

    $telefono = trim(
        (string) $telefono
    );

    if ($telefono === '') {

        throw new Exception(
            'Debe ingresar un teléfono o marcar "Sin teléfono".'
        );

    }

    if (!preg_match(
        '/^[0-9]{7,15}$/',
        $telefono
    )) {

        throw new Exception(
            'El teléfono no es válido.'
        );

    }

    return $telefono;
}

/* ==========================================================
   VALIDAR DUPLICADO
========================================================== */

function validarDuplicadoJoven(
    PDO $pdo,
    string $nombre,
    ?string $telefono,
    int $id = 0
): void
{
    if (
        existeJovenDuplicado(
            $pdo,
            $nombre,
            $telefono,
            $id
        )
    ) {

        throw new Exception(
            'Ya existe un joven con ese nombre y teléfono.'
        );

    }
}

/* ==========================================================
   VALIDAR OBSERVACIONES
========================================================== */

function validarObservacionesJoven(
    ?string $texto
): ?string
{
    $texto = trim(
        (string) $texto
    );

    if ($texto === '') {

        return null;

    }

    if (mb_strlen($texto) > 5000) {

        throw new Exception(
            'Las observaciones son demasiado largas.'
        );

    }

    return $texto;
}

/* ==========================================================
   PREPARAR DATOS DEL JOVEN
========================================================== */

function prepararDatosJoven(
    PDO $pdo,
    array $datos,
    int $id = 0
): array
{
    /* ==========================================
       NOMBRE
    ========================================== */

    $nombre = validarNombreJoven(
        $datos['nombre_completo'] ?? ''
    );

    /* ==========================================
       GÉNERO
    ========================================== */

    $genero = validarGeneroJoven(
        $datos['genero'] ?? ''
    );

    /* ==========================================
       ESTADO ESPIRITUAL
    ========================================== */

    $estadoEspiritual =
        validarEstadoEspiritualJoven(
            $datos['estado_espiritual'] ?? ''
        );

    /* ==========================================
       SERVIDOR
    ========================================== */

    $esServidor =
        validarServidorJoven(
            (int)($datos['es_servidor'] ?? 0)
        );

    /* ==========================================
       FECHA INGRESO
    ========================================== */

    $fechaIngreso =
        validarFechaIngresoJoven(
            $datos['fecha_ingreso'] ?? null
        );

    /* ==========================================
       EDAD
    ========================================== */

    [

        $fechaNacimiento,

        $edadManual,

        $fechaActualizacionEdad

    ] = validarEdadJoven(

        !empty($datos['fecha_nacimiento'])
            ? $datos['fecha_nacimiento']
            : null,

        !empty($datos['edad_manual'])
            ? (int)$datos['edad_manual']
            : null

    );

    /* ==========================================
       TELÉFONO
    ========================================== */

    $telefono =
        validarTelefonoJoven(

            $datos['telefono'] ?? null,

            isset($datos['sinTelefono'])

        );

    /* ==========================================
       DUPLICADOS
    ========================================== */

    validarDuplicadoJoven(

        $pdo,

        $nombre,

        $telefono,

        $id

    );

    /* ==========================================
       OBSERVACIONES
    ========================================== */

    $observaciones =
        validarObservacionesJoven(
            $datos['observaciones'] ?? null
        );

    /* ==========================================
       RESPUESTA
    ========================================== */

    return [

        'nombre' => $nombre,

        'fechaNacimiento' => $fechaNacimiento,

        'edadManual' => $edadManual,

        'fechaActualizacionEdad' => $fechaActualizacionEdad,

        'telefono' => $telefono,

        'genero' => $genero,

        'estadoEspiritual' => $estadoEspiritual,

        'fechaIngreso' => $fechaIngreso,

        'esServidor' => $esServidor,

        'observaciones' => $observaciones

    ];
}

/* ==========================================================
   CREAR JOVEN
========================================================== */

function crearJoven(
    PDO $pdo,
    array $datos
): int
{
    exigirPermiso('gestionar_jovenes');

    $datos = prepararDatosJoven(
        $pdo,
        $datos
    );

    $stmt = $pdo->prepare("
        INSERT INTO jovenes (

            nombre_completo,
            fecha_nacimiento,
            edad_manual,
            fecha_actualizacion_edad,
            telefono,
            genero,
            estado_espiritual,
            estado_actividad,
            fecha_ingreso,
            es_servidor,
            observaciones

        ) VALUES (

            :nombre,
            :fechaNacimiento,
            :edadManual,
            :fechaActualizacionEdad,
            :telefono,
            :genero,
            :estadoEspiritual,
            'ACTIVO',
            :fechaIngreso,
            :esServidor,
            :observaciones

        )
    ");

    $stmt->execute([

        ':nombre' => $datos['nombre'],
        ':fechaNacimiento' => $datos['fechaNacimiento'],
        ':edadManual' => $datos['edadManual'],
        ':fechaActualizacionEdad' => $datos['fechaActualizacionEdad'],
        ':telefono' => $datos['telefono'],
        ':genero' => $datos['genero'],
        ':estadoEspiritual' => $datos['estadoEspiritual'],
        ':fechaIngreso' => $datos['fechaIngreso'],
        ':esServidor' => $datos['esServidor'],
        ':observaciones' => $datos['observaciones']

    ]);

    return (int)$pdo->lastInsertId();
}

/* ==========================================================
   EDITAR JOVEN
========================================================== */

function editarJoven(
    PDO $pdo,
    int $id,
    array $datos
): void
{
    exigirPermiso('gestionar_jovenes');

    validarJoven(
        $pdo,
        $id
    );

    $datos = prepararDatosJoven(
        $pdo,
        $datos,
        $id
    );

    $stmt = $pdo->prepare("
        UPDATE jovenes
        SET

            nombre_completo = :nombre,
            fecha_nacimiento = :fechaNacimiento,
            edad_manual = :edadManual,
            fecha_actualizacion_edad = :fechaActualizacionEdad,
            telefono = :telefono,
            genero = :genero,
            estado_espiritual = :estadoEspiritual,
            fecha_ingreso = :fechaIngreso,
            es_servidor = :esServidor,
            observaciones = :observaciones

        WHERE id = :id
    ");

    $stmt->execute([

        ':nombre' => $datos['nombre'],
        ':fechaNacimiento' => $datos['fechaNacimiento'],
        ':edadManual' => $datos['edadManual'],
        ':fechaActualizacionEdad' => $datos['fechaActualizacionEdad'],
        ':telefono' => $datos['telefono'],
        ':genero' => $datos['genero'],
        ':estadoEspiritual' => $datos['estadoEspiritual'],
        ':fechaIngreso' => $datos['fechaIngreso'],
        ':esServidor' => $datos['esServidor'],
        ':observaciones' => $datos['observaciones'],
        ':id' => $id

    ]);
}

/* ==========================================================
   CAMBIAR ESTADO
========================================================== */

function cambiarEstadoJoven(
    PDO $pdo,
    int $id,
    string $estado
): void
{
    exigirPermiso('gestionar_jovenes');

    validarJoven(
        $pdo,
        $id
    );

    if (!in_array(
        $estado,
        ESTADOS_ACTIVIDAD,
        true
    )) {

        throw new Exception(
            'Estado inválido.'
        );

    }

    $stmt = $pdo->prepare("
        UPDATE jovenes
        SET estado_actividad = :estado
        WHERE id = :id
    ");

    $stmt->execute([

        ':estado' => $estado,
        ':id' => $id

    ]);
}

/* ==========================================================
   ELIMINAR
========================================================== */

function eliminarJoven(
    PDO $pdo,
    int $id
): void
{
    cambiarEstadoJoven(
        $pdo,
        $id,
        'ELIMINADO'
    );
}

/* ==========================================================
   RECUPERAR
========================================================== */

function recuperarJoven(
    PDO $pdo,
    int $id
): void
{
    cambiarEstadoJoven(
        $pdo,
        $id,
        'ACTIVO'
    );
}

/* ==========================================================
   ELIMINAR DEFINITIVAMENTE
========================================================== */

function eliminarDefinitivo(
    PDO $pdo,
    int $id
): void
{
    exigirPermiso(
        'eliminar_jovenes'
    );

    validarJoven(
        $pdo,
        $id
    );

    $stmt = $pdo->prepare("
        DELETE
        FROM jovenes
        WHERE id = :id
    ");

    $stmt->execute([

        ':id' => $id

    ]);
}

