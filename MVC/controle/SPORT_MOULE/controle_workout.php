<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once __DIR__ . '/../../model/SPORT_MOULE/workout.php';
include_once __DIR__ . '/../../model/config.php';
include_once __DIR__ . '/controle_categorie.php';

class controle_workout
{
    function add_workout($workout) {
    $sql = "INSERT INTO workout (name_work, pic_work, cal_work, duree_work, id_user, id_cat)
        VALUES (:name_work, :pic_work, :cal_work, :duree_work, :id_user, :id_cat)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue('name_work',  $workout->getNameWork());
            $query->bindValue('pic_work',   $workout->getPicWork(), PDO::PARAM_LOB);
            $query->bindValue('cal_work',   $workout->getCalWork());
            $query->bindValue('duree_work', $workout->getDureeWork());
            $query->bindValue('id_user',    $workout->getIdUser());
            $query->bindValue('id_cat',     $workout->getIdCat(), PDO::PARAM_INT);
            $query->execute();
            return (int)$db->lastInsertId();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function update_workout($workout, $id)
    {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE workout SET
                    name_work  = :name_work,
                    pic_work   = :pic_work,
                    cal_work   = :cal_work,
                    duree_work = :duree_work,
                    id_user    = :id_user,
                    id_cat     = :id_cat
                WHERE id_work = :id'
            );
            $query->execute([
                'id'         => $id,
                'name_work'  => $workout->getNameWork(),
                'pic_work'   => $workout->getPicWork(),
                'cal_work'   => $workout->getCalWork(),
                'duree_work' => $workout->getDureeWork(),
                'id_user'    => $workout->getIdUser(),
                'id_cat'     => $workout->getIdCat(),
            ]);
            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    function delete_workout($id)
    {
        $db = config::getConnexion();
        try {
            $db->beginTransaction();

            $deleteBelong = $db->prepare("DELETE FROM belong WHERE id_work = :id");
            $deleteBelong->bindValue(':id', $id, PDO::PARAM_INT);
            $deleteBelong->execute();

            $deleteWorkout = $db->prepare("DELETE FROM workout WHERE id_work = :id");
            $deleteWorkout->bindValue(':id', $id, PDO::PARAM_INT);
            $deleteWorkout->execute();

            $db->commit();
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            die('Error: ' . $e->getMessage());
        }
    }

    
function replace_belong_for_workout(int $workoutId, array $selectedExercises)
{
    $db = config::getConnexion();

    try {
        $db->beginTransaction();

        $delete = $db->prepare("DELETE FROM belong WHERE id_work = :id_work");
        $delete->execute(['id_work' => $workoutId]);

        $insert = $db->prepare(
            "INSERT INTO belong (id_ex, id_work, sets, weight, `time`, reps)
             VALUES (:id_ex, :id_work, :sets, :weight, :time, :reps)"
        );

        foreach ($selectedExercises as $item) {
            $idEx = (int)($item['id_ex'] ?? 0);
            if ($idEx <= 0) {
                continue;
            }

            $isCardio = strtolower(trim((string)($item['type_ex'] ?? ''))) === 'cardio';

            $sets = $isCardio ? 0 : max(1, (int)($item['sets'] ?? 0));
            $weight = $isCardio ? 0 : max(0, (float)($item['weight'] ?? 0));
            $time = $isCardio
                ? max(1, (int)($item['time'] ?? 0))
                : max(1, (int)($item['reps'] ?? 0));
            $reps = $isCardio ? 0 : max(1, (int)($item['reps'] ?? $time));

            $insert->execute([
                'id_ex' => $idEx,
                'id_work' => $workoutId,
                'sets' => $sets,
                'weight' => $weight,
                'time' => $time,
                'reps' => $reps,
            ]);
        }

        $db->commit();
        return true;
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return $e->getMessage();
    }
}



function compute_workout_calories(array $selectedExercises): int
{
    $exerciseIds = array_values(array_unique(array_filter(array_map(
        fn($item) => (int)($item['id_ex'] ?? 0),
        $selectedExercises
    ))));

    if (empty($exerciseIds)) {
        return 0;
    }

    $db = config::getConnexion();
    $placeholders = implode(',', array_fill(0, count($exerciseIds), '?'));

    $stmt = $db->prepare("SELECT id_ex, cal_ex FROM exercice WHERE id_ex IN ($placeholders)");
    $stmt->execute($exerciseIds);

    $caloriesByExercise = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $caloriesByExercise[(int)$row['id_ex']] = (float)$row['cal_ex'];
    }

    $totalCalories = 0;
    foreach ($selectedExercises as $item) {
        if (strtolower(trim((string)($item['type_ex'] ?? ''))) === 'cardio') {
            continue;
        }

        $idEx = (int)($item['id_ex'] ?? 0);
        $sets = max(1, (int)($item['sets'] ?? 0));
        $reps = max(1, (int)($item['reps'] ?? 0));
        $calories = (float)($caloriesByExercise[$idEx] ?? 0);

        $totalCalories += $sets * $reps * $calories;
    }

    return (int) round($totalCalories);
}


}//end of class controle_workout




// ── Handling POST Requests ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action'] ?? 'add';
    $controller = new controle_workout();
    $categoryController = new controle_categorie_workout();

    if ($action === 'add_category') {
        $newCategoryName = trim((string)($_POST['new_work_categorie'] ?? ''));
        $createdCategoryId = $categoryController->add_category(new Categorie($newCategoryName));

        if (is_int($createdCategoryId) && $createdCategoryId > 0) {
            header('Location: ../../view/back_office/SPORT_MOULE/form-elements-component.php?section=workout&category_added=1&category_id=' . $createdCategoryId);
        } else {
            $error = urlencode((string)$createdCategoryId);
            header('Location: ../../view/back_office/SPORT_MOULE/form-elements-component.php?section=workout&category_error=' . $error);
        }
        exit;
    }

    if ($action === 'delete') {
        $controller->delete_workout((int)$_POST['delete_id']);
        header('Location: ../../view/back_office/SPORT_MOULE/form-elements-component.php');
        exit;
    }

    if ($action === 'delete_category') {
        $idCat = (int)($_POST['delete_id_cat'] ?? 0);
        $deleteResult = $categoryController->delete_category($idCat);

        if ($deleteResult === true) {
            header('Location: ../../view/back_office/SPORT_MOULE/form-elements-component.php?section=workout&category_deleted=1');
        } else {
            $error = urlencode((string)$deleteResult);
            header('Location: ../../view/back_office/SPORT_MOULE/form-elements-component.php?section=workout&category_delete_error=' . $error);
        }
        exit;
    }

    // Capture Inputs
    $name  = $_POST['work_name'] ?? '';
    $duree = (int)($_POST['work_duree'] ?? 0);
    $user  = (int)($_POST['id_user'] ?? 0);
    $selectedCategoryId = (int)($_POST['id_cat'] ?? 0);
    $newCategoryName = trim((string)($_POST['new_work_categorie'] ?? ''));

    $resolvedCategoryId = $categoryController->resolve_workout_category_id($selectedCategoryId, $newCategoryName);
    if (!is_int($resolvedCategoryId) || $resolvedCategoryId <= 0) {
        echo "<script>alert('Error: " . addslashes((string)$resolvedCategoryId) . "');</script>";
        exit;
    }
    
    // Handle File Upload
    $pic = null;
    if (isset($_FILES['work_picture']) && $_FILES['work_picture']['error'] === UPLOAD_ERR_OK) {
        $pic = file_get_contents($_FILES['work_picture']['tmp_name']);
    }

    $selectedExercises = [];
    if (!empty($_POST['selected_exercises'])) {
        $decoded = json_decode($_POST['selected_exercises'], true);
        if (is_array($decoded)) {
            $selectedExercises = $decoded;
        }
    }

    $cal = $controller->compute_workout_calories($selectedExercises);

    $workout = new Workout($name, $pic, $cal, $duree, $user, $resolvedCategoryId);

    if ($action === 'update') {
        $workoutId = (int)$_POST['edit_id'];
        $result = $controller->update_workout($workout, $workoutId);
        if ($result === true && isset($_POST['selected_exercises']) && trim((string)$_POST['selected_exercises']) !== '') {
            $result = $controller->replace_belong_for_workout($workoutId, $selectedExercises);
        }
    } else {
        $newWorkoutId = $controller->add_workout($workout);
        if (is_int($newWorkoutId) && $newWorkoutId > 0) {
            $result = $controller->replace_belong_for_workout($newWorkoutId, $selectedExercises);
        } else {
            $result = $newWorkoutId;
        }
    }

    if ($result === true) {
        header('Location: ../../view/back_office/SPORT_MOULE/form-elements-component.php');
    } else {
        echo "<script>alert('Error: " . addslashes($result) . "');</script>";
    }
    exit;
}
?>