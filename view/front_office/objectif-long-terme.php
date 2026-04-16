<?php
require_once '../../controller/ObjectifLongTerme_Controller.php';

$controller = new ObjectifLongTerme_Controller();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id_obj'])) {
  $controller->delete_objectif((int) $_POST['delete_id_obj']);
  header('Location: objectif-long-terme.php');
  exit;
}

$objectifs = $controller->list_objectifs();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>View long-term goals</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta name="description" content="">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/vendor.css">
    <link rel="stylesheet" type="text/css" href="style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Open+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

    <style>
      .consultation-hero {
        background-image: url('images/banner-1.jpg');
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
      }

      .consultation-hero .hero-overlay {
        background: rgba(255, 255, 255, 0.84);
      }

      .consultation-card {
        border: 1px solid #f0f0f0;
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.06);
      }

      .consultation-card .table thead th {
        white-space: nowrap;
      }

      .consultation-card .table tbody td {
        white-space: nowrap;
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
            <a href="index.html" class="d-inline-block">
              <img src="images/logo.svg" alt="logo" class="img-fluid" style="max-height: 54px;">
            </a>
          </div>

          <div class="col-12 col-md-5 mb-3 mb-md-0">
            <div class="search-bar row bg-light p-2 rounded-4">
              <div class="col-11">
                <form class="text-center" action="index.html" method="post">
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
                <a href="index.html" class="nav-link top-nav-link">Home</a>
              </li>
              <li class="nav-item">
                <a href="../back_office/form-elements-component.php" class="nav-link top-nav-link">Add</a>
              </li>
              <li class="nav-item active">
                <a href="objectif-long-terme.php" class="nav-link top-nav-link text-primary">View</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </header>

    <section class="consultation-hero">
      <div class="hero-overlay py-5">
        <div class="container-lg py-4">
          <p class="text-uppercase fw-semibold text-secondary mb-2">Foovia goals</p>
          <h1 class="display-4 mb-3"><span class="fw-bold text-primary">View</span> long-term goals</h1>
          <p class="fs-5 mb-0">Track your nutrition goals, physical activity, and progress on one page.</p>
        </div>
      </div>
    </section>

    <section class="py-5 bg-light">
      <div class="container-lg">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
          <div>
            <p class="text-uppercase text-muted mb-1">Overview</p>
            <h2 class="section-title mb-0">Goal list</h2>
          </div>
        </div>

        <div class="consultation-card bg-white p-0 overflow-hidden">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Target value</th>
                    <th>Initial value</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <th>Reminder</th>
                    <th>Sport</th>
                    <th>Diet</th>
                    <th>Calories</th>
                    <th>Fat</th>
                    <th>Protein</th>
                    <th>Carbs</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($objectifs)): ?>
                    <?php foreach ($objectifs as $objectif): ?>
                      <tr>
                        <td><?php echo htmlspecialchars((string) $objectif['id_obj']); ?></td>
                        <td><?php echo htmlspecialchars($objectif['type_obj']); ?></td>
                        <td><?php echo htmlspecialchars((string) $objectif['val_cible_obj']); ?></td>
                        <td><?php echo htmlspecialchars((string) $objectif['val_init_obj']); ?></td>
                        <td><?php echo htmlspecialchars($objectif['date_deb_obj']); ?></td>
                        <td><?php echo htmlspecialchars($objectif['date_fin_obj']); ?></td>
                        <td><?php echo htmlspecialchars(str_replace(['en_attente', 'en_cours', 'termine'], ['pending', 'in progress', 'completed'], (string) $objectif['status_obj'])); ?></td>
                        <td><?php echo htmlspecialchars((string) $objectif['frequency_rappel_obj']); ?></td>
                        <td><?php echo htmlspecialchars((string) $objectif['consistancy_sport_obj']); ?></td>
                        <td><?php echo htmlspecialchars((string) $objectif['consistency_alim_obj']); ?></td>
                        <td><?php echo htmlspecialchars((string) $objectif['obj_cal_obj']); ?></td>
                        <td><?php echo htmlspecialchars((string) $objectif['obj_fat_obj']); ?></td>
                        <td><?php echo htmlspecialchars((string) $objectif['obj_prot_obj']); ?></td>
                        <td><?php echo htmlspecialchars((string) $objectif['obj_carb_obj']); ?></td>
                        <td class="pe-3">
                          <a href="../back_office/edit-objectif-long-terme.php?id_obj=<?php echo urlencode((string) $objectif['id_obj']); ?>" class="btn btn-sm btn-primary rounded-pill me-1">Edit</a>
                          <button
                            type="button"
                            class="btn btn-sm btn-danger rounded-pill"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteConfirmModal"
                            data-id="<?php echo htmlspecialchars((string) $objectif['id_obj']); ?>"
                          >
                            Delete
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="15" class="text-center py-4 text-muted">No long-term goals found.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm deletion</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Do you really want to delete this goal?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
            <form id="deleteObjectifForm" method="post" action="" class="d-inline">
              <input type="hidden" name="delete_id_obj" id="delete_id_obj" value="">
              <button type="submit" class="btn btn-danger rounded-pill">Delete</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <footer class="py-5">
      <div class="container-lg">
        <div class="row g-4">
          <div class="col-lg-4 col-md-6">
            <img src="images/logo.svg" width="210" height="60" alt="logo">
            <p class="mt-3 mb-0">Foovia helps you manage your health goals with clarity and consistency.</p>
          </div>
          <div class="col-lg-2 col-md-6">
            <h5 class="widget-title">Navigation</h5>
            <ul class="menu-list list-unstyled">
              <li class="menu-item"><a href="index.html" class="nav-link">Home</a></li>
              <li class="menu-item"><a href="../back_office/form-elements-component.php" class="nav-link">Add</a></li>
              <li class="menu-item"><a href="objectif-long-terme.php" class="nav-link">View</a></li>
            </ul>
          </div>
          <div class="col-lg-3 col-md-6">
            <h5 class="widget-title">Tracking</h5>
            <p class="mb-0">View target intake for calories, fat, protein, and carbs directly in the table.</p>
          </div>
          <div class="col-lg-3 col-md-6">
            <h5 class="widget-title">Newsletter</h5>
            <form class="d-flex mt-3 gap-0" action="index.html">
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

    <script src="js/jquery-1.11.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <script src="js/plugins.js"></script>
    <script src="js/script.js"></script>
    <script>
      const deleteConfirmModal = document.getElementById('deleteConfirmModal');
      if (deleteConfirmModal) {
        deleteConfirmModal.addEventListener('show.bs.modal', function (event) {
          const triggerButton = event.relatedTarget;
          const objectifId = triggerButton.getAttribute('data-id');
          const hiddenInput = document.getElementById('delete_id_obj');
          if (hiddenInput) {
            hiddenInput.value = objectifId;
          }
        });
      }
    </script>
  </body>
</html>