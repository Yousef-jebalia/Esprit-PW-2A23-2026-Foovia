<?php
session_start();
include_once(__DIR__ . '/../../../Controller/Controller_user.php');

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup_submit'])) {
    $name     = trim($_POST['name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';
    $lastname  = trim($_POST['lastname'] ?? $name);
    $gender    = $_POST['gender'] ?? 'Not specified';
    $birthday  = $_POST['birthday'] ?? '';
    $height    = intval($_POST['height'] ?? 0);
    $weight    = intval($_POST['weight'] ?? 0);
    $bmi       = intval($_POST['bmi'] ?? 0);
    $activity  = $_POST['activity'] ?? '';
    $illness   = $_POST['illness'] ?? '';
    $allergie  = $_POST['allergie'] ?? '';
    $medicament = $_POST['medicament'] ?? '';

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (!isset($_POST['terms_accepted'])) {
        $error_message = 'You must accept the Terms & Conditions.';
    } else {
        try {
            $user = new User(
                0,
                $name,
                $lastname,
                $email,
                $password,
                $phone,
                $gender,
                $birthday,
                $height,
                $weight,
                $bmi,
                $activity,
                $illness,
                $allergie,
                $medicament,
                date('Y-m-d H:i:s'),
                'user',
                'normal',
                'active',
                '00:00:00'
            );

            $controller = new Controller_user();
            $result = $controller->add_user($user);

            $success_message = 'Account created successfully! Redirecting to survey...';
            $_SESSION['signup_email'] = $email;
            header('Location: survey.php');
            exit;
        } catch (Exception $e) {
            $error_message = 'An error occurred: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign Up - Foovia</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/pages/waves/css/waves.min.css" type="text/css" media="all">
    <link rel="stylesheet" type="text/css" href="assets/icon/themify-icons/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="assets/icon/icofont/css/icofont.css">
    <link rel="stylesheet" type="text/css" href="assets/icon/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <style>
        .field-error { color: #e74c3c; font-size: 12px; margin-top: 4px; display: none; }
        .form-control.invalid { border-bottom: 2px solid #e74c3c !important; }
        .form-control.valid   { border-bottom: 2px solid #2ecc71 !important; }
    </style>
</head>
<body themebg-pattern="theme1">

<div class="theme-loader">
    <div class="loader-track">
        <div class="preloader-wrapper">
            <div class="spinner-layer spinner-blue">
                <div class="circle-clipper left"><div class="circle"></div></div>
                <div class="gap-patch"><div class="circle"></div></div>
                <div class="circle-clipper right"><div class="circle"></div></div>
            </div>
            <div class="spinner-layer spinner-red">
                <div class="circle-clipper left"><div class="circle"></div></div>
                <div class="gap-patch"><div class="circle"></div></div>
                <div class="circle-clipper right"><div class="circle"></div></div>
            </div>
            <div class="spinner-layer spinner-yellow">
                <div class="circle-clipper left"><div class="circle"></div></div>
                <div class="gap-patch"><div class="circle"></div></div>
                <div class="circle-clipper right"><div class="circle"></div></div>
            </div>
            <div class="spinner-layer spinner-green">
                <div class="circle-clipper left"><div class="circle"></div></div>
                <div class="gap-patch"><div class="circle"></div></div>
                <div class="circle-clipper right"><div class="circle"></div></div>
            </div>
        </div>
    </div>
</div>

<section class="login-block">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <form class="md-float-material form-material" id="signupForm" action="" method="POST" novalidate>
                    <div class="text-center">
                        <img src="assets/images/logo.png" alt="logo.png">
                    </div>
                    <div class="auth-box card">
                        <div class="card-block">
                            <div class="row m-b-20">
                                <div class="col-md-12">
                                    <h3 class="text-center txt-primary">Sign up</h3>
                                </div>
                            </div>

                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger"><?php echo $error_message; ?></div>
                            <?php endif; ?>
                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success"><?php echo $success_message; ?></div>
                            <?php endif; ?>

                            <!-- Username -->
                            <div class="form-group form-primary">
                                <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                                <span class="form-bar"></span>
                                <label class="float-label">Choose Username</label>
                                <span class="field-error" id="name-error">Username is required.</span>
                            </div>

                            <!-- Email -->
                            <div class="form-group form-primary">
                                <input type="text" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                <span class="form-bar"></span>
                                <label class="float-label">Your Email Address</label>
                                <span class="field-error" id="email-error">Email must end with @gmail.com</span>
                            </div>

                            <!-- Phone -->
                            <div class="form-group form-primary">
                                <input type="text" name="phone" id="phone" class="form-control" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                <span class="form-bar"></span>
                                <label class="float-label">Phone Number</label>
                                <span class="field-error" id="phone-error">Phone must be exactly 8 digits.</span>
                            </div>

                            <!-- Password -->
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group form-primary">
                                        <input type="password" name="password" id="password" class="form-control" value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>">
                                        <span class="form-bar"></span>
                                        <label class="float-label">Password</label>
                                        <span class="field-error" id="password-error">Password must be at least 8 characters.</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group form-primary">
                                        <input type="password" name="confirm-password" id="confirm-password" class="form-control" value="<?php echo htmlspecialchars($_POST['confirm-password'] ?? ''); ?>">
                                        <span class="form-bar"></span>
                                        <label class="float-label">Confirm Password</label>
                                        <span class="field-error" id="confirm-password-error">Passwords do not match.</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms -->
                            <div class="row m-t-25 text-left">
                                <div class="col-md-12">
                                    <div class="checkbox-fade fade-in-primary">
                                        <label>
                                            <input type="checkbox" name="terms_accepted" id="terms_accepted" value="1">
                                            <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                            <span class="text-inverse">I read and accept <a href="#">Terms &amp; Conditions.</a></span>
                                        </label>
                                    </div>
                                    <span class="field-error" id="terms-error">You must accept the Terms & Conditions.</span>
                                </div>
                            </div>

                            <div class="row m-t-30">
                                <div class="col-md-12">
                                    <button type="submit" name="signup_submit" class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20">Sign up now</button>
                                </div>
                            </div>
                            <hr/>
                            <div class="row">
                                <div class="col-md-10"></div>
                                <div class="col-md-2">
                                    <img src="assets/images/auth/Logo-small-bottom.png" alt="small-logo.png">
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript" src="assets/js/jquery/jquery.min.js"></script>
<script type="text/javascript" src="assets/js/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="assets/js/popper.js/popper.min.js"></script>
<script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/pages/waves/js/waves.min.js"></script>
<script type="text/javascript" src="assets/js/jquery-slimscroll/jquery.slimscroll.js"></script>
<script type="text/javascript" src="assets/js/common-pages.js"></script>

<script>

function showError(fieldId, errorId) {
    const field = document.getElementById(fieldId);
    const error = document.getElementById(errorId);
    field.classList.add('invalid');
    field.classList.remove('valid');
    error.style.display = 'block';
    return false;
}
function showValid(fieldId, errorId) {
    const field = document.getElementById(fieldId);
    const error = document.getElementById(errorId);
    field.classList.remove('invalid');
    field.classList.add('valid');
    error.style.display = 'none';
    return true;
}

document.getElementById('phone').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 8);
});

document.getElementById('name').addEventListener('blur', validateName);
document.getElementById('email').addEventListener('blur', validateEmail);
document.getElementById('phone').addEventListener('blur', validatePhone);
document.getElementById('password').addEventListener('blur', validatePassword);
document.getElementById('confirm-password').addEventListener('blur', validateConfirm);

function validateName() {
    const val = document.getElementById('name').value.trim();
    return val.length > 0
        ? showValid('name', 'name-error')
        : showError('name', 'name-error');
}

function validateEmail() {
    const val = document.getElementById('email').value.trim();

    return /^[a-zA-Z0-9._%+\-]+@gmail\.com$/.test(val)
        ? showValid('email', 'email-error')
        : showError('email', 'email-error');
}

function validatePhone() {
    const val = document.getElementById('phone').value.trim();

    return /^\d{8}$/.test(val)
        ? showValid('phone', 'phone-error')
        : showError('phone', 'phone-error');
}

function validatePassword() {
    const val = document.getElementById('password').value;
    return val.length >= 8
        ? showValid('password', 'password-error')
        : showError('password', 'password-error');
}

function validateConfirm() {
    const pw  = document.getElementById('password').value;
    const cpw = document.getElementById('confirm-password').value;
    return pw === cpw && cpw.length > 0
        ? showValid('confirm-password', 'confirm-password-error')
        : showError('confirm-password', 'confirm-password-error');
}

document.getElementById('signupForm').addEventListener('submit', function (e) {
    const nameOk    = validateName();
    const emailOk   = validateEmail();
    const phoneOk   = validatePhone();
    const passOk    = validatePassword();
    const confirmOk = validateConfirm();

    const terms = document.getElementById('terms_accepted');
    const termsError = document.getElementById('terms-error');
    let termsOk = true;
    if (!terms.checked) {
        termsError.style.display = 'block';
        termsOk = false;
    } else {
        termsError.style.display = 'none';
    }

    if (!nameOk || !emailOk || !phoneOk || !passOk || !confirmOk || !termsOk) {
        e.preventDefault();
    }
});

// Add an eye icon to toggle password visibility
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Add event listeners for password toggle
const passwordToggle = document.createElement('i');
passwordToggle.className = 'fa fa-eye';
passwordToggle.id = 'password-toggle';
passwordToggle.style.cursor = 'pointer';
passwordToggle.style.position = 'absolute';
passwordToggle.style.right = '10px';
passwordToggle.style.top = '50%';
passwordToggle.style.transform = 'translateY(-50%)';

document.getElementById('password').parentNode.style.position = 'relative';
document.getElementById('password').parentNode.appendChild(passwordToggle);

passwordToggle.addEventListener('click', () => togglePasswordVisibility('password', 'password-toggle'));

const confirmPasswordToggle = document.createElement('i');
confirmPasswordToggle.className = 'fa fa-eye';
confirmPasswordToggle.id = 'confirm-password-toggle';
confirmPasswordToggle.style.cursor = 'pointer';
confirmPasswordToggle.style.position = 'absolute';
confirmPasswordToggle.style.right = '10px';
confirmPasswordToggle.style.top = '50%';
confirmPasswordToggle.style.transform = 'translateY(-50%)';

document.getElementById('confirm-password').parentNode.style.position = 'relative';
document.getElementById('confirm-password').parentNode.appendChild(confirmPasswordToggle);

confirmPasswordToggle.addEventListener('click', () => togglePasswordVisibility('confirm-password', 'confirm-password-toggle'));
</script>
</body>
</html>
