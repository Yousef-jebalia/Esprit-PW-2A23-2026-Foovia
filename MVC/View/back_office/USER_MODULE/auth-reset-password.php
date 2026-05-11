<?php
session_start();
include_once(__DIR__ . '/../../../Model/config.php');

$error_message = '';
$success_message = '';
$token_valid = false;
$user_id = null;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    try {
        $db = config::getConnexion();
        $sql = "SELECT id_user FROM user WHERE reset_token = :token AND reset_token_expires_at > NOW()";
        $query = $db->prepare($sql);
        $query->execute(['token' => $token]);
        $user = $query->fetch();

        if ($user) {
            $token_valid = true;
            $user_id = $user['id_user'];
        } else {
            $error_message = 'Invalid or expired password reset token.';
        }
    } catch (Exception $e) {
        $error_message = 'Database error: ' . $e->getMessage();
    }
} else {
    $error_message = 'No reset token provided.';
}

if ($token_valid && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_submit'])) {
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
            $db = config::getConnexion();
            $sql = "UPDATE user SET password_user = :password, reset_token = NULL, reset_token_expires_at = NULL WHERE id_user = :id_user";
            $query = $db->prepare($sql);
            $query->execute([
                'password' => $password,
                'id_user' => $user_id
            ]);

            $success_message = 'Your password has been successfully reset. Redirecting to sign in...';
            $token_valid = false; // Hide form
            header("refresh:3;url=auth-normal-sign-in.php");
        } catch (Exception $e) {
            $error_message = 'Failed to reset password: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Set New Password - Foovia Backoffice</title>
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

                    <?php if ($token_valid): ?>
                    <form class="md-float-material form-material" method="POST" action="" id="resetForm" novalidate>
                        <div class="text-center">
                            <img src="assets/images/logo.png" alt="logo.png">
                        </div>
                        <div class="auth-box card">
                            <div class="card-block">
                                <div class="row m-b-20">
                                    <div class="col-md-12">
                                        <h3 class="text-center">Set New Password</h3>
                                    </div>
                                </div>
                                <div class="form-group form-primary">
                                    <input type="password" name="password" id="reset-password" class="form-control" value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>">
                                    <span class="form-bar"></span>
                                    <label class="float-label">New Password (min 8 characters)</label>
                                </div>
                                <div class="form-group form-primary">
                                    <input type="password" name="confirm_password" id="reset-confirm-password" class="form-control" value="<?php echo htmlspecialchars($_POST['confirm_password'] ?? ''); ?>">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Confirm New Password</label>
                                </div>
                                <div class="row m-t-30">
                                    <div class="col-md-12">
                                        <button type="submit" name="reset_submit" class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20">Reset Password</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php elseif(!empty($error_message) && !isset($_GET['token'])): ?>
                        <!-- No token provided -->
                        <div class="text-center">
                            <p style="color:white; margin-top:20px;">Return to <a href="auth-normal-sign-in.php" style="color:#fff; text-decoration:underline;">Sign in</a>.</p>
                        </div>
                    <?php endif; ?>
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
    document.getElementById('resetForm')?.addEventListener('submit', function (e) {
        const password = document.getElementById('reset-password')?.value || '';
        const confirmPassword = document.getElementById('reset-confirm-password')?.value || '';
        const validPassword = password.length >= 8;
        const validConfirm = confirmPassword.length >= 8 && password === confirmPassword;

        if (!validPassword || !validConfirm) {
            e.preventDefault();
            alert('Password must be at least 8 characters and both fields must match.');
        }
    });
    </script>
</body>
</html>
