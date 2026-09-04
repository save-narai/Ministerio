<?php
declare(strict_types=1);
require_once __DIR__ . '/controller.php';
controllerInit(); $pdo = controllerPdo();
controllerRun(['guardar_material_discipulado' => function () use ($pdo) {
    controllerRequirePermission('gestionar_reuniones');
    $baseId = (int)($_POST['clase_base_id'] ?? 0); $file = $_FILES['pdf'] ?? null;
    if ($baseId < 1 || !$file || $file['error'] !== UPLOAD_ERR_OK || $file['size'] > 15728640) throw new Exception('Seleccione un PDF de hasta 15 MB.');
    $extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if ($extension !== 'pdf' || $mime !== 'application/pdf') throw new Exception('El archivo debe ser un PDF válido.');
    $nombre = bin2hex(random_bytes(16)) . '.pdf'; $destino = __DIR__ . '/../storage/discipulado/' . $nombre;
    if (!move_uploaded_file($file['tmp_name'], $destino)) throw new Exception('No fue posible guardar el PDF.');
    $anterior = $pdo->prepare('SELECT archivo_generado FROM materiales_discipulado WHERE clase_base_id=:id'); $anterior->execute(['id'=>$baseId]); $previo=$anterior->fetchColumn();
    $stmt=$pdo->prepare('INSERT INTO materiales_discipulado (clase_base_id,nombre_original,archivo_generado,mime_type,tamano_bytes) VALUES (:id,:original,:archivo,:mime,:tamano) ON DUPLICATE KEY UPDATE nombre_original=VALUES(nombre_original),archivo_generado=VALUES(archivo_generado),mime_type=VALUES(mime_type),tamano_bytes=VALUES(tamano_bytes)');
    $stmt->execute(['id'=>$baseId,'original'=>basename((string)$file['name']),'archivo'=>$nombre,'mime'=>$mime,'tamano'=>(int)$file['size']]);
    if ($previo && is_file(__DIR__ . '/../storage/discipulado/' . basename($previo))) unlink(__DIR__ . '/../storage/discipulado/' . basename($previo));
    return controllerRedirect('../views/formacion/discipulado/materiales.php','Material guardado correctamente.');
}], ['redirect'=>'../views/formacion/discipulado/materiales.php']);
