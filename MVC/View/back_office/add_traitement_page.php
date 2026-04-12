<?php
include __DIR__ . '/../../../MVC/Controller/Traitemant_Controller.php';

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
        $controller->add_traitement($traitement);
        header('Location: Support_admin.php');
        exit;
    } else {
        $error = 'Tous les champs sont requis pour ajouter un traitement.';
    }
}

$id_traitement = $id_traitement ?? '';
$id_reclam = $id_reclam ?? '';
$comment = $comment ?? '';
$status = $status ?? '';
$date_trait = $date_trait ?? '';
$id_user = $id_user ?? '';
$editMode = false;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Ajouter / Modifier Traitement</title>
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
                            <h5 class="mb-0"><?php echo $editMode ? 'Modifier traitement' : 'Ajouter traitemant'; ?></h5>
                            <small><?php echo $editMode ? 'Modifiez les informations du traitement existant.' : 'Remplissez les informations pour ajouter un nouveau traitement.'; ?></small>
                        </div>
                        <a href="Support_admin.php" class="btn btn-secondary btn-sm">Retour</a>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        <form method="post">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label for="id_traitement" class="form-label">ID Traitement</label>
                                <input type="number" class="form-control" id="id_traitement" name="id_traitement" value="<?php echo htmlspecialchars($id_traitement); ?>" <?php echo $editMode ? 'readonly' : ''; ?> required>
                            </div>
                            <div class="mb-3">
                                <label for="id_reclam" class="form-label">ID Réclamation</label>
                                <input type="text" class="form-control" id="id_reclam" name="id_reclam" value="<?php echo htmlspecialchars($id_reclam); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Commentaire</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3" required><?php echo htmlspecialchars($comment); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <input type="text" class="form-control" id="status" name="status" value="<?php echo htmlspecialchars($status); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="date_trait" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date_trait" name="date_trait" value="<?php echo htmlspecialchars($date_trait); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="id_user" class="form-label">ID User</label>
                                <input type="number" class="form-control" id="id_user" name="id_user" value="<?php echo htmlspecialchars($id_user); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-success"><?php echo $editMode ? 'Mettre à jour' : 'Ajouter'; ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
