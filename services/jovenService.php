<?php

/* =========================================================
   PREPARAR DATOS DEL JOVEN
========================================================= */

function prepararDatosJoven(
    PDO $pdo,
    int $id = 0
): array {

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

    $genero = $_POST["genero"] ?? null;

    if (!validarGenero($genero)) {

        throw new Exception(
            "Género inválido."
        );
    }

    /* =====================================================
       FECHA INGRESO
    ===================================================== */

    $fechaIngreso =
        $_POST["fecha_ingreso"] ?? null;

    if (!validarFecha($fechaIngreso)) {

        throw new Exception(
            "Fecha ingreso inválida."
        );
    }

    /* =====================================================
       EDAD
    ===================================================== */

    $fechaNacimiento =
        $_POST["fecha_nacimiento"] ?: null;

    $edadManual =
        $_POST["edad_manual"] ?: null;

    if (
        empty($fechaNacimiento)
        &&
        empty($edadManual)
    ) {

        throw new Exception(
            "Debes ingresar edad o fecha."
        );
    }

    if ($fechaNacimiento) {

        $edadManual = null;

        $fechaActualizacionEdad = null;

    } else {

        if (!validarEdad($edadManual)) {

            throw new Exception(
                "Edad inválida."
            );
        }

        $fechaActualizacionEdad =
            date("Y-m-d");
    }

    /* =====================================================
       TELÉFONO
    ===================================================== */

    $sinTelefono =
        isset($_POST["sinTelefono"]);

    $telefono =
        $_POST["telefono"] ?? '';

    if ($sinTelefono) {

        $telefonoFinal = null;

    } else {

        if (empty($telefono)) {

            throw new Exception(
                "Debes ingresar teléfono."
            );
        }

        [$okTel, $telefono] =
            validarTelefono($telefono);

        if (!$okTel) {

            throw new Exception(
                $telefono
            );
        }

        $telefonoFinal = $telefono;
    }

    /* =====================================================
       DUPLICADOS
    ===================================================== */

    if ($telefonoFinal) {

        $sql = "
            SELECT COUNT(*)
            FROM jovenes
            WHERE nombre_completo = :nombre
            AND telefono = :tel
        ";

        if ($id > 0) {

            $sql .= " AND id != :id";
        }

        $stmt = $pdo->prepare($sql);

        $params = [

            "nombre" => $nombre,

            "tel" => $telefonoFinal
        ];

        if ($id > 0) {

            $params["id"] = $id;
        }

        $stmt->execute($params);

        if ($stmt->fetchColumn() > 0) {

            throw new Exception(

                $id > 0

                ? "Ya existe otro joven con ese nombre y teléfono."

                : "Este joven ya existe."
            );
        }
    }

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
            $_POST["estado_espiritual"] ?? null,

        "fechaIngreso" =>
            $fechaIngreso,

        "esServidor" =>
            $_POST["es_servidor"] ?? 0,

        "observaciones" =>
            trim(
                $_POST["observaciones"] ?? ''
            ) ?: null
    ];
}