<?php
require_once '../../controller/ObjectifLongTerme_Controller.php';

$controller = new ObjectifLongTerme_Controller();
$error_message = '';

$id_obj = 0;
if (isset($_GET['id_obj'])) {
    $id_obj = (int) $_GET['id_obj'];
} elseif (isset($_POST['id_obj'])) {
    $id_obj = (int) $_POST['id_obj'];
}

if ($id_obj <= 0) {
    header('Location: ../front_office/objectif-long-terme.php');
    exit;
}

$objectif = $controller->get_objectif_by_id($id_obj);
if (!$objectif) {
    header('Location: ../front_office/objectif-long-terme.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'val_cible_obj' => (float) ($_POST['val_cible_obj'] ?? 0),
        'val_init_obj' => (float) ($_POST['val_init_obj'] ?? 0),
        'date_deb_obj' => $_POST['date_deb_obj'] ?? '',
        'date_fin_obj' => $_POST['date_fin_obj'] ?? '',
        'obj_cal_obj' => (float) ($_POST['obj_cal_obj'] ?? 0),
        'obj_fat_obj' => (float) ($_POST['obj_fat_obj'] ?? 0),
        'obj_prot_obj' => (float) ($_POST['obj_prot_obj'] ?? 0),
        'obj_carb_obj' => (float) ($_POST['obj_carb_obj'] ?? 0)
    ];

    if ($data['val_cible_obj'] <= 0 || $data['val_init_obj'] <= 0 || $data['obj_cal_obj'] <= 0 || $data['obj_fat_obj'] <= 0 || $data['obj_prot_obj'] <= 0 || $data['obj_carb_obj'] <= 0) {
        $error_message = 'All numeric values must be strictly positive.';
    } elseif (empty($data['date_deb_obj']) || empty($data['date_fin_obj'])) {
        $error_message = 'Start and end dates are required.';
    } elseif ($data['date_deb_obj'] > $data['date_fin_obj']) {
        $error_message = 'The start date cannot be later than the end date.';
    } else {
        $updated = $controller->update_objectif_fields($id_obj, $data);
        if ($updated) {
            header('Location: ../front_office/objectif-long-terme.php');
            exit;
        }
        $error_message = 'The update failed.';
    }

    $objectif = array_merge($objectif, $data);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit long-term goal</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../front_office/css/vendor.css">
    <link rel="stylesheet" type="text/css" href="../front_office/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Open+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

    <style>
        .edit-hero {
            background-image: url('../front_office/images/banner-1.jpg');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
        }

        .edit-hero .hero-overlay {
            background: rgba(255, 255, 255, 0.84);
        }

        .form-shell {
            border: 1px solid #f0f0f0;
            border-radius: 16px;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.06);
        }

        .form-section-title {
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #364127;
            margin-bottom: 1rem;
            margin-top: 0.5rem;
        }

        .top-nav-link {
            font-weight: 700;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <header>
        <div class="container-fluid">
            <div class="row py-3 border-bottom align-items-center">
                <div class="col-12 col-md-3 text-center text-md-start mb-3 mb-md-0">
                    <a href="../front_office/index.html" class="d-inline-block">
                        <img src="../front_office/images/logo.svg" alt="logo" class="img-fluid" style="max-height: 54px;">
                    </a>
                </div>

                <div class="col-12 col-md-5 mb-3 mb-md-0">
                    <div class="search-bar row bg-light p-2 rounded-4">
                        <div class="col-11">
                            <form class="text-center" action="../front_office/index.html" method="post">
                                <input type="text" class="form-control border-0 bg-transparent" placeholder="Search in Foovia">
                            </form>
                        </div>
                        <div class="col-1 d-flex align-items-center justify-content-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M21.71 20.29L18 16.61A9 9 0 1 0 16.61 18l3.68 3.68a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.39ZM11 18a7 7 0 1 1 7-7a7 7 0 0 1-7 7Z"/></svg>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <ul class="navbar-nav list-unstyled d-flex flex-row gap-4 justify-content-center justify-content-md-end align-items-center mb-0">
                        <li class="nav-item">
                            <a href="../front_office/index.html" class="nav-link top-nav-link">Home</a>
                        </li>
                        <li class="nav-item">
                            <a href="form-elements-component.php" class="nav-link top-nav-link">Add</a>
                        </li>
                        <li class="nav-item active">
                            <a href="../front_office/objectif-long-terme.php" class="nav-link top-nav-link text-primary">View</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <section class="edit-hero">
        <div class="hero-overlay py-5">
            <div class="container-lg py-4">
                <p class="text-uppercase fw-semibold text-secondary mb-2">Foovia goals</p>
                <h1 class="display-4 mb-3"><span class="fw-bold text-primary">Edit</span> goal #<?php echo htmlspecialchars((string) $id_obj); ?></h1>
                <p class="fs-5 mb-0">This page is similar to Add. Some fields are locked to secure updates.</p>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container-lg">
            <div class="form-shell bg-white p-4 p-md-5">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <form method="post" action="">
                    <input type="hidden" name="id_obj" value="<?php echo htmlspecialchars((string) $id_obj); ?>">

                    <h6 class="form-section-title">Locked information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Goal ID</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars((string) $objectif['id_obj']); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">User ID</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars((string) $objectif['id_user']); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Goal type</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars((string) $objectif['type_obj']); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars(str_replace(['en_attente', 'en_cours', 'termine'], ['pending', 'in progress', 'completed'], (string) $objectif['status_obj'])); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reminder frequency</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars((string) $objectif['frequency_rappel_obj']); ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sport consistency</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars((string) $objectif['consistancy_sport_obj']); ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Diet consistency</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars((string) $objectif['consistency_alim_obj']); ?>" readonly>
                        </div>
                    </div>

                    <h6 class="form-section-title mt-4">Editable fields</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="val_init_obj">Initial value (kg)</label>
                            <input type="number" class="form-control" id="val_init_obj" name="val_init_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) $objectif['val_init_obj']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="val_cible_obj">Target value (kg)</label>
                            <input type="number" class="form-control" id="val_cible_obj" name="val_cible_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) $objectif['val_cible_obj']); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="date_deb_obj">Start date</label>
                            <input type="date" class="form-control" id="date_deb_obj" name="date_deb_obj" required value="<?php echo htmlspecialchars((string) $objectif['date_deb_obj']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="date_fin_obj">End date</label>
                            <input type="date" class="form-control" id="date_fin_obj" name="date_fin_obj" required value="<?php echo htmlspecialchars((string) $objectif['date_fin_obj']); ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="obj_cal_obj">Calories</label>
                            <input type="number" class="form-control" id="obj_cal_obj" name="obj_cal_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) $objectif['obj_cal_obj']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="obj_fat_obj">Fat</label>
                            <input type="number" class="form-control" id="obj_fat_obj" name="obj_fat_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) $objectif['obj_fat_obj']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="obj_prot_obj">Protein</label>
                            <input type="number" class="form-control" id="obj_prot_obj" name="obj_prot_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) $objectif['obj_prot_obj']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="obj_carb_obj">Carbs</label>
                            <input type="number" class="form-control" id="obj_carb_obj" name="obj_carb_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) $objectif['obj_carb_obj']); ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="../front_office/objectif-long-terme.php" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <footer class="py-5">
        <div class="container-lg">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <img src="../front_office/images/logo.svg" width="210" height="60" alt="logo">
                    <p class="mt-3 mb-0">Foovia helps you manage your health goals with clarity and consistency.</p>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5 class="widget-title">Navigation</h5>
                    <ul class="menu-list list-unstyled">
                        <li class="menu-item"><a href="../front_office/index.html" class="nav-link">Home</a></li>
                        <li class="menu-item"><a href="form-elements-component.php" class="nav-link">Add</a></li>
                        <li class="menu-item"><a href="../front_office/objectif-long-terme.php" class="nav-link">View</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="widget-title">Secure editing</h5>
                    <p class="mb-0">Only authorized values can be edited on this page.</p>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="widget-title">Newsletter</h5>
                    <form class="d-flex mt-3 gap-0" action="../front_office/index.html">
                        <input class="form-control rounded-start rounded-0 bg-light" type="email" placeholder="Email Address" aria-label="Email Address">
                        <button class="btn btn-dark rounded-end rounded-0" type="submit">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </footer>

    <div id="footer-bottom">
        <div class="container-lg">
            <div class="row">
                <div class="col-md-6 copyright">
                    <p>© 2026 Foovia. All rights reserved.</p>
                </div>
                <div class="col-md-6 credit-link text-start text-md-end">
                </div>
            </div>
        </div>
    </div>

    <script src="../front_office/js/jquery-1.11.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <script src="../front_office/js/plugins.js"></script>
    <script src="../front_office/js/script.js"></script>
</body>
</html>
