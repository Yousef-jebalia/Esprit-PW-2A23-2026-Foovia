<?php
session_start();

include_once(__DIR__ . '/../../Model/config.php');

include_once(__DIR__ . '/../../Controller/Controller_user.php');

$error_message = '';
$success_message = '';
$token_valid = false;
$user_id = null;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    try {
        $controller = new Controller_user();
        $user = $controller->get_user_by_token($token);

        if ($user) {
            $token_valid = true;
            $user_id = $user['id_user'];
        } else {
            $error_message = 'Invalid or expired password reset token.';
        }
    } catch (Exception $e) {
        $error_message = 'Database error occurred.';
    }
} else {
    $error_message = 'No reset token provided.';
}

if ($token_valid && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_submit'])) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm_password)) {
        $error_message = 'Both fields are required.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error_message = 'Password must be at least 8 characters long.';
    } else {
        try {
            $controller = new Controller_user();
            $controller->update_user_password_by_token((int)$user_id, $password);

            $success_message = 'Your password has been successfully reset! Redirecting...';
            $token_valid = false; // Hide form
            header("refresh:3;url=foovia-backoffice.php");
        } catch (Exception $e) {
            $error_message = 'Failed to reset password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="assets/Plan de travail 1 no bg (3) (1).png">
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA - Set New Password</title>
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
    <h1>Account<br>recovery<br><span>secured.</span></h1>
    <p>Please choose a strong password to protect your administrator privileges.</p>
  </div>

  <div class="left-pills">
    <div class="pill"><div class="pill-dot pill-dot-yellow"></div>Minimum 8 characters</div>
    <div class="pill"><div class="pill-dot pill-dot-green"></div>Unique phrase recommended</div>
  </div>
</div>

<div class="right-panel">
  <p class="form-eyebrow">Admin area</p>
  <h1 class="form-title">Set new<br>password</h1>

  <?php if (!empty($success_message)): ?>
    <div class="backoffice-alert backoffice-alert-success"><?php echo htmlspecialchars($success_message); ?></div>
  <?php endif; ?>

  <?php if (!empty($error_message)): ?>
    <div class="backoffice-alert backoffice-alert-error"><?php echo htmlspecialchars($error_message); ?></div>
  <?php endif; ?>

  <?php if ($token_valid): ?>
  <form method="POST" action="" id="backofficeResetForm">
    <div class="field-group">
      <div class="field">
        <label for="password">New Password</label>
        <div class="field-wrap">
          <input type="password" id="password" name="password" placeholder="At least 8 characters" autocomplete="new-password" value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>"/>
          <button class="toggle-pw" type="button" onclick="togglePw('password', this)">Show</button>
        </div>
        <span class="field-error" id="err-password">Password must be at least 8 characters.</span>
      </div>

      <div class="field">
        <label for="confirm_password">Confirm Password</label>
        <div class="field-wrap">
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-type your password" autocomplete="new-password" value="<?php echo htmlspecialchars($_POST['confirm_password'] ?? ''); ?>"/>
          <button class="toggle-pw" type="button" onclick="togglePw('confirm_password', this)">Show</button>
        </div>
        <span class="field-error" id="err-confirm-password">Passwords do not match.</span>
      </div>
    </div>

    <button type="submit" name="reset_submit" class="btn-submit" style="margin-top: 30px;">Update Password</button>
  </form>
  <?php elseif(!empty($error_message) && !isset($_GET['token'])): ?>
    <div style="margin-top: 20px;">
        <a class="btn-submit" href="foovia-backoffice.php" style="text-decoration:none; text-align:center; display:block;">Return to Sign In</a>
    </div>
  <?php endif; ?>
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

const form = document.getElementById('backofficeResetForm');
if(form) {
    form.addEventListener('submit', function(e) {
      const p1 = document.getElementById('password').value;
      const p2 = document.getElementById('confirm_password').value;

      const validPassword = validate('password', v => v.length >= 8, 'err-password');

      const validConfirm = p1 === p2;
      const cInput = document.getElementById('confirm_password');
      const cErr = document.getElementById('err-confirm-password');
      cInput.classList.toggle('error', !validConfirm);
      cErr.classList.toggle('visible', !validConfirm);

      if (!validPassword || !validConfirm) {
        e.preventDefault();
      }
    });
}
</script>
</body>
</html>
