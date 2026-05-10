<?php
session_start();
include_once(__DIR__ . '/Controller_user.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$subscription = $data['subscription'] ?? '';

if (empty($subscription)) {
    echo json_encode(['success' => false, 'message' => 'Invalid subscription plan']);
    exit;
}

$controller = new Controller_user();
$success = $controller->update_user_subscription($_SESSION['user_id'], $subscription);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Subscription updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update subscription']);
}
?>
