<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: foovia-signin.php');
    exit;
}

require_once '../../controller/Controller_user.php';
require_once '../../model/config.php';

$controller = new Controller_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_profile'])) {
    $controller->delete_user($_SESSION['user_id']);
    session_destroy();
    header('Location: foovia-signin.php');
    exit;
}

$pwd_error = '';
$pwd_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    $user_record = $controller->get_user($_SESSION['user_id']);
    
    // Check if the current password is correct
    if ($current_pass !== $user_record['password_user']) {
        $pwd_error = "Current password is incorrect.";
    } elseif ($new_pass === $current_pass) {
        $pwd_error = "New password cannot be the same as the current one.";
    } elseif (strlen($new_pass) < 6 || !preg_match('/[A-Za-z]/', $new_pass) || !preg_match('/[0-9]/', $new_pass)) {
        $pwd_error = "New password is too weak (must be at least 6 chars with letters and numbers).";
    } elseif ($new_pass !== $confirm_pass) {
        $pwd_error = "Passwords do not match.";
    } else {
        $db = config::getConnexion();
        $stmt = $db->prepare("UPDATE user SET password_user = :pwd WHERE id_user = :id");
        $stmt->execute(['pwd' => $new_pass, 'id' => $_SESSION['user_id']]);
        $pwd_success = "Password changed successfully.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $current = $controller->get_user($_SESSION['user_id']);
    $email = $_POST['email_user'] ?? $current['email_user'];
    
    if (strpos($email, '@gmail.com') === false) {
        $error_message = 'Email must be in the format: example@gmail.com';
    } else {
    $height = (int)($_POST['height_user'] ?? $current['height_user']);
    $weight = (int)($_POST['weight_user'] ?? $current['weight_user']);
    $bmi    = ($height > 0) ? (int)round($weight / (($height / 100) ** 2)) : (int)$current['bmi_user'];

    $user = new User(
        (int)$_SESSION['user_id'],
        $_POST['name_user']         ?? $current['name_user'],
        $_POST['lastname_user']     ?? $current['lastname_user'],
        $email,
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
        $current['role_user'],
        $current['subscription_user'] ?? 'normal',
        $current['account_state_user'] ?? 'active',
        $current['duration_user'] ?? '00:00:00'
    );

    $controller->update_user($user, $_SESSION['user_id']);
    $saved = true;
    }
    
    // Update session names if they changed
    $_SESSION['user_name'] = $_POST['name_user'] ?? $current['name_user'];
}

$user_data = $controller->get_user($_SESSION['user_id']) ?? [];
$is_logged_in = true;
$user_name = $_SESSION['user_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Foovia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="foovia.css">
    <style>
        body { padding-top: 80px; } /* For fixed nav */
        .field-value  { display: block; color: var(--page-muted); }
        .field-input  { display: none; background: var(--panel-bg); border-color: var(--surface-border); color: var(--page-text); }
        .field-input:focus { background: var(--panel-bg); color: var(--page-text); border-color: var(--green); box-shadow: none; }
        select.field-input option { background: var(--panel-bg); color: var(--page-text); }
        .editing .field-value { display: none; }
        .editing .field-input { display: block; }

        /* Validation styles */
        .field-error  { color: var(--red); font-size: 12px; margin-top: 4px; display: none; }
        .form-control.invalid { border-color: var(--red) !important; }
        .form-control.valid   { border-color: var(--green) !important; }
        
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .profile-card {
            background: var(--panel-bg);
            border: 1px solid var(--surface-border);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 20px;
        }
        .profile-card h3 { font-family: 'Syne', sans-serif; margin-bottom: 20px; font-weight: 600; }
        .profile-card p.mb-1, .profile-card p.mb-1 strong { 
            font-family: 'DM Sans', sans-serif; 
            font-size: 0.9rem; 
            font-weight: normal !important; 
            color: var(--page-text); 
            margin-bottom: 4px !important; 
        }
        .profile-card .field-value { 
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem; 
            font-weight: normal; 
            color: var(--page-muted); 
        }
        .profile-card .form-control, .profile-card .form-select { font-family: 'DM Sans', sans-serif; font-size: 0.9rem; background-color: var(--page-bg); color: var(--page-text); border-color: var(--surface-border); }
        
        .nav-logo-img { height: 32px; margin-right: 8px; }

        /* Modal fixes */
        .modal-content {
            background-color: var(--panel-bg) !important;
            border: 1px solid var(--surface-border) !important;
            color: var(--page-text) !important;
        }
        .modal-title, .modal-body label, .modal-body small {
            font-family: 'DM Sans', sans-serif;
        }
        .modal-title { font-family: 'Syne', sans-serif; font-weight: 700; }
        .modal-body .form-control {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--page-bg) !important;
            border-color: var(--surface-border) !important;
            color: var(--page-text) !important;
        }
    </style>
</head>
<body>

<!-- NAV -->
<nav style="position: fixed; top: 0; width: 100%; z-index: 1000; background: var(--nav-bg); border-bottom: 1px solid var(--nav-border);">
  <a href="foovia.php" class="nav-logo">
    <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" class="nav-logo-img">
    FOOVIA
  </a>
  <ul class="nav-links">
    <li><a href="foovia.php#features">Features</a></li>
    <li><a href="foovia.php#how">How it works</a></li>
    <li><a href="foovia.php#marketplace">Marketplace</a></li>
    <li><a href="foovia.php#community">Community</a></li>
  </ul>
  <div class="nav-actions">
    <a href="foovia-backoffice.php" class="nav-btn nav-backoffice">Backoffice</a>
    <button class="theme-toggle" type="button" aria-label="Switch to dark mode" aria-pressed="false">
      <svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor" stroke-width="2" fill="none"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"></path></svg>
      <svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor" stroke-width="2" fill="none"><path d="M21 14.5A8.5 8.5 0 1 1 9.5 3a7 7 0 1 0 11.5 11.5z"></path></svg>
    </button>
    <div class="dropdown">
      <a href="#" class="nav-btn dropdown-toggle" role="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--page-text); text-decoration: none;">
        Welcome, <?php echo htmlspecialchars($user_name); ?>
      </a>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu" style="background: var(--panel-bg); border-color: var(--surface-border);">
        <li><a class="dropdown-item" href="profile.php" style="color: var(--page-text);">My Account</a></li>
        <li><hr class="dropdown-divider" style="border-color: var(--surface-border);"></li>
        <li><a class="dropdown-item" href="logout.php" style="color: var(--page-text);">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="profile-container">
    <h2 class="mb-4" style="font-family: 'Syne', sans-serif;">My Account</h2>

    <?php if (!empty($error_message) ?? false): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="background: #fee; color: var(--red); border-color: var(--red);">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($pwd_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="background: #fee; color: var(--red); border-color: var(--red);">
            <?php echo htmlspecialchars($pwd_error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($pwd_success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="background: #efe; color: var(--green); border-color: var(--green);">
            <?php echo htmlspecialchars($pwd_success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($saved) && $saved): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="background: #efe; color: var(--green); border-color: var(--green);">
            Profile updated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="profile.php" id="profileForm" novalidate>
        <input type="hidden" name="save_profile" value="1">

        <div class="profile-card" id="profileCard">
            <h3>Personal Information</h3>
            <!-- First Name / Last Name -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>First Name:</strong></p>
                    <span class="field-value"><?php echo htmlspecialchars($user_data['name_user'] ?? 'N/A'); ?></span>
                    <input class="form-control field-input" type="text" name="name_user" id="name_user" value="<?php echo htmlspecialchars($user_data['name_user'] ?? ''); ?>">
                    <span class="field-error" id="name_user-error">First name must be at least 3 characters.</span>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Last Name:</strong></p>
                    <span class="field-value"><?php echo htmlspecialchars($user_data['lastname_user'] ?? 'N/A'); ?></span>
                    <input class="form-control field-input" type="text" name="lastname_user" id="lastname_user" value="<?php echo htmlspecialchars($user_data['lastname_user'] ?? ''); ?>">
                    <span class="field-error" id="lastname_user-error">Last name must be at least 3 characters.</span>
                </div>
            </div>

            <!-- Email / Phone -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Email:</strong></p>
                    <span class="field-value"><?php echo htmlspecialchars($user_data['email_user'] ?? 'N/A'); ?></span>
                    <input class="form-control field-input" type="text" name="email_user" id="email_user" value="<?php echo htmlspecialchars($user_data['email_user'] ?? ''); ?>">
                    <span class="field-error" id="email_user-error">Email must end with @gmail.com</span>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Phone:</strong></p>
                    <span class="field-value"><?php echo htmlspecialchars($user_data['phone_user'] ?? 'N/A'); ?></span>
                    <input class="form-control field-input" type="text" name="phone_user" id="phone_user" value="<?php echo htmlspecialchars($user_data['phone_user'] ?? ''); ?>">
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
                            <option value="<?php echo $g; ?>" <?php echo ($user_data['gender_user'] ?? '') === $g ? 'selected' : ''; ?>><?php echo $g; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Birthday:</strong></p>
                    <span class="field-value"><?php echo htmlspecialchars($user_data['birthday_user'] ?? 'N/A'); ?></span>
                    <input class="form-control field-input" type="date" name="birthday_user" id="birthday_user" value="<?php echo htmlspecialchars($user_data['birthday_user'] ?? ''); ?>">
                    <span class="field-error" id="birthday_user-error">You must be at least 15 years old.</span>
                </div>
            </div>

            <!-- Height / Weight -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Height (cm):</strong></p>
                    <span class="field-value"><?php echo htmlspecialchars($user_data['height_user'] ?? 'N/A'); ?></span>
                    <input class="form-control field-input" type="number" name="height_user" id="height_user" value="<?php echo htmlspecialchars($user_data['height_user'] ?? ''); ?>">
                    <span class="field-error" id="height_user-error">Height must be between 100 and 250 cm.</span>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Weight (kg):</strong></p>
                    <span class="field-value"><?php echo htmlspecialchars($user_data['weight_user'] ?? 'N/A'); ?></span>
                    <input class="form-control field-input" type="number" name="weight_user" id="weight_user" value="<?php echo htmlspecialchars($user_data['weight_user'] ?? ''); ?>">
                    <span class="field-error" id="weight_user-error">Weight must be between 30 and 300 kg.</span>
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
                    <select class="form-select field-input" name="activitylvl_user" id="activitylvl_user">
                        <?php foreach (['Sedentary', 'Light', 'Moderate', 'Active', 'Very Active'] as $lvl): ?>
                            <option value="<?php echo $lvl; ?>" <?php echo ($user_data['activitylvl_user'] ?? '') === $lvl ? 'selected' : ''; ?>><?php echo $lvl; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-error" id="activitylvl_user-error">Please select your activity level.</span>
                </div>
            </div>

            <!-- Illness -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <p class="mb-1"><strong>Illness:</strong></p>
                    <span class="field-value"><?php echo htmlspecialchars($user_data['illness_user'] ?? 'None'); ?></span>
                    <input class="form-control field-input" type="text" name="illness_user" value="<?php echo htmlspecialchars($user_data['illness_user'] ?? ''); ?>">
                </div>
            </div>

            <!-- Allergies -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <p class="mb-1"><strong>Allergies:</strong></p>
                    <span class="field-value"><?php echo htmlspecialchars($user_data['allergie_user'] ?? 'None'); ?></span>
                    <input class="form-control field-input" type="text" name="allergie_user" value="<?php echo htmlspecialchars($user_data['allergie_user'] ?? ''); ?>">
                </div>
            </div>

            <!-- Medications -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <p class="mb-1"><strong>Medications:</strong></p>
                    <span class="field-value"><?php echo htmlspecialchars($user_data['medicament_user'] ?? 'None'); ?></span>
                    <input class="form-control field-input" type="text" name="medicament_user" value="<?php echo htmlspecialchars($user_data['medicament_user'] ?? ''); ?>">
                </div>
            </div>

            <!-- Read-only fields -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Registration Date:</strong></p>
                    <span class="field-value"><?php echo htmlspecialchars($user_data['inscriptiondate_user'] ?? 'N/A'); ?></span>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Role:</strong></p>
                    <span class="field-value"><?php echo htmlspecialchars((string) ($user_data['role_user'] ?? 'N/A')); ?></span>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="d-flex gap-2 flex-wrap mt-4">
                <button type="button" class="btn" id="editBtn" onclick="enableEdit()" style="background: var(--green); border-color: var(--green); color: #000; font-weight: 500;">Edit Profile</button>
                <button type="submit" class="btn d-none" id="saveBtn" style="background: var(--green); border-color: var(--green); color: #000; font-weight: 500;">Save Changes</button>
                <button type="button" class="btn btn-secondary d-none" id="cancelBtn" onclick="cancelEdit()">Cancel</button>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#passwordModal" style="font-weight: 500;">Change Password</button>
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete Account</button>
            </div>
        </div>
    </form>
</div>

<!-- Password Change Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="profile.php" id="changePasswordForm" novalidate>
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="passwordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="change_password" value="1">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                        <small class="field-error" id="current_password-error">Current password is required.</small>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                        <small class="text-muted" style="color: var(--page-muted) !important;">Must be at least 6 characters and contain letters and numbers.</small>
                        <small class="field-error" id="new_password-error">Use at least 6 characters with letters and numbers.</small>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        <small class="field-error" id="confirm_password-error">Passwords do not match.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background: var(--green); border-color: var(--green); color: #000; font-weight: 500;">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger" id="deleteModalLabel">Delete Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
    // Theme logic from foovia.php
    (function() {
        const root = document.documentElement;
        const toggle = document.querySelector('.theme-toggle');

        const setTheme = (theme) => {
            const isDark = theme === 'dark';
            root.setAttribute('data-theme', theme);
            root.style.colorScheme = theme;
            toggle.setAttribute('aria-pressed', String(isDark));
            toggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
            
            // Fix modal close button colors
            document.querySelectorAll('.btn-close').forEach(btn => {
                if (isDark) {
                    btn.classList.add('btn-close-white');
                } else {
                    btn.classList.remove('btn-close-white');
                }
            });
        };

        const stored = localStorage.getItem('theme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const initialTheme = stored || (prefersDark ? 'dark' : 'light');
        setTheme(initialTheme);

        toggle.addEventListener('click', () => {
            const currentTheme = root.getAttribute('data-theme') || 'light';
            const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', nextTheme);
            setTheme(nextTheme);
        });
    })();

    // Profile Edit Logic
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
            el.classList.remove('invalid', 'valid');
        });
        document.querySelectorAll('.field-error').forEach(e => e.style.display = 'none');
        card.classList.remove('editing');
        editBtn.classList.remove('d-none');
        saveBtn.classList.add('d-none');
        cancelBtn.classList.add('d-none');
    }

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

    function validateName() {
        const val = (document.getElementById('name_user')?.value ?? '').trim();
        return val.length >= 3 ? showValid('name_user', 'name_user-error') : showError('name_user', 'name_user-error');
    }
    function validateLastname() {
        const val = (document.getElementById('lastname_user')?.value ?? '').trim();
        return val.length >= 3 ? showValid('lastname_user', 'lastname_user-error') : showError('lastname_user', 'lastname_user-error');
    }
    function validateEmail() {
        const val = (document.getElementById('email_user')?.value ?? '').trim();
        return /^[a-zA-Z0-9._%+\-]+@gmail\.com$/.test(val) ? showValid('email_user', 'email_user-error') : showError('email_user', 'email_user-error');
    }
    function validatePhone() {
        const val = (document.getElementById('phone_user')?.value ?? '').trim();
        return /^\d{8}$/.test(val) ? showValid('phone_user', 'phone_user-error') : showError('phone_user', 'phone_user-error');
    }
    function validateBirthday() {
        const val = document.getElementById('birthday_user')?.value ?? '';
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const cutoff = new Date(today);
        cutoff.setFullYear(cutoff.getFullYear() - 15);
        const selected = new Date(val);
        return val && selected <= cutoff ? showValid('birthday_user', 'birthday_user-error') : showError('birthday_user', 'birthday_user-error');
    }
    function validateHeight() {
        const val = parseInt(document.getElementById('height_user')?.value ?? 0);
        return val >= 100 && val <= 250 ? showValid('height_user', 'height_user-error') : showError('height_user', 'height_user-error');
    }
    function validateWeight() {
        const val = parseInt(document.getElementById('weight_user')?.value ?? 0);
        return val >= 30 && val <= 300 ? showValid('weight_user', 'weight_user-error') : showError('weight_user', 'weight_user-error');
    }
    function validateActivity() {
        const val = (document.getElementById('activitylvl_user')?.value ?? '').trim();
        return val.length > 0 ? showValid('activitylvl_user', 'activitylvl_user-error') : showError('activitylvl_user', 'activitylvl_user-error');
    }

    document.getElementById('phone_user')?.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 8);
    });

    document.getElementById('name_user')?.addEventListener('blur', validateName);
    document.getElementById('lastname_user')?.addEventListener('blur', validateLastname);
    document.getElementById('email_user')?.addEventListener('blur', validateEmail);
    document.getElementById('phone_user')?.addEventListener('blur', validatePhone);
    document.getElementById('birthday_user')?.addEventListener('blur', validateBirthday);
    document.getElementById('height_user')?.addEventListener('blur', validateHeight);
    document.getElementById('weight_user')?.addEventListener('blur', validateWeight);
    document.getElementById('activitylvl_user')?.addEventListener('change', validateActivity);

    document.getElementById('profileForm').addEventListener('submit', function (e) {
        if (saveBtn.classList.contains('d-none')) return;
        const nameOk = validateName(), lastnameOk = validateLastname(), emailOk = validateEmail(), phoneOk = validatePhone(), birthdayOk = validateBirthday(), heightOk = validateHeight(), weightOk = validateWeight(), activityOk = validateActivity();
        if (!nameOk || !lastnameOk || !emailOk || !phoneOk || !birthdayOk || !heightOk || !weightOk || !activityOk) e.preventDefault();
    });

    (() => {
        const birthdayInput = document.getElementById('birthday_user');
        if (!birthdayInput) return;
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        today.setFullYear(today.getFullYear() - 15);
        birthdayInput.max = today.toISOString().split('T')[0];
    })();

    function validatePasswordField(fieldId, check, errorId) {
        const field = document.getElementById(fieldId);
        const error = document.getElementById(errorId);
        const ok = check(field.value);
        field.classList.toggle('invalid', !ok);
        field.classList.toggle('valid', ok);
        if (error) {
            error.style.display = ok ? 'none' : 'block';
        }
        return ok;
    }

    document.getElementById('changePasswordForm')?.addEventListener('submit', function (e) {
        const currentOk = validatePasswordField('current_password', v => v.trim().length > 0, 'current_password-error');
        const newOk = validatePasswordField('new_password', v => v.length >= 6 && /[A-Za-z]/.test(v) && /\d/.test(v), 'new_password-error');
        const confirmOk = validatePasswordField('confirm_password', v => v === document.getElementById('new_password').value && v.length > 0, 'confirm_password-error');

        if (!currentOk || !newOk || !confirmOk) {
            e.preventDefault();
        }
    });
</script>
</body>
</html>