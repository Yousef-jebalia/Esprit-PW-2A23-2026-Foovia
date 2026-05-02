<?php
session_start();
require_once '../../controller/tracking/ObjectifLongTerme_Controller.php';
require_once '../../controller/tracking/ObjectifHebdomadaire_Controller.php';

$longTermController = new ObjectifLongTerme_Controller();
$weeklyController = new ObjectifHebdomadaire_Controller();

$currentUserId = (int) ($_SESSION['user_id'] ?? 1);

$allGoals = $longTermController->list_objectifs();
$currentUserGoal = null;
foreach ($allGoals as $goal) {
    if ((int) ($goal['id_user'] ?? 0) === $currentUserId) {
        $currentUserGoal = $goal;
        break;
    }
}

$weeklyRows = $weeklyController->list_objectifs_by_user($currentUserId);

function h($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Material Able bootstrap admin template by Codedthemes</title>
    <!-- HTML5 Shim and Respond.js IE10 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 10]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
      <![endif]-->
    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <meta name="keywords" content="bootstrap, bootstrap admin template, admin theme, admin dashboard, dashboard template, admin template, responsive" />
    <meta name="author" content="Codedthemes" />
    <!-- Favicon icon -->
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">
    <!-- waves.css -->
    <link rel="stylesheet" href="assets/pages/waves/css/waves.min.css" type="text/css" media="all">
    <!-- Required Fremwork -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">
    <!-- waves.css -->
    <link rel="stylesheet" href="assets/pages/waves/css/waves.min.css" type="text/css" media="all">
    <link rel="stylesheet" type="text/css" href="assets/icon/themify-icons/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome-n.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/jquery.mCustomScrollbar.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <style>
        .table thead th {
            white-space: nowrap;
        }
        .tracking-empty {
            color: #666;
            font-weight: 600;
        }
    </style>
</head>
<body>
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

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <nav class="navbar header-navbar pcoded-header">
                <div class="navbar-wrapper">
                    <div class="navbar-logo">
                        <a class="mobile-menu waves-effect waves-light" id="mobile-collapse" href="#!">
                            <i class="ti-menu"></i>
                        </a>
                        <div class="mobile-search waves-effect waves-light">
                            <div class="header-search">
                                <div class="main-search morphsearch-search">
                                    <div class="input-group">
                                        <span class="input-group-prepend search-close"><i class="ti-close input-group-text"></i></span>
                                        <input type="text" class="form-control" placeholder="Enter Keyword">
                                        <span class="input-group-append search-btn"><i class="ti-search input-group-text"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a href="index.html">
                            <img class="img-fluid" src="assets/images/logo.png" alt="Theme-Logo" />
                        </a>
                        <a class="mobile-options waves-effect waves-light">
                            <i class="ti-more"></i>
                        </a>
                    </div>
                    <div class="navbar-container container-fluid">
                        <ul class="nav-left">
                            <li>
                                <div class="sidebar_toggle"><a href="javascript:void(0)"><i class="ti-menu"></i></a></div>
                            </li>
                            <li>
                                <a href="#!" onclick="javascript:toggleFullScreen()" class="waves-effect waves-light">
                                    <i class="ti-fullscreen"></i>
                                </a>
                            </li>
                        </ul>
                        <ul class="nav-right">
                            <li>
                                <a href="../front_office/foovia.html" class="nav-btn nav-frontoffice waves-effect waves-light" title="Open Front Office">
                                    <i class="fa fa-home" aria-hidden="true"></i>&nbsp;Front Office
                                </a>
                            </li>
                            <li class="header-notification">
                                <a href="#!" class="waves-effect waves-light">
                                    <i class="ti-bell"></i>
                                    <span class="badge bg-c-red"></span>
                                </a>
                            </li>
                            <li class="user-profile header-notification">
                                <a href="#!" class="waves-effect waves-light">
                                    <img src="assets/images/avatar-4.jpg" class="img-radius" alt="User-Profile-Image">
                                    <span>John Doe</span>
                                    <i class="ti-angle-down"></i>
                                </a>
                                <ul class="show-notification profile-notification">
                                    <li class="waves-effect waves-light"><a href="#"><i class="ti-settings"></i> Settings</a></li>
                                    <li class="waves-effect waves-light"><a href="user-profile.html"><i class="ti-user"></i> Profile</a></li>
                                    <li class="waves-effect waves-light"><a href="auth-normal-sign-in.html"><i class="ti-layout-sidebar-left"></i> Logout</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <nav class="pcoded-navbar">
                        <div class="sidebar_toggle"><a href="#"><i class="icon-close icons"></i></a></div>
                        <div class="pcoded-inner-navbar main-menu">
                            <div class="">
                                <div class="main-menu-header">
                                    <img class="img-80 img-radius" src="assets/images/avatar-4.jpg" alt="User-Profile-Image">
                                    <div class="user-details">
                                        <span id="more-details">John Doe<i class="fa fa-caret-down"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="pcoded-navigation-label">Navigation</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="">
                                    <a href="index.html" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-home"></i><b>D</b></span>
                                        <span class="pcoded-mtext">Dashboard</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                                <li class="active">
                                    <a href="tracking.php" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-target"></i><b>T</b></span>
                                        <span class="pcoded-mtext">Tracking</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </nav>

                    <div class="pcoded-content">
                        <div class="page-header">
                            <div class="page-block">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="page-header-title">
                                            <h5 class="m-b-10">Tracking</h5>
                                            <p class="m-b-0">Long-term goal and weekly tracking details</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <ul class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="index.html"><i class="fa fa-home"></i></a></li>
                                            <li class="breadcrumb-item"><a href="#">Tracking</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pcoded-inner-content">
                            <div class="main-body">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-xl-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>User Information</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <h6 class="m-b-0">User ID: <strong><?php echo h($currentUserId); ?></strong></h6>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-12">
                                                <div class="card table-card">
                                                    <div class="card-header">
                                                        <h5>Long-Term Goal Details</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <?php if ($currentUserGoal === null): ?>
                                                            <p class="tracking-empty m-b-0">No long-term goal found for this user.</p>
                                                        <?php else: ?>
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered table-striped mb-0">
                                                                    <tbody>
                                                                        <tr><th>ID Goal</th><td><?php echo h($currentUserGoal['id_obj'] ?? ''); ?></td></tr>
                                                                        <tr><th>ID User</th><td><?php echo h($currentUserGoal['id_user'] ?? ''); ?></td></tr>
                                                                        <tr><th>Type</th><td><?php echo h($currentUserGoal['type_obj'] ?? ''); ?></td></tr>
                                                                        <tr><th>Initial Value</th><td><?php echo h($currentUserGoal['val_init_obj'] ?? ''); ?></td></tr>
                                                                        <tr><th>Target Value</th><td><?php echo h($currentUserGoal['val_cible_obj'] ?? ''); ?></td></tr>
                                                                        <tr><th>Start Date</th><td><?php echo h($currentUserGoal['date_deb_obj'] ?? ''); ?></td></tr>
                                                                        <tr><th>End Date</th><td><?php echo h($currentUserGoal['date_fin_obj'] ?? ''); ?></td></tr>
                                                                        <tr><th>Status</th><td><?php echo h($currentUserGoal['status_obj'] ?? ''); ?></td></tr>
                                                                        <tr><th>Reminder Frequency</th><td><?php echo h($currentUserGoal['frequency_rappel_obj'] ?? ''); ?></td></tr>
                                                                        <tr><th>Sport Consistency</th><td><?php echo h($currentUserGoal['consistancy_sport_obj'] ?? ''); ?></td></tr>
                                                                        <tr><th>Diet Consistency</th><td><?php echo h($currentUserGoal['consistency_alim_obj'] ?? ''); ?></td></tr>
                                                                        <tr><th>Calories Target</th><td><?php echo h($currentUserGoal['obj_cal_obj'] ?? ''); ?></td></tr>
                                                                        <tr><th>Fat Target</th><td><?php echo h($currentUserGoal['obj_fat_obj'] ?? ''); ?></td></tr>
                                                                        <tr><th>Protein Target</th><td><?php echo h($currentUserGoal['obj_prot_obj'] ?? ''); ?></td></tr>
                                                                        <tr><th>Carbs Target</th><td><?php echo h($currentUserGoal['obj_carb_obj'] ?? ''); ?></td></tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-12">
                                                <div class="card table-card">
                                                    <div class="card-header">
                                                        <h5>Weekly Tracking Entries</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <?php if (empty($weeklyRows)): ?>
                                                            <p class="tracking-empty m-b-0">No weekly tracking entries found for this user.</p>
                                                        <?php else: ?>
                                                            <div class="table-responsive">
                                                                <table class="table table-hover table-bordered mb-0">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>ID Suiv</th>
                                                                            <th>ID Goal</th>
                                                                            <th>Date</th>
                                                                            <th>Weight</th>
                                                                            <th>Calories</th>
                                                                            <th>Fat</th>
                                                                            <th>Protein</th>
                                                                            <th>Carbs</th>
                                                                            <th>Status</th>
                                                                            <th>Water Glasses</th>
                                                                            <th>Sleep Hours</th>
                                                                            <th>Steps</th>
                                                                            <th>Note</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($weeklyRows as $row): ?>
                                                                            <tr>
                                                                                <td><?php echo h($row['id_suiv'] ?? ''); ?></td>
                                                                                <td><?php echo h($row['id_obj'] ?? ''); ?></td>
                                                                                <td><?php echo h($row['date_suiv'] ?? ''); ?></td>
                                                                                <td><?php echo h($row['poids_suiv'] ?? ''); ?></td>
                                                                                <td><?php echo h($row['val_cal_suiv'] ?? ''); ?></td>
                                                                                <td><?php echo h($row['val_fat_suiv'] ?? ''); ?></td>
                                                                                <td><?php echo h($row['val_prot_suiv'] ?? ''); ?></td>
                                                                                <td><?php echo h($row['val_carb_suiv'] ?? ''); ?></td>
                                                                                <td><?php echo h($row['status_obj_quot_suiv'] ?? ''); ?></td>
                                                                                <td><?php echo h($row['nb_verre_eau_suiv'] ?? ''); ?></td>
                                                                                <td><?php echo h($row['nb_h_sommeil_suiv'] ?? ''); ?></td>
                                                                                <td><?php echo h($row['nb_pas_suiv'] ?? ''); ?></td>
                                                                                <td><?php echo h($row['note_suiv'] ?? ''); ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="styleSelector"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="assets/js/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="assets/js/jquery-ui/jquery-ui.min.js"></script>
    <script type="text/javascript" src="assets/js/popper.js/popper.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/pages/waves/js/waves.min.js"></script>
    <script type="text/javascript" src="assets/js/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script src="assets/js/vertical/vertical-layout.min.js"></script>
    <script type="text/javascript" src="assets/js/script.js"></script>
</body>
</html>
