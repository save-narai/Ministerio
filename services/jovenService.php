<?php

<<<<<<< HEAD
=======
declare(strict_types=1);

require_once __DIR__ . "/../helpers/validaciones.php";

>>>>>>> 3e2d89c (Actualización del proyecto)
/* =========================================================
   PREPARAR DATOS DEL JOVEN
========================================================= */

function prepararDatosJoven(
    PDO $pdo,
    int $id = 0
): array {

<<<<<<< HEAD
    /* =====================================================
       NOMBRE
    ===================================================== */

    [$ok, $nombre] = validarNombre(
        $_POST["nombre_completo"] ?? ''
    );

    if (!$ok) {

        throw new Exception($nombre);
    }

    /* =====================================================
       GÉNERO
    ===================================================== */

    $genero = strtoupper(
        trim($_POST["genero"] ?? '')
    );

    if (!validarGenero($genero)) {

        throw new Exception(
            "Género inválido."
        );
    }

    /* =====================================================
       ESTADO ESPIRITUAL
    ===================================================== */

    $estadoEspiritual = strtoupper(
        trim($_POST["estado_espiritual"] ?? '')
    );

    $estadosValidos = [

        "NUEVO",

        "CONGREGANTE",

        "DISCIPULADO",

        "SERVIDOR",

        "LIDER"

=======
    [$ok, $nombre] = validarNombre(
        $_POST["nombre_completo"] ?? ""
    );

    if (!$ok) {
        throw new Exception($nombre);
    }

    $genero = strtoupper(
        trim($_POST["genero"] ?? "")
    );

    if (!validarGenero($genero)) {
        throw new Exception("Género inválido.");
    }

    $estadoEspiritual = strtoupper(
        trim($_POST["estado_espiritual"] ?? "")
    );

    $estadosValidos = [
        "NUEVO",
        "CONGREGANTE",
        "DISCIPULADO",
        "SERVIDOR",
        "LIDER"
>>>>>>> 3e2d89c (Actualización del proyecto)
    ];

    if (!in_array(
        $estadoEspiritual,
        $estadosValidos,
        true
    )) {

        throw new Exception(
            "Estado espiritual inválido."
        );
<<<<<<< HEAD
    }

    /* =====================================================
       SERVIDOR
    ===================================================== */

    $esServidor = (int) (
=======

    }

    $esServidor = (int)(
>>>>>>> 3e2d89c (Actualización del proyecto)
        $_POST["es_servidor"] ?? 0
    );

    if (!in_array(
        $esServidor,
        [0, 1],
        true
    )) {

        throw new Exception(
            "Valor de servidor inválido."
        );
<<<<<<< HEAD
    }

    /* =====================================================
       FECHA INGRESO
    ===================================================== */
=======

    }
>>>>>>> 3e2d89c (Actualización del proyecto)

    $fechaIngreso =
        $_POST["fecha_ingreso"] ?? null;

    if (!validarFecha($fechaIngreso)) {

        throw new Exception(
<<<<<<< HEAD
            "Fecha ingreso inválida."
        );
    }

    /* =====================================================
       EDAD
    ===================================================== */
=======
            "Fecha de ingreso inválida."
        );

    }
>>>>>>> 3e2d89c (Actualización del proyecto)

    $fechaNacimiento =
        $_POST["fecha_nacimiento"] ?: null;

    $edadManual =
        $_POST["edad_manual"] ?: null;

    if (
<<<<<<< HEAD

        empty($fechaNacimiento)

        &&

        empty($edadManual)

    ) {

        throw new Exception(
            "Debes ingresar edad o fecha."
        );
=======
        empty($fechaNacimiento)
        &&
        empty($edadManual)
    ) {

        throw new Exception(
            "Debes ingresar la edad o la fecha de nacimiento."
        );

>>>>>>> 3e2d89c (Actualización del proyecto)
    }

    if ($fechaNacimiento) {

        $edadManual = null;
<<<<<<< HEAD

=======
>>>>>>> 3e2d89c (Actualización del proyecto)
        $fechaActualizacionEdad = null;

    } else {

        if (!validarEdad($edadManual)) {

            throw new Exception(
                "Edad inválida."
            );
<<<<<<< HEAD
=======

>>>>>>> 3e2d89c (Actualización del proyecto)
        }

        $fechaActualizacionEdad =
            date("Y-m-d");
<<<<<<< HEAD
    }

    /* =====================================================
       TELÉFONO
    ===================================================== */
=======

    }
>>>>>>> 3e2d89c (Actualización del proyecto)

    $sinTelefono =
        isset($_POST["sinTelefono"]);

    $telefono =
<<<<<<< HEAD
        trim($_POST["telefono"] ?? '');
=======
        trim($_POST["telefono"] ?? "");
>>>>>>> 3e2d89c (Actualización del proyecto)

    if ($sinTelefono) {

        $telefonoFinal = null;

    } else {

<<<<<<< HEAD
        if (empty($telefono)) {

            throw new Exception(
                "Debes ingresar teléfono."
            );
=======
        if ($telefono === "") {

            throw new Exception(
                "Debes ingresar un teléfono."
            );

>>>>>>> 3e2d89c (Actualización del proyecto)
        }

        [$okTel, $telefono] =
            validarTelefono($telefono);

        if (!$okTel) {

            throw new Exception(
                $telefono
            );
<<<<<<< HEAD
        }

        $telefonoFinal = $telefono;
    }

    /* =====================================================
       DUPLICADOS
    ===================================================== */
=======

        }

        $telefonoFinal = $telefono;

    }
>>>>>>> 3e2d89c (Actualización del proyecto)

    if ($telefonoFinal) {

        $sql = "
            SELECT COUNT(*)
            FROM jovenes
<<<<<<< HEAD
            WHERE telefono = :tel
=======
            WHERE telefono = :telefono
>>>>>>> 3e2d89c (Actualización del proyecto)
            AND nombre_completo = :nombre
        ";

        if ($id > 0) {

<<<<<<< HEAD
            $sql .= " AND id != :id";
=======
            $sql .= "
                AND id != :id
            ";

>>>>>>> 3e2d89c (Actualización del proyecto)
        }

        $stmt = $pdo->prepare($sql);

        $params = [

<<<<<<< HEAD
            "tel" => $telefonoFinal,

=======
            "telefono" => $telefonoFinal,
>>>>>>> 3e2d89c (Actualización del proyecto)
            "nombre" => $nombre

        ];

        if ($id > 0) {

            $params["id"] = $id;
<<<<<<< HEAD
=======

>>>>>>> 3e2d89c (Actualización del proyecto)
        }

        $stmt->execute($params);

        if ($stmt->fetchColumn() > 0) {

            throw new Exception(

                $id > 0

                    ? "Ya existe otro joven con ese nombre y teléfono."

                    : "Este joven ya existe."

            );
<<<<<<< HEAD
        }
    }

    /* =====================================================
       RESPUESTA
    ===================================================== */
=======

        }

    }
>>>>>>> 3e2d89c (Actualización del proyecto)

    return [

        "nombre" => $nombre,

        "fechaNacimiento" =>
            $fechaNacimiento,

        "edadManual" =>
            $edadManual,

        "fechaActualizacionEdad" =>
            $fechaActualizacionEdad,

        "telefono" =>
            $telefonoFinal,

        "genero" =>
            $genero,

        "estadoEspiritual" =>
            $estadoEspiritual,

        "fechaIngreso" =>
            $fechaIngreso,

        "esServidor" =>
            $esServidor,

        "observaciones" =>
            trim(
<<<<<<< HEAD
                $_POST["observaciones"] ?? ''
            ) ?: null

    ];
=======
                $_POST["observaciones"] ?? ""
            ) ?: null

    ];

}

/* =========================================================
   CREAR JOVEN
========================================================= */

function crearJoven(
    PDO $pdo,
    array $post
): void {

    $datos = prepararDatosJoven($pdo);

    $pdo->beginTransaction();

    try {

        $sql = "
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
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([

            "nombre" =>
                $datos["nombre"],

            "fechaNacimiento" =>
                $datos["fechaNacimiento"],

            "edadManual" =>
                $datos["edadManual"],

            "fechaActualizacionEdad" =>
                $datos["fechaActualizacionEdad"],

            "telefono" =>
                $datos["telefono"],

            "genero" =>
                $datos["genero"],

            "estadoEspiritual" =>
                $datos["estadoEspiritual"],

            "fechaIngreso" =>
                $datos["fechaIngreso"],

            "esServidor" =>
                $datos["esServidor"],

            "observaciones" =>
                $datos["observaciones"]

        ]);

        $pdo->commit();

    } catch (Throwable $e) {

        $pdo->rollBack();

        throw $e;

    }

}                                                                                                                                                     /* =========================================================
   EDITAR JOVEN
========================================================= */

function editarJoven(
    PDO $pdo,
    array $post
): void {

    $id = (int)($post["id"] ?? 0);

    if ($id <= 0) {

        throw new Exception(
            "Joven no válido."
        );

    }

    $datos = prepararDatosJoven(
        $pdo,
        $id
    );

    $pdo->beginTransaction();

    try {

        $sql = "
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
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([

            "nombre" =>
                $datos["nombre"],

            "fechaNacimiento" =>
                $datos["fechaNacimiento"],

            "edadManual" =>
                $datos["edadManual"],

            "fechaActualizacionEdad" =>
                $datos["fechaActualizacionEdad"],

            "telefono" =>
                $datos["telefono"],

            "genero" =>
                $datos["genero"],

            "estadoEspiritual" =>
                $datos["estadoEspiritual"],

            "fechaIngreso" =>
                $datos["fechaIngreso"],

            "esServidor" =>
                $datos["esServidor"],

            "observaciones" =>
                $datos["observaciones"],

            "id" =>
                $id

        ]);

        $pdo->commit();

    } catch (Throwable $e) {

        $pdo->rollBack();

        throw $e;

    }

}

/* =========================================================
   ELIMINAR JOVEN (ELIMINACIÓN LÓGICA)
========================================================= */

function eliminarJoven(
    PDO $pdo,
    array $post
): void {

    $id = (int)($post["id"] ?? 0);

    if ($id <= 0) {

        throw new Exception(
            "Joven no válido."
        );

    }

    $stmt = $pdo->prepare("
        UPDATE jovenes
        SET estado_actividad = 'ELIMINADO'
        WHERE id = :id
    ");

    $stmt->execute([

        "id" => $id

    ]);

    if ($stmt->rowCount() === 0) {

        throw new Exception(
            "No fue posible eliminar el joven."
        );

    }

}                                                                                                                                                     /* =========================================================
   RECUPERAR JOVEN
========================================================= */

function recuperarJoven(
    PDO $pdo,
    array $post
): void {

    $id = (int)($post["id"] ?? 0);

    if ($id <= 0) {

        throw new Exception(
            "Joven no válido."
        );

    }

    $stmt = $pdo->prepare("
        UPDATE jovenes
        SET estado_actividad = 'ACTIVO'
        WHERE id = :id
    ");

    $stmt->execute([

        "id" => $id

    ]);

    if ($stmt->rowCount() === 0) {

        throw new Exception(
            "No fue posible recuperar el joven."
        );

    }

}

/* =========================================================
   ELIMINAR DEFINITIVAMENTE
========================================================= */

function eliminarDefinitivo(
    PDO $pdo,
    array $post
): void {

    $id = (int)($post["id"] ?? 0);

    if ($id <= 0) {

        throw new Exception(
            "Joven no válido."
        );

    }

    $pdo->beginTransaction();

    try {

        /*
        |----------------------------------------------------
        | Eliminar registros relacionados
        |----------------------------------------------------
        */

        $tablasRelacionadas = [

            [
                "tabla" => "seguimientos",
                "campo" => "joven_id"
            ],

            [
                "tabla" => "asistencia",
                "campo" => "joven_id"
            ]

        ];

        foreach ($tablasRelacionadas as $relacion) {

            $sql = sprintf(
                "DELETE FROM %s WHERE %s = :id",
                $relacion["tabla"],
                $relacion["campo"]
            );

            $stmt = $pdo->prepare($sql);

            $stmt->execute([

                "id" => $id

            ]);

        }

        /*
        |----------------------------------------------------
        | Eliminar joven
        |----------------------------------------------------
        */

        $stmt = $pdo->prepare("
            DELETE FROM jovenes
            WHERE id = :id
        ");

        $stmt->execute([

            "id" => $id

        ]);

        if ($stmt->rowCount() === 0) {

            throw new Exception(
                "No fue posible eliminar el joven."
            );

        }

        $pdo->commit();

    } catch (Throwable $e) {

        $pdo->rollBack();

        throw $e;

    }

>>>>>>> 3e2d89c (Actualización del proyecto)
}