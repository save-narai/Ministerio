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

    ];

    if (!in_array(
        $estadoEspiritual,
        $estadosValidos,
        true
    )) {

        throw new Exception(
            "Estado espiritual inválido."
        );
    }

    /* =====================================================
       SERVIDOR
    ===================================================== */

    $esServidor = (int) (
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
        trim($_POST["telefono"] ?? '');

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
            WHERE telefono = :tel
            AND nombre_completo = :nombre
        ";

        if ($id > 0) {

            $sql .= " AND id != :id";
        }

        $stmt = $pdo->prepare($sql);

        $params = [

            "tel" => $telefonoFinal,

            "nombre" => $nombre

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

    /* =====================================================
       RESPUESTA
    ===================================================== */

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
                $_POST["observaciones"] ?? ''
            ) ?: null

    ];
}