<?php
include_once __DIR__ . '/../../../Controller/SUPPORT_MODULE/Reclamtion_Controller.php';

$error = '';
$success = '';
$controller = new Controller_reclamation();
$reclamationToEdit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_recl = $_POST['id_recl'] ?? '';
    $etat = $_POST['etat_rec'] ?? '';
    $date_fermeture = $_POST['date_fermeture'] ?? '';

    if ($id_recl !== '' && $etat !== '' && $date_fermeture !== '') {
        $reclamation = new Reclamations(
            $id_recl,
            0,
            '',
            $etat,
            '',
            '',
            $date_fermeture
        );
        try {
            $controller->update_reclamation_status_and_close($reclamation);
            header('Location: Support_admin.php');
            exit;
        } catch (Exception $e) {
            $error = 'Update error: ' . $e->getMessage();
        }
    } else {
        $error = 'Status and Closing Date are required.';
    }
}

if (isset($_GET['id']) && $_GET['id'] !== '') {
    $reclamationToEdit = $controller->get_reclamation_by_id($_GET['id']);
    if (!$reclamationToEdit) {
        $error = 'Claim not found.';
    }
} else {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $error = 'Claim ID missing.';
    }
}

$id_recl = $reclamationToEdit['id_reclam'] ?? ($_POST['id_recl'] ?? '');
$description = $reclamationToEdit['description_reclam'] ?? '';
$etat = $reclamationToEdit['etat_reclam'] ?? ($_POST['etat_rec'] ?? '');
$date_fermeture = $reclamationToEdit['dateferm_reclam'] ?? ($_POST['date_fermeture'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Claim</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>

<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Edit Claim</h5>
                            <small>Change only the status and closing date.</small>
                        </div>
                        <a href="Support_admin.php" class="btn btn-secondary btn-sm">Back</a>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        <form method="post" novalidate>
                            <input type="hidden" name="id_recl" value="<?php echo htmlspecialchars($id_recl); ?>">
                            <div class="mb-3">
                                <label for="id_recl" class="form-label">Claim ID</label>
                                <input type="text" class="form-control" id="id_recl" value="<?php echo htmlspecialchars($id_recl); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" rows="3" readonly><?php echo htmlspecialchars($description); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="etat_rec" class="form-label">Status</label>
                                <input type="text" class="form-control" id="etat_rec" name="etat_rec" value="<?php echo htmlspecialchars($etat); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="date_fermeture" class="form-label">Closing Date</label>
                                <input type="date" class="form-control" id="date_fermeture" name="date_fermeture" value="<?php echo htmlspecialchars($date_fermeture); ?>">
                            </div>
                            <button type="submit" class="btn btn-success">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
