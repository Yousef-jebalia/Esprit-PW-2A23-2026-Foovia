<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../../controller/Controller_user.php';

$controller = new Controller_user();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_profile'])) {
    $controller->delete_user($_SESSION['user_id']);
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {

    $current = $controller->get_user($_SESSION['user_id']);

    $height = (int)($_POST['height_user'] ?? $current['height_user']);
    $weight = (int)($_POST['weight_user'] ?? $current['weight_user']);
    $bmi    = ($height > 0) ? (int)round($weight / (($height / 100) ** 2)) : (int)$current['bmi_user'];

    $user = new User(
        (int)$_SESSION['user_id'],
        $_POST['name_user']         ?? $current['name_user'],
        $_POST['lastname_user']     ?? $current['lastname_user'],
        $_POST['email_user']        ?? $current['email_user'],
        $current['password_user'],
        (int)($_POST['phone_user']  ?? $current['phone_user']),
        $_POST['gender_user']       ?? $current['gender_user'],
        $_POST['birthday_user']     ?? $current['birthday_user'],
        $height,
        $weight,
        $bmi,
        $_POST['activitylvl_user']  ?? $current['activitylvl_user'],
        $_POST['illness_user']      ?? $current['illness_user'],
        $_POST['allergie_user']     ?? $current['allergie_user'],
        $_POST['medicament_user']   ?? $current['medicament_user'],
        $current['inscriptiondate_user'],
        $current['role_user']
    );

    $controller->update_user($user, $_SESSION['user_id']);

    $saved = true;
}

$user_data = $controller->get_user($_SESSION['user_id']) ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Foovia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        .field-value  { display: block; }
        .field-input  { display: none; }
        .editing .field-value { display: none; }
        .editing .field-input { display: block; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>My Account</h2>

    <?php if (isset($saved) && $saved): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Profile updated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="profile.php" id="profileForm">
        <input type="hidden" name="save_profile" value="1">

        <div class="card" id="profileCard">
            <div class="card-body">

                <!-- First Name / Last Name -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>First Name:</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['name_user'] ?? 'N/A'); ?></span>
                        <input class="form-control field-input" type="text" name="name_user"
                               value="<?php echo htmlspecialchars($user_data['name_user'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Last Name:</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['lastname_user'] ?? 'N/A'); ?></span>
                        <input class="form-control field-input" type="text" name="lastname_user"
                               value="<?php echo htmlspecialchars($user_data['lastname_user'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Email / Phone -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Email:</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['email_user'] ?? 'N/A'); ?></span>
                        <input class="form-control field-input" type="email" name="email_user"
                               value="<?php echo htmlspecialchars($user_data['email_user'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Phone:</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['phone_user'] ?? 'N/A'); ?></span>
                        <input class="form-control field-input" type="text" name="phone_user"
                               value="<?php echo htmlspecialchars($user_data['phone_user'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Gender / Birthday -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Gender:</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['gender_user'] ?? 'N/A'); ?></span>
                        <select class="form-select field-input" name="gender_user">
                            <?php foreach (['Male', 'Female', 'Other'] as $g): ?>
                                <option value="<?php echo $g; ?>" <?php echo ($user_data['gender_user'] ?? '') === $g ? 'selected' : ''; ?>>
                                    <?php echo $g; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Birthday:</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['birthday_user'] ?? 'N/A'); ?></span>
                        <input class="form-control field-input" type="date" name="birthday_user"
                               value="<?php echo htmlspecialchars($user_data['birthday_user'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Height / Weight -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Height (cm):</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['height_user'] ?? 'N/A'); ?></span>
                        <input class="form-control field-input" type="number" name="height_user"
                               value="<?php echo htmlspecialchars($user_data['height_user'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Weight (kg):</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['weight_user'] ?? 'N/A'); ?></span>
                        <input class="form-control field-input" type="number" name="weight_user"
                               value="<?php echo htmlspecialchars($user_data['weight_user'] ?? ''); ?>">
                    </div>
                </div>

                <!-- BMI / Activity Level -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>BMI:</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['bmi_user'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Activity Level:</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['activitylvl_user'] ?? 'N/A'); ?></span>
                        <select class="form-select field-input" name="activitylvl_user">
                            <?php foreach (['Sedentary', 'Light', 'Moderate', 'Active', 'Very Active'] as $lvl): ?>
                                <option value="<?php echo $lvl; ?>" <?php echo ($user_data['activitylvl_user'] ?? '') === $lvl ? 'selected' : ''; ?>>
                                    <?php echo $lvl; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Illness -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <p class="mb-1"><strong>Illness:</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['illness_user'] ?? 'None'); ?></span>
                        <input class="form-control field-input" type="text" name="illness_user"
                               value="<?php echo htmlspecialchars($user_data['illness_user'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Allergies -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <p class="mb-1"><strong>Allergies:</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['allergie_user'] ?? 'None'); ?></span>
                        <input class="form-control field-input" type="text" name="allergie_user"
                               value="<?php echo htmlspecialchars($user_data['allergie_user'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Medications -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <p class="mb-1"><strong>Medications:</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['medicament_user'] ?? 'None'); ?></span>
                        <input class="form-control field-input" type="text" name="medicament_user"
                               value="<?php echo htmlspecialchars($user_data['medicament_user'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Read-only fields -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Registration Date:</strong> <?php echo htmlspecialchars($user_data['inscriptiondate_user'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Role:</strong> <?php echo htmlspecialchars($user_data['role_user'] ?? 'User'); ?></p>
                    </div>
                </div>

                <hr>

                <!-- Action buttons -->
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-primary" id="editBtn" onclick="enableEdit()">Edit Profile</button>
                    <button type="submit" class="btn btn-success d-none" id="saveBtn">Save Changes</button>
                    <button type="button" class="btn btn-secondary d-none" id="cancelBtn" onclick="cancelEdit()">Cancel</button>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        Delete Account
                    </button>
                    <a href="index.php" class="btn btn-secondary">Back to Home</a>
                </div>

            </div><!-- card-body -->
        </div><!-- card -->
    </form>
</div>

<!-- ✅ Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger" id="deleteModalLabel">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete your account? This action <strong>cannot be undone</strong>.
            </div>
            <div class="modal-footer border-0">
                <!-- No button — closes modal, stays on page -->
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, keep my account</button>
                <!-- Yes button — submits a separate delete form -->
                <form method="POST" action="profile.php">
                    <input type="hidden" name="delete_profile" value="1">
                    <button type="submit" class="btn btn-danger">Yes, delete it</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const card      = document.getElementById('profileCard');
    const editBtn   = document.getElementById('editBtn');
    const saveBtn   = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');

    let originals = {};

    function enableEdit() {
        document.querySelectorAll('.field-input').forEach(el => {
            originals[el.name] = el.value;
        });
        card.classList.add('editing');
        editBtn.classList.add('d-none');
        saveBtn.classList.remove('d-none');
        cancelBtn.classList.remove('d-none');
    }

    function cancelEdit() {
        document.querySelectorAll('.field-input').forEach(el => {
            if (originals[el.name] !== undefined) el.value = originals[el.name];
        });
        card.classList.remove('editing');
        editBtn.classList.remove('d-none');
        saveBtn.classList.add('d-none');
        cancelBtn.classList.add('d-none');
    }
</script>
</body>
</html>