<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/ai_workout.php';

    $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
    $isJson = stripos($contentType, 'application/json') !== false;

    if ($isJson) {
        $data = json_decode(file_get_contents('php://input'), true);
        $workoutName = $data['workoutName'] ?? null;
        $targetMuscles = $data['targetMuscles'] ?? [];
        $userId = $data['userId'] ?? null;
        $aiService = $data['aiService'] ?? 'gemini';
        $picWork = null;
    } else {
        $workoutName = trim((string)($_POST['workoutName'] ?? ''));
        $targetMuscles = json_decode((string)($_POST['targetMuscles'] ?? '[]'), true);
        $userId = $_POST['userId'] ?? ($_SESSION['user_id'] ?? null);
        $aiService = $_POST['aiService'] ?? 'gemini';
        $picWork = null;

        if (isset($_FILES['work_picture']) && $_FILES['work_picture']['error'] === UPLOAD_ERR_OK) {
            $picWork = file_get_contents($_FILES['work_picture']['tmp_name']);
        }
    }

    if ($workoutName === '' || $userId === null || $userId === '' || empty($targetMuscles)) {
        $missing = [];
        if ($workoutName === '') $missing[] = 'workoutName';
        if ($userId === null || $userId === '') $missing[] = 'userId';
        if (empty($targetMuscles)) $missing[] = 'targetMuscles';

        echo json_encode([
            'error' => 'Missing required fields: ' . implode(', ', $missing),
            'debug' => [
                'workoutName' => $workoutName,
                'userId' => $userId,
                'targetMuscles' => $targetMuscles,
                'POST' => $_POST
            ]
        ]);
        exit;
    }

    // Generate AI workout
    $aiData = generateAIWorkout($workoutName, $targetMuscles, $aiService);

    if (!$aiData || isset($aiData['error'])) {
        echo json_encode($aiData ?? ['error' => 'Unknown error generating workout']);
        exit;
    }

    // Save to database
    $saved = saveAIWorkout($workoutName, $aiData, $userId, $picWork ?? null);

    if (!$saved) {
        echo json_encode(['error' => 'Failed to save workout to database']);
        exit;
    }

    echo json_encode($saved);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Exception: ' . $e->getMessage(),
        'debug' => [
            'type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]
    ]);
}
