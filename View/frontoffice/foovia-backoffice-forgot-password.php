<?php
session_start();

include_once(__DIR__ . '/../../model/config.php');
include_once(__DIR__ . '/../../controller/Controller_user.php');
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_submit'])) {
    $email = strtolower(trim($_POST['email'] ?? ''));

    if (empty($email)) {
        $error_message = 'Please enter your email address.';
    } else {
        try {
            $controller = new Controller_user();
            $user = $controller->get_user_by_email($email);

            if ($user && strtolower(trim($user['role_user'])) === 'admin') {
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $controller->set_user_reset_token((int)$user['id_user'], $token, $expires_at);

                // Send email
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    
                    $env = parse_ini_file(__DIR__ . '/../../.env');
                    $mail->Username   = $env['SMTP_USERNAME']; 
                    $mail->Password   = $env['SMTP_PASSWORD']; 
                    
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('noreply@foovia.com', 'FOOVIA Admin Center');
                    $mail->addAddress($email, $user['name_user']);

                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST'];
                    $base_dir = dirname($_SERVER['PHP_SELF']);
                    $reset_link = $protocol . "://" . $host . $base_dir . "/foovia-backoffice-reset-password.php?token=" . $token;

                    $mail->isHTML(true);
                    $mail->Subject = 'Foovia Backoffice - Password Reset';
                    $mail->Body    = "
                        <h2>Admin Password Reset</h2>
                        <p>Hello {$user['name_user']},</p>
                        <p>A password reset was requested for your admin account.</p>
                        <p>Click the link below to securely set your new password:</p>
                        <p><a href='{$reset_link}'>{$reset_link}</a></p>
                        <p>This link will expire in 1 hour.</p>
                        <p>If you didn't request this, contact another administrator immediately.</p>
                    ";
                    $mail->AltBody = "Hello {$user['name_user']},\n\nA password reset was requested for your admin account. Click the link to reset it: {$reset_link}\n\nThis link will expire in 1 hour.";

                    $mail->send();
                    $success_message = 'If that admin account exists, a secure reset link has been sent.';
                } catch (Exception $e) {
                    $error_message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                // For security, do not confirm existence
                $success_message = 'If that admin account exists, a secure reset link has been sent.';
            }
        } catch (Exception $e) {
            $error_message = 'An error occurred while processing your request.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA - Backoffice Recover</title>
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
    <h1>Account<br>recovery<br><span>portal.</span></h1>
    <p>Lost your admin password? Enter your verified email address to regain access to your control center.</p>
  </div>

  <div class="left-pills">
    <div class="pill"><div class="pill-dot pill-dot-yellow"></div>Secure email verification</div>
    <div class="pill"><div class="pill-dot pill-dot-green"></div>256-bit token encryption</div>
  </div>
</div>

<div class="right-panel">
  <p class="form-eyebrow">Admin area</p>
  <h1 class="form-title">Reset your<br>password</h1>
  <p class="form-sub">Remembered it? <a href="foovia-backoffice.php">Sign in instead</a></p>

  <?php if (!empty($success_message)): ?>
    <div class="backoffice-alert backoffice-alert-success"><?php echo htmlspecialchars($success_message); ?></div>
  <?php endif; ?>

  <?php if (!empty($error_message)): ?>
    <div class="backoffice-alert backoffice-alert-error"><?php echo htmlspecialchars($error_message); ?></div>
  <?php endif; ?>

  <form method="POST" action="" id="backofficeRecoverForm">
    <div class="field-group">
      <div class="field">
        <label for="email">Admin Email Address</label>
        <div class="field-wrap">
          <input type="text" id="email" name="email" placeholder="admin@example.com" autocomplete="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
          <span class="field-icon">@</span>
        </div>
        <span class="field-error" id="err-email">Email must be in format: example@gmail.com</span>
      </div>
    </div>

    <button type="submit" name="forgot_submit" class="btn-submit" style="margin-top: 30px;">Send Recovery Link</button>
  </form>

  <div class="divider">
    <div class="divider-line"></div>
    <span class="divider-text">quick links</span>
    <div class="divider-line"></div>
  </div>

  <div class="social-btns">
    <a class="social-btn" href="foovia-signin.php">Regular user sign in</a>
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

document.getElementById('backofficeRecoverForm').addEventListener('submit', function(e) {
  const validEmail = validate('email', v => /^[a-zA-Z0-9._%+\-]+@gmail\.com$/.test(v), 'err-email');
  if (!validEmail) {
    e.preventDefault();
  }
});
</script>
</body>
</html>
