<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once __DIR__ . '/../../model/SPORT_MOULE/exercice.php';
include_once __DIR__ . '/../../Model/config.php';


class controle_exercice
{
    function add_exercise($exercise) {
        $sql = "INSERT INTO exercice (name_ex, type_ex, muscle_ex, cal_ex, fatigue_ex, PR_ex, description_ex, gif_ex)
                VALUES (:name_ex, :type_ex, :muscle_ex, :cal_ex, :fatigue_ex, :PR_ex, :description_ex, :gif_ex)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue('name_ex',        $exercise->getNameEx());
            $query->bindValue('type_ex',        $exercise->getTypeEx());
            $query->bindValue('muscle_ex',      $exercise->getMuscleEx());
            $query->bindValue('cal_ex',         $exercise->getCalEx());
            $query->bindValue('fatigue_ex',     $exercise->getFatigueEx());
            $query->bindValue('PR_ex',          $exercise->getPREx());
            $query->bindValue('description_ex', $exercise->getDescriptionEx());
            $query->bindValue('gif_ex',         $exercise->getGifEx(), PDO::PARAM_LOB);
            $query->execute();
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function update_exercise($exercise, $id)
    {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE exercice SET
                    name_ex        = :name_ex,
                    type_ex        = :type_ex,
                    muscle_ex      = :muscle_ex,
                    cal_ex         = :cal_ex,
                    fatigue_ex     = :fatigue_ex,
                    PR_ex          = :PR_ex,
                    description_ex = :description_ex
                WHERE id_ex = :id'
            );
            $query->execute([
                'id'             => $id,
                'name_ex'        => $exercise->getNameEx(),
                'type_ex'        => $exercise->getTypeEx(),
                'muscle_ex'      => $exercise->getMuscleEx(),
                'cal_ex'         => $exercise->getCalEx(),
                'fatigue_ex'     => $exercise->getFatigueEx(),
                'PR_ex'          => $exercise->getPREx(),
                'description_ex' => $exercise->getDescriptionEx(),
            ]);
            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    function delete_exercise($id)
    {
        $sql = "DELETE FROM exercice WHERE id_ex = :id";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id', $id, PDO::PARAM_INT);
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

} // end of class


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action     = $_POST['action'] ?? 'add';
    $controller = new controle_exercice();

    // ── DELETE ──────────────────────────────────────────────
    if ($action === 'delete') {
        $controller->delete_exercise((int)$_POST['delete_id']);
        header('Location: ../../view/back_office/SPORT_MOULE/form-elements-component.php');
        exit;
    }

    // ── Shared: extract & process muscles ───────────────────
    $muscleRaw = $_POST['ex_target_muscle'] ?? [];
    $muscle    = is_array($muscleRaw) ? implode(',', array_slice($muscleRaw, 0, 3)) : $muscleRaw;

    // ── UPDATE ──────────────────────────────────────────────
    if ($action === 'update') {
        $name        = $_POST['ex_name']        ?? '';
        $type        = $_POST['ex_type']        ?? '';
        $description = $_POST['ex_description'] ?? '';
        $calories    = (int)($_POST['ex_calories'] ?? 0);
        $fatigue     = (float)($_POST['ex_fatigue'] ?? 0);

        $exercise = new Exercise($name, $type, $muscle, $calories, $fatigue, $description, null, null, 0.0);
        $result   = $controller->update_exercise($exercise, (int)$_POST['edit_id']);

        if ($result === true) {
            header('Location: ../../view/back_office/SPORT_MOULE/form-elements-component.php');
        } else {
            echo "<script>alert('Error: " . addslashes($result) . "');</script>";
        }
        exit;
    }

    // ── ADD (default) ────────────────────────────────────────
    $name        = $_POST['ex_name']        ?? '';
    $type        = $_POST['ex_type']        ?? '';
    $description = $_POST['ex_description'] ?? '';
    $calories    = (int)($_POST['ex_calories'] ?? 0);
    $fatigue     = (float)($_POST['ex_fatigue'] ?? 0);
    $gif         = null;

    if (isset($_FILES['ex_picture']) && $_FILES['ex_picture']['error'] === UPLOAD_ERR_OK) {
        $gif = file_get_contents($_FILES['ex_picture']['tmp_name']);
    }

    $exercise = new Exercise($name, $type, $muscle, $calories, $fatigue, $description, $gif, null, 0.0);
    $result   = $controller->add_exercise($exercise);

    if ($result === true) {
        header('Location: ../../view/back_office/SPORT_MOULE/form-elements-component.php');
    } else {
        echo "<script>alert('Error: " . addslashes($result) . "');</script>";
    }
    exit;
}
?>