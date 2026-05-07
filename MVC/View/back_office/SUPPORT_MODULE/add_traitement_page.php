<?php
include_once __DIR__ . '/../../../Controller/SUPPORT_MODULE/Traitemant_Controller.php';
include_once __DIR__ . '/../../../Controller/SUPPORT_MODULE/Reclamtion_Controller.php';

$error = '';
$success = '';
$controller = new Controller_traitement();
$reclamationController = new Controller_reclamation();
$traitementToEdit = null;

// Get claim IDs and user IDs for dropdowns
$claimIds = $reclamationController->get_all_claim_ids();
$userIds = $reclamationController->get_all_user_ids();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_reclam = $_POST['id_reclam'] ?? '';
    $comment = $_POST['comment'] ?? '';
    $status = $_POST['status'] ?? '';
    $date_trait = $_POST['date_trait'] ?? '';
    $id_user = $_POST['id_user'] ?? '';

    if ($id_reclam !== '' && $comment !== '' && $status !== '' && $date_trait !== '' && $id_user !== '') {
        $traitement = new Traitements(
            0,
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
        $error = 'All fields are required to add a treatment.';
    }
}

$id_reclam = $_GET['id_reclam'] ?? $id_reclam ?? '';
$comment = $comment ?? '';
$status = $status ?? '';
$date_trait = $date_trait ?? '';
$id_user = $_GET['id_user'] ?? $id_user ?? '';
$editMode = false;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Add / Edit Treatment</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo $editMode ? 'Edit Treatment' : 'Add Treatment'; ?></h5>
                            <small><?php echo $editMode ? 'Modify the information of the existing treatment.' : 'Fill in the information to add a new treatment.'; ?></small>
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
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label for="id_reclam" class="form-label">Claim ID</label>
                                <?php if ($id_reclam !== ''): ?>
                                    <input type="hidden" name="id_reclam" value="<?php echo htmlspecialchars($id_reclam); ?>">
                                    <input type="text" class="form-control" id="id_reclam" value="<?php echo htmlspecialchars($id_reclam); ?>" readonly>
                                <?php else: ?>
                                    <select class="form-control" id="id_reclam" name="id_reclam">
                                        <option value="">-- Select Claim ID --</option>
                                        <?php foreach ($claimIds as $claimId): ?>
                                            <option value="<?php echo htmlspecialchars($claimId); ?>" <?php echo $id_reclam === $claimId ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($claimId); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Comment</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3"><?php echo htmlspecialchars($comment); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <input type="text" class="form-control" id="status" name="status" value="<?php echo htmlspecialchars($status); ?>" maxlength="10">
                            </div>
                            <div class="mb-3">
                                <label for="date_trait" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date_trait" name="date_trait" value="<?php echo htmlspecialchars($date_trait); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="id_user" class="form-label">User ID</label>
                                <?php if ($id_user !== ''): ?>
                                    <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($id_user); ?>">
                                    <input type="text" class="form-control" id="id_user" value="<?php echo htmlspecialchars($id_user); ?>" readonly>
                                <?php else: ?>
                                    <select class="form-control" id="id_user" name="id_user">
                                        <option value="">-- Select User ID --</option>
                                        <?php foreach ($userIds as $userId): ?>
                                            <option value="<?php echo htmlspecialchars($userId); ?>" <?php echo $id_user == $userId ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($userId); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-success"><?php echo $editMode ? 'Update' : 'Add'; ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="verif_traitements.js"></script>
</body>

</html>
