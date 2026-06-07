<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . '/Thread_Controller.php';

$threadController = new Thread_Controller();
$userId = (int) ($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $threadId = (int) ($_GET['thread_id'] ?? 0);
    $afterId = (int) ($_GET['after_id'] ?? 0);

    if ($threadId <= 0) {
        echo json_encode(['error' => 'Invalid thread.']);
        exit;
    }

    $rows = $threadController->get_messages_after($threadId, $afterId);
    $messages = array_map([$threadController, 'to_timeline_item'], $rows);
    echo json_encode(['messages' => $messages]);
    exit;
}

if ($userId <= 0) {
    echo json_encode(['error' => 'Not logged in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        echo json_encode(['error' => 'Invalid request.']);
        exit;
    }

    $threadId = (int) ($input['thread_id'] ?? 0);
    $body = trim((string) ($input['body'] ?? ''));

    if ($threadId <= 0) {
        echo json_encode(['error' => 'Invalid thread.']);
        exit;
    }
    if ($body === '') {
        echo json_encode(['error' => 'Reply cannot be empty.']);
        exit;
    }

    try {
        $msg = new ThreadMessage(0, $threadId, $userId, $body, '');
        $newId = $threadController->add_message($msg);
        $row = $threadController->get_message_by_id($newId);
        if (!$row) {
            echo json_encode(['error' => 'Message saved but could not be loaded.']);
            exit;
        }
        echo json_encode([
            'success' => true,
            'message' => $threadController->to_timeline_item($row),
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Could not send reply.']);
    }
    exit;
}

echo json_encode(['error' => 'Unsupported method.']);
