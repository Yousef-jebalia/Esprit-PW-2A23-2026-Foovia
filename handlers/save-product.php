<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/marketplace_repository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../back-office/products.php');
    exit;
}

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
        header('Location: ../back-office/products.php?status=updated');
        exit;
    }

    marketplace_create_product($payload, is_array($image) ? $image : []);
    header('Location: ../back-office/products.php?status=success');
    exit;
} catch (Throwable $exception) {
    header('Location: ../back-office/products.php?status=dberror');
    exit;
}
