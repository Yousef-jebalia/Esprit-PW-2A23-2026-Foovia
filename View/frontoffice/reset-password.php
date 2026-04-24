<?php
session_start();
include_once(__DIR__ . '/../../model/config.php');

$error_message = '';
$success_message = '';

if (!isset($_GET['token']) && !isset($_POST['token'])) {
    die("Invalid token.");
}

$token = $_GET['token'] ?? $_POST['token'];
$first_login = isset($_GET['first_login']) || isset($_POST['first_login']);

try {
    $db = config::getConnexion();
    $sql = "SELECT id_user, name_user, email_user, reset_token_expires_at FROM user WHERE reset_token = :token";
    $query = $db->prepare($sql);
    $query->execute(['token' => $token]);
    $user = $query->fetch();

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
            // Update password and clear token
            $updateSql = "UPDATE user SET password_user = :password, reset_token = NULL, reset_token_expires_at = NULL WHERE id_user = :id_user";
            $updateQuery = $db->prepare($updateSql);
            $updateQuery->execute([
                'password' => $password, // using plaintext password for now based on login logic seen earlier
                'id_user' => $user['id_user']
            ]);

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
    <form method="POST" action="">
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
      <?php if ($first_login): ?>
        <input type="hidden" name="first_login" value="1">
      <?php endif; ?>
      <div class="field-group">
        <div class="field">
          <label for="password">New Password</label>
          <div class="field-wrap">
            <input type="password" id="password" name="password" placeholder="New password" required/>
          </div>
        </div>
        <div class="field">
          <label for="confirm_password">Confirm New Password</label>
          <div class="field-wrap">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required/>
          </div>
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

</body>
</html>
