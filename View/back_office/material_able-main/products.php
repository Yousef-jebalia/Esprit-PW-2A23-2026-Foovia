<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../Model/Marchandise.php';

$stores = marketplace_fetch_stores();
$products = marketplace_fetch_products();
$summary = marketplace_fetch_summary();
$status = (string) ($_GET['status'] ?? '');
$editId = (int) ($_GET['edit'] ?? 0);
$editingProduct = $editId > 0 ? marketplace_fetch_product_by_id($editId) : null;
$isEditing = $editingProduct !== null;

$message = match ($status) {
    'success' => ['class' => 'alert-success', 'text' => 'Product added successfully. It is now available in the front office.'],
    'updated' => ['class' => 'alert-success', 'text' => 'Product updated successfully in both back office and front office.'],
    'deleted' => ['class' => 'alert-success', 'text' => 'Product removed successfully from the back office and front office.'],
    'error' => ['class' => 'alert-warning', 'text' => 'Some product information is missing. Please complete the form and try again.'],
    'dberror' => ['class' => 'alert-danger', 'text' => 'The product could not be saved. Check the DB connection and table structure.'],
    'deleteerror' => ['class' => 'alert-danger', 'text' => 'The product could not be deleted. Please try again.'],
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
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">
    <link rel="stylesheet" href="assets/pages/waves/css/waves.min.css" type="text/css" media="all">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/icon/themify-icons/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="assets/icon/icofont/css/icofont.css">
    <link rel="stylesheet" type="text/css" href="assets/icon/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/jquery.mCustomScrollbar.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="../../../assets/css/marketplace.css">
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
                            <span class="foovia-admin-brand-mark">F</span>
                            <span class="foovia-admin-name">Foovia</span>
                        </a>
                    </div>
                    <div class="navbar-container container-fluid">
                        <ul class="nav-left">
                            <li><div class="sidebar_toggle"><a href="javascript:void(0)"><i class="ti-menu"></i></a></div></li>
                        </ul>
                        <ul class="nav-right">
                            <li class="user-profile header-notification">
                                <a href="#!" class="waves-effect waves-light">
                                    <img src="assets/images/avatar-4.jpg" class="img-radius" alt="Administrator">
                                    <span>Foovia Admin</span>
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
                                <img class="img-80 img-radius" src="assets/images/avatar-4.jpg" alt="Admin profile">
                                <div class="user-details"><span>Foovia Team</span></div>
                            </div>
                            <div class="pcoded-navigation-label">Foovia</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="active">
                                    <a href="products.php" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-package"></i></span>
                                        <span class="pcoded-mtext">Products</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="../../front_office/organic-1.0.0/marketplace.php" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-shopping-cart"></i></span>
                                        <span class="pcoded-mtext">Front Office</span>
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
                                                        <span><?= $isEditing ? 'Select a product, modify the fields below, then save the changes.' : 'The HTML stays clean while the validation lives in JavaScript only.' ?></span>
                                                    </div>
                                                    <div class="card-block">
                                                        <form action="../../../Controller/Marchandise_Controller.php?action=save" method="post" enctype="multipart/form-data" data-product-form data-editing-mode="<?= $isEditing ? 'true' : 'false' ?>">
                                                            <input type="hidden" name="id_march" value="<?= $isEditing ? (int) $editingProduct['id_march'] : 0 ?>">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="id_mag">Magasin</label>
                                                                        <select id="id_mag" name="id_mag" class="form-control form-select">
                                                                            <option value="">Select a store</option>
                                                                            <?php foreach ($stores as $store): ?>
                                                                                <option value="<?= (int) $store['id_mag'] ?>" <?= $isEditing && (int) $editingProduct['id_mag'] === (int) $store['id_mag'] ? 'selected' : '' ?>><?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?></option>
                                                                            <?php endforeach; ?>
                                                                        </select>
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
                                                                <label for="description_march">Description</label>
                                                                <textarea id="description_march" name="description_march" rows="4" class="form-control" placeholder="Short product description"><?= htmlspecialchars($isEditing ? (string) $editingProduct['description_march'] : '', ENT_QUOTES) ?></textarea>
                                                                <span class="validation-message" data-error-for="description_march"></span>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label for="price_march">Price</label>
                                                                        <input id="price_march" name="price_march" type="text" class="form-control" placeholder="45" value="<?= htmlspecialchars($isEditing ? (string) $editingProduct['price_march'] : '', ENT_QUOTES) ?>">
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
                                                                    <img src="../../../Controller/Marchandise_Controller.php?action=image&id=<?= (int) $editingProduct['id_march'] ?>" alt="<?= htmlspecialchars((string) $editingProduct['name_march'], ENT_QUOTES) ?>">
                                                                <?php else: ?>
                                                                    <span class="text-muted">Image preview will appear here</span>
                                                                <?php endif; ?>
                                                            </div>

                                                            <button type="submit" class="btn btn-primary waves-effect waves-light"><?= $isEditing ? 'Update Product' : 'Save Product' ?></button>
                                                            <?php if ($isEditing): ?>
                                                                <a href="products.php" class="btn btn-default waves-effect m-l-10">Cancel Edit</a>
                                                            <?php endif; ?>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-5 col-md-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Available Stores</h5>
                                                        <span>The selected store is linked through the <code>vendre</code> table.</span>
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

                                        <div class="card">
                                            <div class="card-header">
                                                <h5>Recently Published Products</h5>
                                                <span>The same items are rendered in the front office page.</span>
                                            </div>
                                            <div class="card-block table-border-style">
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Image</th>
                                                                <th>Name</th>
                                                                <th>Store</th>
                                                                <th>Price</th>
                                                                <th>Stock</th>
                                                                <th>Expires</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if ($products === []): ?>
                                                                <tr><td colspan="7" class="text-center">No products available yet.</td></tr>
                                                            <?php else: ?>
                                                                <?php foreach ($products as $product): ?>
                                                                    <tr>
                                                                        <td><img class="admin-table-thumb" src="../../../Controller/Marchandise_Controller.php?action=image&id=<?= (int) $product['id_march'] ?>" alt="<?= htmlspecialchars($product['name_march'], ENT_QUOTES) ?>"></td>
                                                                        <td><strong><?= htmlspecialchars($product['name_march'], ENT_QUOTES) ?></strong><br><small><?= htmlspecialchars($product['description_march'], ENT_QUOTES) ?></small></td>
                                                                        <td><?= htmlspecialchars($product['name_mag'] ?? 'No store', ENT_QUOTES) ?></td>
                                                                        <td><?= (int) $product['price_march'] ?> TND</td>
                                                                        <td><?= (int) $product['quantity_march'] ?></td>
                                                                        <td><?= htmlspecialchars($product['date_expiration_march'], ENT_QUOTES) ?></td>
                                                                        <td>
                                                                            <a href="products.php?edit=<?= (int) $product['id_march'] ?>" class="btn btn-info btn-sm waves-effect waves-light m-r-5">Modify</a>
                                                                            <form action="../../../Controller/Marchandise_Controller.php?action=delete" method="post" data-delete-product-form data-product-name="<?= htmlspecialchars($product['name_march'], ENT_QUOTES) ?>">
                                                                                <input type="hidden" name="id_march" value="<?= (int) $product['id_march'] ?>">
                                                                                <button type="submit" class="btn btn-danger btn-sm waves-effect waves-light">Delete</button>
                                                                            </form>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
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
        </div>
    </div>

    <script type="text/javascript" src="assets/js/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="assets/js/jquery-ui/jquery-ui.min.js"></script>
    <script type="text/javascript" src="assets/js/popper.js/popper.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/pages/waves/js/waves.min.js"></script>
    <script type="text/javascript" src="assets/js/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script type="text/javascript" src="assets/js/modernizr/modernizr.js"></script>
    <script type="text/javascript" src="assets/js/SmoothScroll.js"></script>
    <script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script src="assets/js/vertical/vertical-layout.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="../../../assets/js/backoffice-validation.js"></script>
    <script src="../../../assets/js/backoffice-actions.js"></script>
</body>
</html>
