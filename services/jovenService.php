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
   CONSULTAS
========================================================== */

/* ==========================================================
   OBTENER JOVEN
========================================================== */

function obtenerJovenPorId(
    PDO $pdo,
    int $id
): ?array
{
    $stmt = $pdo->prepare("
        SELECT
            id,
            nombre_completo,
            telefono,
            genero,
            fecha_nacimiento,
            edad_manual,
            fecha_actualizacion_edad,
            estado_espiritual,
            estado_actividad,
            fecha_ingreso,
            es_servidor,
            observaciones,
            ultima_actividad
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
        WHERE
            nombre_completo = :nombre
        AND
            telefono <=> :telefono
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
        ':nombre' => $nombre,
        ':telefono' => $telefono
    ];

    if ($ignorarId > 0) {
        $params[':id'] = $ignorarId;
    }

    $stmt->execute($params);

    return (bool) $stmt->fetch();
}

/* ==========================================================
   LISTAR JÓVENES
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
   LISTAR ACTIVOS
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
        ORDER BY nombre_completo
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ==========================================================
   CONTAR ACTIVOS
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
   PREPARAR DATOS
========================================================== */

function prepararDatosJoven(
    PDO $pdo,
    array $datos,
    int $id = 0
): array
{
    $nombre = trim(
        $datos['nombre_completo'] ?? ''
    );

    $genero = $datos['genero'] ?? '';

    $estadoEspiritual =
        $datos['estado_espiritual'] ?? '';

    $fechaIngreso =
        $datos['fecha_ingreso'] ?? null;

    $fechaNacimiento = !empty(
        $datos['fecha_nacimiento']
    )
        ? $datos['fecha_nacimiento']
        : null;

    $edadManual = !empty(
        $datos['edad_manual']
    )
        ? (int) $datos['edad_manual']
        : null;

    $fechaActualizacionEdad = null;

    $telefono =
        $datos['telefono'] ?? null;

    $sinTelefono = isset(
        $datos['sinTelefono']
    );

    $esServidor = (int) (
        $datos['es_servidor'] ?? 0
    );

    $observaciones =
        $datos['observaciones'] ?? null;

    validarNombreJoven($nombre);
    validarGeneroJoven($genero);
    validarEstadoEspiritualJoven($estadoEspiritual);
    validarServidorJoven($esServidor);
    validarFechaIngresoJoven($fechaIngreso);

    validarEdadJoven(
        $fechaNacimiento,
        $edadManual,
        $fechaActualizacionEdad
    );

    validarTelefonoJoven(
        $telefono,
        $sinTelefono
    );

    validarDuplicadoJoven(
        $pdo,
        $nombre,
        $telefono,
        $id
    );

    validarObservacionesJoven(
        $observaciones
    );

    return [

        'nombre' => $nombre,

        'fechaNacimiento' =>
            $fechaNacimiento,

        'edadManual' =>
            $edadManual,

        'fechaActualizacionEdad' =>
            $fechaActualizacionEdad,

        'telefono' =>
            $telefono,

        'genero' =>
            $genero,

        'estadoEspiritual' =>
            $estadoEspiritual,

        'fechaIngreso' =>
            $fechaIngreso,

        'esServidor' =>
            $esServidor,

        'observaciones' =>
            $observaciones

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
    exigirPermiso(
        'gestionar_jovenes'
    );

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

        )
        VALUES (

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

    return (int) $pdo->lastInsertId();
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
    exigirPermiso(
        'gestionar_jovenes'
    );

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
    exigirPermiso(
        'gestionar_jovenes'
    );

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
   ELIMINAR DEFINITIVO
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
   SERVIDOR
========================================================== */

function validarServidorJoven(
    int $servidor
): void
{
    if (!in_array($servidor, [0, 1], true)) {
        throw new Exception('Valor de servidor inválido.');
    }
}

/* ==========================================================
   FECHA INGRESO
========================================================== */

function validarFechaIngresoJoven(
    ?string $fecha
): void
{
    if ($fecha === null || $fecha === '') {
        return;
    }

    if (!strtotime($fecha)) {
        throw new Exception('Fecha de ingreso inválida.');
    }
}

/* ==========================================================
   EDAD
========================================================== */

function validarEdadJoven(
    ?string $fechaNacimiento,
    ?int $edadManual,
    ?string $fechaActualizacionEdad
): void
{
    if ($fechaNacimiento !== null && !strtotime($fechaNacimiento)) {
        throw new Exception('Fecha de nacimiento inválida.');
    }

    if ($edadManual !== null && $edadManual < 0) {
        throw new Exception('Edad inválida.');
    }
}

/* ==========================================================
   TELÉFONO
========================================================== */

function validarTelefonoJoven(
    ?string &$telefono,
    bool $sinTelefono
): void
{
    if ($sinTelefono) {
        $telefono = null;
        return;
    }

    if ($telefono === null || trim($telefono) === '') {
        throw new Exception('Debe ingresar un teléfono o marcar "Sin teléfono".');
    }

    $telefono = trim($telefono);
}

/* ==========================================================
   DUPLICADOS
========================================================== */

function validarDuplicadoJoven(
    PDO $pdo,
    string $nombre,
    ?string $telefono,
    int $id = 0
): void
{
    if (existeJovenDuplicado(
        $pdo,
        $nombre,
        $telefono,
        $id
    )) {
        throw new Exception(
            'Ya existe un joven con ese nombre y teléfono.'
        );
    }
}

/* ==========================================================
   OBSERVACIONES
========================================================== */

function validarObservacionesJoven(
    ?string $texto
): void
{
    if ($texto !== null && mb_strlen($texto) > 5000) {
        throw new Exception(
            'Las observaciones son demasiado largas.'
        );
    }
}
