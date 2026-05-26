<?php
session_start();
require_once __DIR__ . '/../../Model/config.php';
require_once __DIR__ . '/../tracking/ObjectifHebdomadaire_Controller.php';
require_once __DIR__ . '/../tracking/ObjectifLongTerme_Controller.php';
require_once __DIR__ . '/../../Model/tracking/ObjectifLongTerme.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['dates'])) {
    $userId = (int)$_SESSION['user_id'];
    $dates = explode(',', $_GET['dates']);
    $results = [];

    $db = config::getConnexion();
    foreach ($dates as $date) {
        // Find suivi for this date
        $sql = "SELECT id_suiv FROM objectifhebdomadaire WHERE id_user = :id_user AND date_suiv = :date LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id_user' => $userId, 'date' => $date]);
        $suivi = $stmt->fetch();

        if ($suivi) {
            $id_suiv = (int)$suivi['id_suiv'];
            $sqlLogs = "SELECT id_rec, meal_type, meal_time, quantity FROM log_meal WHERE id_suiv = :id_suiv";
            $stmtLogs = $db->prepare($sqlLogs);
            $stmtLogs->execute(['id_suiv' => $id_suiv]);
            $logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);
            foreach ($logs as $log) {
                $results[] = [
                    'date' => $date,
                    'id_rec' => (int)$log['id_rec'],
                    'meal_type' => $log['meal_type'],
                    'meal_time' => $log['meal_time'],
                    'quantity' => (int)($log['quantity'] ?? 100)
                ];
            }
        }
    }

    echo json_encode(['success' => true, 'logs' => $results]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['meals']) || !isset($data['date'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request data.']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$date = $data['date'];
$mealsToLog = $data['meals'];
$action = $data['action'] ?? 'add';

$hebdoController = new ObjectifHebdomadaire_Controller();
$ltController = new ObjectifLongTerme_Controller();

// 1. Find the daily tracking entry for this date
$suivi = $hebdoController->get_objectif_by_user_and_date($userId, $date);

if ($action === 'delete') {
    if (!$suivi) {
        echo json_encode(['success' => true]); // Already gone
        exit;
    }
    $id_suiv = (int)$suivi['id_suiv'];
    $db = config::getConnexion();
    foreach ($mealsToLog as $m) {
        $id_rec = (int)$m['id_rec'];
        $sql = "DELETE FROM log_meal WHERE id_suiv = :id_suiv AND id_rec = :id_rec";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id_suiv' => $id_suiv, 'id_rec' => $id_rec]);
    }
    echo json_encode(['success' => true]);
    exit;
}

if (!$suivi) {
    // We need an id_obj from the long term goal to create a daily entry
    $allLtGoals = $ltController->list_objectifs();
    $userLtGoal = null;
    foreach ($allLtGoals as $g) {
        if ((int)$g['id_user'] === $userId) {
            $userLtGoal = $g;
            break;
        }
    }

    if (!$userLtGoal) {
        // Auto-create a minimal long-term goal for the user so meal logging can proceed.
        $db = config::getConnexion();
        $newId = $ltController->get_next_objectif_id();
        $today = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+31 days'));

        try {
            $insertGoal = $db->prepare(
                "INSERT INTO objectiflongterme (
                    id_obj,
                    id_user,
                    type_obj,
                    val_cible_obj,
                    val_init_obj,
                    date_deb_obj,
                    date_fin_obj,
                    status_obj,
                    frequency_rappel_obj,
                    consistancy_sport_obj,
                    consistency_alim_obj,
                    obj_cal_obj,
                    obj_fat_obj,
                    obj_prot_obj,
                    obj_carb_obj
                ) VALUES (
                    :id_obj,
                    :id_user,
                    :type_obj,
                    :val_cible_obj,
                    :val_init_obj,
                    :date_deb_obj,
                    :date_fin_obj,
                    :status_obj,
                    :frequency_rappel_obj,
                    :consistancy_sport_obj,
                    :consistency_alim_obj,
                    :obj_cal_obj,
                    :obj_fat_obj,
                    :obj_prot_obj,
                    :obj_carb_obj
                )"
            );
            $insertGoal->execute([
                'id_obj' => $newId,
                'id_user' => $userId,
                'type_obj' => 'maintien_de_poids',
                'val_cible_obj' => 70.0,
                'val_init_obj' => 70.0,
                'date_deb_obj' => $today,
                'date_fin_obj' => $endDate,
                'status_obj' => 'en_cours',
                'frequency_rappel_obj' => 1,
                'consistancy_sport_obj' => 0,
                'consistency_alim_obj' => 0,
                'obj_cal_obj' => 2000.0,
                'obj_fat_obj' => 65.0,
                'obj_prot_obj' => 150.0,
                'obj_carb_obj' => 200.0,
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Failed to auto-create a default long-term goal.']);
            exit;
        }

        $userLtGoal = [
            'id_obj' => $newId,
            'val_init_obj' => 70.0,
            'obj_cal_obj' => 2000.0,
            'obj_fat_obj' => 65.0,
            'obj_prot_obj' => 150.0,
            'obj_carb_obj' => 200.0,
        ];
    }

    // Create a minimal daily entry
    $db = $db ?? config::getConnexion();
    $id_suiv = $hebdoController->get_next_suivi_id();
    $dailyData = [
        'id_obj' => (int)$userLtGoal['id_obj'],
        'date_suiv' => $date,
        'val_cal_suiv' => 0,
        'poids_suiv' => (float)$userLtGoal['val_init_obj'],
        'val_fat_suiv' => 1, // Minimal positive values to pass validation
        'val_prot_suiv' => 1,
        'val_carb_suiv' => 1,
        'note_suiv' => 'Auto-created from Meal Planner',
        'status_obj_quot_suiv' => 'en_cours',
        'nb_verre_eau_suiv' => 0,
        'nb_h_sommeil_suiv' => 0,
        'nb_pas_suiv' => 0,
        'id_user' => $userId
    ];

    $id_suiv = $hebdoController->get_next_suivi_id();
    try {
        $dailyInsert = $db->prepare(
            "INSERT INTO objectifhebdomadaire (
                id_suiv,
                id_obj,
                date_suiv,
                val_cal_suiv,
                poids_suiv,
                val_fat_suiv,
                val_prot_suiv,
                val_carb_suiv,
                note_suiv,
                status_obj_quot_suiv,
                nb_verre_eau_suiv,
                nb_h_sommeil_suiv,
                nb_pas_suiv,
                id_user
            ) VALUES (
                :id_suiv,
                :id_obj,
                :date_suiv,
                :val_cal_suiv,
                :poids_suiv,
                :val_fat_suiv,
                :val_prot_suiv,
                :val_carb_suiv,
                :note_suiv,
                :status_obj_quot_suiv,
                :nb_verre_eau_suiv,
                :nb_h_sommeil_suiv,
                :nb_pas_suiv,
                :id_user
            )"
        );
        $dailyInsert->execute([
            'id_suiv' => $id_suiv,
            'id_obj' => (int)$userLtGoal['id_obj'],
            'date_suiv' => $date,
            'val_cal_suiv' => 0,
            'poids_suiv' => (float)$userLtGoal['val_init_obj'],
            'val_fat_suiv' => 1,
            'val_prot_suiv' => 1,
            'val_carb_suiv' => 1,
            'note_suiv' => 'Auto-created from Meal Planner',
            'status_obj_quot_suiv' => 'en_cours',
            'nb_verre_eau_suiv' => 0,
            'nb_h_sommeil_suiv' => 0,
            'nb_pas_suiv' => 0,
            'id_user' => $userId,
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Failed to initialize daily tracking. ' . $e->getMessage()]);
        exit;
    }
} else {
    $id_suiv = (int)$suivi['id_suiv'];
}

// 2. Log the meals
$db = config::getConnexion();
$successCount = 0;

$lastDbError = null;

// Auto-patch schema if quantity column is missing
try {
    $db->query("SELECT quantity FROM log_meal LIMIT 1");
} catch (Exception $e) {
    try {
        $db->exec("ALTER TABLE log_meal ADD COLUMN quantity INT DEFAULT 100");
    } catch (Exception $e2) {
        // Fallback or ignore if cannot alter
    }
}

foreach ($mealsToLog as $m) {
    $id_rec = (int)$m['id_rec'];
    $type = $m['meal_type'] ?: 'Planned Meal';
    $qty = isset($m['quantity']) ? (int)$m['quantity'] : (isset($m['qty']) ? (int)$m['qty'] : 100);

    // Check if already logged to avoid duplicates
    $check = $db->prepare("SELECT 1 FROM log_meal WHERE id_suiv = :id_suiv AND id_rec = :id_rec AND meal_type = :type LIMIT 1");
    $check->execute(['id_suiv' => $id_suiv, 'id_rec' => $id_rec, 'type' => $type]);
    if ($check->fetch()) continue;

    // We try to insert with quantity if it exists, else fallback to standard
    try {
        $sql = "INSERT INTO log_meal (id_rec, id_suiv, meal_time, meal_type, quantity)
                VALUES (:id_rec, :id_suiv, :meal_time, :meal_type, :qty)";
        $query = $db->prepare($sql);
        $ok = $query->execute([
            'id_rec' => $id_rec,
            'id_suiv' => $id_suiv,
            'meal_time' => date('Y-m-d H:i:s'),
            'meal_type' => $type,
            'qty' => $qty
        ]);
        if (!$ok) {
            $info = $query->errorInfo();
            $lastDbError = isset($info[2]) ? $info[2] : implode(' | ', $info);
        }
    } catch (Exception $e) {
        $sql = "INSERT INTO log_meal (id_rec, id_suiv, meal_time, meal_type)
                VALUES (:id_rec, :id_suiv, :meal_time, :meal_type)";
        $query = $db->prepare($sql);
        $ok = $query->execute([
            'id_rec' => $id_rec,
            'id_suiv' => $id_suiv,
            'meal_time' => date('Y-m-d H:i:s'),
            'meal_type' => $type
        ]);
        if (!$ok) {
            $info = $query->errorInfo();
            $lastDbError = isset($info[2]) ? $info[2] : implode(' | ', $info);
        }
    }
    if ($ok) $successCount++;
}

if ($successCount === 0 && $lastDbError) {
    echo json_encode(['success' => false, 'error' => 'DB insert failed: ' . $lastDbError]);
    exit;
}

echo json_encode(['success' => true, 'logged_count' => $successCount]);
