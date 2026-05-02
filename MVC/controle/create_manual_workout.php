<?php
require_once __DIR__ . '/../model/config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!$input || empty($input['name']) || empty($input['id_cat']) || empty($input['exercises'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$name = $input['name'];
$id_cat = (int)$input['id_cat'];
$duree_work = isset($input['duree_work']) ? (int)$input['duree_work'] : 30;
$cal_work = isset($input['cal_work']) ? (int)$input['cal_work'] : 200;
$pic_work = $input['pic_work'] ?? null;
$id_user = isset($input['id_user']) ? (int)$input['id_user'] : 1;
$exercises = $input['exercises']; // array of exercise ids or objects with details

if (!is_array($exercises) || empty($exercises)) {
    http_response_code(400);
    echo json_encode(['error' => 'No exercises selected']);
    exit;
}

try {
    $db = config::getConnexion();
    
    // 1) Insert into workout table
    $insertWorkout = $db->prepare(
        "INSERT INTO workout (name_work, pic_work, cal_work, duree_work, id_user, id_cat) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $insertWorkout->execute([$name, $pic_work, $cal_work, $duree_work, $id_user, $id_cat]);
    $workoutId = $db->lastInsertId();
    
    // 2) Insert exercises into belong table
    $insertBelong = $db->prepare(
        "INSERT INTO belong (id_work, id_ex, sets, weight, `time`) 
         VALUES (?, ?, ?, ?, ?)"
    );
    
    foreach ($exercises as $ex) {
        // Handle if exercise is just an ID or an object with details
        if (is_array($ex)) {
            $ex_id = (int)$ex['id_ex'];
            $sets = isset($ex['sets']) ? (int)$ex['sets'] : 3;
            $weight = isset($ex['weight']) ? (float)$ex['weight'] : 0;
            $time = isset($ex['time']) ? (int)$ex['time'] : 30;
        } else {
            $ex_id = (int)$ex;
            $sets = 3;
            $weight = 0;
            $time = 30;
        }
        
        $insertBelong->execute([
            $workoutId,
            $ex_id,
            $sets,
            $weight,
            $time
        ]);
    }
    
    echo json_encode([
        'ok' => true,
        'id_work' => $workoutId,
        'message' => 'Workout created successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

