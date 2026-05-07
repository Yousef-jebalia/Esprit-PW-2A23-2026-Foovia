<?php
include_once __DIR__ . '/../../../Controller/SUPPORT_MODULE/Thread_Controller.php';
include_once __DIR__ . '/../../../Controller/SUPPORT_MODULE/Reclamtion_Controller.php';

$threadCtrl = new Thread_Controller();
$recCtrl    = new Controller_reclamation();
$error      = '';
$success    = '';

// Pre-fill from "Publish as Thread" button on claims list
$prefill_id_reclam   = (int) ($_GET['id_reclam'] ?? 0);
$prefill_description = '';
if ($prefill_id_reclam > 0) {
    $claim = $recCtrl->get_reclamation_by_id($prefill_id_reclam);
    if ($claim) {
        $prefill_description = $claim['description_reclam'] ?? '';
    }
}

// POST handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'create_thread') {
        $title      = trim($_POST['title']       ?? '');
        $desc       = trim($_POST['description'] ?? '');
        $id_reclam  = (int) ($_POST['id_reclam'] ?? 0);

        if ($title === '' || $desc === '') {
            $error = 'Title and description are required.';
        } else {
            try {
                $thread = new Thread(0, $id_reclam > 0 ? $id_reclam : null, $title, $desc, '', 0);
                $threadCtrl->create_thread($thread);
                $success = 'Thread published successfully!';
            } catch (Exception $e) {
                $error = 'Could not publish thread: ' . $e->getMessage();
            }
        }
    }

    if ($_POST['action'] === 'delete_thread') {
        $tid = (int) ($_POST['id_thread'] ?? 0);
        if ($tid > 0) {
            try {
                $threadCtrl->delete_thread($tid);
                $success = 'Thread deleted.';
            } catch (Exception $e) {
                $error = 'Could not delete thread: ' . $e->getMessage();
            }
        }
    }
}

$threads    = $threadCtrl->get_threads();
$claims     = $recCtrl->get_reclamations();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Thread Admin – Foovia</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/pages/waves/css/waves.min.css" type="text/css" media="all">
  <link rel="stylesheet" type="text/css" href="assets/icon/themify-icons/themify-icons.css">
  <link rel="stylesheet" type="text/css" href="assets/icon/feather/css/feather.css">
  <link rel="stylesheet" type="text/css" href="assets/icon/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" type="text/css" href="assets/css/style.css">
  <link rel="stylesheet" type="text/css" href="assets/css/jquery.mCustomScrollbar.css">
</head>
<body>
  <!-- Pre-loader -->
  <div class="theme-loader">
    <div class="loader-track">
      <div class="preloader-wrapper">
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

      <!-- Top navbar -->
      <nav class="navbar header-navbar pcoded-header">
        <div class="navbar-wrapper">
          <div class="navbar-logo">
            <a class="mobile-menu waves-effect waves-light" id="mobile-collapse" href="#!">
              <i class="ti-menu"></i>
            </a>
            <a href="index.html">FOOVIA Admin</a>
            <a class="mobile-options waves-effect waves-light"><i class="ti-more"></i></a>
          </div>
          <div class="navbar-container container-fluid">
            <ul class="nav-left">
              <li><div class="sidebar_toggle"><a href="javascript:void(0)"><i class="ti-menu"></i></a></div></li>
            </ul>
            <ul class="nav-right">
              <li class="user-profile header-notification">
                <a href="#!" class="waves-effect waves-light">
                  <img src="assets/images/avatar-4.jpg" class="img-radius" alt="Admin">
                  <span>Admin</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
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
                                            <a href="tabs.php" class="waves-effect waves-dark">
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
                                            <a href="hot_path_te3ek.php" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">MENU</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="hot_path_te3ek.php" class="waves-effect waves-dark">
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

      <div class="pcoded-main-container">
        <div class="pcoded-wrapper">

          <!-- Sidebar -->
          <nav class="pcoded-navbar">
            <div class="pcoded-inner-navbar main-menu">
              <ul class="pcoded-item pcoded-left-item">
                <li class="">
                  <a href="Support_admin.php" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-support"></i></span>
                    <span class="pcoded-mtext">Support</span>
                  </a>
                </li>
                <li class="active">
                  <a href="thread_admin_page.php" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-comments"></i></span>
                    <span class="pcoded-mtext">Threads</span>
                  </a>
                </li>
              </ul>
            </div>
          </nav>

          <!-- Page content -->
          <div class="pcoded-content">
            <div class="page-header">
              <div class="page-block">
                <div class="row align-items-center">
                  <div class="col-md-8">
                    <div class="page-header-title">
                      <h5 class="m-b-10">Thread Management</h5>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <ul class="breadcrumb">
                      <li class="breadcrumb-item"><a href="Support_admin.php"><i class="fa fa-home"></i></a></li>
                      <li class="breadcrumb-item">Threads</li>
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

                      <!-- Publish form -->
                      <div class="col-sm-12 col-md-5">
                        <div class="card">
                          <div class="card-header">
                            <h5 class="mb-0">Publish New Thread</h5>
                            <span>Fill in the fields to create a new discussion thread.</span>
                          </div>
                          <div class="card-block">
                            <?php if ($error): ?>
                              <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            <?php if ($success): ?>
                              <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                            <?php endif; ?>
                            <form method="post">
                              <input type="hidden" name="action" value="create_thread">

                              <div class="form-group">
                                <label>Link to Claim (optional)</label>
                                <select name="id_reclam" class="form-control">
                                  <option value="0">— None —</option>
                                  <?php foreach ($claims as $c): ?>
                                    <option value="<?php echo (int) $c['id_reclam']; ?>"
                                      <?php echo $prefill_id_reclam === (int) $c['id_reclam'] ? 'selected' : ''; ?>>
                                      #<?php echo (int) $c['id_reclam']; ?>
                                      – <?php echo htmlspecialchars(mb_substr($c['description_reclam'], 0, 40)); ?>
                                    </option>
                                  <?php endforeach; ?>
                                </select>
                              </div>

                              <div class="form-group">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control"
                                       placeholder="Thread title" required maxlength="255">
                              </div>

                              <div class="form-group">
                                <label>Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="5"
                                          placeholder="Describe the topic…" required
                                          maxlength="10000"><?php echo htmlspecialchars($prefill_description); ?></textarea>
                              </div>

                              <button type="submit" class="btn btn-primary btn-md waves-effect waves-light">
                                Publish Thread
                              </button>
                            </form>
                          </div>
                        </div>
                      </div>

                      <!-- Threads list -->
                      <div class="col-sm-12 col-md-7">
                        <div class="card">
                          <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                              <h5 class="mb-0">Published Threads</h5>
                              <span><?php echo count($threads); ?> total</span>
                            </div>
                            <a href="../../front_office/SUPPORT_MODULE/threads_page.php"
                               class="btn btn-sm btn-outline-secondary" target="_blank">
                              View Front-end
                            </a>
                          </div>
                          <div class="card-block">
                            <div class="mb-3">
                              <input id="thread-search" type="text" class="form-control"
                                     placeholder="Search threads…">
                            </div>
                            <div class="table-responsive">
                              <table class="table table-striped table-bordered" id="thread-table">
                                <thead>
                                  <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Claim</th>
                                    <th>Replies</th>
                                    <th>Published</th>
                                    <th>Actions</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <?php if (!empty($threads)): ?>
                                    <?php foreach ($threads as $t): ?>
                                      <tr>
                                        <td><?php echo (int) $t['id_thread']; ?></td>
                                        <td><?php echo htmlspecialchars(mb_substr($t['title'], 0, 50)); ?></td>
                                        <td><?php echo $t['id_reclam'] ? '#' . (int) $t['id_reclam'] : '—'; ?></td>
                                        <td><?php echo (int) ($t['reply_count'] ?? 0); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($t['published_at'])); ?></td>
                                        <td>
                                          <a href="../../front_office/SUPPORT_MODULE/thread_detail_page.php?id=<?php echo (int) $t['id_thread']; ?>"
                                             class="btn btn-info btn-sm" target="_blank">View</a>
                                          <form method="post" style="display:inline-block;margin-left:6px"
                                                onsubmit="return confirm('Delete this thread and all its replies?');">
                                            <input type="hidden" name="action"    value="delete_thread">
                                            <input type="hidden" name="id_thread" value="<?php echo (int) $t['id_thread']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                          </form>
                                        </td>
                                      </tr>
                                    <?php endforeach; ?>
                                  <?php else: ?>
                                    <tr><td colspan="6" class="text-center">No threads yet.</td></tr>
                                  <?php endif; ?>
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                      </div>

                    </div><!-- row -->
                  </div>
                </div>
              </div>
            </div>
          </div><!-- pcoded-content -->

        </div>
      </div>
      <div id="styleSelector"></div>
    </div>
  </div>

  <script type="text/javascript" src="assets/js/jquery/jquery.min.js "></script>
  <script type="text/javascript" src="assets/js/jquery-ui/jquery-ui.min.js "></script>
  <script type="text/javascript" src="assets/js/popper.js/popper.min.js"></script>
  <script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js "></script>
  <script src="assets/pages/waves/js/waves.min.js"></script>
  <script type="text/javascript" src="assets/js/jquery-slimscroll/jquery.slimscroll.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script src="assets/js/vertical/vertical-layout.min.js"></script>
  <script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
  <script type="text/javascript" src="assets/js/script.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#thread-search').on('keyup', function () {
        var q = $(this).val().toLowerCase();
        $('#thread-table tbody tr').each(function () {
          $(this).toggle($(this).text().toLowerCase().indexOf(q) !== -1);
        });
      });
    });
  </script>
</body>
</html>
