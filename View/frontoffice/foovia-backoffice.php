<?php
session_start();
include_once(__DIR__ . '/../../model/config.php');
include_once(__DIR__ . '/../../controller/Controller_user.php');

$error_message = '';
$warning_message = '';

$controller = new Controller_user();
$controller->release_expired_bans();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signin_submit'])) {
  $email = strtolower(trim($_POST['email'] ?? ''));
  $password = $_POST['password'] ?? '';

  if (empty($email) || empty($password)) {
    $error_message = 'Email and password are required.';
  } else {
    try {
      $user = $controller->get_user_by_email($email);

      if (!$user) {
        $error_message = 'Username or password is false.';
      } else {
        $banState = $controller->process_ban_countdown((int) $user['id_user']);

        if ($banState['is_banned']) {
          $error_message = 'Your account is banned. Try again in ' . $banState['remaining'] . '.';
        } else {
          $role = strtolower(trim($user['role_user'] ?? ''));

          if ($password === $user['password_user'] && $role === 'admin') {
            $controller->reset_failed_login_attempts((int) $user['id_user']);
            
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_name'] = $user['name_user'];
            $_SESSION['user_email'] = $user['email_user'];
            $_SESSION['user_role'] = $user['role_user'];

            header('Location: ../backoffice/backoffice_work.php');
            exit;
          } else {
            if ($role !== 'admin') {
              $error_message = 'Access denied. This area is for admin users only.';
            } else {
              $attemptState = $controller->register_failed_login_attempt((int) $user['id_user']);

              if ($attemptState['is_banned']) {
                $error_message = 'Your account is now banned for 5 minutes. Try again in ' . $attemptState['remaining'] . '.';
              } else {
                $error_message = 'Username or password is false. Remaining attempts: ' . $attemptState['remaining_attempts'];
              }
            }
          }
        }
      }
    } catch (Exception $e) {
      $error_message = 'An error occurred while signing in.';
    }
  }
}

$tries_left = 3;
$is_locked = false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA - Backoffice Access</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Boldonse&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="foovia-signin.css">
<link rel="stylesheet" href="foovia-backoffice.css">
</head>
<body>

<div class="left-panel">
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>
  <div class="blob blob-3"></div>

  <a href="foovia.php" class="left-logo">FOOVIA</a>

  <div class="left-body">
    <h1>Backoffice<br>control<br><span>center.</span></h1>
    <p>Manage users, follow platform activity, and operate your admin tools from one secure space.</p>
  </div>

  <div class="left-pills">
    <div class="pill"><div class="pill-dot pill-dot-yellow"></div>User and account supervision</div>
    <div class="pill"><div class="pill-dot pill-dot-green"></div>Dashboard and analytics pages</div>
    <div class="pill"><div class="pill-dot pill-dot-orange"></div>Backoffice authentication flow</div>
  </div>
</div>

<div class="right-panel">
  <p class="form-eyebrow">Admin area</p>
  <h1 class="form-title">Sign in to<br>Backoffice</h1>
  <p class="form-sub">Need an admin account? <a href="../backoffice/foovia-signup.php">Create one now</a></p>

  <?php if (!empty($warning_message)): ?>
    <div class="backoffice-alert backoffice-alert-warning"><?php echo htmlspecialchars($warning_message); ?></div>
  <?php endif; ?>

  <?php if (!empty($error_message)): ?>
    <div class="backoffice-alert backoffice-alert-error"><?php echo htmlspecialchars($error_message); ?></div>
  <?php endif; ?>

  <?php /* if ($tries_left > 0): ?>
    <p class="tries-note">Security notice: you have <?php echo $tries_left; ?> <?php echo $tries_left === 1 ? 'try' : 'tries'; ?> left.</p>
  <?php else: ?>
    <p class="tries-note tries-note-locked">Security notice: your 3 tries are used. Login is blocked for this session.</p>
  <?php endif; */ ?>

  <form method="POST" action="" id="backofficeForm">
    <div class="field-group">
      <div class="field">
        <label for="email">Email address</label>
        <div class="field-wrap">
          <input type="text" id="email" name="email" placeholder="admin@example.com" autocomplete="email" <?php echo $is_locked ? 'disabled' : ''; ?> value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"/>
          <span class="field-icon">@</span>
        </div>
        <span class="field-error" id="err-email">Please enter a valid email address.</span>
      </div>

      <div class="field">
        <label for="password">Password</label>
        <div class="field-wrap">
          <input type="password" id="password" name="password" placeholder="Your password" autocomplete="current-password" <?php echo $is_locked ? 'disabled' : ''; ?> value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>"/>
          <button class="toggle-pw" type="button" onclick="togglePw('password', this)" <?php echo $is_locked ? 'disabled' : ''; ?>>Show</button>
        </div>
        <span class="field-error" id="err-password">Password cannot be empty.</span>
      </div>
    </div>

    <div class="forgot-row"><a href="foovia-backoffice-forgot-password.php">Forgot your password?</a></div>

    <button type="submit" name="signin_submit" class="btn-submit" <?php echo $is_locked ? 'disabled' : ''; ?>>Continue to Backoffice</button>
  </form>

  <div class="divider">
    <div class="divider-line"></div>
    <span class="divider-text">quick links</span>
    <div class="divider-line"></div>
  </div>

  <div class="social-btns">
    <a class="social-btn" href="../frontoffice/foovia-signin.php">Sign in page</a>
    <a class="social-btn" href="../backoffice/foovia-signup.php">Sign up page</a>
  </div>
</div>

<script>
function togglePw(id, btn) {
  const input = document.getElementById(id);
  const hidden = input.type === 'password';
  input.type = hidden ? 'text' : 'password';
  btn.textContent = hidden ? 'Hide' : 'Show';
}

function validate(id, check, errId) {
  const input = document.getElementById(id);
  const err = document.getElementById(errId);
  const ok = check(input.value);
  input.classList.toggle('error', !ok);
  err.classList.toggle('visible', !ok);
  return ok;
}

document.getElementById('backofficeForm').addEventListener('submit', function(e) {
  const validEmail = validate('email', v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v), 'err-email');
  const validPassword = validate('password', v => v.length > 0, 'err-password');
  if (!validEmail || !validPassword) {
    e.preventDefault();
  }
});
</script>
</body>
</html>
