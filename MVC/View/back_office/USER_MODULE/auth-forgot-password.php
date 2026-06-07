<?php
session_start();
include_once(__DIR__ . '/../../../Model/config.php');
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../Support/PHPMailerStubs.php';
require_once __DIR__ . '/../../../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../../vendor/phpmailer/phpmailer/src/PHPMailer.php';

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
            $db = config::getConnexion();
            $sql = "SELECT id_user, name_user FROM user WHERE LOWER(email_user) = :email";
            $query = $db->prepare($sql);
            $query->execute(['email' => $email]);
            $user = $query->fetch();

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $updateSql = "UPDATE user SET reset_token = :token, reset_token_expires_at = :expires_at WHERE id_user = :id_user";
                $updateQuery = $db->prepare($updateSql);
                $updateQuery->execute([
                    'token' => $token,
                    'expires_at' => $expires_at,
                    'id_user' => $user['id_user']
                ]);

                // Send email
                $mail = new PHPMailer(true);

                try {
                    $envFile = __DIR__ . '/../../../../.env';
                    $env = is_file($envFile) ? parse_ini_file($envFile) : [];
                    if (!is_array($env)) {
                        $env = [];
                    }

                    $mail->isSMTP();
                    $mail->Host       = $env['SMTP_HOST'] ?? 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;

                    $mailUsername = $env['SMTP_USERNAME'] ?? '';
                    $mailPassword = $env['SMTP_PASSWORD'] ?? '';
                    $mailPort = (int)($env['SMTP_PORT'] ?? 587);
                    $mailEncryption = strtolower(trim($env['SMTP_ENCRYPTION'] ?? 'tls'));

                    if ($mailUsername === '' || $mailPassword === '') {
                        throw new Exception('SMTP credentials are not configured in .env.');
                    }

                    $mail->Username   = $mailUsername;
                    $mail->Password   = $mailPassword;

                    $mail->SMTPSecure = $mailEncryption === 'ssl'
                        ? PHPMailer::ENCRYPTION_SMTPS
                        : PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = $mailPort;

                    $mail->setFrom('noreply@foovia.com', 'FOOVIA Backoffice');
                    $mail->addAddress($email, $user['name_user']);

                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST'];
                    $base_dir = dirname($_SERVER['PHP_SELF']);
                    $reset_link = $protocol . "://" . $host . $base_dir . "/auth-reset-password.php?token=" . $token;

                    $mail->isHTML(true);
                    $mail->Subject = 'Foovia Backoffice - Password Reset Request';
                    $mail->Body    = "
                        <h2>Password Reset Request</h2>
                        <p>Hello {$user['name_user']},</p>
                        <p>We received a request to reset your admin password for the Foovia Backoffice.</p>
                        <p>Click the link below to set a new password:</p>
                        <p><a href='{$reset_link}'>{$reset_link}</a></p>
                        <p>If you didn't request this, you can safely ignore this email.</p>
                        <p>This link will expire in 1 hour.</p>
                    ";
                    $mail->AltBody = "Hello {$user['name_user']},\n\nWe received a request to reset your admin password. Click the link to reset it: {$reset_link}\n\nIf you didn't request this, please ignore this email.";

                    $mail->send();
                    $success_message = 'A password reset link has been sent to your email address.';
                } catch (Exception $e) {
                    $error_message = "Message could not be sent. Mail transport error: {$mail->ErrorInfo}";
                }
            } else {
                // For security, don't reveal if the email exists
                $success_message = 'If that email is in our database, a password reset link has been sent.';
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
    <title>Forgot Password - Foovia Backoffice</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/pages/waves/css/waves.min.css" type="text/css" media="all">
    <link rel="stylesheet" type="text/css" href="assets/icon/themify-icons/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="assets/icon/icofont/css/icofont.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body themebg-pattern="theme1">
    <section class="login-block">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                            <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                            <strong>Success:</strong> <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <form class="md-float-material form-material" method="POST" action="" id="forgotForm" novalidate>
                        <div class="text-center">
                            <img src="assets/images/logo.png" alt="logo.png">
                        </div>
                        <div class="auth-box card">
                            <div class="card-block">
                                <div class="row m-b-20">
                                    <div class="col-md-12">
                                        <h3 class="text-center">Recover your password</h3>
                                    </div>
                                </div>
                                <p class="text-muted text-center p-b-5">Enter your email address and we'll send you a link to reset your password.</p>
                                <div class="form-group form-primary">
                                    <input type="text" name="email" id="forgot-email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Your Email Address</label>
                                </div>
                                <div class="row m-t-30">
                                    <div class="col-md-12">
                                        <button type="submit" name="forgot_submit" class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20">Send Reset Link</button>
                                    </div>
                                </div>
                                <p class="f-w-600 text-right">Back to <a href="auth-normal-sign-in.php">Sign in.</a></p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <script type="text/javascript" src="assets/js/jquery/jquery.min.js "></script>
    <script type="text/javascript" src="assets/js/jquery-ui/jquery-ui.min.js "></script>
    <script type="text/javascript" src="assets/js/popper.js/popper.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js "></script>
    <script src="assets/pages/waves/js/waves.min.js"></script>
    <script type="text/javascript" src="assets/js/common-pages.js"></script>
    <script>
    document.getElementById('forgotForm')?.addEventListener('submit', function (e) {
        const email = (document.getElementById('forgot-email')?.value || '').trim();
        const validEmail = /^[a-zA-Z0-9._%+\-]+@gmail\.com$/.test(email);
        if (!validEmail) {
            e.preventDefault();
            alert('Please enter a valid email address (format: example@gmail.com).');
        }
    });
    </script>
</body>
</html>
