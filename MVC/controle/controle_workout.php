<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once __DIR__ . '/../model/workout.php';
include_once __DIR__ . '/../model/config.php';

class controle_workout
{
    function add_workout($workout) {
        $sql = "INSERT INTO workout (name_work, pic_work, nb_work, cal_work, duree_work, id_user)
                VALUES (:name_work, :pic_work, :nb_work, :cal_work, :duree_work, :id_user)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue('name_work',  $workout->getNameWork());
            $query->bindValue('pic_work',   $workout->getPicWork(), PDO::PARAM_LOB);
            $query->bindValue('nb_work',    $workout->getNbWork());
            $query->bindValue('cal_work',   $workout->getCalWork());
            $query->bindValue('duree_work', $workout->getDureeWork());
            $query->bindValue('id_user',    $workout->getIdUser());
            $query->execute();
            return true;
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
                    nb_work    = :nb_work,
                    cal_work   = :cal_work,
                    duree_work = :duree_work,
                    id_user    = :id_user
                WHERE id_work = :id'
            );
            $query->execute([
                'id'         => $id,
                'name_work'  => $workout->getNameWork(),
                'pic_work'   => $workout->getPicWork(),
                'nb_work'    => $workout->getNbWork(),
                'cal_work'   => $workout->getCalWork(),
                'duree_work' => $workout->getDureeWork(),
                'id_user'    => $workout->getIdUser(),
            ]);
            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    function delete_workout($id)
    {
        $sql = "DELETE FROM workout WHERE id_work = :id";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id', $id, PDO::PARAM_INT);
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}

// ── Handling POST Requests ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action'] ?? 'add';
    $controller = new controle_workout();

    if ($action === 'delete') {
        $controller->delete_workout((int)$_POST['delete_id']);
        header('Location: ../view/back_office/form-elements-component.php');
        exit;
    }

    // Capture Inputs
    $name  = (int)($_POST['work_name'] ?? 0);
    $nb    = (int)($_POST['work_nb'] ?? 0);
    $cal   = (int)($_POST['work_cal'] ?? 0);
    $duree = (int)($_POST['work_duree'] ?? 0);
    $user  = (int)($_POST['id_user'] ?? 0);
    
    // Handle File Upload
    $pic = null;
    if (isset($_FILES['work_picture']) && $_FILES['work_picture']['error'] === UPLOAD_ERR_OK) {
        $pic = file_get_contents($_FILES['work_picture']['tmp_name']);
    }

    $workout = new Workout($name, $pic, $nb, $cal, $duree, $user);

    if ($action === 'update') {
        $result = $controller->update_workout($workout, (int)$_POST['edit_id']);
    } else {
        $result = $controller->add_workout($workout);
    }

    if ($result === true) {
        header('Location: ../view/back_office/form-elements-component.php');
    } else {
        echo "<script>alert('Error: " . addslashes($result) . "');</script>";
    }
    exit;
}
?>