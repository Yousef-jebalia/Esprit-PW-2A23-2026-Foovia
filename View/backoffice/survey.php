<?php
session_start();
include(__DIR__ . '/../../controller/Controller_user.php');

// Check if user has just signed up
if (!isset($_SESSION['signup_email'])) {
    header('Location: auth-sign-up.php');
    exit;
}

$email = $_SESSION['signup_email'];
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['survey_submit'])) {
    try {
        // Get the user by email
        $controller = new Controller_user();
        $db = config::getConnexion();
        
        $sql = "SELECT id_user, name_user, password_user, phone_user FROM user WHERE email_user = :email";
        $query = $db->prepare($sql);
        $query->execute(['email' => $email]);
        
        $result = $query->fetch();
        
        if (!$result) {
            $error_message = 'User not found.';

        } else {
            $user_id = $result['id_user'];
            $user_pw = $result['password_user'];
            $user_phone = $result['phone_user'];

            
            // Create User object with updated information
            $user = new User(
                $user_id,
                $result['name_user'],
                $_POST['lastname'] ?? $result['name_user'],
                $email,
                $user_pw, // password remains unchanged
                $user_phone, // phone remains unchanged
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
                'user'
            );
            
            // Update user in database
            $controller->update_user($user, $user_id);
            
            $success_message = 'Survey completed successfully! Redirecting to login...';
            
            // Clear session and redirect to login
            unset($_SESSION['signup_email']);
            header('refresh:2;url=../frontoffice/login.php');
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
</head>

<body themebg-pattern="theme1">
    <section class="login-block">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <form class="md-float-material form-material" action="" method="POST">
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

                                <!-- Last Name -->
                                <div class="form-group form-primary">
                                    <input type="text" name="lastname" class="form-control">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Last Name</label>
                                </div>

                                <!-- Gender & Birthday in rows -->
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
                                            <input type="date" name="birthday" class="form-control">
                                            <span class="form-bar"></span>
                                            <label class="float-label">Birthday</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Physical Measurements -->
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group form-primary">
                                            <input type="number" name="height" class="form-control" placeholder="cm">
                                            <span class="form-bar"></span>
                                            <label class="float-label">Height (cm)</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group form-primary">
                                            <input type="number" name="weight" class="form-control" placeholder="kg">
                                            <span class="form-bar"></span>
                                            <label class="float-label">Weight (kg)</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group form-primary">
                                            <input type="number" name="bmi" class="form-control" placeholder="kg/m²">
                                            <span class="form-bar"></span>
                                            <label class="float-label">BMI</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Activity Level -->
                                <div class="form-group form-primary">
                                    <input type="text" name="activity" class="form-control" placeholder="e.g., Sedentary, Moderate, Active">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Activity Level</label>
                                </div>

                                <!-- Health Information -->
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

    <script type="text/javascript" src="assets/js/jquery/jquery.min.js "></script>
    <script type="text/javascript" src="assets/js/jquery-ui/jquery-ui.min.js "></script>
    <script type="text/javascript" src="assets/js/popper.js/popper.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js "></script>
    <script src="assets/pages/waves/js/waves.min.js"></script>
    <script type="text/javascript" src="assets/js/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script type="text/javascript" src="assets/js/common-pages.js"></script>
</body>

</html>
