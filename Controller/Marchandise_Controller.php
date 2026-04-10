<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/Marchandise.php';

$action = (string) ($_GET['action'] ?? $_POST['action'] ?? 'index');

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'id_march' => (int) ($_POST['id_march'] ?? 0),
        'id_mag' => (int) ($_POST['id_mag'] ?? 0),
        'name_march' => (string) ($_POST['name_march'] ?? ''),
        'description_march' => (string) ($_POST['description_march'] ?? ''),
        'price_march' => (int) ($_POST['price_march'] ?? 0),
        'quantity_march' => (int) ($_POST['quantity_march'] ?? 0),
        'date_expiration_march' => (string) ($_POST['date_expiration_march'] ?? ''),
        'point_acces_march' => (string) ($_POST['point_acces_march'] ?? ''),
    ];

    $image = $_FILES['img_march'] ?? null;

    try {
        if ($payload['id_march'] > 0) {
            marketplace_update_product($payload, is_array($image) ? $image : []);
            header('Location: ../View/back_office/material_able-main/products.php?status=updated');
            exit;
        }

        marketplace_create_product($payload, is_array($image) ? $image : []);
        header('Location: ../View/back_office/material_able-main/products.php?status=success');
        exit;
    } catch (Throwable $exception) {
        header('Location: ../View/back_office/material_able-main/products.php?status=dberror');
        exit;
    }
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        marketplace_delete_product((int) ($_POST['id_march'] ?? 0));
        header('Location: ../View/back_office/material_able-main/products.php?status=deleted');
        exit;
    } catch (Throwable $exception) {
        header('Location: ../View/back_office/material_able-main/products.php?status=deleteerror');
        exit;
    }
}

if ($action === 'image') {
    $productId = (int) ($_GET['id'] ?? 0);

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
    exit;
}

header('Location: ../View/front_office/organic-1.0.0/marketplace.php');
exit;
