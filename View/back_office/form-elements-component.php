<?php
include '../../Controller/menu_module/controle_Menu.php';
include '../../Controller/menu_module/controle_categ_rec.php';
include '../../Controller/menu_module/controle_ingrediant.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'add_category') {
        $categoryName = isset($_POST['name_cat_rec']) ? trim($_POST['name_cat_rec']) : '';
        $categoryColor = isset($_POST['color_cat_rec']) ? trim($_POST['color_cat_rec']) : '';
        $categoryImagePath = isset($_POST['img_cat_rec']) ? trim($_POST['img_cat_rec']) : '';

        if ($categoryName !== '' && $categoryColor !== '') {
            if (isset($_FILES['img_cat_file']) && $_FILES['img_cat_file']['error'] === 0) {
                $uploadDir = 'assets/images/categories/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = basename($_FILES['img_cat_file']['name']);
                $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
                $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array(strtolower($fileExt), $allowedExts)) {
                    $newFileName = uniqid('category_') . '.' . $fileExt;
                    $uploadPath = $uploadDir . $newFileName;

                    if (move_uploaded_file($_FILES['img_cat_file']['tmp_name'], $uploadPath)) {
                        $categoryImagePath = $uploadPath;
                    } else {
                        $error = "Failed to upload category image.";
                    }
                } else {
                    $error = "Invalid category image format. Only JPG, PNG, GIF, WebP allowed.";
                }
            }

            if ($categoryImagePath === '' && empty($error)) {
                $error = "Please import a category image or provide an image path.";
            }

            if (empty($error)) {
            $category = new categ_rec(
                0,
                $categoryName,
                $categoryImagePath,
                $categoryColor
            );
            $categoryController = new controle_categ_rec();
            if ($categoryController->add_categ_rec($category)) {
                $success = "Category added successfully.";
            } else {
                $error = "Failed to add category.";
            }
            }
        } else {
            $error = "Category name and color are required.";
        }
    } elseif (isset($_POST["delete_id_rec"]) && !empty($_POST["delete_id_rec"])) {
        $controller = new Controller_menu();
        $controller->delete_recipe((int)$_POST["delete_id_rec"]);
        $success = "Recipe deleted successfully.";
    } elseif (isset($_POST['form_type']) && $_POST['form_type'] === 'add_recipe') {
        $requiredFields = [
            'nom_rec' => 'Recipe Name',
            'description_rec' => 'Description',
            'prot_rec' => 'Protein',
            'fat_rec' => 'Fat',
            'carb_rec' => 'Carbs',
            'cal_rec' => 'Calories',
            'instructions_rec' => 'Instructions',
            'origin_rec' => 'Origin'
        ];

        $missingFields = [];
        foreach ($requiredFields as $fieldKey => $fieldLabel) {
            if (!isset($_POST[$fieldKey]) || trim((string)$_POST[$fieldKey]) === '') {
                $missingFields[] = $fieldLabel;
            }
        }

        if (!empty($missingFields)) {
            $error = 'Please fill: ' . implode(', ', $missingFields) . '.';
        }

        if (empty($error)) {
            $imagePath = "";
            $selectedCategoryIds = [];
            if (isset($_POST['categorie_rec']) && is_array($_POST['categorie_rec'])) {
                foreach ($_POST['categorie_rec'] as $categoryIdRaw) {
                    $categoryId = (int)$categoryIdRaw;
                    if ($categoryId > 0) {
                        $selectedCategoryIds[$categoryId] = $categoryId;
                    }
                }
            }
            $categoryController = new controle_categ_rec();
            $selectedCategoryNames = [];

            foreach ($selectedCategoryIds as $categoryId) {
                $selectedCategory = $categoryController->get_categ_rec_by_id($categoryId);
                if (!empty($selectedCategory) && isset($selectedCategory['nom_categ'])) {
                    $selectedCategoryName = trim($selectedCategory['nom_categ']);
                    if ($selectedCategoryName !== '') {
                        $selectedCategoryNames[] = $selectedCategoryName;
                    }
                }
            }

            if (empty($selectedCategoryNames)) {
                $error = "Please select at least one valid category.";
            }

            $selectedCategoryName = implode(', ', $selectedCategoryNames);
            
            // Handle image file upload
            if (empty($error) && isset($_FILES['imag_rec']) && $_FILES['imag_rec']['error'] == 0) {
                $uploadDir = 'assets/images/recipes/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = basename($_FILES['imag_rec']['name']);
                $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
                $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array(strtolower($fileExt), $allowedExts)) {
                    $newFileName = uniqid('recipe_') . '.' . $fileExt;
                    $uploadPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($_FILES['imag_rec']['tmp_name'], $uploadPath)) {
                        $imagePath = $uploadPath;
                    } else {
                        $error = "Failed to upload image file.";
                    }
                } else {
                    $error = "Invalid image file format. Only JPG, PNG, GIF, WebP allowed.";
                }
            }
            
            if (empty($error)) {
                $recipe = new Recipe(
                    0,
                    $_POST['nom_rec'],
                    $selectedCategoryName,
                    $_POST['description_rec'],
                    (float)$_POST['prot_rec'],
                    (float)$_POST['fat_rec'],
                    (float)$_POST['carb_rec'],
                    (float)$_POST['cal_rec'],
                    $_POST['instructions_rec'],
                    $_POST['origin_rec'],
                    $imagePath
                );
                $controller = new Controller_menu();
                $newRecipeId = $controller->add_recipe($recipe);
                if (!$newRecipeId) {
                    $error = "Failed to add recipe. Please check that Recipe ID is unique and all values are valid.";
                } else {
                    foreach ($selectedCategoryIds as $categoryId) {
                        if (!$categoryController->affecter_categ_rec((int)$newRecipeId, (int)$categoryId)) {
                            $error = "Recipe saved, but failed to link one or more categories.";
                            break;
                        }
                    }

                    if (empty($error)) {
                        $ingredientRows = [];
                        if (isset($_POST['ingredients']) && is_array($_POST['ingredients'])) {
                            foreach ($_POST['ingredients'] as $ingredientRow) {
                                if (!is_array($ingredientRow)) {
                                    continue;
                                }

                                $ingredientRows[] = [
                                    'id_ing' => isset($ingredientRow['id_ing']) ? (int)$ingredientRow['id_ing'] : 0,
                                    'quantity' => isset($ingredientRow['quantity']) ? trim((string)$ingredientRow['quantity']) : '',
                                    'unity' => isset($ingredientRow['unity']) ? trim((string)$ingredientRow['unity']) : '',
                                ];
                            }
                        }

                        if (!$controller->add_recipe_ingredients((int)$newRecipeId, $ingredientRows)) {
                            $error = "Recipe saved, but failed to link one or more ingredients.";
                        }
                    }
                }

                if (empty($error)) {
                    $success = "Recipe added successfully.";
                }
            }
        }
    } else {
        $error = "Missing form data.";
    }
}

$controller = new Controller_menu();
$allRecipes = $controller->list_recipe();
$categoryController = new controle_categ_rec();
$recipeCategories = $categoryController->list_categ_rec();
$ingrediantController = new Controller_ingrediant();
$availableIngrediants = $ingrediantController->list_ingrediants();

// Pagination for recipes
$itemsPerPage = 10;
$currentPage = isset($_GET['recipe_page']) ? max(1, (int)$_GET['recipe_page']) : 1;
$totalRecipes = count($allRecipes);
$totalPages = ceil($totalRecipes / $itemsPerPage);
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $itemsPerPage;
$recipes = array_slice($allRecipes, $offset, $itemsPerPage);
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
                                <li class="active">
                                    <a href="form-elements-component.php" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-layers"></i><b>FC</b></span>
                                        <span class="pcoded-mtext">Recipe</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                                <li class="">
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
                                                    <div class="alert alert-danger" role="alert" style="margin-bottom: 15px;">
                                                        <?php echo htmlspecialchars($error); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($success)): ?>
                                                    <div class="alert alert-success" role="alert" style="margin-bottom: 15px;">
                                                        <?php echo htmlspecialchars($success); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Recipe List</h5>
                                                    </div>
                                                    <div class="card-block table-border-style">
                                                        <div class="table-responsive">
                                                            <table class="table table-striped table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Image</th>
                                                                        <th>Name</th>
                                                                        <th>ID</th>
                                                                        <th>Category</th>
                                                                        <th>Description</th>
                                                                        <th>Protein</th>
                                                                        <th>Fat</th>
                                                                        <th>Carbs</th>
                                                                        <th>Calories</th>
                                                                        <th>Instructions</th>
                                                                        <th>Origin</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (!empty($recipes)): ?>
                                                                        <?php foreach ($recipes as $recipe): ?>
                                                                            <tr>
                                                                                <td>
                                                                                    <?php if (!empty($recipe['img_rec'])): ?>
                                                                                        <img src="<?php echo htmlspecialchars($recipe['img_rec']); ?>" alt="Recipe image" style="width:60px;height:60px;object-fit:cover;border-radius:6px;">
                                                                                    <?php else: ?>
                                                                                        No image
                                                                                    <?php endif; ?>
                                                                                </td>
                                                                                <td><?php echo htmlspecialchars($recipe['name_rec']); ?></td>
                                                                                <td><?php echo htmlspecialchars($recipe['id_rec']); ?></td>
                                                                                <td><?php echo htmlspecialchars($recipe['categorie_rec']); ?></td>
                                                                                <td><?php echo htmlspecialchars($recipe['description_rec']); ?></td>
                                                                                <td><?php echo htmlspecialchars($recipe['prot_rec']); ?></td>
                                                                                <td><?php echo htmlspecialchars($recipe['fat_rec']); ?></td>
                                                                                <td><?php echo htmlspecialchars($recipe['carb_rec']); ?></td>
                                                                                <td><?php echo htmlspecialchars($recipe['cal_rec']); ?></td>
                                                                                <td><?php echo htmlspecialchars($recipe['instruction_rec']); ?></td>
                                                                                <td><?php echo htmlspecialchars($recipe['origin_rec']); ?></td>
                                                                                <td>
                                                                                    <form method="POST" action="" onsubmit="return confirm('Delete this recipe?');" style="margin:0;">
                                                                                        <input type="hidden" name="form_type" value="delete_recipe">
                                                                                        <input type="hidden" name="delete_id_rec" value="<?php echo (int)$recipe['id_rec']; ?>">
                                                                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                                                    </form>
                                                                                    <a href="edit-recipe.php?id_rec=<?php echo (int)$recipe['id_rec']; ?>" class="btn btn-primary btn-sm" style="margin-top:8px;display:inline-block;">Edit</a>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    <?php else: ?>
                                                                        <tr>
                                                                            <td colspan="12" class="text-center">No recipes found.</td>
                                                                        </tr>
                                                                    <?php endif; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <?php if ($totalPages > 1): ?>
                                                        <nav aria-label="Recipe pagination" style="margin-top: 20px;">
                                                            <ul class="pagination">
                                                                <?php if ($currentPage > 1): ?>
                                                                    <li class="page-item">
                                                                        <a class="page-link" href="?recipe_page=1">First</a>
                                                                    </li>
                                                                    <li class="page-item">
                                                                        <a class="page-link" href="?recipe_page=<?php echo $currentPage - 1; ?>">Previous</a>
                                                                    </li>
                                                                <?php endif; ?>
                                                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                                    <li class="page-item <?php echo ($i === $currentPage) ? 'active' : ''; ?>">
                                                                        <a class="page-link" href="?recipe_page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                                    </li>
                                                                <?php endfor; ?>
                                                                <?php if ($currentPage < $totalPages): ?>
                                                                    <li class="page-item">
                                                                        <a class="page-link" href="?recipe_page=<?php echo $currentPage + 1; ?>">Next</a>
                                                                    </li>
                                                                    <li class="page-item">
                                                                        <a class="page-link" href="?recipe_page=<?php echo $totalPages; ?>">Last</a>
                                                                    </li>
                                                                <?php endif; ?>
                                                            </ul>
                                                        </nav>
                                                        <p class="text-muted" style="margin-top: 10px;">Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?> (<?php echo $totalRecipes; ?> total recipes)</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>ADD Recipe</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <h4 class="sub-title">Recipe Information</h4>
                                                        <form method="POST" action="" enctype="multipart/form-data">
                                                            <input type="hidden" name="form_type" value="add_recipe">
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Recipe Name</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="nom_rec" class="form-control" placeholder="Recipe name">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Category</label>
                                                                <div class="col-sm-10">
                                                                    <div class="border rounded p-2" style="max-height:180px;overflow-y:auto;">
                                                                        <?php if (!empty($recipeCategories)): ?>
                                                                            <?php foreach ($recipeCategories as $dbCategory): ?>
                                                                                <?php
                                                                                $categoryName = isset($dbCategory['nom_categ']) ? trim($dbCategory['nom_categ']) : '';
                                                                                $categoryId = isset($dbCategory['id_categ_rec']) ? (int)$dbCategory['id_categ_rec'] : 0;
                                                                                if ($categoryName === '' || $categoryId <= 0) {
                                                                                    continue;
                                                                                }
                                                                                ?>
                                                                                <div class="form-check mb-2">
                                                                                    <input class="form-check-input" type="checkbox" name="categorie_rec[]" id="categ_rec_<?php echo $categoryId; ?>" value="<?php echo $categoryId; ?>">
                                                                                    <label class="form-check-label" for="categ_rec_<?php echo $categoryId; ?>"><?php echo htmlspecialchars($categoryName); ?></label>
                                                                                </div>
                                                                            <?php endforeach; ?>
                                                                        <?php else: ?>
                                                                            <p class="mb-0 text-muted">No categories available.</p>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <small class="form-text text-muted">You can select multiple categories.</small>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Description</label>
                                                                <div class="col-sm-10">
                                                                    <textarea name="description_rec" class="form-control" rows="3" placeholder="Recipe description"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Protein</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="prot_rec" class="form-control" placeholder="Protein in grams">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Fat</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="fat_rec" class="form-control" placeholder="Fat in grams">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Carbs</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="carb_rec" class="form-control" placeholder="Carbs in grams">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Calories</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="cal_rec" class="form-control" placeholder="Calories">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Instructions</label>
                                                                <div class="col-sm-10">
                                                                    <textarea name="instructions_rec" class="form-control" rows="4" placeholder="Preparation instructions"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Origin</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="origin_rec" class="form-control" placeholder="Recipe origin">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Ingrediants</label>
                                                                <div class="col-sm-10">
                                                                    <div id="ingredientsRepeater" class="d-flex flex-wrap gap-3 align-items-start"></div>
                                                                    <small class="form-text text-muted d-block mt-2">Click the big + to add an ingrediant from the database.</small>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Image</label>
                                                                <div class="col-sm-10">
                                                                    <input type="file" name="imag_rec" id="imageInput" class="form-control-file" accept="image/*">
                                                                    <small class="form-text text-muted">Supported formats: JPG, PNG, GIF, WebP (Max 5MB)</small>
                                                                    <div id="imagePreview" class="mt-3" style="display:none;">
                                                                        <img id="previewImg" style="width:120px;height:120px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <script>
                                                                (function() {
                                                                    const form = document.querySelector('form[enctype="multipart/form-data"]');
                                                                    if (!form) {
                                                                        return;
                                                                    }

                                                                    const imageInput = document.getElementById('imageInput');
                                                                    const imagePreview = document.getElementById('imagePreview');
                                                                    const previewImg = document.getElementById('previewImg');
                                                                    const ingredientsRepeater = document.getElementById('ingredientsRepeater');
                                                                    const ingredientOptions = <?php echo json_encode(array_map(function($ingredient) {
                                                                        return [
                                                                            'id' => (int)$ingredient['id_ing'],
                                                                            'name' => (string)$ingredient['name_ing'],
                                                                        ];
                                                                    }, $availableIngrediants ?: []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
                                                                    let ingredientRowCounter = 0;

                                                                    const nameInput = form.querySelector('input[name="nom_rec"]');
                                                                    const protInput = form.querySelector('input[name="prot_rec"]');
                                                                    const fatInput = form.querySelector('input[name="fat_rec"]');
                                                                    const carbInput = form.querySelector('input[name="carb_rec"]');
                                                                    const calInput = form.querySelector('input[name="cal_rec"]');
                                                                    const originInput = form.querySelector('input[name="origin_rec"]');
                                                                    const descInput = form.querySelector('textarea[name="description_rec"]');
                                                                    const instInput = form.querySelector('textarea[name="instructions_rec"]');
                                                                    const categoryChecks = form.querySelectorAll('input[name="categorie_rec[]"]');
                                                                    const floatFields = [protInput, fatInput, carbInput, calInput];

                                                                    const triggerImagePreview = function(file) {
                                                                        if (!file || !file.type.startsWith('image/')) {
                                                                            return;
                                                                        }

                                                                        const reader = new FileReader();
                                                                        reader.onload = function(event) {
                                                                            previewImg.src = event.target.result;
                                                                            imagePreview.style.display = 'block';
                                                                        };
                                                                        reader.readAsDataURL(file);
                                                                    };

                                                                    if (imageInput) {
                                                                        imageInput.addEventListener('change', function(e) {
                                                                            const file = e.target.files[0];
                                                                            if (file && file.type.startsWith('image/')) {
                                                                                triggerImagePreview(file);
                                                                            } else if (file) {
                                                                                alert('Please select a valid image file');
                                                                            }
                                                                        });
                                                                    }

                                                                    const restrictText = function(input, maxLength) {
                                                                        if (!input) {
                                                                            return;
                                                                        }

                                                                        input.addEventListener('input', function() {
                                                                            this.value = this.value.replace(/[^A-Za-z\s]/g, '').slice(0, maxLength);
                                                                        });
                                                                    };

                                                                    const restrictMaxLength = function(input, maxLength) {
                                                                        if (!input) {
                                                                            return;
                                                                        }

                                                                        input.addEventListener('input', function() {
                                                                            if (this.value.length > maxLength) {
                                                                                this.value = this.value.slice(0, maxLength);
                                                                            }
                                                                        });
                                                                    };

                                                                    const restrictFloatField = function(input) {
                                                                        if (!input) {
                                                                            return;
                                                                        }

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

                                                                    restrictText(nameInput, 20);
                                                                    restrictText(originInput, 20);
                                                                    restrictMaxLength(descInput, 500);
                                                                    restrictMaxLength(instInput, 500);
                                                                    floatFields.forEach(restrictFloatField);

                                                                    const isValidStep001 = function(value) {
                                                                        const scaled = Number(value) * 100;
                                                                        return Math.abs(scaled - Math.round(scaled)) < 1e-9;
                                                                    };

                                                                    const isTextValue = function(value) {
                                                                        return /[A-Za-z]/.test(value);
                                                                    };

                                                                    const createPlusSlot = function() {
                                                                        const slot = document.createElement('div');
                                                                        slot.className = 'ingredient-slot border rounded-3 bg-light d-flex align-items-center justify-content-center';
                                                                        slot.style.width = '100px';
                                                                        slot.style.height = '100px';
                                                                        slot.innerHTML = '<button type="button" class="btn btn-outline-primary rounded-circle d-flex align-items-center justify-content-center ingredient-add-btn" aria-label="Add ingrediant" style="width:72px;height:72px;font-size:2rem;line-height:1;">+</button>';
                                                                        return slot;
                                                                    };

                                                                    const createEditorSlot = function() {
                                                                        const slot = document.createElement('div');
                                                                        slot.className = 'ingredient-slot border rounded-3 bg-white p-3 shadow-sm';
                                                                        slot.style.width = '260px';
                                                                        const optionsHtml = ingredientOptions.map(function(option) {
                                                                            return '<option value="' + option.id + '">' + option.name + '</option>';
                                                                        }).join('');

                                                                        slot.innerHTML = [
                                                                            '<div class="d-flex flex-column gap-2">',
                                                                            '<div>',
                                                                            '<label class="form-label mb-1 fw-semibold">Ingrediant</label>',
                                                                            '<select class="form-select ingredient-select">',
                                                                            '<option value="">Choose ingrediant</option>',
                                                                            optionsHtml,
                                                                            '</select>',
                                                                            '</div>',
                                                                            '<div class="row g-2">',
                                                                            '<div class="col-7">',
                                                                            '<label class="form-label mb-1 fw-semibold">Quantity</label>',
                                                                            '<input type="text" class="form-control ingredient-quantity" placeholder="e.g. 250">',
                                                                            '</div>',
                                                                            '<div class="col-5">',
                                                                            '<label class="form-label mb-1 fw-semibold">Unity</label>',
                                                                            '<input type="text" class="form-control ingredient-unity" placeholder="g">',
                                                                            '</div>',
                                                                            '</div>',
                                                                            '<div class="d-flex gap-2 justify-content-between">',
                                                                            '<button type="button" class="btn btn-sm btn-outline-danger ingredient-cancel-btn">Cancel</button>',
                                                                            '<button type="button" class="btn btn-sm btn-primary ingredient-confirm-btn">Done</button>',
                                                                            '</div>',
                                                                            '</div>'
                                                                        ].join('');

                                                                        return slot;
                                                                    };

                                                                    const createSummarySlot = function(ingredientId, ingredientName, quantity, unity, rowIndex) {
                                                                        const slot = document.createElement('div');
                                                                        slot.className = 'ingredient-slot border rounded-3 bg-white p-3 shadow-sm ingredient-summary-slot';
                                                                        slot.style.width = '260px';
                                                                        slot.innerHTML = [
                                                                            '<div class="d-flex justify-content-between align-items-start gap-2 mb-2">',
                                                                            '<div>',
                                                                            '<div class="fw-bold">' + ingredientName + '</div>',
                                                                            '<div class="text-muted small">' + quantity + ' ' + unity + '</div>',
                                                                            '</div>',
                                                                            '<button type="button" class="btn btn-sm btn-link text-danger p-0 ingredient-remove-btn">Remove</button>',
                                                                            '</div>',
                                                                            '<input type="hidden" name="ingredients[' + rowIndex + '][id_ing]" value="' + ingredientId + '">',
                                                                            '<input type="hidden" name="ingredients[' + rowIndex + '][quantity]" value="' + quantity + '">',
                                                                            '<input type="hidden" name="ingredients[' + rowIndex + '][unity]" value="' + unity + '">'
                                                                        ].join('');
                                                                        return slot;
                                                                    };

                                                                    const ensurePlusSlot = function() {
                                                                        if (!ingredientsRepeater || ingredientsRepeater.querySelector('.ingredient-add-btn')) {
                                                                            return;
                                                                        }

                                                                        ingredientsRepeater.appendChild(createPlusSlot());
                                                                        bindPlusSlots();
                                                                    };

                                                                    const bindEditorSlot = function(slot) {
                                                                        const confirmButton = slot.querySelector('.ingredient-confirm-btn');
                                                                        const cancelButton = slot.querySelector('.ingredient-cancel-btn');
                                                                        const select = slot.querySelector('.ingredient-select');
                                                                        const quantity = slot.querySelector('.ingredient-quantity');
                                                                        const unity = slot.querySelector('.ingredient-unity');

                                                                        if (confirmButton) {
                                                                            confirmButton.addEventListener('click', function() {
                                                                                const ingredientValue = select ? select.value : '';
                                                                                const ingredientName = select && select.selectedIndex >= 0 ? select.options[select.selectedIndex].text : '';
                                                                                const ingredientQuantity = quantity ? quantity.value.trim() : '';
                                                                                const ingredientUnity = unity ? unity.value.trim() : '';

                                                                                if (!ingredientValue || !ingredientName || !ingredientQuantity || !ingredientUnity) {
                                                                                    alert('Choose an ingrediant, quantity, and unity first.');
                                                                                    return;
                                                                                }

                                                                                const rowIndex = String(ingredientRowCounter++);
                                                                                const summarySlot = createSummarySlot(ingredientValue, ingredientName, ingredientQuantity, ingredientUnity, rowIndex);
                                                                                slot.replaceWith(summarySlot);
                                                                                bindSummarySlot(summarySlot);
                                                                                ensurePlusSlot();
                                                                            });
                                                                        }

                                                                        if (cancelButton) {
                                                                            cancelButton.addEventListener('click', function() {
                                                                                slot.remove();
                                                                                ensurePlusSlot();
                                                                            });
                                                                        }
                                                                    };

                                                                    const bindSummarySlot = function(slot) {
                                                                        const removeButton = slot.querySelector('.ingredient-remove-btn');
                                                                        if (!removeButton) {
                                                                            return;
                                                                        }

                                                                        removeButton.addEventListener('click', function() {
                                                                            slot.remove();
                                                                            ensurePlusSlot();
                                                                        });
                                                                    };

                                                                    const bindPlusSlots = function() {
                                                                        if (!ingredientsRepeater) {
                                                                            return;
                                                                        }

                                                                        const addButtons = ingredientsRepeater.querySelectorAll('.ingredient-add-btn');
                                                                        addButtons.forEach(function(button) {
                                                                            if (button.dataset.bound === '1') {
                                                                                return;
                                                                            }

                                                                            button.dataset.bound = '1';
                                                                            button.addEventListener('click', function() {
                                                                                const currentSlot = button.closest('.ingredient-slot');
                                                                                if (!currentSlot) {
                                                                                    return;
                                                                                }

                                                                                const editorSlot = createEditorSlot();
                                                                                currentSlot.replaceWith(editorSlot);
                                                                                bindEditorSlot(editorSlot);
                                                                                editorSlot.insertAdjacentElement('afterend', createPlusSlot());
                                                                                bindPlusSlots();
                                                                            });
                                                                        });
                                                                    };

                                                                    if (ingredientsRepeater) {
                                                                        ingredientsRepeater.appendChild(createPlusSlot());
                                                                        bindPlusSlots();
                                                                    }

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
                                                                            const label = field.name === 'prot_rec' ? 'Protein' :
                                                                                field.name === 'fat_rec' ? 'Fat' :
                                                                                field.name === 'carb_rec' ? 'Carbs' : 'Calories';
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

                                                                        const originRaw = originInput.value.trim();
                                                                        if (originRaw.length > 20) {
                                                                            errors.push('Origin max length is 20.');
                                                                        }
                                                                        if (originRaw && !isTextValue(originRaw)) {
                                                                            errors.push('Origin must be a string value.');
                                                                        }

                                                                        if (descInput.value.trim().length > 500) {
                                                                            errors.push('Description max length is 500.');
                                                                        }
                                                                        if (instInput.value.trim().length > 500) {
                                                                            errors.push('Instructions max length is 500.');
                                                                        }

                                                                        if (categoryChecks.length > 0) {
                                                                            const selectedCategories = Array.from(categoryChecks).filter(function(checkbox) {
                                                                                return checkbox.checked;
                                                                            });
                                                                            if (selectedCategories.length === 0) {
                                                                                errors.push('Select at least one category.');
                                                                            }
                                                                        }

                                                                        const editorSlots = ingredientsRepeater ? ingredientsRepeater.querySelectorAll('.ingredient-select') : [];
                                                                        const summarySlots = ingredientsRepeater ? ingredientsRepeater.querySelectorAll('.ingredient-summary-slot') : [];

                                                                        if (editorSlots.length > 0) {
                                                                            errors.push('Confirm or cancel the ingredient row before saving.');
                                                                        }

                                                                        if (summarySlots.length === 0) {
                                                                            errors.push('Add at least one ingredient.');
                                                                        }

                                                                        if (errors.length > 0) {
                                                                            e.preventDefault();
                                                                            alert(errors.join('\n'));
                                                                        }
                                                                    });
                                                                })();
                                                            </script>
                                                            <div class="form-group row">
                                                                <div class="col-sm-10 offset-sm-2">
                                                                    <button type="submit" class="btn btn-primary">Save Recipe</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>ADD Category</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <h4 class="sub-title">Category Information</h4>
                                                        <form method="POST" action="" enctype="multipart/form-data">
                                                            <input type="hidden" name="form_type" value="add_category">
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Category Name</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="name_cat_rec" class="form-control" placeholder="Category name" required>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Category Image</label>
                                                                <div class="col-sm-10">
                                                                    <input type="file" name="img_cat_file" class="form-control-file" accept="image/*">
                                                                    <small class="form-text text-muted">Use the import button above to upload an image, or fill an existing path below.</small>
                                                                    <input type="text" name="img_cat_rec" class="form-control mt-2" placeholder="Optional image path or URL">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-2 col-form-label">Category Color</label>
                                                                <div class="col-sm-10">
                                                                    <input type="color" name="color_cat_rec" class="form-control" value="#f59e0b" required>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <div class="col-sm-10 offset-sm-2">
                                                                    <button type="submit" class="btn btn-success">Save Category</button>
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
