<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../Model/Magasin.php';

$magasinModel = new Magasin();
$stores = $magasinModel->fetchAll();
$status = (string) ($_GET['status'] ?? '');
$editId = (int) ($_GET['edit'] ?? 0);
$editingStore = $editId > 0 ? $magasinModel->findById($editId) : null;
$isEditing = $editingStore !== null;

$message = match ($status) {
    'success' => ['class' => 'alert-success', 'text' => 'Magasin added successfully.'],
    'updated' => ['class' => 'alert-success', 'text' => 'Magasin updated successfully.'],
    'deleted' => ['class' => 'alert-success', 'text' => 'Magasin removed successfully.'],
    'dberror' => ['class' => 'alert-danger', 'text' => 'The magasin could not be saved. Check the database connection and table structure.'],
    'deleteerror' => ['class' => 'alert-danger', 'text' => 'The magasin could not be deleted. Please try again.'],
    default => null,
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Foovia Magasins</title>
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/pages/waves/css/waves.min.css" type="text/css" media="all">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/icon/themify-icons/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="assets/icon/icofont/css/icofont.css">
    <link rel="stylesheet" type="text/css" href="assets/icon/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/jquery.mCustomScrollbar.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="../../assets/css/marketplace.css?v=foovia-welcome-style-1">
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
                        <a href="products.php" class="foovia-admin-brand">
                            <img src="../../assets/imges-autre/pic_logo.png" class="foovia-admin-logo-img" style="height: 22px; width: auto; max-height: 22px;" alt="Foovia logo">
                            <img src="../../assets/imges-autre/pic_name.png" class="foovia-admin-name-img" style="height: 12px; width: auto; max-height: 12px;" alt="Foovia">
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
                                <li>
                                    <a href="products.php" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-package"></i></span>
                                        <span class="pcoded-mtext">Products</span>
                                    </a>
                                </li>
                                <li class="active">
                                    <a href="magasins.php" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-home"></i></span>
                                        <span class="pcoded-mtext">Magasins</span>
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
                                            <h5 class="m-b-10">Foovia Magasin Management</h5>
                                            <p class="m-b-0">Add and manage the stores that can sell marketplace products.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pcoded-inner-content">
                            <div class="main-body">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <?php if ($message !== null): ?>
                                            <div class="alert <?= htmlspecialchars($message['class'], ENT_QUOTES) ?> admin-alert"><?= htmlspecialchars($message['text'], ENT_QUOTES) ?></div>
                                        <?php endif; ?>

                                        <div class="row">
                                            <div class="col-xl-5 col-md-12">
                                                <div class="card admin-form-card">
                                                    <div class="card-header">
                                                        <h5><?= $isEditing ? 'Modify Selected Magasin' : 'Add New Magasin' ?></h5>
                                                        <span>Form validation is handled only by JavaScript.</span>
                                                    </div>
                                                    <div class="card-block">
                                                        <form action="../../../Controller/Magasin_Controller.php?action=save" method="post" enctype="multipart/form-data" data-store-form data-editing-mode="<?= $isEditing ? 'true' : 'false' ?>">
                                                            <input type="hidden" name="id_mag" value="<?= $isEditing ? (int) $editingStore['id_mag'] : 0 ?>">

                                                            <div class="form-group">
                                                                <label for="name_mag">Magasin Name</label>
                                                                <input id="name_mag" name="name_mag" type="text" class="form-control" placeholder="Foovia Tunis" value="<?= htmlspecialchars($isEditing ? (string) $editingStore['name_mag'] : '', ENT_QUOTES) ?>">
                                                                <span class="validation-message" data-error-for="name_mag"></span>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="email_mag">Email</label>
                                                                <input id="email_mag" name="email_mag" type="text" class="form-control" placeholder="store@foovia.tn" value="<?= htmlspecialchars($isEditing ? (string) $editingStore['email_mag'] : '', ENT_QUOTES) ?>">
                                                                <span class="validation-message" data-error-for="email_mag"></span>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="phone_mag">Phone</label>
                                                                <input id="phone_mag" name="phone_mag" type="text" class="form-control" placeholder="22111222" value="<?= htmlspecialchars($isEditing ? (string) $editingStore['phone_mag'] : '', ENT_QUOTES) ?>">
                                                                <span class="validation-message" data-error-for="phone_mag"></span>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="adress_mag">Address</label>
                                                                <textarea id="adress_mag" name="adress_mag" rows="4" class="form-control" placeholder="Store address"><?= htmlspecialchars($isEditing ? (string) $editingStore['adress_mag'] : '', ENT_QUOTES) ?></textarea>
                                                                <span class="validation-message" data-error-for="adress_mag"></span>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="img_mag">Magasin Image</label>
                                                                <input id="img_mag" name="img_mag" type="file" class="form-control">
                                                                <?php if ($isEditing && (int) ($editingStore['has_image'] ?? 0) === 1): ?>
                                                                    <small class="text-muted d-block m-t-5">Leave this empty to keep the current image.</small>
                                                                <?php endif; ?>
                                                                <span class="validation-message" data-error-for="img_mag"></span>
                                                            </div>

                                                            <div class="admin-preview admin-store-preview m-b-20" data-store-image-preview>
                                                                <?php if ($isEditing && (int) ($editingStore['has_image'] ?? 0) === 1): ?>
                                                                    <img src="../../../Controller/Magasin_Controller.php?action=image&id=<?= (int) $editingStore['id_mag'] ?>" alt="<?= htmlspecialchars((string) $editingStore['name_mag'], ENT_QUOTES) ?>">
                                                                <?php else: ?>
                                                                    <span class="text-muted">Magasin image preview will appear here</span>
                                                                <?php endif; ?>
                                                            </div>

                                                            <button type="submit" class="btn btn-primary waves-effect waves-light"><?= $isEditing ? 'Update Magasin' : 'Save Magasin' ?></button>
                                                            <?php if ($isEditing): ?>
                                                                <a href="magasins.php" class="btn btn-default waves-effect m-l-10">Cancel Edit</a>
                                                            <?php endif; ?>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-7 col-md-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Available Magasins</h5>
                                                        <span>These stores become checkbox choices in the product form.</span>
                                                    </div>
                                                    <div class="card-block table-border-style">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Image</th>
                                                                        <th>Name</th>
                                                                        <th>Email</th>
                                                                        <th>Phone</th>
                                                                        <th>Address</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if ($stores === []): ?>
                                                                        <tr><td colspan="6" class="text-center">No magasins available yet.</td></tr>
                                                                    <?php else: ?>
                                                                        <?php foreach ($stores as $store): ?>
                                                                            <tr>
                                                                                <td>
                                                                                    <?php if ((int) ($store['has_image'] ?? 0) === 1): ?>
                                                                                        <img class="admin-table-thumb" src="../../../Controller/Magasin_Controller.php?action=image&id=<?= (int) $store['id_mag'] ?>" alt="<?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?>">
                                                                                    <?php else: ?>
                                                                                        <span class="store-thumb-empty">No image</span>
                                                                                    <?php endif; ?>
                                                                                </td>
                                                                                <td><strong><?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?></strong></td>
                                                                                <td><?= htmlspecialchars($store['email_mag'], ENT_QUOTES) ?></td>
                                                                                <td><?= htmlspecialchars((string) $store['phone_mag'], ENT_QUOTES) ?></td>
                                                                                <td><?= htmlspecialchars($store['adress_mag'], ENT_QUOTES) ?></td>
                                                                                <td class="admin-action-cell">
                                                                                    <a href="magasins.php?edit=<?= (int) $store['id_mag'] ?>" class="admin-action-btn admin-action-modify">Modify</a>
                                                                                    <form action="../../../Controller/Magasin_Controller.php?action=delete" method="post" data-delete-store-form data-store-name="<?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?>">
                                                                                        <input type="hidden" name="id_mag" value="<?= (int) $store['id_mag'] ?>">
                                                                                        <button type="submit" class="admin-action-btn admin-action-delete">Delete</button>
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
    <script src="../../assets/js/magasin-validation.js?v=store-validation-2"></script>
    <script src="../../assets/js/backoffice-actions.js"></script>
</body>
</html>
