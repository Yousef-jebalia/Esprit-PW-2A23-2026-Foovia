<?php
require_once __DIR__ . '/../../Model/config.php';

header('Content-Type: application/json');

try {
    $db = config::getConnexion();
    $stmt = $db->query("SELECT id_ex, name_ex, type_ex, muscle_ex, cal_ex FROM exercice ORDER BY name_ex ASC");
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($exercises);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
