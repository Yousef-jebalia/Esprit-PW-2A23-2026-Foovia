<?php
require_once __DIR__ . '/../../model/config.php';

header('Content-Type: application/json');

try {
    $db = config::getConnexion();
    
    // Get all workouts with exercise counts
    $stmt = $db->prepare("
        SELECT 
            w.id_work,
            w.name_work,
            w.cal_work,
            w.duree_work,
            w.id_cat,
            COUNT(b.id_ex) as exercises_count
        FROM workout w
        LEFT JOIN belong b ON b.id_work = w.id_work
        GROUP BY w.id_work
        ORDER BY w.id_work DESC
    ");
    $stmt->execute();
    $workouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'workouts' => $workouts
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
