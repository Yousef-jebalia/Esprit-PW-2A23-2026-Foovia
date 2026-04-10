<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/marketplace_repository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../back-office/products.php');
    exit;
}

$productId = (int) ($_POST['id_march'] ?? 0);

try {
    marketplace_delete_product($productId);
    header('Location: ../back-office/products.php?status=deleted');
    exit;
} catch (Throwable $exception) {
    header('Location: ../back-office/products.php?status=deleteerror');
    exit;
}
