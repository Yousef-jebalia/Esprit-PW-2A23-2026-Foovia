<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/../../model/config.php';

    // Get user ID (from session or request)
    $userId = $_GET['user_id'] ?? null;

    if (!$userId) {
        echo json_encode(['error' => 'User ID required']);
        exit;
    }

    $db = config::getConnexion();

    // Fetch workouts with category id = 6 only
    $stmt = $db->prepare("
        SELECT w.id_work, w.name_work, w.cal_work, w.duree_work, COUNT(b.id_ex) as exercises_count
        FROM workout w
        LEFT JOIN belong b ON w.id_work = b.id_work
        WHERE w.id_user = :user_id AND w.id_cat = 6
        GROUP BY w.id_work
        ORDER BY w.id_work DESC
    ");

    $stmt->execute([':user_id' => $userId]);
    $workouts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($workouts ?: []);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
}
