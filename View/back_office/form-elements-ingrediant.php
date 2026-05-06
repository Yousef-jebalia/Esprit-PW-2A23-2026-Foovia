<?php
include '../../Controller/menu_module/controle_ingrediant.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["delete_id_ing"]) && !empty($_POST["delete_id_ing"])) {
        $controller = new Controller_ingrediant();
        $controller->delete_ingrediant((int)$_POST["delete_id_ing"]);
        $success = "Ingrediant deleted successfully.";
    } elseif (
        isset($_POST["name_ing"]) && isset($_POST["prot_ing"]) &&
        isset($_POST["fat_ing"]) && isset($_POST["carb_ing"]) && isset($_POST["cal_ing"])
    ) {
        if (
            !empty($_POST["name_ing"]) && !empty($_POST["prot_ing"]) &&
            !empty($_POST["fat_ing"]) && !empty($_POST["carb_ing"]) && !empty($_POST["cal_ing"])
        ) {
            $imagePath = "";

            // Image is required when adding a new ingredient.
            if (!isset($_FILES['img_ing']) || $_FILES['img_ing']['error'] === UPLOAD_ERR_NO_FILE) {
                $error = "Image is required.";
            } elseif ($_FILES['img_ing']['error'] !== UPLOAD_ERR_OK) {
                $error = "Upload error code: " . (int)$_FILES['img_ing']['error'];
            } else {
                $absoluteUploadDir = __DIR__ . '/assets/images/ingredients/';
                $relativeUploadDir = 'assets/images/ingredients/';

                if (!is_dir($absoluteUploadDir)) {
                    mkdir($absoluteUploadDir, 0755, true);
                }

                $fileName = basename($_FILES['img_ing']['name']);
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $tmpPath = $_FILES['img_ing']['tmp_name'];
                $mimeType = mime_content_type($tmpPath);

                if (!in_array($fileExt, $allowedExts) || !in_array($mimeType, $allowedMimes)) {
                    $error = "Invalid image file format. Only JPG, PNG, GIF, WebP allowed.";
                } else {
                    $newFileName = uniqid('ing_', true) . '.' . $fileExt;
                    $absoluteUploadPath = $absoluteUploadDir . $newFileName;

                    if (move_uploaded_file($tmpPath, $absoluteUploadPath)) {
                        $imagePath = $relativeUploadDir . $newFileName;
                    } else {
                        $error = "Failed to upload image file.";
                    }
                }
            }
            
            if (empty($error)) {
                $ingrediant = new Ingrediant(
                    0,
                    $_POST['name_ing'],
                    (float)$_POST['prot_ing'],
                    $_POST['fat_ing'],
                    $_POST['carb_ing'],
                    $_POST['cal_ing'],
                    $imagePath
                );
                $controller = new Controller_ingrediant();
                if ($controller->add_ingrediant($ingrediant)) {
                    $success = "Ingrediant added successfully.";
                } else {
                    $error = "Failed to add ingrediant: " . $controller->getLastError();
                }
            }
        } else {
            $error = "Name, protein, fat, carbs and calories are required.";
        }
    } else {
        $error = "Missing form data.";
    }
}

$controller = new Controller_ingrediant();
$allIngrediants = $controller->list_ingrediants();

// Pagination for ingredients
$itemsPerPage = 10;
$currentPage = isset($_GET['ing_page']) ? max(1, (int)$_GET['ing_page']) : 1;
$totalIngrediants = count($allIngrediants);
$totalPages = ceil($totalIngrediants / $itemsPerPage);
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $itemsPerPage;
$ingrediants = array_slice($allIngrediants, $offset, $itemsPerPage);
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
    <!-- themify-icons line icon -->
    <link rel="stylesheet" type="text/css" href="assets/icon/themify-icons/themify-icons.css">
    <!-- ico font -->
    <link rel="stylesheet" type="text/css" href="assets/icon/icofont/css/icofont.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css" href="assets/icon/font-awesome/css/font-awesome.min.css">
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="assets/css/jquery.mCustomScrollbar.css">
</head>
<body>
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
                            <li class="header-notification">
                                <a href="#!" class="waves-effect waves-light">
                                    <i class="ti-bell"></i>
                                    <span class="badge bg-c-red"></span>
                                </a>
                                <ul class="show-notification">
                                    <li>
                                        <h6>Notifications</h6>
                                        <label class="label label-danger">New</label>
                                    </li>
                                    <li class="waves-effect waves-light">
                                        <div class="media">
                                            <img class="d-flex align-self-center img-radius" src="assets/images/avatar-2.jpg" alt="Generic placeholder image">
                                            <div class="media-body">
                                                <h5 class="notification-user">John Doe</h5>
                                                <p class="notification-msg">Lorem ipsum dolor sit amet, consectetuer elit.</p>
                                                <span class="notification-time">30 minutes ago</span>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="waves-effect waves-light">
                                        <div class="media">
                                            <img class="d-flex align-self-center img-radius" src="assets/images/avatar-4.jpg" alt="Generic placeholder image">
                                            <div class="media-body">
                                                <h5 class="notification-user">Joseph William</h5>
                                                <p class="notification-msg">Lorem ipsum dolor sit amet, consectetuer elit.</p>
                                                <span class="notification-time">30 minutes ago</span>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="waves-effect waves-light">
                                        <div class="media">
                                            <img class="d-flex align-self-center img-radius" src="assets/images/avatar-3.jpg" alt="Generic placeholder image">
                                            <div class="media-body">
                                                <h5 class="notification-user">Sara Soudein</h5>
                                                <p class="notification-msg">Lorem ipsum dolor sit amet, consectetuer elit.</p>
                                                <span class="notification-time">30 minutes ago</span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <li class="user-profile header-notification">
                                <a href="#!" class="waves-effect waves-light">
                                    <img src="assets/images/avatar-4.jpg" class="img-radius" alt="User-Profile-Image">
                                    <span>John Doe</span>
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
                            <div class="main-menu-header">
                                <img class="img-80 img-radius" src="assets/images/avatar-4.jpg" alt="User-Profile-Image">
                                <div class="user-details">
                                    <span id="more-details">John Doe<i class="fa fa-caret-down"></i></span>
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
                            <div class="p-15 p-b-0">
                                <form class="form-material">
                                    <div class="form-group form-primary">
                                        <input type="text" name="footer-email" class="form-control">
                                        <span class="form-bar"></span>
                                        <label class="float-label"><i class="fa fa-search m-r-10"></i>Search Friend</label>
                                    </div>
                                </form>
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
                            </ul>
                            <div class="pcoded-navigation-label">UI Element</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="pcoded-hasmenu">
                                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                                        <span class="pcoded-mtext">Basic</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class=" ">
                                            <a href="breadcrumb.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Breadcrumbs</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="button.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Button</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class="">
                                            <a href="accordion.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Accordion</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="tabs.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Tabs</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="color.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Color</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="label-badge.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Label Badge</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="tooltip.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Tooltip And Popover</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="typography.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Typography</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="notification.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Notifications</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                            <div class="pcoded-navigation-label">Forms</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="">
                                    <a href="form-elements-component.php" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-layers"></i><b>FC</b></span>
                                        <span class="pcoded-mtext">Recipe</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                                <li class="active">
                                    <a href="form-elements-ingrediant.php" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-layers"></i><b>FC</b></span>
                                        <span class="pcoded-mtext">Ingrediants</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            </ul>
                            <div class="pcoded-navigation-label">Tables</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="">
                                    <a href="bs-basic-table.html" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-receipt"></i><b>B</b></span>
                                        <span class="pcoded-mtext">Table</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            </ul>
                            <div class="pcoded-navigation-label">Chart And Maps</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="">
                                    <a href="chart-morris.html" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-bar-chart-alt"></i><b>C</b></span>
                                        <span class="pcoded-mtext">Charts</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                                <li class="">
                                    <a href="map-google.html" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-map-alt"></i><b>M</b></span>
                                        <span class="pcoded-mtext">Maps</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            </ul>
                            <div class="pcoded-navigation-label">Pages</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="pcoded-hasmenu ">
                                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-id-badge"></i><b>A</b></span>
                                        <span class="pcoded-mtext">Pages</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="">
                                            <a href="auth-normal-sign-in.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Login</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class="">
                                            <a href="auth-sign-up.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Registration</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class="">
                                            <a href="sample-page.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-layout-sidebar-left"></i><b>S</b></span>
                                                <span class="pcoded-mtext">Sample Page</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
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
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <?php if (!empty($error)): ?>
                                                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                                <?php endif; ?>

                                                <?php if (!empty($success)): ?>
                                                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                                                <?php endif; ?>

                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Ingrediants List</h5>
                                                    </div>
                                                    <div class="card-block table-border-style">
                                                        <div class="table-responsive">
                                                            <table class="table table-striped table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Image</th>
                                                                        <th>Name</th>
                                                                        <th>ID</th>
                                                                        <th>Protein</th>
                                                                        <th>Fat</th>
                                                                        <th>Carbs</th>
                                                                        <th>Calories</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (!empty($ingrediants)): ?>
                                                                        <?php foreach ($ingrediants as $ingrediant): ?>
                                                                            <tr>
                                                                                <td>
                                                                                    <?php if (!empty($ingrediant['img_ing'])): ?>
                                                                                        <img src="<?php echo htmlspecialchars($ingrediant['img_ing']); ?>" alt="Ingrediant image" style="width:60px;height:60px;object-fit:cover;border-radius:6px;">
                                                                                    <?php else: ?>
                                                                                        No image
                                                                                    <?php endif; ?>
                                                                                </td>
                                                                                <td><?php echo htmlspecialchars($ingrediant['name_ing']); ?></td>
                                                                                <td><?php echo htmlspecialchars($ingrediant['id_ing']); ?></td>
                                                                                <td><?php echo htmlspecialchars($ingrediant['prot_ing']); ?></td>
                                                                                <td><?php echo htmlspecialchars($ingrediant['fat_ing']); ?></td>
                                                                                <td><?php echo htmlspecialchars($ingrediant['carb_ing']); ?></td>
                                                                                <td><?php echo htmlspecialchars($ingrediant['cal_ing']); ?></td>
                                                                                <td>
                                                                                    <form method="POST" action="" onsubmit="return confirm('Delete this ingrediant?');" style="margin:0;">
                                                                                        <input type="hidden" name="delete_id_ing" value="<?php echo (int)$ingrediant['id_ing']; ?>">
                                                                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                                                    </form>
                                                                                    <a href="edit-ingrediant.php?id_ing=<?php echo (int)$ingrediant['id_ing']; ?>" class="btn btn-primary btn-sm" style="margin-top:8px;display:inline-block;">Edit</a>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    <?php else: ?>
                                                                        <tr>
                                                                            <td colspan="8" class="text-center">No ingrediants found.</td>
                                                                        </tr>
                                                                    <?php endif; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <?php if ($totalPages > 1): ?>
                                                        <nav aria-label="Ingredient pagination" style="margin-top: 20px;">
                                                            <ul class="pagination">
                                                                <?php if ($currentPage > 1): ?>
                                                                    <li class="page-item">
                                                                        <a class="page-link" href="?ing_page=1">First</a>
                                                                    </li>
                                                                    <li class="page-item">
                                                                        <a class="page-link" href="?ing_page=<?php echo $currentPage - 1; ?>">Previous</a>
                                                                    </li>
                                                                <?php endif; ?>
                                                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                                    <li class="page-item <?php echo ($i === $currentPage) ? 'active' : ''; ?>">
                                                                        <a class="page-link" href="?ing_page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                                    </li>
                                                                <?php endfor; ?>
                                                                <?php if ($currentPage < $totalPages): ?>
                                                                    <li class="page-item">
                                                                        <a class="page-link" href="?ing_page=<?php echo $currentPage + 1; ?>">Next</a>
                                                                    </li>
                                                                    <li class="page-item">
                                                                        <a class="page-link" href="?ing_page=<?php echo $totalPages; ?>">Last</a>
                                                                    </li>
                                                                <?php endif; ?>
                                                            </ul>
                                                        </nav>
                                                        <p class="text-muted" style="margin-top: 10px;">Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?> (<?php echo $totalIngrediants; ?> total ingredients)</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>ADD Ingrediant</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <h4 class="sub-title">Ingrediant Information</h4>
                                                        <form method="POST" action="" enctype="multipart/form-data">
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Ingrediant Name</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="name_ing" class="form-control" placeholder="Ingrediant name">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Protein</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="prot_ing" class="form-control" placeholder="Protein in grams">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Fat</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="fat_ing" class="form-control" placeholder="Fat in grams">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Carbs</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="carb_ing" class="form-control" placeholder="Carbs in grams">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Calories</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="cal_ing" class="form-control" placeholder="Calories">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Image</label>
                                                                <div class="col-sm-10">
                                                                    <input type="file" name="img_ing" id="imageInputIng" class="form-control-file" accept="image/*">
                                                                    <small class="form-text text-muted">Supported formats: JPG, PNG, GIF, WebP (Max 5MB)</small>
                                                                    <div id="imagePreviewIng" class="mt-3" style="display:none;">
                                                                        <img id="previewImgIng" style="width:120px;height:120px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <script>
                                                                document.getElementById('imageInputIng').addEventListener('change', function(e) {
                                                                    const file = e.target.files[0];
                                                                    if (file && file.type.startsWith('image/')) {
                                                                        const reader = new FileReader();
                                                                        reader.onload = function(event) {
                                                                            document.getElementById('previewImgIng').src = event.target.result;
                                                                            document.getElementById('imagePreviewIng').style.display = 'block';
                                                                        };
                                                                        reader.readAsDataURL(file);
                                                                    } else if (file) {
                                                                        alert('Please select a valid image file');
                                                                        document.getElementById('previewImgIng').src = '';
                                                                        document.getElementById('imagePreviewIng').style.display = 'none';
                                                                    } else {
                                                                        document.getElementById('previewImgIng').src = '';
                                                                        document.getElementById('imagePreviewIng').style.display = 'none';
                                                                    }
                                                                });

                                                                (function() {
                                                                    const form = document.querySelector('form[enctype="multipart/form-data"]');
                                                                    if (!form) return;

                                                                    const nameInput = form.querySelector('input[name="name_ing"]');
                                                                    const protInput = form.querySelector('input[name="prot_ing"]');
                                                                    const fatInput = form.querySelector('input[name="fat_ing"]');
                                                                    const carbInput = form.querySelector('input[name="carb_ing"]');
                                                                    const calInput = form.querySelector('input[name="cal_ing"]');
                                                                    const floatFields = [protInput, fatInput, carbInput, calInput];

                                                                    const restrictDigits = function(input, maxLength) {
                                                                        input.addEventListener('input', function() {
                                                                            this.value = this.value.replace(/\D/g, '').slice(0, maxLength);
                                                                        });
                                                                    };

                                                                    const restrictText = function(input, maxLength) {
                                                                        input.addEventListener('input', function() {
                                                                            this.value = this.value.replace(/[^A-Za-z\s]/g, '').slice(0, maxLength);
                                                                        });
                                                                    };

                                                                    const restrictFloatField = function(input) {
                                                                        input.addEventListener('input', function() {
                                                                            let value = this.value.replace(',', '.').replace(/[^0-9.]/g, '');

                                                                            const firstDotIndex = value.indexOf('.');
                                                                            if (firstDotIndex !== -1) {
                                                                                value = value.slice(0, firstDotIndex + 1) + value.slice(firstDotIndex + 1).replace(/\./g, '');
                                                                            }

                                                                            const parts = value.split('.');
                                                                            if (parts.length > 1) {
                                                                                parts[1] = parts[1].slice(0, 2);
                                                                                value = parts[0] + '.' + parts[1];
                                                                            }

                                                                            if (value !== '' && Number(value) > 2000) {
                                                                                value = '2000';
                                                                            }

                                                                            this.value = value;
                                                                        });
                                                                    };

                                                                    const isValidStep001 = function(value) {
                                                                        const scaled = Number(value) * 100;
                                                                        return Math.abs(scaled - Math.round(scaled)) < 1e-9;
                                                                    };

                                                                    const isTextValue = function(value) {
                                                                        return /[A-Za-z]/.test(value);
                                                                    };

                                                                    restrictText(nameInput, 20);
                                                                    floatFields.forEach(restrictFloatField);

                                                                    form.addEventListener('submit', function(e) {
                                                                        const errors = [];

                                                                        const nameRaw = nameInput.value.trim();
                                                                        if (nameRaw.length > 20) {
                                                                            errors.push('Name max length is 20.');
                                                                        }
                                                                        if (nameRaw && !isTextValue(nameRaw)) {
                                                                            errors.push('Name must be a string value.');
                                                                        }

                                                                        floatFields.forEach(function(field) {
                                                                            const label = field.name === 'prot_ing' ? 'Protein' :
                                                                                field.name === 'fat_ing' ? 'Fat' :
                                                                                field.name === 'carb_ing' ? 'Carbs' : 'Calories';
                                                                            const raw = field.value.trim();
                                                                            const val = Number(raw);

                                                                            if (raw === '' || !Number.isFinite(val)) {
                                                                                errors.push(label + ' must be a float value.');
                                                                                return;
                                                                            }
                                                                            if (!isValidStep001(raw)) {
                                                                                errors.push(label + ' must use a 0.01 step.');
                                                                            }
                                                                            if (val > 2000) {
                                                                                errors.push(label + ' max value is 2000.');
                                                                            }
                                                                        });

                                                                        if (errors.length > 0) {
                                                                            e.preventDefault();
                                                                            alert(errors.join('\n'));
                                                                        }
                                                                    });
                                                                })();
                                                            </script>
                                                            <div class="form-group row">
                                                                <div class="col-sm-10 offset-sm-2">
                                                                    <button type="submit" class="btn btn-primary">Save Ingrediant</button>
                                                                </div>
                                                            </div>
                                                        </form>
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
            </div>
        </div>
    </div>
    <script type="text/javascript" src="assets/js/jquery/jquery.min.js "></script>
    <script type="text/javascript" src="assets/js/jquery-ui/jquery-ui.min.js "></script>
    <script type="text/javascript" src="assets/js/popper.js/popper.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js "></script>
    <!-- waves js -->
    <script src="assets/pages/waves/js/waves.min.js"></script>
    <!-- jquery slimscroll js -->
    <script type="text/javascript" src="assets/js/jquery-slimscroll/jquery.slimscroll.js"></script>

    <!-- Custom js -->
    <script src="assets/js/pcoded.min.js"></script>
    <script src="assets/js/vertical/vertical-layout.min.js"></script>
    <script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script type="text/javascript" src="assets/js/script.js"></script>
</body>

</html>
