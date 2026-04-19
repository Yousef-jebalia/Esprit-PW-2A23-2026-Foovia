<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../../controller/Controller_user.php';

$controller = new Controller_user();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_profile'])) {
    $controller->delete_user($_SESSION['user_id']);
    session_destroy();
    header('Location: login.php');
    exit;
}


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

        /* Validation styles (same as sign-up) */
        .field-error  { color: #e74c3c; font-size: 12px; margin-top: 4px; display: none; }
        .form-control.invalid { border-color: #e74c3c !important; box-shadow: 0 0 0 0.2rem rgba(231,76,60,.15); }
        .form-control.valid   { border-color: #2ecc71 !important; box-shadow: 0 0 0 0.2rem rgba(46,204,113,.15); }
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

    <form method="POST" action="profile.php" id="profileForm" novalidate>
        <input type="hidden" name="save_profile" value="1">

        <div class="card" id="profileCard">
            <div class="card-body">

                <!-- First Name / Last Name -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>First Name:</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['name_user'] ?? 'N/A'); ?></span>
                        <input class="form-control field-input" type="text" name="name_user" id="name_user"
                               value="<?php echo htmlspecialchars($user_data['name_user'] ?? ''); ?>">
                        <span class="field-error" id="name_user-error">First name is required.</span>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Last Name:</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['lastname_user'] ?? 'N/A'); ?></span>
                        <input class="form-control field-input" type="text" name="lastname_user" id="lastname_user"
                               value="<?php echo htmlspecialchars($user_data['lastname_user'] ?? ''); ?>">
                        <span class="field-error" id="lastname_user-error">Last name is required.</span>
                    </div>
                </div>

                <!-- Email / Phone -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Email:</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['email_user'] ?? 'N/A'); ?></span>
                        <input class="form-control field-input" type="email" name="email_user" id="email_user"
                               value="<?php echo htmlspecialchars($user_data['email_user'] ?? ''); ?>">
                        <span class="field-error" id="email_user-error">Email must end with @gmail.com</span>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Phone:</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['phone_user'] ?? 'N/A'); ?></span>
                        <input class="form-control field-input" type="text" name="phone_user" id="phone_user" maxlength="8"
                               value="<?php echo htmlspecialchars($user_data['phone_user'] ?? ''); ?>">
                        <span class="field-error" id="phone_user-error">Phone must be exactly 8 digits.</span>
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
                        <input class="form-control field-input" type="date" name="birthday_user" id="birthday_user"
                               value="<?php echo htmlspecialchars($user_data['birthday_user'] ?? ''); ?>">
                        <span class="field-error" id="birthday_user-error">Birthday must be before today.</span>
                    </div>
                </div>

                <!-- Height / Weight -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Height (cm):</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['height_user'] ?? 'N/A'); ?></span>
                        <input class="form-control field-input" type="number" name="height_user" id="height_user" min="50" max="250"
                               value="<?php echo htmlspecialchars($user_data['height_user'] ?? ''); ?>">
                        <span class="field-error" id="height_user-error">Height must be between 50 and 250 cm.</span>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Weight (kg):</strong></p>
                        <span class="field-value"><?php echo htmlspecialchars($user_data['weight_user'] ?? 'N/A'); ?></span>
                        <input class="form-control field-input" type="number" name="weight_user" id="weight_user" min="20" max="300"
                               value="<?php echo htmlspecialchars($user_data['weight_user'] ?? ''); ?>">
                        <span class="field-error" id="weight_user-error">Weight must be between 20 and 300 kg.</span>
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
                    <a href="foovia.php" class="btn btn-secondary">Back to Home</a>
                </div>

            </div><!-- card-body -->
        </div><!-- card -->
    </form>
</div>

<!-- Delete Confirmation Modal -->
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, keep my account</button>
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
    // ── Références DOM ─────────────────────────────────────────────────────────
    const card      = document.getElementById('profileCard');
    const editBtn   = document.getElementById('editBtn');
    const saveBtn   = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');

    let originals = {};

    // ── Activer le mode édition ────────────────────────────────────────────────
    function enableEdit() {
        document.querySelectorAll('.field-input').forEach(el => {
            originals[el.name] = el.value;
        });
        card.classList.add('editing');
        editBtn.classList.add('d-none');
        saveBtn.classList.remove('d-none');
        cancelBtn.classList.remove('d-none');
    }

    // ── Annuler l'édition et remettre les valeurs originales ──────────────────
    function cancelEdit() {
        document.querySelectorAll('.field-input').forEach(el => {
            if (originals[el.name] !== undefined) el.value = originals[el.name];
            // Réinitialise les classes de validation
            el.classList.remove('invalid', 'valid');
        });
        // Cache tous les messages d'erreur
        document.querySelectorAll('.field-error').forEach(e => e.style.display = 'none');
        card.classList.remove('editing');
        editBtn.classList.remove('d-none');
        saveBtn.classList.add('d-none');
        cancelBtn.classList.add('d-none');
    }

    // ── Helpers (identiques au sign-up) ───────────────────────────────────────
    function showError(fieldId, errorId) {
        const field = document.getElementById(fieldId);
        const error = document.getElementById(errorId);
        if (field) { field.classList.add('invalid'); field.classList.remove('valid'); }
        if (error)   error.style.display = 'block';
        return false;
    }

    function showValid(fieldId, errorId) {
        const field = document.getElementById(fieldId);
        const error = document.getElementById(errorId);
        if (field) { field.classList.remove('invalid'); field.classList.add('valid'); }
        if (error)   error.style.display = 'none';
        return true;
    }

    // ── Fonctions de validation (mêmes règles que sign-up) ────────────────────
    function validateName() {
        const val = (document.getElementById('name_user')?.value ?? '').trim();
        return val.length > 0
            ? showValid('name_user', 'name_user-error')
            : showError('name_user', 'name_user-error');
    }

    function validateLastname() {
        const val = (document.getElementById('lastname_user')?.value ?? '').trim();
        return val.length > 0
            ? showValid('lastname_user', 'lastname_user-error')
            : showError('lastname_user', 'lastname_user-error');
    }

    function validateEmail() {
        const val = (document.getElementById('email_user')?.value ?? '').trim();
        // Même regex que sign-up : doit finir par @gmail.com
        return /^[a-zA-Z0-9._%+\-]+@gmail\.com$/.test(val)
            ? showValid('email_user', 'email_user-error')
            : showError('email_user', 'email_user-error');
    }

    function validatePhone() {
        const val = (document.getElementById('phone_user')?.value ?? '').trim();
        // Exactement 8 chiffres comme sign-up
        return /^\d{8}$/.test(val)
            ? showValid('phone_user', 'phone_user-error')
            : showError('phone_user', 'phone_user-error');
    }

    function validateBirthday() {
    const val = document.getElementById('birthday_user')?.value ?? '';
    const today = new Date();
    today.setHours(0, 0, 0, 0); // on ignore l'heure, on compare seulement la date
    const selected = new Date(val);
    return val && selected < today
        ? showValid('birthday_user', 'birthday_user-error')
        : showError('birthday_user', 'birthday_user-error');
}

    function validateHeight() {
        const val = parseInt(document.getElementById('height_user')?.value ?? 0);
        return val >= 50 && val <= 250
            ? showValid('height_user', 'height_user-error')
            : showError('height_user', 'height_user-error');
    }

    function validateWeight() {
        const val = parseInt(document.getElementById('weight_user')?.value ?? 0);
        return val >= 20 && val <= 300
            ? showValid('weight_user', 'weight_user-error')
            : showError('weight_user', 'weight_user-error');
    }

    // ── Phone : autoriser uniquement les chiffres, max 8 (identique sign-up) ──
    document.getElementById('phone_user')?.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 8);
    });

    // ── Validation au blur (live, comme sign-up) ───────────────────────────────
    document.getElementById('name_user')?.addEventListener('blur', validateName);
    document.getElementById('lastname_user')?.addEventListener('blur', validateLastname);
    document.getElementById('email_user')?.addEventListener('blur', validateEmail);
    document.getElementById('phone_user')?.addEventListener('blur', validatePhone);
    document.getElementById('birthday_user')?.addEventListener('blur', validateBirthday);
    document.getElementById('height_user')?.addEventListener('blur', validateHeight);
    document.getElementById('weight_user')?.addEventListener('blur', validateWeight);

    // ── Validation au submit : bloque si un champ est invalide ────────────────
    document.getElementById('profileForm').addEventListener('submit', function (e) {
        // On valide seulement si le formulaire est en mode édition (saveBtn visible)
        if (saveBtn.classList.contains('d-none')) return;

        const nameOk     = validateName();
        const lastnameOk = validateLastname();
        const emailOk    = validateEmail();
        const phoneOk    = validatePhone();
        const birthdayOk = validateBirthday();
        const heightOk   = validateHeight();
        const weightOk   = validateWeight();

        if (!nameOk || !lastnameOk || !emailOk || !phoneOk || !birthdayOk || !heightOk || !weightOk) {
            e.preventDefault(); // Bloque l'envoi si erreur
        }
    });
</script>
</body>
</html>