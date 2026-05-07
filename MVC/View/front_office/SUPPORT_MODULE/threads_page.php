<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../../../Controller/SUPPORT_MODULE/Thread_Controller.php';

$is_logged_in = isset($_SESSION['user_id']);
$logged_in_user_id = (int) ($_SESSION['user_id'] ?? 0);
$logged_in_user_name = trim((string) ($_SESSION['user_name'] ?? ''));

$controller = new Thread_Controller();
$per_page   = 8;
$page       = max(1, (int) ($_GET['page'] ?? 1));
$total      = $controller->count_threads();
$total_pages = (int) ceil($total / $per_page);
$page       = min($page, max(1, $total_pages));
$threads    = $controller->get_threads_paged($page, $per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Community Threads – Foovia</title>
  <link rel="icon" type="image/png" sizes="32x32" href="images/logo_web.png"/>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>
    (function () {
      try {
        var t = localStorage.getItem('theme');
        if (t === 'dark' || t === 'light') {
          document.documentElement.setAttribute('data-theme', t);
          document.documentElement.style.colorScheme = t;
        }
      } catch (e) {}
    })();
  </script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="css/vendor.css">
  <link rel="stylesheet" type="text/css" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Open+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
  <style>
    body { background-color: var(--page-bg, #fff); color: var(--page-text, #1a1a1a); }
    :root[data-theme="dark"] header { background-color: var(--nav-bg) !important; }
    :root[data-theme="dark"] .thread-card { background: #1e1e1e; border-color: #333; }
    :root[data-theme="dark"] .thread-card:hover { border-color: #4BAE52; }

    .threads-hero {
      background: linear-gradient(135deg, #2E4A28 0%, #4BAE52 100%);
      color: #fff;
      padding: 56px 0 40px;
      text-align: center;
    }
    .threads-hero h1 { font-size: 2.2rem; font-weight: 700; margin-bottom: .5rem; }
    .threads-hero p  { opacity: .88; font-size: 1.05rem; }

    .thread-card {
      border: 1.5px solid #e0e7e0;
      border-radius: 14px;
      padding: 22px 24px;
      transition: border-color .2s, box-shadow .2s;
      background: #fff;
    }
    .thread-card:hover {
      border-color: #4BAE52;
      box-shadow: 0 4px 18px rgba(75,174,82,.13);
    }
    .thread-card a.thread-title-link {
      font-size: 1.1rem;
      font-weight: 700;
      color: #2E4A28;
      text-decoration: none;
    }
    .thread-card a.thread-title-link:hover { color: #4BAE52; }
    .thread-meta { font-size: .82rem; color: #64748b; margin-top: 6px; }
    .thread-desc { font-size: .93rem; color: #444; margin-top: 10px;
                   display: -webkit-box; -webkit-line-clamp: 2;
                   -webkit-box-orient: vertical; overflow: hidden; }
    :root[data-theme="dark"] .thread-desc { color: #bbb; }
    .badge-replies {
      background: #eaf6eb;
      color: #2E4A28;
      font-size: .78rem;
      border-radius: 20px;
      padding: 3px 10px;
      font-weight: 600;
    }
    :root[data-theme="dark"] .badge-replies { background: #1e3320; color: #7dd47e; }

    .pagination .page-link {
      color: #2E4A28;
      border-radius: 8px !important;
      margin: 0 2px;
    }
    .pagination .page-item.active .page-link {
      background: #4BAE52;
      border-color: #4BAE52;
      color: #fff;
    }
  </style>
</head>
<body>

<!-- Inline SVG defs (subset needed for nav icons) -->
<svg xmlns="http://www.w3.org/2000/svg" style="display:none">
  <defs>
    <symbol id="menu" viewBox="0 0 24 24"><path fill="currentColor" d="M2 6a1 1 0 0 1 1-1h18a1 1 0 1 1 0 2H3a1 1 0 0 1-1-1m0 6.032a1 1 0 0 1 1-1h18a1 1 0 1 1 0 2H3a1 1 0 0 1-1-1m1 5.033a1 1 0 1 0 0 2h18a1 1 0 0 0 0-2z"/></symbol>
  </defs>
</svg>

<nav>
  <a href="#" class="nav-logo">
    <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" style="height:50px;width:auto;">
    FOOVIA
  </a>
  <ul class="nav-links">
    <li><a href="#features">Features</a></li>
    <li><a href="#how">How it works</a></li>
    <li><a href="#marketplace">Marketplace</a></li>
    <li><a href="#community">Community</a></li>
    <li><a href="support_rec_page.php">Support</a></li>
    <li><a href="threads_page.php" style="color:#4BAE52;font-weight:700">Threads</a></li>
  </ul>
  <div class="nav-actions">
    <a href="../foovia-backoffice.php" class="nav-btn nav-backoffice">Backoffice</a>
    <button class="theme-toggle" type="button" aria-label="Switch to dark mode" aria-pressed="false">
      <svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true">
        <circle cx="12" cy="12" r="4"></circle>
        <path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"></path>
      </svg>
      <svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M21 14.5A8.5 8.5 0 1 1 9.5 3a7 7 0 1 0 11.5 11.5z"></path>
      </svg>
    </button>
    <?php if ($is_logged_in): ?>
      <div style="display: flex; align-items: center; gap: 12px;">
        <span style="color: #666; font-size: 0.9rem;">Welcome, <strong><?php echo htmlspecialchars($logged_in_user_name); ?></strong></span>
        <a href="../foovia.php?logout=1" class="nav-btn nav-signin" style="background: #d94f00;">Logout</a>
      </div>
    <?php else: ?>
      <a href="../foovia-signin.php" class="nav-btn nav-signin">Sign In</a>
      <a href="../foovia-signup.php" class="nav-btn nav-signup">Sign Up</a>
    <?php endif; ?>
  </div>
</nav>

<!-- Hero -->
<section class="threads-hero">
  <div class="container">
    <h1>Community Threads</h1>
    <p>Browse topics published by the Foovia support team and join the conversation.</p>
  </div>
</section>

<!-- Thread list -->
<section class="container mt-5 mb-5">
  <?php if (empty($threads)): ?>
    <div class="text-center text-muted py-5">
      <p style="font-size:1.1rem">No threads published yet. Check back soon!</p>
      <a href="support_rec_page.php" class="btn btn-outline-success mt-2">Go to Support</a>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($threads as $t): ?>
        <div class="col-12 col-md-6 col-lg-6">
          <div class="thread-card h-100">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <a href="thread_detail_page.php?id=<?php echo (int) $t['id_thread']; ?>" class="thread-title-link">
                <?php echo htmlspecialchars($t['title']); ?>
              </a>
              <span class="badge-replies flex-shrink-0">
                <?php echo (int) $t['reply_count']; ?> repl<?php echo $t['reply_count'] == 1 ? 'y' : 'ies'; ?>
              </span>
            </div>
            <div class="thread-meta">
              <?php
                $dt = $t['published_at'] ?? '';
                echo $dt ? date('M j, Y \a\t H:i', strtotime($dt)) : '';
              ?>
              <?php if (!empty($t['id_reclam'])): ?>
                &nbsp;·&nbsp; <span title="Linked claim">Claim #<?php echo (int) $t['id_reclam']; ?></span>
              <?php endif; ?>
            </div>
            <p class="thread-desc"><?php echo htmlspecialchars($t['description']); ?></p>
            <a href="thread_detail_page.php?id=<?php echo (int) $t['id_thread']; ?>"
               class="btn btn-sm btn-outline-success mt-3">View &amp; Reply →</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <nav class="mt-5 d-flex justify-content-center" aria-label="Thread pages">
        <ul class="pagination">
          <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page - 1; ?>">‹ Prev</a>
          </li>
          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
              <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next ›</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/vendor.js"></script>
<script src="js/theme.js"></script>
</body>
</html>
