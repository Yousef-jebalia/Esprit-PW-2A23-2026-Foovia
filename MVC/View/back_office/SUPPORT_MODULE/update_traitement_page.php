<?php
include_once __DIR__ . '/../../../Controller/SUPPORT_MODULE/Traitemant_Controller.php';

$error = '';
$success = '';
$controller = new Controller_traitement();
$traitementToEdit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_traitement = $_POST['id_traitement'] ?? '';
    $id_reclam = $_POST['id_reclam'] ?? '';
    $comment = $_POST['comment'] ?? '';
    $status = $_POST['status'] ?? '';
    $date_trait = $_POST['date_trait'] ?? '';
    $id_user = $_POST['id_user'] ?? '';

    if ($id_traitement !== '' && $id_reclam !== '' && $comment !== '' && $status !== '' && $date_trait !== '' && $id_user !== '') {
        $traitement = new Traitements(
            (int)$id_traitement,
            (int)$id_user,
            $id_reclam,
            $comment,
            $status,
            $date_trait
        );
        $controller->update_traitement($traitement);
        header('Location: Support_admin.php');
        exit;
    }

    $error = 'All fields are required to edit the treatment.';
}

if (isset($_GET['id']) && $_GET['id'] !== '') {
    $traitementToEdit = $controller->get_traitement_by_id($_GET['id']);
    if (!$traitementToEdit) {
        $error = 'Treatment not found.';
    }
} else {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $error = 'Treatment ID missing.';
    }
}

$id_traitement = $traitementToEdit['id_traitement'] ?? ($_POST['id_traitement'] ?? '');
$id_reclam = $traitementToEdit['id_reclam'] ?? ($_POST['id_reclam'] ?? '');
$comment = $traitementToEdit['comment_trait'] ?? ($_POST['comment'] ?? '');
$status = $traitementToEdit['status_trait'] ?? ($_POST['status'] ?? '');
$date_trait = $traitementToEdit['date__trait'] ?? ($_POST['date_trait'] ?? '');
$id_user = $traitementToEdit['id_user'] ?? ($_POST['id_user'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Edit Treatment</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
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
                            <h5 class="mb-0">Edit Treatment</h5>
                            <small>Edit the existing treatment details.</small>
                        </div>
                        <a href="Support_admin.php" class="btn btn-secondary btn-sm">Back</a>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="post" novalidate>
                            <div class="mb-3">
                                <label for="id_traitement" class="form-label">Treatment ID</label>
                                <input type="text" class="form-control" id="id_traitement" name="id_traitement" value="<?php echo htmlspecialchars($id_traitement); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="id_reclam" class="form-label">Claim ID</label>
                                <input type="text" class="form-control" id="id_reclam" name="id_reclam" value="<?php echo htmlspecialchars($id_reclam); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Comment</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3"><?php echo htmlspecialchars($comment); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <input type="text" class="form-control" id="status" name="status" value="<?php echo htmlspecialchars($status); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="date_trait" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date_trait" name="date_trait" value="<?php echo htmlspecialchars($date_trait); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="id_user" class="form-label">ID User</label>
                                <input type="text" class="form-control" id="id_user" name="id_user" value="<?php echo htmlspecialchars($id_user); ?>">
                            </div>
                            <button type="submit" class="btn btn-success">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="verif_traitements.js"></script>
</body>

</html>
