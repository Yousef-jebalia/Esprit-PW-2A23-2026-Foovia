<?php   

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once __DIR__ . '/../model/exercice.php';
include_once __DIR__ . '/../model/config.php';


class controle_exercice
{
    
   function add_exercise($exercise) {
    $sql = "INSERT INTO exercice VALUES (NULL, :name_ex, :type_ex, :muscle_ex, :cal_ex, :fatigue_ex, :PR_ex, :description_ex, :gif_ex)";
    $db = config::getConnexion();
    try {
        $query = $db->prepare($sql);
        $query->execute([
            'name_ex'        => $exercise->getNameEx(),
            'type_ex'        => $exercise->getTypeEx(),
            'muscle_ex'      => $exercise->getMuscleEx(),
            'cal_ex'         => $exercise->getCalEx(),
            'fatigue_ex'     => $exercise->getFatigueEx(),
            'PR_ex'          => $exercise->getPREx(),
            'description_ex' => $exercise->getDescriptionEx(),
            'gif_ex'         => $exercise->getGifEx(),
        ]);
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
                description_ex = :description_ex,
                gif_ex         = :gif_ex
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
            'gif_ex'         => $exercise->getGifEx(),
        ]);
        echo $query->rowCount() . " records UPDATED successfully <br>";
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
    }
}

function delete_exercise($id)
{
    $sql = "DELETE FROM exercise WHERE id_ex = :id";
    $db = config::getConnexion();
    $req = $db->prepare($sql);
    $req->bindValue(':id', $id);
    try {
        $req->execute();
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    // Handle delete
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $controller = new controle_exercice();
        $controller->delete_exercise( (int)$_POST['delete_id'] );
        header('Location: ../view/back_office/form-elements-component.php');
        exit;
    }

    
echo '<pre>POST: ' . print_r($_POST, true) . "\nFILES: " . print_r($_FILES, true) . '</pre>';
    $name = $_POST['ex_name'] ?? '';
    $type = $_POST['ex_type'] ?? '';
    $muscle = $_POST['ex_target_muscle'] ?? '';
    $description = $_POST['ex_description'] ?? '';
    $calories = (int)($_POST['ex_calories'] ?? 0);
    $fatigue = (float)($_POST['ex_fatigue'] ?? 0);
    $gif = null;
    if (isset($_FILES['ex_picture']) && $_FILES['ex_picture']['error'] === UPLOAD_ERR_OK) {
        $gif = $_FILES['ex_picture']['name'];
    }

    $exercise = new Exercise(null, $name, $type, $muscle, $calories, $fatigue, 0.0, $description, $gif);
    $controller = new controle_exercice();
    $result = $controller->add_exercise($exercise);

    if ($result === true) {
        echo "<script>alert('Exercise added successfully!'); window.location = '../view/back_office/form-elements-component.php';</script>";
    } else {
        echo "<script>alert('Error: $result');</script>";
    }
    exit;
}

?>