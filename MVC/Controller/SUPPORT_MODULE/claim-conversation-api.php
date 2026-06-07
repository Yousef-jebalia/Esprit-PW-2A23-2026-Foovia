<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . '/ReclamationMessage_Controller.php';

$userId = (int) ($_SESSION['user_id'] ?? 0);
if ($userId <= 0) {
    echo json_encode(['error' => 'Not logged in.']);
    exit;
}

$messageController = new ReclamationMessage_Controller();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $claimId = (int) ($_GET['claim_id'] ?? 0);
    $afterId = (int) ($_GET['after_id'] ?? 0);

    if ($claimId <= 0) {
        echo json_encode(['error' => 'Invalid claim.']);
        exit;
    }

    $rows = $messageController->get_messages_after($claimId, $afterId);
    $messages = array_map([$messageController, 'to_timeline_item'], $rows);
    echo json_encode(['messages' => $messages]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        echo json_encode(['error' => 'Invalid request.']);
        exit;
    }

    $claimId = (int) ($input['claim_id'] ?? 0);
    $body = trim((string) ($input['body'] ?? ''));

    if ($claimId <= 0) {
        echo json_encode(['error' => 'Invalid claim.']);
        exit;
    }
    if ($body === '') {
        echo json_encode(['error' => 'Reply cannot be empty.']);
        exit;
    }

    try {
        $msg = new ReclamationMessage(0, $claimId, $userId, $body, '');
        $newId = $messageController->add_message($msg);
        $row = $messageController->get_message_by_id($newId);
        if (!$row) {
            echo json_encode(['error' => 'Message saved but could not be loaded.']);
            exit;
        }
        echo json_encode([
            'success' => true,
            'message' => $messageController->to_timeline_item($row),
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Could not send reply.']);
    }
    exit;
}

echo json_encode(['error' => 'Unsupported method.']);
