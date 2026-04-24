<?php
session_start();
include_once(__DIR__ . '/../../model/config.php');
include_once(__DIR__ . '/../../controller/Controller_user.php');
require_once __DIR__ . '/google-config.php';

$googleLoginUrl = $client->createAuthUrl();

$error_message = '';
$success_message = '';

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signin_submit'])) {
  $controller = new Controller_user();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = 'Email and password are required.';
    } else {
        try {
            $db = config::getConnexion();
            
            $sql = "SELECT id_user, name_user, email_user, password_user, account_state_user, duration_user, ban_until_user FROM user WHERE LOWER(email_user) = :email";
            $query = $db->prepare($sql);
            $query->execute(['email' => $email]);
            $user = $query->fetch();

            if (!$user) {
                $error_message = 'Username or password is false';
            } else {
              $banState = $controller->process_ban_countdown((int) $user['id_user']);

              if ($banState['is_banned']) {
                $error_message = 'Your account is banned. Try again in ' . $banState['remaining'] . '.';
              } else {
                
                if ($password === $user['password_user']) {
                  $controller->reset_failed_login_attempts((int) $user['id_user']);

                  // Password is correct
                  $_SESSION['user_id'] = $user['id_user'];
                  $_SESSION['user_name'] = $user['name_user'];
                  $_SESSION['user_email'] = $user['email_user'];
                  $success_message = 'Connected successfully! Redirecting...';
                  header('refresh:2;url=foovia.php');
                  exit;
                } else {
                  $attemptState = $controller->register_failed_login_attempt((int) $user['id_user']);

                  if ($attemptState['is_banned']) {
                    $error_message = 'Your account is now banned for 1 hour. Try again in ' . $attemptState['remaining'] . '.';
                  } else {
                    $error_message = 'Username or password is false. Remaining attempts: ' . $attemptState['remaining_attempts'];
                  }
                }
                }
            }
        } catch (Exception $e) {
            $error_message = 'An error occurred: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA — Sign In</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Boldonse&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="foovia-signin.css">
</head>
<body>

<!-- SUCCESS OVERLAY -->
<div class="success-overlay" id="success-overlay">
  <div class="success-box">
    <div class="success-icon">🎉</div>
    <h2>Welcome back!</h2>
    <p>You've signed in successfully. Let's get back to crushing your goals.</p>
    <a href="foovia.php" class="btn-go">Go to my tracker →</a>
  </div>
</div>

<!-- LEFT PANEL -->
<div class="left-panel">
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>
  <div class="blob blob-3"></div>

  <a href="foovia.php" class="left-logo">🌿 FOOVIA</a>

  <div class="left-body">
    <h1>Good to have<br>you <span>back.</span></h1>
    <p>Your nutrition goals, workout plans, and marketplace — all waiting for you. Sign in and keep the momentum going.</p>
  </div>

  <div class="left-pills">
    <div class="pill"><div class="pill-dot" style="background:var(--yellow)"></div>AI-powered recipe suggestions</div>
    <div class="pill"><div class="pill-dot" style="background:var(--green)"></div>Daily macro & hydration tracking</div>
    <div class="pill"><div class="pill-dot" style="background:var(--orange)"></div>Local fresh food marketplace</div>
  </div>
</div>

<!-- RIGHT PANEL -->
<div class="right-panel">
  <p class="form-eyebrow">Welcome back</p>
  <h1 class="form-title">Sign in to<br>Foovia</h1>
  <p class="form-sub">Don't have an account? <a href="../backoffice/foovia-signup.php">Create one free →</a></p>

  <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-bottom: 20px; padding: 12px; background: #fee; color: var(--red); border: 1px solid var(--red); border-radius: 8px;">
      <strong>Error!</strong> <?php echo htmlspecialchars($error_message); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 20px; padding: 12px; background: #efe; color: var(--green); border: 1px solid var(--green); border-radius: 8px;">
      <strong>Success!</strong> <?php echo htmlspecialchars($success_message); ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="" id="signinForm">
  <div class="field-group">
    <!-- EMAIL -->
    <div class="field">
      <label for="email">Email address</label>
      <div class="field-wrap">
        <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" required/>
        <span class="field-icon">✉</span>
      </div>
      <span class="field-error" id="err-email">Please enter a valid email address.</span>
    </div>

    <!-- PASSWORD -->
    <div class="field">
      <label for="password">Password</label>
      <div class="field-wrap">
        <input type="password" id="password" name="password" placeholder="Your password" autocomplete="current-password" required/>
        <button class="toggle-pw" type="button" onclick="togglePw('password', this)">Show</button>
      </div>
      <span class="field-error" id="err-password">Password cannot be empty.</span>
    </div>
  </div>

  <div class="forgot-row"><a href="forgot-password.php">Forgot your password?</a></div>

  <button type="submit" name="signin_submit" class="btn-submit">Sign in to my account</button>
  </form>

  <div class="divider">
    <div class="divider-line"></div>
    <span class="divider-text">or continue with</span>
    <div class="divider-line"></div>
  </div>

  <div class="social-btns">
    <a href="<?php echo htmlspecialchars($googleLoginUrl); ?>" class="social-btn" style="text-decoration: none; color: inherit; display: inline-flex; align-items: center; justify-content: center;">
      <span class="social-icon">G</span> Google
    </a>
    <button type="button" class="social-btn">
      <span class="social-icon">f</span> Facebook
    </button>
    <button class="social-btn">
      <span class="social-icon">🍎</span> Apple
    </button>
  </div>
</div>

<script>
function togglePw(id, btn) {
  const input = document.getElementById(id);
  const hidden = input.type === 'password';
  input.type = hidden ? 'text' : 'password';
  btn.textContent = hidden ? 'Hide' : 'Show';
}

function validate(id, check, errId) {
  const input = document.getElementById(id);
  const err   = document.getElementById(errId);
  const ok    = check(input.value);
  input.classList.toggle('error', !ok);
  err.classList.toggle('visible', !ok);
  return ok;
}

function handleSignIn() {
  const v1 = validate('email',    v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v), 'err-email');
  const v2 = validate('password', v => v.length > 0, 'err-password');
  return v1 && v2; // Return true to allow form submission, false to prevent
}

// Add form submit handler
document.getElementById('signinForm').addEventListener('submit', function(e) {
  if (!handleSignIn()) {
    e.preventDefault(); // Prevent form submission if validation fails
  }
});

// Check for PHP success message and show overlay
<?php if (!empty($success_message)): ?>
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('success-overlay').classList.add('show');
});
<?php endif; ?>
</script>
</body>
</html>
