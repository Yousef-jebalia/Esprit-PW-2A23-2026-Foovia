<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$marketplaceUrl = 'MARKETPLACE_MODULE/organic-1.0.0/marketplace.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: foovia-signin.php?redirect=marketplace');
    exit;
}

header('Location: ' . $marketplaceUrl);
exit;
