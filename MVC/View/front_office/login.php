<?php
session_start();
include_once(__DIR__ . '/../../Model/config.php');
include_once(__DIR__ . '/../../Controller/Controller_user.php');

$error_message = '';
$success_message = '';
$controller = new Controller_user();
$controller->release_expired_bans();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signin_submit'])) {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = 'Email and password are required.';
    } else {
        try {
            $db = config::getConnexion();

            $sql = "SELECT id_user, name_user, email_user, password_user, role_user FROM user WHERE LOWER(email_user) = :email";
            $query = $db->prepare($sql);
            $query->execute(['email' => $email]);
            $user = $query->fetch();

            if (!$user) {
                $error_message = 'Username or password is false';
            } else {

                if ($password === $user['password_user']) {
                    $controller->increment_user_login_count((int) $user['id_user']);
                    // Password is correct
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['user_name'] = $user['name_user'];
                    $_SESSION['user_email'] = $user['email_user'];
                    $_SESSION['role_user'] = $user['role_user'] ?? 'user';
                    $success_message = 'Connected successfully! Redirecting...';
                    header('refresh:2;url=foovia.php');
                    exit;
                } else {
                    $error_message = 'Username or password is false';
                }
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
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Foovia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 90vh;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        }
        .login-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-card h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-weight: 700;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
        .btn-signin {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-signin:hover {
            transform: translateY(-2px);
        }
        .signup-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .signup-link a {
            color: #FFD700;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .back-link a {
            color: #FFD700;
            text-decoration: none;
            font-size: 14px;
        }
        .field-error {
            color: #dc3545;
            font-size: 13px;
            display: none;
            margin-top: 6px;
        }
        .input-invalid {
            border-color: #dc3545 !important;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="back-link">
                <a href="foovia.php">← Back to Home</a>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <h2>Sign In</h2>

            <form method="POST" action="" id="signinForm" novalidate>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email">
                    <div class="field-error" id="err-email">Please enter a valid email address.</div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password">
                    <div class="field-error" id="err-password">Password is required.</div>
                </div>

                <button type="submit" name="signin_submit" class="btn-signin">Sign In</button>
            </form>

            <div class="signup-link">
                Don't have an account? <a href="../back_office/USER_MODULE/auth-sign-up.php">Sign Up</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function validateField(id, check, errorId) {
            const input = document.getElementById(id);
            const error = document.getElementById(errorId);
            const ok = check(input.value.trim());
            input.classList.toggle('input-invalid', !ok);
            error.style.display = ok ? 'none' : 'block';
            return ok;
        }

        document.getElementById('signinForm').addEventListener('submit', function (e) {
            const emailOk = validateField('email', v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v), 'err-email');
            const passwordOk = validateField('password', v => v.length > 0, 'err-password');
            if (!emailOk || !passwordOk) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
