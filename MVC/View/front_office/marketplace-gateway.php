<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$marketplaceRelativePath = 'MARKETPLACE_MODULE/organic-1.0.0/marketplace.php';
$marketplaceFile = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $marketplaceRelativePath);

if (!is_file($marketplaceFile)) {
    http_response_code(500);
    echo 'Marketplace file was not found at: ' . htmlspecialchars($marketplaceFile, ENT_QUOTES);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: foovia-signin.php?redirect=marketplace');
    exit;
}

$requestDir = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
$marketplaceUrl = rtrim($requestDir, '/') . '/' . $marketplaceRelativePath . '?top=1';

header('Location: ' . $marketplaceUrl);
exit;
