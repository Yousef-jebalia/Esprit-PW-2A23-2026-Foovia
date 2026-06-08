<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../../../Controller/SUPPORT_MODULE/Traitemant_Controller.php';
include __DIR__ . '/../../../Controller/SUPPORT_MODULE/Reclamtion_Controller.php';
include __DIR__ . '/../../../Controller/SUPPORT_MODULE/ReclamationMessage_Controller.php';

$error = '';
$success = '';
$controller = new Controller_traitement();
$reclamationController = new Controller_reclamation();
$claimMessageController = new ReclamationMessage_Controller();
$traitements = [];
$reclamations = [];
$admin_user_id = (int) ($_SESSION['user_id'] ?? 0);
$active_claim_id = (int) ($_GET['open_claim'] ?? ($_POST['open_claim_id'] ?? 0));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'reply_claim_admin') {
        $claimId = (int) ($_POST['open_claim_id'] ?? 0);
        $body = trim((string) ($_POST['reply_body'] ?? ''));

        if ($claimId <= 0) {
            $error = 'Invalid claim selected.';
        } elseif ($body === '') {
            $error = 'Reply cannot be empty.';
        } elseif ($admin_user_id <= 0) {
            $error = 'You must be signed in as an admin to reply.';
        } else {
            $claim = $reclamationController->get_reclamation_by_id($claimId);
            if (!$claim) {
                $error = 'Claim not found.';
            } else {
                try {
                    $message = new ReclamationMessage(0, $claimId, $admin_user_id, $body, '');
                    $claimMessageController->add_message($message);
                    header('Location: Support_admin.php?open_claim=' . $claimId . '&posted_claim=1');
                    exit;
                } catch (Exception $e) {
                    $error = 'Could not post your reply. Please try again.';
                }
            }
        }
    } elseif ($_POST['action'] === 'delete_traitement' && !empty($_POST['id_traitement'])) {
        $deleteTraitement = new Traitements(
            (int)$_POST['id_traitement'],
            0,
            '',
            '',
            '',
            ''
        );
        $controller->suppression_traitement($deleteTraitement);
        $success = 'Treatment deleted successfully.';
    } elseif ($_POST['action'] === 'delete_reclamation' && !empty($_POST['id_reclamation'])) {
        $deleteReclamation = new Reclamations(
            $_POST['id_reclamation'],
            0,
            '',
            '',
            '',
            '',
            ''
        );
        $reclamationController->suppression_reclamation($deleteReclamation);
        $success = 'Claim deleted successfully.';
    }
}

$traitements = $controller->get_traitements();
$reclamations = $reclamationController->get_reclamations();

if (isset($_GET['posted_claim'])) {
    $success = 'Your reply was posted!';
}

$claim_conversations = [];
foreach ($reclamations as $reclamation) {
    $claimId = (int) ($reclamation['id_reclam'] ?? 0);
    if ($claimId <= 0) {
        continue;
    }
    $storedMessages = $claimMessageController->get_messages($claimId);
    $claim_conversations[] = [
        'id' => (string) $claimId,
        'ownerUserId' => (int) ($reclamation['id_user'] ?? 0),
        'subject' => trim((string) ($reclamation['subject'] ?? '')),
        'description' => trim((string) ($reclamation['description_reclam'] ?? '')),
        'status' => trim((string) ($reclamation['etat_reclam'] ?? '')),
        'type' => trim((string) ($reclamation['type_reclam'] ?? '')),
        'openingDate' => trim((string) ($reclamation['dateouvert_reclam'] ?? '')),
        'replyCount' => count($storedMessages),
        'messages' => $claimMessageController->build_timeline($reclamation, $storedMessages, 'Client'),
    ];
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

    <meta name="keywords" content="flat ui, admin Admin , Responsive, Landing, Bootstrap, App, Template, Mobile, iOS, Android, apple, creative app">
    <meta name="author" content="Codedthemes" />
    <!-- Favicon icon -->
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
    <!-- Required Fremwork -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">
    <!-- waves.css -->
    <link rel="stylesheet" href="assets/pages/waves/css/waves.min.css" type="text/css" media="all">
    <!-- themify-icons line icon -->
    <link rel="stylesheet" type="text/css" href="assets/icon/themify-icons/themify-icons.css">
    <!-- feather icon -->
    <link rel="stylesheet" type="text/css" href="assets/icon/feather/css/feather.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css" href="assets/icon/font-awesome/css/font-awesome.min.css">
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="assets/css/jquery.mCustomScrollbar.css">
    <style>
        .claim-reply-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(17, 16, 8, 0.65);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .claim-reply-modal-overlay.open { display: flex; }
        .claim-reply-modal {
            background: #fff;
            border-radius: 12px;
            width: 100%;
            max-width: 720px;
            max-height: 88vh;
            overflow-y: auto;
            padding: 24px;
            box-shadow: 0 24px 72px rgba(0, 0, 0, 0.24);
        }
        .claim-reply-modal__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }
        .claim-reply-modal__title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .claim-reply-modal__meta {
            font-size: 0.85rem;
            color: #64748b;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .claim-reply-modal__close {
            background: rgba(0, 0, 0, 0.08);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1rem;
            line-height: 1;
            flex-shrink: 0;
        }
        .claim-reply-modal__section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 14px;
        }
        .claim-reply-modal__label {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #4BAE52;
            margin-bottom: 8px;
        }
        .claim-reply-modal__messages {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .claim-reply-modal__message {
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 0.92rem;
            line-height: 1.55;
        }
        .claim-reply-modal__message.client {
            background: #fff;
            border: 1px solid #e2e8f0;
        }
        .claim-reply-modal__message.support {
            background: rgba(75, 174, 82, 0.08);
            border: 1px solid rgba(75, 174, 82, 0.14);
        }
        .claim-reply-modal__message-meta {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        .claim-reply-modal__reply-box {
            width: 100%;
            min-height: 92px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            resize: vertical;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <!-- Pre-loader start -->
    <div class="theme-loader">
        <div class="loader-track">
            <div class="preloader-wrapper">
                <div class="spinner-layer spinner-blue">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="gap-patch">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
                <div class="spinner-layer spinner-red">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="gap-patch">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
                <div class="spinner-layer spinner-yellow">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="gap-patch">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
                <div class="spinner-layer spinner-green">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="gap-patch">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pre-loader end -->
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

                            </li>
                            <li class="user-profile header-notification">
                                <a href="#!" class="waves-effect waves-light">
                                    <img src="assets/images/avatar-4.jpg" class="img-radius" alt="User-Profile-Image">
                                    <span>Si ysf</span>
                                    <i class="ti-angle-down"></i>
                                </a>
                                <ul class="show-notification profile-notification">
                                    <li class="waves-effect waves-light">
                                        <a href="#!">
                                            <i class="ti-settings"></i> Settings
                                        </a>
                                    </li>
                                    <li class="waves-effect waves-light">
                                        <a href="user-profile.html">
                                            <i class="ti-user"></i> Profile
                                        </a>
                                    </li>
                                    <li class="waves-effect waves-light">
                                        <a href="email-inbox.html">
                                            <i class="ti-email"></i> My Messages
                                        </a>
                                    </li>
                                    <li class="waves-effect waves-light">
                                        <a href="auth-lock-screen.html">
                                            <i class="ti-lock"></i> Lock Screen
                                        </a>
                                    </li>
                                    <li class="waves-effect waves-light">
                                        <a href="auth-normal-sign-in.html">
                                            <i class="ti-layout-sidebar-left"></i> Logout
                                        </a>
                                    </li>
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
                                        <span id="more-details">Si ysf<i class="fa fa-caret-down"></i></span>
                                    </div>
                                </div>
                                <div class="main-menu-content">
                                    <ul>
                                        <li class="more-details">
                                            <a href="user-profile.html"><i class="ti-user"></i>View Profile</a>
                                            <a href="#!"><i class="ti-settings"></i>Settings</a>
                                            <a href="auth-normal-sign-in.html"><i class="ti-layout-sidebar-left"></i>Logout</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="p-15 p-b-0">
                                <form class="form-material">
                                    <div class="form-group form-primary">
                                        <input type="text" name="footer-email" class="form-control">
                                        <span class="form-bar"></span>
                                        <label class="float-label"><i class="fa fa-search m-r-10"></i>Search Friend</label>
                                    </div>
                                </form>
                            </div>
                            <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
          <nav class="pcoded-navbar" navbar-theme="theme1" active-item-theme="theme1">
            <div class="pcoded-inner-navbar main-menu">
              <div class="pcoded-navigation-label">Navigation</div>
               <ul class="pcoded-item pcoded-left-item">
                                <li class="pcoded-hasmenu active pcoded-trigger">
                                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                                        <span class="pcoded-mtext">MODULES</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class=" ">
                                            <a href="../SUPPORT_MODULE/support_admin.php" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">SUPPORT</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="../TRACK_MODULE/tracking.php" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">TRACKING</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="../USER_MODULE/tabs.php" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">USER</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="../SPORT_MOULE/form-elements-component.php" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">SPORT</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="../menu_module/form-elements-component.php" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">MENU</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="../menu_module/form-elements-ingrediant.php" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">INGREDIANTS</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class="pcoded-hasmenu">
                                            <a href="javascript:void(0)" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">MARKETPLACE</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                            <ul class="pcoded-submenu">
                                                <li>
                                                    <a href="../MARKETPLACE_MODULE/products.php" class="waves-effect waves-dark">
                                                        <span class="pcoded-mtext">Products</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="../MARKETPLACE_MODULE/magasins.php" class="waves-effect waves-dark">
                                                        <span class="pcoded-mtext">Magasins</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
            </div>
          </nav>

          <div class="pcoded-content">
            <div class="pcoded-inner-content">
              <div class="main-body">
                <div class="page-wrapper">
                  <div class="page-shell">
    <div class="hero">
                    </nav>

                    <div class="pcoded-content">
                        <!-- Page-header start -->
                        <div class="page-header">
                            <div class="page-block">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="page-header-title d-flex flex-wrap align-items-center gap-2 justify-content-between">
                                            <div>
                                                <h5 class="m-b-10">Support page</h5>
                                                <p class="m-b-0"></p>
                                            </div>
                                            <a href="thread_admin_page.php" class="btn btn-success btn-sm waves-effect waves-light m-b-10">
                                                <i class="ti-comments m-r-5"></i>Thread Management
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <ul class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a href="index.html"> <i class="fa fa-home"></i> </a>
                                            </li>
                                            <li class="breadcrumb-item"><a href="#!">Support page</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Page-header end -->
                        <div class="pcoded-inner-content">
                            <div class="main-body">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-sm-12">
                                            <div class="card">
                                                <div class="card-header d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <h5 class="mb-0">Treatments List</h5>
                                                        <span>Manage saved treatments</span>
                                                    </div>
                                                    <a href="add_traitement_page.php" class="btn btn-primary btn-sm">Add treatment</a>
                                                </div>
                                                <div class="card-block">
                                                    <?php if (!empty($error)): ?>
                                                        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($success)): ?>
                                                        <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success); ?></div>
                                                    <?php endif; ?>
                                                    <div class="mb-3">
                                                        <input id="traitement-search" type="text" class="form-control" placeholder="Search in the list of treatments...">
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Treatment ID</th>
                                                                    <th>Claim ID</th>
                                                                    <th>Comment</th>
                                                                    <th>Status</th>
                                                                    <th>Date</th>
                                                                    <th>User ID</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($traitements)): ?>
                                                                    <?php foreach ($traitements as $traitement): ?>
                                                                        <tr>
                                                                            <td><?php echo htmlspecialchars($traitement['id_traitement']); ?></td>
                                                                            <td><?php echo htmlspecialchars($traitement['id_reclam']); ?></td>
                                                                            <td><?php echo htmlspecialchars($traitement['comment_trait']); ?></td>
                                                                            <td><?php echo htmlspecialchars($traitement['status_trait']); ?></td>
                                                                            <td><?php echo htmlspecialchars($traitement['date__trait']); ?></td>
                                                                            <td><?php echo htmlspecialchars($traitement['id_user']); ?></td>
                                                                            <td>
                                                                                <a href="update_traitement_page.php?id=<?php echo urlencode($traitement['id_traitement']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                                                                <form method="post" style="display:inline-block; margin-left:8px;" onsubmit="return confirm('Delete this treatment?');">
                                                                                    <input type="hidden" name="action" value="delete_traitement">
                                                                                    <input type="hidden" name="id_traitement" value="<?php echo htmlspecialchars($traitement['id_traitement']); ?>">
                                                                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="7" class="text-center">No treatments found.</td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 mt-4">
                                            <div class="card">
                                                <div class="card-header d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <h5 class="mb-0">Claims List</h5>
                                                        <span>Manage saved claims</span>
                                                    </div>
                                                </div>
                                                <div class="card-block">
                                                    <div class="mb-3">
                                                        <input id="reclamation-search" type="text" class="form-control" placeholder="Search in the list of claims...">
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Claim ID</th>
                                                                    <th>User ID</th>
                                                                    <th>Description</th>
                                                                    <th>Status</th>
                                                                    <th>Type</th>
                                                                    <th>Opening Date</th>
                                                                    <th>Closing Date</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($reclamations)): ?>
                                                                    <?php foreach ($reclamations as $reclamation): ?>
                                                                        <tr>
                                                                            <td class="claim-id"><?php echo htmlspecialchars($reclamation['id_reclam']); ?></td>
                                                                            <td class="user-id"><?php echo htmlspecialchars($reclamation['id_user']); ?></td>
                                                                            <td><?php echo htmlspecialchars($reclamation['description_reclam']); ?></td>
                                                                            <td><?php echo htmlspecialchars($reclamation['etat_reclam']); ?></td>
                                                                            <td><?php echo htmlspecialchars($reclamation['type_reclam']); ?></td>
                                                                            <td><?php echo htmlspecialchars($reclamation['dateouvert_reclam']); ?></td>
                                                                            <td><?php echo htmlspecialchars($reclamation['dateferm_reclam'] ?? '-'); ?></td>
                                                                            <td>
                                                                                <a href="update_reclamation_page.php?id=<?php echo urlencode($reclamation['id_reclam']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                                                                <button type="button" class="btn btn-primary btn-sm claim-reply-btn" data-claim-id="<?php echo (int) $reclamation['id_reclam']; ?>" style="margin-left:8px;">Reply</button>
                                                                                <button type="button" class="btn btn-info btn-sm handle-treatment-btn" style="margin-left:8px;">Handle</button>
                                                                                <a href="thread_admin_page.php?id_reclam=<?php echo urlencode($reclamation['id_reclam']); ?>"
                                                                                   class="btn btn-success btn-sm" style="margin-left:8px;" title="Publish this claim as a community thread">
                                                                                   Publish Thread
                                                                                </a>
                                                                                <form method="post" style="display:inline-block; margin-left:8px;" onsubmit="return confirm('Delete this claim?');">
                                                                                    <input type="hidden" name="action" value="delete_reclamation">
                                                                                    <input type="hidden" name="id_reclamation" value="<?php echo htmlspecialchars($reclamation['id_reclam']); ?>">
                                                                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="8" class="text-center">No claims found.</td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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

    <div class="claim-reply-modal-overlay" id="claim-reply-modal" onclick="if(event.target===this) closeClaimReplyModal();">
        <div class="claim-reply-modal" role="dialog" aria-modal="true" aria-labelledby="claim-reply-modal-title">
            <div class="claim-reply-modal__head">
                <div>
                    <div class="claim-reply-modal__title" id="claim-reply-modal-title">Claim #0000</div>
                    <div id="claim-reply-modal-meta" class="claim-reply-modal__meta"></div>
                </div>
                <button class="claim-reply-modal__close" type="button" aria-label="Close conversation" onclick="closeClaimReplyModal()">✕</button>
            </div>
            <div class="claim-reply-modal__section">
                <div class="claim-reply-modal__label">Subject</div>
                <div id="claim-reply-modal-subject"></div>
            </div>
            <div class="claim-reply-modal__section">
                <div class="claim-reply-modal__label">Conversation</div>
                <div class="claim-reply-modal__messages" id="claim-reply-modal-messages"></div>
            </div>
            <div class="claim-reply-modal__section">
                <div class="claim-reply-modal__label">Reply as support</div>
                <form id="claim-reply-modal-form">
                    <input type="hidden" id="claim-reply-modal-claim-id" value="0">
                    <textarea class="claim-reply-modal__reply-box" id="claim-reply-modal-reply-box" rows="4" placeholder="Write a reply to the client…" required maxlength="5000"></textarea>
                    <button type="submit" class="btn btn-success btn-sm">Send reply</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Required Jquery -->
    <script type="text/javascript" src="assets/js/jquery/jquery.min.js "></script>
    <script type="text/javascript" src="assets/js/jquery-ui/jquery-ui.min.js "></script>
    <script type="text/javascript" src="assets/js/popper.js/popper.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js "></script>
    <!-- waves js -->
    <script src="assets/pages/waves/js/waves.min.js"></script>
    <!-- jquery slimscroll js -->
    <script type="text/javascript" src="assets/js/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script src="assets/js/vertical/vertical-layout.min.js"></script>
    <script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <!-- Custom js -->
    <script type="text/javascript" src="assets/js/script.min.js"></script>
    <script>
        const CLAIM_CONVERSATIONS = <?php echo json_encode($claim_conversations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const ACTIVE_CLAIM_ID = <?php echo (int) $active_claim_id; ?>;
        const CLAIM_CONVERSATION_API = '../../../Controller/SUPPORT_MODULE/claim-conversation-api.php';
        const CLAIM_POLL_INTERVAL_MS = 5000;

        var claimPollTimer = null;
        var activeClaimConversationId = 0;
        var activeClaimOwnerUserId = 0;
        var lastClaimMessageId = 0;

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function getClaimConversationById(claimId) {
            return CLAIM_CONVERSATIONS.find(function (claim) {
                return Number(claim.id) === Number(claimId);
            }) || null;
        }

        function getMaxClaimMessageId(messages) {
            var maxId = 0;
            (messages || []).forEach(function (message) {
                var id = Number(message.id || 0);
                if (id > maxId) maxId = id;
            });
            return maxId;
        }

        function adminClaimMessageHtml(message, ownerUserId) {
            var isClient = Number(message.idUser || 0) === Number(ownerUserId || 0);
            var author = message.authorName || (isClient ? 'Client' : 'Support');
            var time = message.sentLabel || message.sentAt || 'Just now';
            var body = escapeHtml(message.body || '');
            var msgId = Number(message.id || 0);
            return '<div class="claim-reply-modal__message ' + (isClient ? 'client' : 'support') + '" data-message-id="' + msgId + '">' +
                '<div class="claim-reply-modal__message-meta">' + escapeHtml(author) + ' · ' + escapeHtml(time) + '</div>' +
                '<div>' + body + '</div>' +
            '</div>';
        }

        function scrollAdminClaimMessagesToBottom() {
            var box = document.getElementById('claim-reply-modal-messages');
            if (box) box.scrollTop = box.scrollHeight;
        }

        function appendAdminClaimMessages(messages, ownerUserId) {
            var box = document.getElementById('claim-reply-modal-messages');
            if (!box || !messages || !messages.length) return;

            var empty = box.querySelector('.text-muted');
            if (empty && empty.textContent.indexOf('No messages') !== -1) empty.remove();

            messages.forEach(function (message) {
                var msgId = Number(message.id || 0);
                if (msgId > 0 && box.querySelector('[data-message-id="' + msgId + '"]')) return;
                box.insertAdjacentHTML('beforeend', adminClaimMessageHtml(message, ownerUserId));
                if (msgId > lastClaimMessageId) lastClaimMessageId = msgId;
            });
            scrollAdminClaimMessagesToBottom();
        }

        function stopClaimPolling() {
            if (claimPollTimer) {
                clearInterval(claimPollTimer);
                claimPollTimer = null;
            }
            activeClaimConversationId = 0;
        }

        function pollClaimMessages() {
            if (activeClaimConversationId <= 0) return;
            fetch(CLAIM_CONVERSATION_API + '?claim_id=' + activeClaimConversationId + '&after_id=' + lastClaimMessageId, {
                credentials: 'same-origin'
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.messages && data.messages.length) {
                        appendAdminClaimMessages(data.messages, activeClaimOwnerUserId);
                    }
                })
                .catch(function () {});
        }

        function startClaimPolling(claimId, ownerUserId, messages) {
            stopClaimPolling();
            activeClaimConversationId = Number(claimId || 0);
            activeClaimOwnerUserId = Number(ownerUserId || 0);
            lastClaimMessageId = getMaxClaimMessageId(messages);
            claimPollTimer = setInterval(pollClaimMessages, CLAIM_POLL_INTERVAL_MS);
        }

        function renderAdminClaimMessages(messages, ownerUserId) {
            if (!messages || !messages.length) {
                return '<div class="text-muted">No messages yet.</div>';
            }

            return messages.map(function (message) {
                return adminClaimMessageHtml(message, ownerUserId);
            }).join('');
        }

        function openClaimReplyModal(claimId) {
            var claim = getClaimConversationById(claimId);
            if (!claim) {
                alert('Unable to load this claim conversation.');
                return;
            }

            document.getElementById('claim-reply-modal-title').textContent = 'Claim #' + claim.id;
            document.getElementById('claim-reply-modal-meta').innerHTML =
                '<span>Status: ' + escapeHtml(claim.status || '-') + '</span>' +
                '<span>Type: ' + escapeHtml(claim.type || '-') + '</span>' +
                '<span>Opened: ' + escapeHtml(claim.openingDate || '-') + '</span>' +
                '<span>Replies: ' + escapeHtml(String(claim.replyCount || 0)) + '</span>';
            document.getElementById('claim-reply-modal-subject').textContent = claim.subject || 'Untitled claim';
            document.getElementById('claim-reply-modal-messages').innerHTML = renderAdminClaimMessages(claim.messages || [], claim.ownerUserId);
            document.getElementById('claim-reply-modal-claim-id').value = String(claim.id || '0');

            var replyBox = document.getElementById('claim-reply-modal-reply-box');
            if (replyBox) replyBox.value = '';

            document.getElementById('claim-reply-modal').classList.add('open');
            startClaimPolling(claim.id, claim.ownerUserId, claim.messages || []);
        }

        function closeClaimReplyModal() {
            stopClaimPolling();
            document.getElementById('claim-reply-modal').classList.remove('open');
        }

        $(document).ready(function() {
            function attachSearch(inputSelector) {
                $(inputSelector).on('keyup', function() {
                    var query = $(this).val().toLowerCase();
                    var table = $(this).closest('.card-block').find('table').first();
                    table.find('tbody tr').each(function() {
                        var rowText = $(this).text().toLowerCase();
                        $(this).toggle(rowText.indexOf(query) !== -1);
                    });
                });
            }

            attachSearch('#traitement-search');
            attachSearch('#reclamation-search');

            $(document).on('click', '.handle-treatment-btn', function() {
                var row = $(this).closest('tr');
                var claimId = row.find('.claim-id').text().trim();
                var userId = row.find('.user-id').text().trim();
                if (!claimId || !userId) {
                    alert('Unable to determine claim or user ID from this row.');
                    return;
                }
                window.location.href = 'add_traitement_page.php?id_reclam=' + encodeURIComponent(claimId) + '&id_user=' + encodeURIComponent(userId);
            });

            $(document).on('click', '.claim-reply-btn', function() {
                var claimId = Number($(this).data('claim-id') || 0);
                if (claimId > 0) {
                    openClaimReplyModal(claimId);
                }
            });

            if (ACTIVE_CLAIM_ID > 0) {
                openClaimReplyModal(ACTIVE_CLAIM_ID);
            }

            var claimReplyForm = document.getElementById('claim-reply-modal-form');
            if (claimReplyForm) {
                claimReplyForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    var claimId = Number(document.getElementById('claim-reply-modal-claim-id').value || 0);
                    var replyBox = document.getElementById('claim-reply-modal-reply-box');
                    var body = replyBox ? replyBox.value.trim() : '';
                    if (claimId <= 0 || body === '') return;

                    fetch(CLAIM_CONVERSATION_API, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ claim_id: claimId, body: body }),
                        credentials: 'same-origin'
                    })
                        .then(function (res) { return res.json(); })
                        .then(function (data) {
                            if (data.error) {
                                alert(data.error);
                                return;
                            }
                            if (data.message) {
                                appendAdminClaimMessages([data.message], activeClaimOwnerUserId);
                            }
                            if (replyBox) replyBox.value = '';
                        })
                        .catch(function () {
                            alert('Could not send reply. Please try again.');
                        });
                });
            }
        });
    </script>
</body>

</html>
