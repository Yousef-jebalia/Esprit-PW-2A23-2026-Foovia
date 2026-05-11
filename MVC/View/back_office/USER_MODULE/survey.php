<?php
session_start();
include(__DIR__ . '/../../../Controller/Controller_user.php');

if (!isset($_SESSION['signup_email'])) {
    header('Location: auth-sign-up.php');
    exit;
}

$email = $_SESSION['signup_email'];
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['survey_submit'])) {
    try {
        $controller = new Controller_user();
        $db = config::getConnexion();

        $sql = "SELECT id_user, name_user, password_user, phone_user FROM user WHERE email_user = :email";
        $query = $db->prepare($sql);
        $query->execute(['email' => $email]);
        $result = $query->fetch();

        if (!$result) {
            $error_message = 'User not found.';
        } else {
            $user_id    = $result['id_user'];
            $user_pw    = $result['password_user'];
            $user_phone = $result['phone_user'];

            $user = new User(
                $user_id,
                $result['name_user'],
                $_POST['lastname'] ?? $result['name_user'],
                $email,
                $user_pw,
                $user_phone,
                $_POST['gender'] ?? '',
                $_POST['birthday'] ?? '',
                intval($_POST['height'] ?? 0),
                intval($_POST['weight'] ?? 0),
                intval($_POST['bmi'] ?? 0),
                $_POST['activity'] ?? '',
                $_POST['illness'] ?? '',
                $_POST['allergie'] ?? '',
                $_POST['medicament'] ?? '',
                date('Y-m-d H:i:s'),
                'user',
                'normal',
                'active',
                '00:00:00'
            );

            $controller->update_user($user, $user_id);

            $success_message = 'Survey completed successfully! Redirecting to login...';
            unset($_SESSION['signup_email']);
            header('refresh:2;url=../../front_office/login.php');
        }
    } catch (Exception $e) {
        $error_message = 'An error occurred: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Health Survey - Foovia</title>
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
    <section class="login-block">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <form class="md-float-material form-material" id="surveyForm" action="" method="POST" novalidate>
                        <div class="text-center">
                            <img src="assets/images/logo.png" alt="logo.png">
                        </div>
                        <div class="auth-box card">
                            <div class="card-block">
                                <div class="row m-b-20">
                                    <div class="col-md-12">
                                        <h3 class="text-center txt-primary">Health Survey</h3>
                                        <p class="text-center">Please complete your health profile</p>
                                    </div>
                                </div>

                                <?php if (!empty($error_message)): ?>
                                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                                <?php endif; ?>
                                <?php if (!empty($success_message)): ?>
                                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                                <?php endif; ?>

                                <!-- Last Name -->
                                <div class="form-group form-primary">
                                    <input type="text" name="lastname" id="lastname" class="form-control">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Last Name</label>
                                    <span class="field-error" id="lastname-error">Last name must contain letters only.</span>
                                </div>

                                <!-- Gender & Birthday -->
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group form-primary">
                                            <select name="gender" class="form-control">
                                                <option value="">Select Gender</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Other">Other</option>
                                            </select>
                                            <span class="form-bar"></span>
                                            <label class="float-label">Gender</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group form-primary">
                                            <input type="date" name="birthday" id="birthday" class="form-control">
                                            <span class="form-bar"></span>
                                            <label class="float-label">Birthday</label>
                                            <span class="field-error" id="birthday-error">You must be at least 15 years old.</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Physical Measurements -->
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group form-primary">
                                            <input type="number" name="height" id="height" class="form-control" placeholder="cm">
                                            <span class="form-bar"></span>
                                            <label class="float-label">Height (cm)</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group form-primary">
                                            <input type="number" name="weight" id="weight" class="form-control" placeholder="kg">
                                            <span class="form-bar"></span>
                                            <label class="float-label">Weight (kg)</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group form-primary">
                                            <input type="number" name="bmi" id="bmi" class="form-control" placeholder="Auto-calculated" readonly style="background:#f5f5f5; cursor:not-allowed;">
                                            <span class="form-bar"></span>
                                            <label class="float-label">BMI (auto)</label>
                                            <span id="bmi-label" style="font-size:11px; margin-top:3px; display:none;"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Activity Level -->
                                <div class="form-group form-primary">
                                    <input type="text" name="activity" class="form-control" placeholder="e.g., Sedentary, Moderate, Active">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Activity Level</label>
                                </div>

                                <!-- Health Info -->
                                <div class="form-group form-primary">
                                    <input type="text" name="illness" class="form-control" placeholder="e.g., Diabetes, Hypertension">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Existing Illnesses</label>
                                </div>

                                <div class="form-group form-primary">
                                    <input type="text" name="allergie" class="form-control" placeholder="e.g., Peanuts, Shellfish">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Allergies</label>
                                </div>

                                <div class="form-group form-primary">
                                    <input type="text" name="medicament" class="form-control" placeholder="e.g., Aspirin, Metformin">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Medications</label>
                                </div>

                                <div class="row m-t-30">
                                    <div class="col-md-12">
                                        <button type="submit" name="survey_submit" class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20">Complete Survey</button>
                                    </div>
                                </div>

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
        if (field)  { field.classList.add('invalid'); field.classList.remove('valid'); }
        if (error)  { error.style.display = 'block'; }
        return false;
    }
    function showValid(fieldId, errorId) {
        const field = document.getElementById(fieldId);
        const error = document.getElementById(errorId);
        if (field)  { field.classList.remove('invalid'); field.classList.add('valid'); }
        if (error)  { error.style.display = 'none'; }
        return true;
    }

    document.getElementById('lastname').addEventListener('input', function () {

        this.value = this.value.replace(/[^a-zA-ZÃ€-Ã¿\s\-]/g, '');
    });

    (function () {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        today.setFullYear(today.getFullYear() - 15);
        document.getElementById('birthday').setAttribute('max', today.toISOString().split('T')[0]);
    })();

    document.getElementById('lastname').addEventListener('blur', validateLastname);
    document.getElementById('birthday').addEventListener('blur', validateBirthday);

    function validateLastname() {
        const val = document.getElementById('lastname').value.trim();
        return /^[a-zA-ZÃ€-Ã¿\s\-]+$/.test(val) && val.length > 0
            ? showValid('lastname', 'lastname-error')
            : showError('lastname', 'lastname-error');
    }

    function validateBirthday() {
        const val = document.getElementById('birthday').value;
        if (!val) return showError('birthday', 'birthday-error');
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        today.setFullYear(today.getFullYear() - 15);
        const chosen = new Date(val);
        return chosen < today
            ? showValid('birthday', 'birthday-error')
            : showError('birthday', 'birthday-error');
    }

    function validateHeight() {
        const value = parseFloat(document.getElementById('height').value);
        if (!value) {
            return true;
        }
        return value >= 50 && value <= 250;
    }

    function validateWeight() {
        const value = parseFloat(document.getElementById('weight').value);
        if (!value) {
            return true;
        }
        return value >= 10 && value <= 300;
    }

    function calcBMI() {
        const height = parseFloat(document.getElementById('height').value);
        const weight = parseFloat(document.getElementById('weight').value);
        const bmiField = document.getElementById('bmi');
        const bmiLabel = document.getElementById('bmi-label');

        if (height > 0 && weight > 0) {
            const heightM = height / 100;
            const bmi = (weight / (heightM * heightM)).toFixed(1);
            bmiField.value = bmi;

            let category = '';
            let color = '';
            if      (bmi < 18.5) { category = 'Underweight'; color = '#3498db'; }
            else if (bmi < 25)   { category = 'Normal weight'; color = '#2ecc71'; }
            else if (bmi < 30)   { category = 'Overweight'; color = '#f39c12'; }
            else                 { category = 'Obese'; color = '#e74c3c'; }

            bmiLabel.textContent = 'BMI ' + bmi + ' — ' + category;
            bmiLabel.style.color = color;
            bmiLabel.style.display = 'block';
        } else {
            bmiField.value = '';
            bmiLabel.style.display = 'none';
        }
    }

    document.getElementById('height').addEventListener('input', calcBMI);
    document.getElementById('weight').addEventListener('input', calcBMI);

    document.getElementById('surveyForm').addEventListener('submit', function (e) {
        const lastnameOk = validateLastname();
        const birthdayOk = validateBirthday();
        const heightOk = validateHeight();
        const weightOk = validateWeight();
        if (!lastnameOk || !birthdayOk || !heightOk || !weightOk) {
            e.preventDefault();
            if (!heightOk || !weightOk) {
                alert('Height must be between 50 and 250 cm and weight between 10 and 300 kg.');
            }
        }
    });
    </script>
</body>
</html>
