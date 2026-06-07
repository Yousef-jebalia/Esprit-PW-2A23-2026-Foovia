<?php
session_start();
include_once(__DIR__ . '/../../Model/config.php');
include_once(__DIR__ . '/../../Controller/Controller_user.php');

$error_message = '';
$success_message = '';

if (!isset($_GET['token']) && !isset($_POST['token'])) {
    die("Invalid token.");
}

$token = $_GET['token'] ?? $_POST['token'];
$first_login = isset($_GET['first_login']) || isset($_POST['first_login']);

try {
    $controller = new Controller_user();
    $user = $controller->get_user_by_token($token);

    if (!$user) {
        $error_message = 'Invalid or expired token.';
    } else {
        $expires = strtotime($user['reset_token_expires_at']);
        if (time() > $expires) {
            $error_message = 'This reset token has expired.';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_submit']) && empty($error_message)) {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($password) || empty($confirm_password)) {
            $error_message = 'Please fill in all fields.';
        } elseif ($password !== $confirm_password) {
            $error_message = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error_message = 'Password must be at least 6 characters long.';
        } else {
            // Update password and clear token via Controller
            $controller->update_user_password_by_token((int)$user['id_user'], $password);

            if ($first_login) {
                // Log them in immediately
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['user_name'] = $user['name_user'];
                $_SESSION['user_email'] = $user['email_user'];
                header("Location: foovia.php");
                exit;
            } else {
                $success_message = 'Your password has been reset successfully. You can now <a href="foovia-signin.php">sign in</a>.';
            }
        }
    }
} catch (Exception $e) {
    $error_message = 'An error occurred: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA — Reset Password</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Boldonse&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="foovia-signin.css">
<style>
  .right-panel { align-items: center; justify-content: center; text-align: center; }
  .form-title { font-size: 2.5rem; margin-bottom: 10px; }
  .form-sub { margin-bottom: 30px; }
  .field-group { width: 100%; max-width: 400px; text-align: left; }
  .btn-submit { width: 100%; max-width: 400px; }
  .back-link { margin-top: 20px; font-size: 0.9rem; }
  .back-link a { color: var(--green); text-decoration: none; font-weight: 500; }
  .back-link a:hover { text-decoration: underline; }
</style>
</head>
<body>

<div class="right-panel" style="width: 100%;">
  <a href="foovia.php" class="left-logo" style="position: absolute; top: 40px; left: 40px; color: var(--green); text-decoration: none; font-family: 'Boldonse', sans-serif; font-size: 1.5rem;">🌿 FOOVIA</a>

  <h1 class="form-title"><?php echo $first_login ? 'Welcome!' : 'Reset Password'; ?></h1>
  <p class="form-sub"><?php echo $first_login ? 'Please set a password for your new account.' : 'Enter your new password below.'; ?></p>

  <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" style="margin-bottom: 20px; padding: 12px; background: #fee; color: var(--red); border: 1px solid var(--red); border-radius: 8px; max-width: 400px; text-align: left;">
      <strong>Error!</strong> <?php echo htmlspecialchars($error_message); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($success_message)): ?>
    <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px; background: #efe; color: var(--green); border: 1px solid var(--green); border-radius: 8px; max-width: 400px; text-align: left;">
      <strong>Success!</strong> <?php echo $success_message; ?>
    </div>
  <?php else: ?>
    <?php if (empty($error_message) || isset($_POST['reset_submit'])): ?>
    <form method="POST" action="" id="resetForm" novalidate>
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
      <?php if ($first_login): ?>
        <input type="hidden" name="first_login" value="1">
      <?php endif; ?>
      <div class="field-group">
        <div class="field">
          <label for="password">New Password</label>
          <div class="field-wrap">
            <input type="password" id="password" name="password" placeholder="New password" value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>"/>
          </div>
          <span class="field-error" id="err-password">Password must be at least 6 characters.</span>
        </div>
        <div class="field">
          <label for="confirm_password">Confirm New Password</label>
          <div class="field-wrap">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" value="<?php echo htmlspecialchars($_POST['confirm_password'] ?? ''); ?>"/>
          </div>
          <span class="field-error" id="err-confirm-password">Passwords do not match.</span>
        </div>
      </div>

      <button type="submit" name="reset_submit" class="btn-submit">Reset Password</button>
    </form>
    <?php endif; ?>
  <?php endif; ?>

  <div class="back-link">
    <a href="foovia-signin.php">← Back to Sign In</a>
  </div>
</div>

<script>
function validate(id, check, errId) {
  const input = document.getElementById(id);
  const err = document.getElementById(errId);
  const ok = check(input.value);
  input.classList.toggle('error', !ok);
  err.classList.toggle('visible', !ok);
  return ok;
}

document.getElementById('resetForm')?.addEventListener('submit', function (e) {
  const p1 = document.getElementById('password').value;
  const p2 = document.getElementById('confirm_password').value;
  const validPassword = validate('password', v => v.length >= 6, 'err-password');
  const validConfirm = validate('confirm_password', v => v === p1 && v.length > 0, 'err-confirm-password');

  if (!validPassword || !validConfirm) {
    e.preventDefault();
  }
});
</script>

</body>
</html>
