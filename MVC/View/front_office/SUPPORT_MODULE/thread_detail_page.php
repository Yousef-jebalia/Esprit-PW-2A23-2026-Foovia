<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../../../Controller/SUPPORT_MODULE/Thread_Controller.php';

$is_logged_in = isset($_SESSION['user_id']);
$logged_in_user_id = (int) ($_SESSION['user_id'] ?? 0);
$logged_in_user_name = trim((string) ($_SESSION['user_name'] ?? ''));

$controller = new Thread_Controller();
$error      = '';
$success    = '';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: threads_page.php');
    exit;
}

$thread = $controller->get_thread_by_id($id);
if ($thread === null) {
    header('Location: threads_page.php');
    exit;
}

// Handle reply POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    if (!$is_logged_in) {
        $error = 'Please sign in to post a reply.';
    } else {
        $body = trim($_POST['body'] ?? '');
        if ($body === '') {
            $error = 'Reply cannot be empty.';
        } elseif (strlen($body) > 5000) {
            $error = 'Reply is too long (max 5000 characters).';
        } else {
            try {
                $msg = new ThreadMessage(0, $id, $logged_in_user_id, $body, '');
                $controller->add_message($msg);
                header('Location: thread_detail_page.php?id=' . $id . '&posted=1');
                exit;
            } catch (Exception $e) {
                $error = 'Could not post reply. Please try again.';
            }
        }
    }
}

// Handle delete message POST (anyone can delete in this simple setup — restrict later)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_message') {
    $mid = (int) ($_POST['id_message'] ?? 0);
    if ($mid > 0) {
        try {
            $controller->delete_message($mid);
            header('Location: thread_detail_page.php?id=' . $id);
            exit;
        } catch (Exception $e) {
            $error = 'Could not delete reply.';
        }
    }
}

if (isset($_GET['posted'])) {
    $success = 'Your reply was posted!';
}

$messages = $controller->get_messages($id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo htmlspecialchars($thread['title']); ?> – Foovia Threads</title>
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

    .thread-hero {
      background: linear-gradient(135deg, #2E4A28 0%, #4BAE52 100%);
      color: #fff;
      padding: 48px 0 32px;
    }
    .thread-hero h1   { font-size: 1.8rem; font-weight: 700; margin-bottom: .4rem; }
    .thread-hero .meta { opacity: .82; font-size: .88rem; }

    .thread-body-card {
      border: 1.5px solid #e0e7e0;
      border-radius: 14px;
      padding: 24px 28px;
      background: #fff;
      margin-bottom: 28px;
    }
    :root[data-theme="dark"] .thread-body-card { background: #1e1e1e; border-color: #333; }

    /* message bubbles */
    .msg-bubble {
      border-radius: 14px;
      padding: 16px 20px;
      background: #f4f8f4;
      border: 1px solid #deeede;
      position: relative;
    }
    :root[data-theme="dark"] .msg-bubble { background: #1a2a1a; border-color: #2d4a2d; }
    .msg-bubble .msg-body  { font-size: .95rem; white-space: pre-wrap; word-break: break-word; }
    .msg-author {
      font-size: .82rem;
      font-weight: 700;
      color: #2E4A28;
      margin-bottom: 8px;
    }
    :root[data-theme="dark"] .msg-author { color: #8fd18f; }
    .msg-bubble .msg-meta  { font-size: .78rem; color: #64748b; margin-top: 8px; }
    .msg-delete-btn {
      position: absolute;
      top: 10px;
      right: 12px;
      font-size: .75rem;
      color: #dc3545;
      background: none;
      border: none;
      cursor: pointer;
      padding: 0;
      opacity: .6;
    }
    .msg-delete-btn:hover { opacity: 1; }

    /* reply form */
    .reply-card {
      border: 1.5px solid #c8e0c8;
      border-radius: 14px;
      padding: 24px 28px;
      background: #f8fbf8;
    }
    :root[data-theme="dark"] .reply-card { background: #181e18; border-color: #2d4a2d; }
    .reply-card textarea {
      border: 2px solid #ddd;
      border-radius: 10px;
      resize: vertical;
      font-size: .95rem;
    }
    .reply-card textarea:focus { border-color: #4BAE52; outline: none; box-shadow: none; }

    .btn-foovia        { background: linear-gradient(135deg,#4BAE52,#2E4A28); color:#fff; border:none; }
    .btn-foovia:hover  { background: linear-gradient(135deg,#3d9a45,#25411f); color:#fff; }

    .back-link { color:#4BAE52; font-size:.9rem; text-decoration:none; }
    .back-link:hover { color:#2E4A28; }
  </style>
</head>
<body>

<svg xmlns="http://www.w3.org/2000/svg" style="display:none"><defs>
  <symbol id="menu" viewBox="0 0 24 24"><path fill="currentColor" d="M2 6a1 1 0 0 1 1-1h18a1 1 0 1 1 0 2H3a1 1 0 0 1-1-1m0 6.032a1 1 0 0 1 1-1h18a1 1 0 1 1 0 2H3a1 1 0 0 1-1-1m1 5.033a1 1 0 1 0 0 2h18a1 1 0 0 0 0-2z"/></symbol>
</defs></svg>

<nav>
  <a href="../foovia.php" class="nav-logo">
    <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" style="height: 50px; width: auto;">
    FOOVIA
  </a>
  <ul class="nav-links">
    <li><a href="../marketplace-gateway.php">Marketplace</a></li>
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
<div class="thread-hero">
  <div class="container">
    <a href="threads_page.php" class="back-link" style="color:rgba(255,255,255,.8)">← Back to Threads</a>
    <h1 class="mt-2"><?php echo htmlspecialchars($thread['title']); ?></h1>
    <div class="meta">
      Published <?php echo date('M j, Y \a\t H:i', strtotime($thread['published_at'])); ?>
      <?php if (!empty($thread['id_reclam'])): ?>
        &nbsp;·&nbsp; Linked to Claim #<?php echo (int) $thread['id_reclam']; ?>
      <?php endif; ?>
      &nbsp;·&nbsp; <?php echo count($messages); ?> repl<?php echo count($messages) == 1 ? 'y' : 'ies'; ?>
    </div>
  </div>
</div>

<div class="container mt-4 mb-5" style="max-width:820px">

  <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <!-- Thread body -->
  <div class="thread-body-card">
    <p style="white-space:pre-wrap;margin:0"><?php echo htmlspecialchars($thread['description']); ?></p>
  </div>

  <!-- Replies -->
  <?php if (!empty($messages)): ?>
    <h5 class="mb-3 fw-bold" style="color:#2E4A28">
      <?php echo count($messages); ?> Repl<?php echo count($messages) == 1 ? 'y' : 'ies'; ?>
    </h5>
    <div class="d-flex flex-column gap-3 mb-4">
      <?php foreach ($messages as $m): ?>
        <div class="msg-bubble">
          <form method="post" style="display:inline"
                onsubmit="return confirm('Delete this reply?');">
            <input type="hidden" name="action"     value="delete_message">
            <input type="hidden" name="id_message" value="<?php echo (int) $m['id_message']; ?>">
            <button type="submit" class="msg-delete-btn" title="Delete reply">✕</button>
          </form>
          <div class="msg-author">
            <?php echo htmlspecialchars($m['author_name'] ?? ('User #' . (int) $m['id_user'])); ?>
          </div>
          <div class="msg-body"><?php echo htmlspecialchars($m['body']); ?></div>
          <div class="msg-meta">
            <?php echo date('M j, Y H:i', strtotime($m['sent_at'])); ?>
            <?php if ($m['id_user'] > 0): ?>
              &nbsp;·&nbsp; User #<?php echo (int) $m['id_user']; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="text-muted mb-4">No replies yet. Be the first!</p>
  <?php endif; ?>

  <!-- Reply form -->
  <?php if ($is_logged_in): ?>
    <div class="reply-card">
      <h6 class="fw-bold mb-3" style="color:#2E4A28">Post a Reply</h6>
      <form method="post">
        <input type="hidden" name="action" value="reply">
        <div class="mb-3">
          <textarea name="body" class="form-control" rows="4"
                    placeholder="Write your reply here…" required
                    maxlength="5000"></textarea>
        </div>
        <button type="submit" class="btn btn-foovia px-4">Post Reply</button>
      </form>
    </div>
  <?php else: ?>
    <div class="reply-card" style="border-color: #ffd700; background: #fffbf0;">
      <h6 class="fw-bold mb-3" style="color:#2E4A28">Post a Reply</h6>
      <p style="color: #555; margin-bottom: 12px;">
        You must be signed in to post a reply.
      </p>
      <div style="display: flex; gap: 10px;">
        <a href="../foovia-signin.php?redirect=support" class="btn btn-foovia px-4">Sign In</a>
        <a href="../foovia-signup.php" class="btn btn-outline-success px-4">Create Account</a>
      </div>
    </div>
  <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/vendor.js"></script>
<script src="js/theme.js"></script>
</body>
</html>
