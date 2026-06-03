<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: foovia-signin.php');
    exit;
}

require_once __DIR__ . '/../../Controller/Controller_user.php';
require_once __DIR__ . '/../../Model/config.php';

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
$profile_full_name = trim(($user_data['name_user'] ?? '') . ' ' . ($user_data['lastname_user'] ?? ''));
if ($profile_full_name === '') {
    $profile_full_name = $user_name !== '' ? $user_name : 'My account';
}
$profile_email = $user_data['email_user'] ?? '';
$profile_subscription = ucfirst((string) ($user_data['subscription_user'] ?? 'normal'));
$profile_role = ucfirst((string) ($user_data['role_user'] ?? 'user'));
$profile_initials = strtoupper(substr((string) ($user_data['name_user'] ?? 'F'), 0, 1) . substr((string) ($user_data['lastname_user'] ?? 'V'), 0, 1));
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
        body { padding-top: 96px; } /* For fixed nav */
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
        .profile-field.is-editing .field-value { display: none; }
        .profile-field.is-editing .field-input { display: block; }
        .field-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }
        .field-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .75rem;
            font-weight: 400;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--page-muted);
            margin: 0;
        }
        .profile-field .field-value,
        .profile-field .field-input,
        .profile-field .form-select {
            font-family: 'DM Sans', sans-serif;
            font-weight: 400;
        }
        .inline-edit-toggle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1px solid var(--surface-border);
            background: rgba(255,255,255,.7);
            color: var(--page-text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform .15s ease, background-color .2s ease, border-color .2s ease, color .2s ease;
        }
        .inline-edit-toggle:hover,
        .inline-edit-toggle:focus-visible {
            transform: translateY(-1px);
            border-color: var(--green);
            color: var(--green);
            outline: none;
        }
        .inline-edit-toggle svg {
            width: 16px;
            height: 16px;
            display: block;
        }
        .inline-edit-toggle.is-save {
            background: var(--green);
            border-color: var(--green);
            color: #fff;
        }

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

        .profile-page {
            max-width: 1360px;
            margin: 0 auto;
            padding: 28px 24px 80px;
            position: relative;
        }

        .profile-page::before {
            content: '';
            position: absolute;
            inset: 24px 24px auto auto;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(245,200,66,.18), rgba(75,174,82,.06) 55%, transparent 72%);
            filter: blur(10px);
            pointer-events: none;
            z-index: 0;
        }

        .profile-layout {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 300px minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }

        .profile-sidebar {
            position: sticky;
            top: 116px;
            background: var(--panel-bg);
            border: 1px solid var(--surface-border);
            border-radius: 28px;
            padding: 24px;
            box-shadow: 0 26px 60px rgba(17,16,8,.08);
            overflow: hidden;
        }

        .profile-sidebar::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 92px;
            background: linear-gradient(135deg, rgba(75,174,82,.16), rgba(245,200,66,.12));
            pointer-events: none;
        }

        .profile-sidebar-inner {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .profile-avatar {
            width: 72px;
            height: 72px;
            border-radius: 22px;
            background: var(--dark);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.4rem;
            letter-spacing: .04em;
            box-shadow: 0 14px 30px rgba(17,16,8,.18);
        }

        .profile-kicker {
            font-family: 'DM Sans', sans-serif;
            font-size: .74rem;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: var(--page-muted);
            margin-bottom: 8px;
        }

        .profile-sidebar h2,
        .profile-hero h1,
        .section-title {
            font-family: 'Syne', sans-serif;
            color: var(--page-text);
        }

        .profile-sidebar h2 {
            font-size: 1.35rem;
            line-height: 1.1;
            margin-bottom: 8px;
        }

        .profile-meta {
            font-family: 'DM Sans', sans-serif;
            color: var(--page-muted);
            font-size: .96rem;
            line-height: 1.55;
            word-break: break-word;
        }

        .profile-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .profile-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(75,174,82,.12);
            color: var(--green);
            font-family: 'Syne', sans-serif;
            font-size: .74rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .profile-nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .profile-nav a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 14px;
            border-radius: 16px;
            border: 1px solid transparent;
            background: rgba(255,255,255,.55);
            color: var(--page-text);
            text-decoration: none;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            transition: transform .15s ease, background-color .2s ease, border-color .2s ease;
        }

        .profile-nav a:hover,
        .profile-nav a:focus-visible {
            transform: translateX(2px);
            background: rgba(75,174,82,.10);
            border-color: rgba(75,174,82,.18);
            outline: none;
        }

        .profile-nav small {
            font-family: 'DM Sans', sans-serif;
            font-weight: 500;
            color: var(--page-muted);
        }

        .profile-main {
            display: flex;
            flex-direction: column;
            gap: 20px;
            min-width: 0;
        }

        .profile-hero,
        .section-card,
        .profile-actions-bar {
            background: var(--panel-bg);
            border: 1px solid var(--surface-border);
            border-radius: 28px;
            box-shadow: 0 18px 40px rgba(17,16,8,.05);
        }

        .profile-hero {
            padding: 28px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
        }

        .profile-hero h1 {
            font-size: clamp(2rem, 3vw, 3rem);
            line-height: 1.04;
            margin-bottom: 10px;
        }

        .profile-hero p {
            margin: 0;
            font-family: 'DM Sans', sans-serif;
            color: var(--page-muted);
            max-width: 620px;
            line-height: 1.7;
        }

        .profile-hero-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 10px;
        }

        .hero-stat-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .hero-stat {
            padding: 18px;
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(75,174,82,.08), rgba(245,200,66,.06));
            border: 1px solid rgba(75,174,82,.12);
        }

        .hero-stat span {
            display: block;
            font-family: 'DM Sans', sans-serif;
            font-size: .76rem;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--page-muted);
            margin-bottom: 8px;
        }

        .hero-stat strong {
            font-family: 'Syne', sans-serif;
            font-size: 1rem;
            line-height: 1.3;
        }

        .section-card {
            padding: 26px 28px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .section-title {
            font-size: 1.35rem;
            margin-bottom: 6px;
        }

        .section-subtitle {
            font-family: 'DM Sans', sans-serif;
            color: var(--page-muted);
            line-height: 1.6;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .profile-field {
            padding: 18px;
            border-radius: 20px;
            background: rgba(255,255,255,.58);
            border: 1px solid var(--surface-border);
            min-width: 0;
        }

        .profile-field--full {
            grid-column: 1 / -1;
        }

        .profile-field label,
        .profile-field .field-label {
            display: block;
            font-family: 'DM Sans', sans-serif;
            font-size: .75rem;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--page-muted);
            margin-bottom: 10px;
        }

        .profile-field .field-value {
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            color: var(--page-text);
            line-height: 1.55;
        }

        .profile-field .field-input,
        .profile-field .form-select {
            width: 100%;
        }

        .profile-section-actions {
            margin-top: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .profile-actions-bar {
            padding: 18px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
        }

        .profile-actions-bar p {
            margin: 0;
            font-family: 'DM Sans', sans-serif;
            color: var(--page-muted);
        }

        .profile-actions-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-foovia {
            background: var(--green);
            border-color: var(--green);
            color: #fff;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            padding-inline: 18px;
            border-radius: 999px;
        }

        .btn-foovia:hover,
        .btn-foovia:focus-visible {
            background: var(--forest);
            border-color: var(--forest);
            color: #fff;
        }

        .btn-foovia-secondary {
            background: transparent;
            border-color: var(--surface-border);
            color: var(--page-text);
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            padding-inline: 18px;
            border-radius: 999px;
        }

        .btn-foovia-secondary:hover,
        .btn-foovia-secondary:focus-visible {
            background: var(--page-text);
            border-color: var(--page-text);
            color: var(--page-bg);
        }

        .profile-section-anchor {
            scroll-margin-top: 128px;
        }

        @media (max-width: 1100px) {
            .profile-layout {
                grid-template-columns: 1fr;
            }

            .profile-sidebar {
                position: relative;
                top: 0;
            }

            .hero-stat-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 760px) {
            .profile-page {
                padding: 20px 16px 64px;
            }

            .profile-hero,
            .section-card,
            .profile-actions-bar,
            .profile-sidebar {
                border-radius: 24px;
                padding: 20px;
            }

            .profile-grid {
                grid-template-columns: 1fr;
            }

            .profile-hero {
                flex-direction: column;
            }

            .profile-hero-actions,
            .profile-actions-row {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>

<!-- NAV -->
<nav style="position: fixed; top: 0; width: 100%; z-index: 1000; background: var(--nav-bg); border-bottom: 1px solid var(--nav-border);">
    <div style="display:flex;align-items:center;gap:2px;margin-left:0;flex-shrink:0;">
        <a href="foovia.php" class="nav-logo">
            <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" class="nav-logo-img">
            FOOVIA
        </a>
        <button class="nav-sidebar-toggle" type="button" aria-label="Open page list" aria-controls="navSidebar" aria-expanded="false" style="width:54px;height:54px;border-radius:12px;gap:4px;padding:0;display:inline-flex;flex-direction:column;align-items:center;justify-content:center;background:rgba(255,255,255,.72);border-color:rgba(17,16,8,.18);margin-right:8px;">
                <span style="width:26px;height:4px;border-radius:999px;display:block;background:#111008;"></span>
                <span style="width:26px;height:4px;border-radius:999px;display:block;background:#111008;"></span>
                <span style="width:26px;height:4px;border-radius:999px;display:block;background:#111008;"></span>
        </button>
    </div>
  <ul class="nav-links">
    <li><a href="foovia.php#features">Features</a></li>
    <li><a href="foovia.php#how">How it works</a></li>
    <li><a href="foovia.php#marketplace">Marketplace</a></li>
    <li><a href="foovia.php#community">Community</a></li>
  </ul>
  <div class="nav-actions">
                <?php if ((isset($_SESSION['role_user']) && strtolower(trim($_SESSION['role_user'])) === 'admin') || (isset($userData) && strtolower(trim($userData['role_user'] ?? '')) === 'admin')): ?>
                    <a href="foovia-backoffice.php" class="nav-btn nav-backoffice">Backoffice</a>
                <?php endif; ?>
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

<div class="profile-page">
    <div class="profile-layout">
        <aside class="profile-sidebar">
            <div class="profile-sidebar-inner">
                <div class="d-flex align-items-center gap-3">
                    <div class="profile-avatar"><?php echo htmlspecialchars($profile_initials); ?></div>
                    <div>
                        <div class="profile-kicker">Account</div>
                        <h2><?php echo htmlspecialchars($profile_full_name); ?></h2>
                        <div class="profile-meta"><?php echo htmlspecialchars($profile_email !== '' ? $profile_email : 'No email added'); ?></div>
                    </div>
                </div>

                <div class="profile-badges">
                    <span class="profile-badge"><?php echo htmlspecialchars($profile_subscription); ?></span>
                    <span class="profile-badge"><?php echo htmlspecialchars($profile_role); ?></span>
                </div>

                <div class="profile-nav" aria-label="Account sections">
                    <a href="#account-info"><span>Account info</span><small>Profile and contact</small></a>
                    <a href="#security"><span>Password</span><small>Access and devices</small></a>
                    <a href="#health"><span>Health</span><small>Measurements and notes</small></a>
                    <a href="#admin"><span>System</span><small>Role and subscription</small></a>
                </div>
            </div>
        </aside>

        <main class="profile-main">
            <section class="profile-hero">
                <div>
                    <div class="profile-kicker">My account</div>
                    <h1>Personal settings, Foovia style.</h1>
                    <p>Keep your profile information, security settings, and wellness data in one place with a cleaner account dashboard.</p>
                </div>
            </section>

            <div class="hero-stat-grid">
                <div class="hero-stat">
                    <span>Username</span>
                    <strong><?php echo htmlspecialchars($user_data['name_user'] ?? 'N/A'); ?></strong>
                </div>
                <div class="hero-stat">
                    <span>Email</span>
                    <strong><?php echo htmlspecialchars($user_data['email_user'] ?? 'N/A'); ?></strong>
                </div>
                <div class="hero-stat">
                    <span>Phone</span>
                    <strong><?php echo htmlspecialchars($user_data['phone_user'] ?? 'Not set'); ?></strong>
                </div>
            </div>

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

                <div id="profileCard">
                <div class="profile-actions-bar">
                    <div>
                        <div class="profile-kicker">Editor</div>
                        <p>Tap the pen icon on any field to edit it, then confirm with the check button.</p>
                    </div>
                </div>

                <div class="section-card profile-section-anchor" id="account-info">
                    <div class="section-header">
                        <div>
                            <div class="profile-kicker">Account info</div>
                            <div class="section-title">Identity and contact details</div>
                            <div class="section-subtitle">This is the public-facing part of your account profile.</div>
                        </div>
                    </div>
                    <div class="profile-grid">
                        <div class="profile-field" data-inline-field data-field-name="name_user">
                            <div class="field-head">
                                <label class="field-title" for="name_user">First name</label>
                                <button type="button" class="inline-edit-toggle" data-inline-toggle aria-label="Edit first name" title="Edit first name">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                </button>
                            </div>
                            <span class="field-value"><?php echo htmlspecialchars($user_data['name_user'] ?? 'N/A'); ?></span>
                            <input class="form-control field-input" type="text" name="name_user" id="name_user" value="<?php echo htmlspecialchars($user_data['name_user'] ?? ''); ?>">
                            <span class="field-error" id="name_user-error">First name must be at least 3 characters.</span>
                        </div>
                        <div class="profile-field" data-inline-field data-field-name="lastname_user">
                            <div class="field-head">
                                <label class="field-title" for="lastname_user">Last name</label>
                                <button type="button" class="inline-edit-toggle" data-inline-toggle aria-label="Edit last name" title="Edit last name">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                </button>
                            </div>
                            <span class="field-value"><?php echo htmlspecialchars($user_data['lastname_user'] ?? 'N/A'); ?></span>
                            <input class="form-control field-input" type="text" name="lastname_user" id="lastname_user" value="<?php echo htmlspecialchars($user_data['lastname_user'] ?? ''); ?>">
                            <span class="field-error" id="lastname_user-error">Last name must be at least 3 characters.</span>
                        </div>
                        <div class="profile-field" data-inline-field data-field-name="email_user">
                            <div class="field-head">
                                <label class="field-title" for="email_user">Email</label>
                                <button type="button" class="inline-edit-toggle" data-inline-toggle aria-label="Edit email" title="Edit email">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                </button>
                            </div>
                            <span class="field-value"><?php echo htmlspecialchars($user_data['email_user'] ?? 'N/A'); ?></span>
                            <input class="form-control field-input" type="text" name="email_user" id="email_user" value="<?php echo htmlspecialchars($user_data['email_user'] ?? ''); ?>">
                            <span class="field-error" id="email_user-error">Email must end with @gmail.com</span>
                        </div>
                        <div class="profile-field" data-inline-field data-field-name="phone_user">
                            <div class="field-head">
                                <label class="field-title" for="phone_user">Phone</label>
                                <button type="button" class="inline-edit-toggle" data-inline-toggle aria-label="Edit phone" title="Edit phone">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                </button>
                            </div>
                            <span class="field-value"><?php echo htmlspecialchars($user_data['phone_user'] ?? 'N/A'); ?></span>
                            <input class="form-control field-input" type="text" name="phone_user" id="phone_user" value="<?php echo htmlspecialchars($user_data['phone_user'] ?? ''); ?>">
                            <span class="field-error" id="phone_user-error">Phone must be exactly 8 digits.</span>
                        </div>
                        <div class="profile-field" data-inline-field data-field-name="gender_user">
                            <div class="field-head">
                                <label class="field-title">Gender</label>
                                <button type="button" class="inline-edit-toggle" data-inline-toggle aria-label="Edit gender" title="Edit gender">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                </button>
                            </div>
                            <span class="field-value"><?php echo htmlspecialchars($user_data['gender_user'] ?? 'N/A'); ?></span>
                            <select class="form-select field-input" name="gender_user">
                                <?php foreach (['Male', 'Female', 'Other'] as $g): ?>
                                    <option value="<?php echo $g; ?>" <?php echo ($user_data['gender_user'] ?? '') === $g ? 'selected' : ''; ?>><?php echo $g; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="profile-field" data-inline-field data-field-name="birthday_user">
                            <div class="field-head">
                                <label class="field-title" for="birthday_user">Birthday</label>
                                <button type="button" class="inline-edit-toggle" data-inline-toggle aria-label="Edit birthday" title="Edit birthday">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                </button>
                            </div>
                            <span class="field-value"><?php echo htmlspecialchars($user_data['birthday_user'] ?? 'N/A'); ?></span>
                            <input class="form-control field-input" type="date" name="birthday_user" id="birthday_user" value="<?php echo htmlspecialchars($user_data['birthday_user'] ?? ''); ?>">
                            <span class="field-error" id="birthday_user-error">You must be at least 15 years old.</span>
                        </div>
                    </div>
                </div>

                <div class="section-card profile-section-anchor" id="security">
                    <div class="section-header">
                        <div>
                            <div class="profile-kicker">Security</div>
                            <div class="section-title">Password and access</div>
                            <div class="section-subtitle">Use a strong password and review connected devices regularly.</div>
                        </div>
                    </div>
                    <div class="profile-grid">
                        <div class="profile-field profile-field--full">
                            <label>Password</label>
                            <span class="field-value">Your current password is protected. Use the action below to change it.</span>
                            <div class="profile-section-actions">
                                <button type="button" class="btn btn-foovia" data-bs-toggle="modal" data-bs-target="#passwordModal">Change Password</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card profile-section-anchor" id="wellness">
                    <div class="section-header">
                        <div>
                            <div class="profile-kicker">Wellness</div>
                            <div class="section-title">Body metrics and activity</div>
                            <div class="section-subtitle">These details help tailor Foovia recommendations to your profile.</div>
                        </div>
                    </div>
                    <div class="profile-grid">
                        <div class="profile-field" data-inline-field data-field-name="height_user">
                            <div class="field-head">
                                <label class="field-title" for="height_user">Height (cm)</label>
                                <button type="button" class="inline-edit-toggle" data-inline-toggle aria-label="Edit height" title="Edit height">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                </button>
                            </div>
                            <span class="field-value"><?php echo htmlspecialchars($user_data['height_user'] ?? 'N/A'); ?></span>
                            <input class="form-control field-input" type="number" name="height_user" id="height_user" value="<?php echo htmlspecialchars($user_data['height_user'] ?? ''); ?>">
                            <span class="field-error" id="height_user-error">Height must be between 100 and 250 cm.</span>
                        </div>
                        <div class="profile-field" data-inline-field data-field-name="weight_user">
                            <div class="field-head">
                                <label class="field-title" for="weight_user">Weight (kg)</label>
                                <button type="button" class="inline-edit-toggle" data-inline-toggle aria-label="Edit weight" title="Edit weight">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                </button>
                            </div>
                            <span class="field-value"><?php echo htmlspecialchars($user_data['weight_user'] ?? 'N/A'); ?></span>
                            <input class="form-control field-input" type="number" name="weight_user" id="weight_user" value="<?php echo htmlspecialchars($user_data['weight_user'] ?? ''); ?>">
                            <span class="field-error" id="weight_user-error">Weight must be between 30 and 300 kg.</span>
                        </div>
                        <div class="profile-field">
                            <label class="field-title">BMI</label>
                            <span class="field-value"><?php echo htmlspecialchars($user_data['bmi_user'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="profile-field" data-inline-field data-field-name="activitylvl_user">
                            <div class="field-head">
                                <label class="field-title" for="activitylvl_user">Activity level</label>
                                <button type="button" class="inline-edit-toggle" data-inline-toggle aria-label="Edit activity level" title="Edit activity level">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                </button>
                            </div>
                            <span class="field-value"><?php echo htmlspecialchars($user_data['activitylvl_user'] ?? 'N/A'); ?></span>
                            <select class="form-select field-input" name="activitylvl_user" id="activitylvl_user">
                                <?php foreach (['Sedentary', 'Light', 'Moderate', 'Active', 'Very Active'] as $lvl): ?>
                                    <option value="<?php echo $lvl; ?>" <?php echo ($user_data['activitylvl_user'] ?? '') === $lvl ? 'selected' : ''; ?>><?php echo $lvl; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="field-error" id="activitylvl_user-error">Please select your activity level.</span>
                        </div>
                    </div>
                </div>

                <div class="section-card profile-section-anchor" id="health">
                    <div class="section-header">
                        <div>
                            <div class="profile-kicker">Health</div>
                            <div class="section-title">Medical notes</div>
                            <div class="section-subtitle">Add the details Foovia should remember when building meal and workout suggestions.</div>
                        </div>
                    </div>
                    <div class="profile-grid">
                        <div class="profile-field profile-field--full" data-inline-field data-field-name="illness_user">
                            <div class="field-head">
                                <label class="field-title" for="illness_user">Illness</label>
                                <button type="button" class="inline-edit-toggle" data-inline-toggle aria-label="Edit illness" title="Edit illness">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                </button>
                            </div>
                            <span class="field-value"><?php echo htmlspecialchars($user_data['illness_user'] ?? 'None'); ?></span>
                            <input class="form-control field-input" type="text" name="illness_user" id="illness_user" value="<?php echo htmlspecialchars($user_data['illness_user'] ?? ''); ?>">
                        </div>
                        <div class="profile-field profile-field--full" data-inline-field data-field-name="allergie_user">
                            <div class="field-head">
                                <label class="field-title" for="allergie_user">Allergies</label>
                                <button type="button" class="inline-edit-toggle" data-inline-toggle aria-label="Edit allergies" title="Edit allergies">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                </button>
                            </div>
                            <span class="field-value"><?php echo htmlspecialchars($user_data['allergie_user'] ?? 'None'); ?></span>
                            <input class="form-control field-input" type="text" name="allergie_user" id="allergie_user" value="<?php echo htmlspecialchars($user_data['allergie_user'] ?? ''); ?>">
                        </div>
                        <div class="profile-field profile-field--full" data-inline-field data-field-name="medicament_user">
                            <div class="field-head">
                                <label class="field-title" for="medicament_user">Medications</label>
                                <button type="button" class="inline-edit-toggle" data-inline-toggle aria-label="Edit medications" title="Edit medications">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                </button>
                            </div>
                            <span class="field-value"><?php echo htmlspecialchars($user_data['medicament_user'] ?? 'None'); ?></span>
                            <input class="form-control field-input" type="text" name="medicament_user" id="medicament_user" value="<?php echo htmlspecialchars($user_data['medicament_user'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="section-card profile-section-anchor" id="admin">
                    <div class="section-header">
                        <div>
                            <div class="profile-kicker">System</div>
                            <div class="section-title">Account status</div>
                            <div class="section-subtitle">Read-only data about registration and access level.</div>
                        </div>
                    </div>
                    <div class="profile-grid">
                        <div class="profile-field">
                            <label>Registration date</label>
                            <span class="field-value"><?php echo htmlspecialchars($user_data['inscriptiondate_user'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="profile-field">
                            <label>Role</label>
                            <span class="field-value"><?php echo htmlspecialchars((string) ($user_data['role_user'] ?? 'N/A')); ?></span>
                        </div>
                    </div>
                </div>

                <div class="profile-actions-bar">
                    <div>
                        <div class="profile-kicker">Danger zone</div>
                        <p>Deleting your account will remove all saved profile data.</p>
                    </div>
                    <div class="profile-actions-row">
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete Account</button>
                    </div>
                </div>
                </div>
            </form>
        </main>
    </div>
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

    // Inline edit logic
    const profileForm = document.getElementById('profileForm');
    const penIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg>';
    const checkIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"></path></svg>';

    function setInlineButtonState(button, editing) {
        button.classList.toggle('is-save', editing);
        button.setAttribute('aria-label', editing ? 'Save field' : 'Edit field');
        button.setAttribute('title', editing ? 'Save field' : 'Edit field');
        button.innerHTML = editing ? checkIcon : penIcon;
    }

    function getFieldValidator(fieldName) {
        const validators = {
            name_user: validateName,
            lastname_user: validateLastname,
            email_user: validateEmail,
            phone_user: validatePhone,
            birthday_user: validateBirthday,
            height_user: validateHeight,
            weight_user: validateWeight,
            activitylvl_user: validateActivity,
        };

        return validators[fieldName] || (() => true);
    }

    document.querySelectorAll('[data-inline-toggle]').forEach((button) => {
        setInlineButtonState(button, false);

        button.addEventListener('click', () => {
            const field = button.closest('[data-inline-field]');
            const input = field?.querySelector('.field-input');
            if (!field || !input) return;

            const isEditing = field.classList.contains('is-editing');
            if (!isEditing) {
                field.classList.add('is-editing');
                setInlineButtonState(button, true);
                if (typeof input.focus === 'function') input.focus();
                if (typeof input.select === 'function' && input.tagName === 'INPUT' && input.type !== 'date' && input.type !== 'number') {
                    input.select();
                }
                return;
            }

            const validator = getFieldValidator(field.dataset.fieldName || input.name);
            if (validator()) {
                profileForm.requestSubmit();
            }
        });
    });

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

    profileForm.addEventListener('submit', function (e) {
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
<script src="js/sidebar.js"></script>
</body>
</html>
