<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($productId <= 0) {
    http_response_code(404);
    exit;
}

$statement = foovia_db()->prepare('SELECT img_march FROM marchandise WHERE id_march = ?');
$statement->bind_param('i', $productId);
$statement->execute();
$statement->bind_result($imageBinary);

if (!$statement->fetch() || $imageBinary === null) {
    http_response_code(404);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->buffer($imageBinary) ?: 'image/jpeg';

header('Content-Type: ' . $mimeType);
echo $imageBinary;
