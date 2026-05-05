                                <?php
session_start();
include_once(__DIR__ . '/../../../model/config.php');
include_once(__DIR__ . '/../../../controller/Controller_user.php');
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['forgot_submit'])) {
    $email = strtolower(trim($_POST['email'] ?? ''));

    if (empty($email)) {
        $error_message = 'Please enter your email address.';
    } else {
        try {
            $controller = new Controller_user();
            $user = $controller->get_user_by_email($email);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $controller->set_user_reset_token((int)$user['id_user'], $token, $expires_at);

                // Send email
                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    
                    // Load credentials from .env
                    $env = parse_ini_file(__DIR__ . '/../../.env');
                    $mail->Username   = $env['SMTP_USERNAME']; 
                    $mail->Password   = $env['SMTP_PASSWORD']; 
                    
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    // Recipients
                    $mail->setFrom('noreply@foovia.com', 'FOOVIA');
                    $mail->addAddress($email, $user['name_user']);

                    // Content
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST'];
                    $base_dir = dirname($_SERVER['PHP_SELF']);
                    $resetLink = $protocol . "://" . $host . $base_dir . '/reset-password.php?token=' . $token;

                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Request';
                    $mail->Body    = "Hi " . htmlspecialchars($user['name_user']) . ",<br><br>You requested to reset your password. Click the link below to reset it:<br><br><a href='" . $resetLink . "'>" . $resetLink . "</a><br><br>If you did not request this, please ignore this email.";
                    $mail->AltBody = "Hi " . $user['name_user'] . ",\n\nYou requested to reset your password. Open the following link in your browser to reset it:\n\n" . $resetLink . "\n\nIf you did not request this, please ignore this email.";

                    $mail->send();
                    $success_message = 'A password reset link has been sent to your email address.';
                } catch (Exception $e) {
                    $error_message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                // To prevent email enumeration, show success even if not found, or show error if you prefer
                $error_message = 'No account found with that email address.';
            }
        } catch (Exception $e) {
            $error_message = 'An error occurred: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA — Forgot Password</title>
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
  
  <h1 class="form-title">Forgot Password</h1>
  <p class="form-sub">Enter your email address to receive a password reset link.</p>

  <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" style="margin-bottom: 20px; padding: 12px; background: #fee; color: var(--red); border: 1px solid var(--red); border-radius: 8px; max-width: 400px; text-align: left;">
      <strong>Error!</strong> <?php echo htmlspecialchars($error_message); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($success_message)): ?>
    <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px; background: #efe; color: var(--green); border: 1px solid var(--green); border-radius: 8px; max-width: 400px; text-align: left;">
      <strong>Success!</strong> <?php echo htmlspecialchars($success_message); ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="" id="forgotForm" novalidate>
    <div class="field-group">
      <div class="field">
        <label for="email">Email address</label>
        <div class="field-wrap">
          <input type="text" id="email" name="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"/>
          <span class="field-icon">✉</span>
        </div>
          <span class="field-error" id="err-email">Email must be in format: example@gmail.com</span>
      </div>
    </div>
    
    <button type="submit" name="forgot_submit" class="btn-submit">Send Reset Link</button>
  </form>
  
  <div class="back-link">
    <a href="foovia-signin.php">← Back to Sign In</a>
  </div>
</div>

<script>
function validate(id, check, errId) {
  const input = document.getElementById(id);
  const err = document.getElementById(errId);
  const ok = check(input.value.trim());
  input.classList.toggle('error', !ok);
  err.classList.toggle('visible', !ok);
  return ok;
}

document.getElementById('forgotForm').addEventListener('submit', function (e) {
  const validEmail = validate('email', v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v), 'err-email');
  if (!validEmail) {
    e.preventDefault();
  }
});
</script>

</body>
</html>
