<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../front_office/foovia-backoffice.php');
    exit;
}

$adminName = trim((string) ($_SESSION['user_name'] ?? 'Foovia Admin'));
$adminEmail = trim((string) ($_SESSION['user_email'] ?? ''));

require_once __DIR__ . '/../../../Model/MARKETPLACE_MODULE/Marchandise.php';
require_once __DIR__ . '/../../../Model/MARKETPLACE_MODULE/Magasin.php';
require_once __DIR__ . '/../../../Model/MARKETPLACE_MODULE/Categorie.php';
require_once __DIR__ . '/../../../Model/MARKETPLACE_MODULE/url_helper.php';

$appBaseUrl = foovia_app_base_url();

$marchandiseModel = new Marchandise();
$magasinModel = new Magasin();
$categorieModel = new Categorie();

$stores = $magasinModel->fetchAll();
$categories = $categorieModel->fetchAll();
$products = $marchandiseModel->fetchAllWithStores();
$summary = $marchandiseModel->fetchSummary();
$reservationsByProduct = $marchandiseModel->fetchReservationsByProduct();
$status = (string) ($_GET['status'] ?? '');
$productsPerPage = 8;
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$totalProducts = count($products);
$totalPages = max(1, (int) ceil($totalProducts / $productsPerPage));
$currentPage = min($currentPage, $totalPages);
$pageOffset = ($currentPage - 1) * $productsPerPage;
$paginatedProducts = array_slice($products, $pageOffset, $productsPerPage);
$editId = (int) ($_GET['edit'] ?? 0);
$editingProduct = $editId > 0 ? $marchandiseModel->findById($editId) : null;
$isEditing = $editingProduct !== null;
$selectedStoreIds = $isEditing && !empty($editingProduct['store_ids'])
    ? array_map('intval', explode(',', (string) $editingProduct['store_ids']))
    : [];
$selectedCategoryIds = $isEditing && !empty($editingProduct['category_ids'])
    ? array_map('intval', explode(',', (string) $editingProduct['category_ids']))
    : [];
$formatPrice = static fn (mixed $price): string => rtrim(rtrim(number_format((float) $price, 3, '.', ''), '0'), '.');
$buildProductPageUrl = static fn (int $page): string => 'products.php?page=' . max(1, $page) . '#products-table';

$message = match ($status) {
    'success' => ['class' => 'alert-success', 'text' => 'Product added successfully. It is now available in the front office.'],
    'updated' => ['class' => 'alert-success', 'text' => 'Product updated successfully in both back office and front office.'],
    'deleted' => ['class' => 'alert-success', 'text' => 'Product removed successfully from the back office and front office.'],
    'reservations_reset' => ['class' => 'alert-success', 'text' => 'Reservations reset successfully for the selected product.'],
    'error' => ['class' => 'alert-warning', 'text' => 'Some product information is missing. Please complete the form and try again.'],
    'dberror' => ['class' => 'alert-danger', 'text' => 'The product could not be saved. Check the DB connection and table structure.'],
    'deleteerror' => ['class' => 'alert-danger', 'text' => 'The product could not be deleted. Please try again.'],
    'reservationerror' => ['class' => 'alert-danger', 'text' => 'Reservations could not be reset. Please try again.'],
    default => null,
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Foovia Back Office</title>
    <link rel="icon" href="../USER_MODULE/assets/images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../USER_MODULE/assets/pages/waves/css/waves.min.css" type="text/css" media="all">
    <link rel="stylesheet" type="text/css" href="../USER_MODULE/assets/css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../USER_MODULE/assets/icon/themify-icons/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="../USER_MODULE/assets/icon/icofont/css/icofont.css">
    <link rel="stylesheet" type="text/css" href="../USER_MODULE/assets/icon/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="../USER_MODULE/assets/css/jquery.mCustomScrollbar.css">
    <link rel="stylesheet" type="text/css" href="../USER_MODULE/assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="../../front_office/MARKETPLACE_MODULE/assets/css/marketplace.css?v=admin-pagination-3">
</head>
<body>
    <div class="theme-loader">
        <div class="loader-track">
            <div class="preloader-wrapper">
                <div class="spinner-layer spinner-blue"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div>
                <div class="spinner-layer spinner-red"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div>
                <div class="spinner-layer spinner-yellow"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div>
                <div class="spinner-layer spinner-green"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div>
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
                        <a href="products.php" class="foovia-admin-brand">
                            <img src="../../front_office/MARKETPLACE_MODULE/assets/imges-autre/pic_logo.png" class="foovia-admin-logo-img" style="height: 22px; width: auto; max-height: 22px;" alt="Foovia logo">
                            <img src="../../front_office/MARKETPLACE_MODULE/assets/imges-autre/pic_name.png" class="foovia-admin-name-img" style="height: 12px; width: auto; max-height: 12px;" alt="Foovia">
                        </a>
                    </div>
                    <div class="navbar-container container-fluid">
                        <ul class="nav-left">
                            <li><div class="sidebar_toggle"><a href="javascript:void(0)"><i class="ti-menu"></i></a></div></li>
                        </ul>
                        <ul class="nav-right">
                            <li>
                                <a href="../../front_office/foovia.php" class="waves-effect waves-light" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:999px;background:#158fbe;color:#fff;font-weight:700;text-decoration:none;">
                                    <i class="ti-home"></i> Welcome page
                                </a>
                            </li>
                            <li class="user-profile header-notification">
                                <a href="#!" class="waves-effect waves-light">
                                    <img src="../USER_MODULE/assets/images/avatar-4.jpg" class="img-radius" alt="Administrator">
                                    <span><?= htmlspecialchars($adminName !== '' ? $adminName : 'Foovia Admin', ENT_QUOTES) ?></span>
                                    <i class="ti-angle-down"></i>
                                </a>
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
                                <img class="img-80 img-radius" src="../USER_MODULE/assets/images/avatar-4.jpg" alt="Admin profile">
                                <div class="user-details">
                                    <span><?= htmlspecialchars($adminName !== '' ? $adminName : 'Foovia Admin', ENT_QUOTES) ?></span>
                                    <?php if ($adminEmail !== ''): ?><small><?= htmlspecialchars($adminEmail, ENT_QUOTES) ?></small><?php endif; ?>
                                </div>
                            </div>
                            <div class="pcoded-navigation-label">Navigation</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="pcoded-hasmenu active pcoded-trigger">
                                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                                        <span class="pcoded-mtext">MODULES</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li>
                                            <a href="../SUPPORT_MODULE/support_admin.php" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">SUPPORT</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="../TRACK_MODULE/tracking.php" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">TRACKING</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="../USER_MODULE/tabs.php" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">USER</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="../SPORT_MOULE/form-elements-component.php" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">SPORT</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="../menu_module/form-elements-component.php" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">MENU</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="../menu_module/form-elements-ingrediant.php" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">INGREDIANTS</span>
                                            </a>
                                        </li>
                                        <li class="pcoded-hasmenu active pcoded-trigger">
                                            <a href="javascript:void(0)" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-shopping-cart"></i></span>
                                                <span class="pcoded-mtext">MARKETPLACE</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                            <ul class="pcoded-submenu">
                                                <li class="active">
                                                    <a href="products.php" class="waves-effect waves-dark">
                                                        <span class="pcoded-mtext">Products</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="magasins.php" class="waves-effect waves-dark">
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
                        <div class="page-header">
                            <div class="page-block">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="page-header-title">
                                            <h5 class="m-b-10">Foovia Product Management</h5>
                                            <p class="m-b-0">Add products through the Foovia back office and publish them directly to the front office.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pcoded-inner-content">
                            <div class="main-body">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="card bg-c-green admin-stat-card"><div class="card-block text-white"><h6 class="m-b-20">Total Products</h6><h2><?= (int) $summary['products'] ?></h2></div></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-c-blue admin-stat-card"><div class="card-block text-white"><h6 class="m-b-20">Registered Stores</h6><h2><?= (int) $summary['stores'] ?></h2></div></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-c-yellow admin-stat-card"><div class="card-block text-white"><h6 class="m-b-20">Global Stock</h6><h2><?= (int) $summary['quantity'] ?></h2></div></div>
                                            </div>
                                        </div>

                                        <?php if ($message !== null): ?>
                                            <div class="alert <?= htmlspecialchars($message['class'], ENT_QUOTES) ?> admin-alert"><?= htmlspecialchars($message['text'], ENT_QUOTES) ?></div>
                                        <?php endif; ?>

                                        <div class="row">
                                            <div class="col-xl-7 col-md-12">
                                                <div class="card admin-form-card">
                                                    <div class="card-header">
                                                        <h5><?= $isEditing ? 'Modify Selected Product' : 'Add New Marchandise' ?></h5>
                                                        <span><?= $isEditing ? 'Select a product, modify the fields below, then save the changes.' : 'Choose stores and food categories before publishing.' ?></span>
                                                    </div>
                                                    <div class="card-block">
                                                        <form action="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/Controller/MARKETPLACE_MODULE/Marchandise_Controller.php?action=save" method="post" enctype="multipart/form-data" data-product-form data-editing-mode="<?= $isEditing ? 'true' : 'false' ?>">
                                                            <input type="hidden" name="id_march" value="<?= $isEditing ? (int) $editingProduct['id_march'] : 0 ?>">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Magasins</label>
                                                                        <div class="admin-checkbox-list" data-store-checkboxes>
                                                                            <?php foreach ($stores as $store): ?>
                                                                                <label class="admin-checkbox-option">
                                                                                    <input type="checkbox" name="id_mag[]" value="<?= (int) $store['id_mag'] ?>" <?= in_array((int) $store['id_mag'], $selectedStoreIds, true) ? 'checked' : '' ?>>
                                                                                    <span><?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?></span>
                                                                                </label>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                        <span class="validation-message" data-error-for="id_mag"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="name_march">Product Name</label>
                                                                        <input id="name_march" name="name_march" type="text" class="form-control" placeholder="Protein" value="<?= htmlspecialchars($isEditing ? (string) $editingProduct['name_march'] : '', ENT_QUOTES) ?>">
                                                                        <span class="validation-message" data-error-for="name_march"></span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label>Food Categories</label>
                                                                <div class="admin-checkbox-list" data-category-checkboxes>
                                                                    <?php foreach ($categories as $category): ?>
                                                                        <label class="admin-checkbox-option">
                                                                            <input type="checkbox" name="id_categ[]" value="<?= (int) $category['id_categ'] ?>" <?= in_array((int) $category['id_categ'], $selectedCategoryIds, true) ? 'checked' : '' ?>>
                                                                            <span><?= htmlspecialchars($category['name_categ'], ENT_QUOTES) ?></span>
                                                                        </label>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                                <span class="validation-message" data-error-for="id_categ"></span>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="description_march">Description</label>
                                                                <textarea id="description_march" name="description_march" rows="4" class="form-control" placeholder="Short product description"><?= htmlspecialchars($isEditing ? (string) $editingProduct['description_march'] : '', ENT_QUOTES) ?></textarea>
                                                                <span class="validation-message" data-error-for="description_march"></span>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label for="price_march">Price</label>
                                                                        <input id="price_march" name="price_march" type="text" class="form-control" placeholder="45.500" value="<?= htmlspecialchars($isEditing ? $formatPrice($editingProduct['price_march']) : '', ENT_QUOTES) ?>">
                                                                        <span class="validation-message" data-error-for="price_march"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label for="quantity_march">Quantity</label>
                                                                        <input id="quantity_march" name="quantity_march" type="text" class="form-control" placeholder="12" value="<?= htmlspecialchars($isEditing ? (string) $editingProduct['quantity_march'] : '', ENT_QUOTES) ?>">
                                                                        <span class="validation-message" data-error-for="quantity_march"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label for="date_expiration_march">Expiration Date</label>
                                                                        <input id="date_expiration_march" name="date_expiration_march" type="date" class="form-control" value="<?= htmlspecialchars($isEditing ? (string) $editingProduct['date_expiration_march'] : '', ENT_QUOTES) ?>">
                                                                        <span class="validation-message" data-error-for="date_expiration_march"></span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="point_acces_march">Point d'acces</label>
                                                                        <input id="point_acces_march" name="point_acces_march" type="text" class="form-control" placeholder="A1" value="<?= htmlspecialchars($isEditing ? (string) $editingProduct['point_acces_march'] : '', ENT_QUOTES) ?>">
                                                                        <span class="validation-message" data-error-for="point_acces_march"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="img_march">Product Image</label>
                                                                        <input id="img_march" name="img_march" type="file" class="form-control">
                                                                        <?php if ($isEditing): ?>
                                                                            <small class="text-muted d-block m-t-5">Leave this empty to keep the current image.</small>
                                                                        <?php endif; ?>
                                                                        <span class="validation-message" data-error-for="img_march"></span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="admin-preview m-b-20" data-image-preview>
                                                                <?php if ($isEditing): ?>
                                                                    <img src="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/Controller/MARKETPLACE_MODULE/Marchandise_Controller.php?action=image&id=<?= (int) $editingProduct['id_march'] ?>" alt="<?= htmlspecialchars((string) $editingProduct['name_march'], ENT_QUOTES) ?>">
                                                                <?php else: ?>
                                                                    <span class="text-muted">Image preview will appear here</span>
                                                                <?php endif; ?>
                                                            </div>

                                                            <button type="submit" class="btn btn-primary waves-effect waves-light"><?= $isEditing ? 'Update Product' : 'Save Product' ?></button>
                                                            <?php if ($isEditing): ?>
                                                                <a href="<?= $buildProductPageUrl($currentPage) ?>" class="btn btn-default waves-effect m-l-10">Cancel Edit</a>
                                                            <?php endif; ?>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-5 col-md-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Available Stores</h5>
                                                        <span>Products can be linked to one or more magasins.</span>
                                                    </div>
                                                    <div class="card-block">
                                                        <?php if ($stores === []): ?>
                                                            <div class="alert alert-warning m-b-0">No magasin records were found in the database yet.</div>
                                                        <?php else: ?>
                                                            <?php foreach ($stores as $store): ?>
                                                                <div class="border rounded p-3 m-b-15">
                                                                    <h6 class="m-b-5"><?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?></h6>
                                                                    <p class="m-b-0 text-muted"><?= htmlspecialchars($store['adress_mag'], ENT_QUOTES) ?></p>
                                                                    <small><?= htmlspecialchars($store['email_mag'], ENT_QUOTES) ?> | <?= htmlspecialchars((string) $store['phone_mag'], ENT_QUOTES) ?></small>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card foovia-admin-products-card" id="products-table">
                                            <div class="card-header">
                                                <h5>Recently Published Products</h5>
                                                <span>Showing <?= $totalProducts === 0 ? 0 : $pageOffset + 1 ?>-<?= min($pageOffset + $productsPerPage, $totalProducts) ?> of <?= $totalProducts ?> products.</span>
                                            </div>
                                            <div class="card-block table-border-style">
                                                <div class="table-responsive">
                                                    <table class="table table-hover foovia-admin-products-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Image</th>
                                                                <th>Name</th>
                                                                <th>Store</th>
                                                                <th>Price</th>
                                                                <th>Stock</th>
                                                                <th>Expires</th>
                                                                <th>Reservations</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if ($paginatedProducts === []): ?>
                                                                <tr><td colspan="8" class="text-center">No products available yet.</td></tr>
                                                            <?php else: ?>
                                                                <?php foreach ($paginatedProducts as $product): ?>
                                                                    <tr>
                                                                        <td><img class="admin-table-thumb" src="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/Controller/MARKETPLACE_MODULE/Marchandise_Controller.php?action=image&id=<?= (int) $product['id_march'] ?>" alt="<?= htmlspecialchars($product['name_march'], ENT_QUOTES) ?>"></td>
                                                                        <td><strong><?= htmlspecialchars($product['name_march'], ENT_QUOTES) ?></strong><br><small><?= htmlspecialchars($product['description_march'], ENT_QUOTES) ?></small></td>
                                                                        <td><?= htmlspecialchars($product['store_names'] ?? 'No store', ENT_QUOTES) ?></td>
                                                                        <td><?= htmlspecialchars($formatPrice($product['price_march']), ENT_QUOTES) ?> TND</td>
                                                                        <td><?= (int) $product['quantity_march'] ?></td>
                                                                        <td><?= htmlspecialchars($product['date_expiration_march'], ENT_QUOTES) ?></td>
                                                                        <td>
                                                                            <strong><?= (int) ($product['reserved_count_march'] ?? 0) ?></strong>
                                                                            <?php if (!empty($reservationsByProduct[(int) $product['id_march']])): ?>
                                                                                <br><small><?= htmlspecialchars($reservationsByProduct[(int) $product['id_march']], ENT_QUOTES) ?></small>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                        <td class="admin-action-cell">
                                                                            <a href="products.php?page=<?= $currentPage ?>&edit=<?= (int) $product['id_march'] ?>" class="admin-action-btn admin-action-modify">Modify</a>
                                                                            <form action="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/Controller/MARKETPLACE_MODULE/Marchandise_Controller.php?action=reset_reservations" method="post">
                                                                                <input type="hidden" name="id_march" value="<?= (int) $product['id_march'] ?>">
                                                                                <button type="submit" class="admin-action-btn admin-action-reset">Reset</button>
                                                                            </form>
                                                                            <form action="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/Controller/MARKETPLACE_MODULE/Marchandise_Controller.php?action=delete" method="post" data-delete-product-form data-product-name="<?= htmlspecialchars($product['name_march'], ENT_QUOTES) ?>">
                                                                                <input type="hidden" name="id_march" value="<?= (int) $product['id_march'] ?>">
                                                                                <button type="submit" class="admin-action-btn admin-action-delete">Delete</button>
                                                                            </form>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <?php if ($totalPages > 1): ?>
                                                    <nav aria-label="Products pagination" style="margin-top: 20px;">
                                                        <ul class="pagination">
                                                            <?php if ($currentPage > 1): ?>
                                                                <li class="page-item">
                                                                    <a class="page-link" href="<?= $buildProductPageUrl(1) ?>">First</a>
                                                                </li>
                                                                <li class="page-item">
                                                                    <a class="page-link" href="<?= $buildProductPageUrl($currentPage - 1) ?>">Previous</a>
                                                                </li>
                                                            <?php endif; ?>
                                                            <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                                                                <li class="page-item <?= $page === $currentPage ? 'active' : '' ?>">
                                                                    <a class="page-link" href="<?= $buildProductPageUrl($page) ?>"><?= $page ?></a>
                                                                </li>
                                                            <?php endfor; ?>
                                                            <?php if ($currentPage < $totalPages): ?>
                                                                <li class="page-item">
                                                                    <a class="page-link" href="<?= $buildProductPageUrl($currentPage + 1) ?>">Next</a>
                                                                </li>
                                                                <li class="page-item">
                                                                    <a class="page-link" href="<?= $buildProductPageUrl($totalPages) ?>">Last</a>
                                                                </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </nav>
                                                    <p class="text-muted" style="margin-top: 10px;">Page <?= $currentPage ?> of <?= $totalPages ?> (<?= $totalProducts ?> total products)</p>
                                                <?php endif; ?>
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

    <script type="text/javascript" src="../USER_MODULE/assets/js/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="../USER_MODULE/assets/js/jquery-ui/jquery-ui.min.js"></script>
    <script type="text/javascript" src="../USER_MODULE/assets/js/popper.js/popper.min.js"></script>
    <script type="text/javascript" src="../USER_MODULE/assets/js/bootstrap/js/bootstrap.min.js"></script>
    <script src="../USER_MODULE/assets/pages/waves/js/waves.min.js"></script>
    <script type="text/javascript" src="../USER_MODULE/assets/js/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script type="text/javascript" src="../USER_MODULE/assets/js/modernizr/modernizr.js"></script>
    <script type="text/javascript" src="../USER_MODULE/assets/js/SmoothScroll.js"></script>
    <script src="../USER_MODULE/assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="../USER_MODULE/assets/js/pcoded.min.js"></script>
    <script src="../USER_MODULE/assets/js/vertical/vertical-layout.min.js"></script>
    <script src="../USER_MODULE/assets/js/script.js"></script>
    <script src="../../front_office/MARKETPLACE_MODULE/assets/js/backoffice-validation.js?v=description-fix-4"></script>
    <script src="../../front_office/MARKETPLACE_MODULE/assets/js/backoffice-actions.js"></script>
</body>
</html>
