<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once(__DIR__ . '/../../Model/config.php');
include_once(__DIR__ . '/../../Controller/Controller_user.php');
require_once __DIR__ . '/google-config.php';
require_once __DIR__ . '/facebook-config.php';

$googleLoginUrl = ($client instanceof Google_Client) ? $client->createAuthUrl() : null;
// $fb_login_url is already provided by facebook-config.php

$error_message = '';
$success_message = '';
$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? '';
$redirectUrl = match ($redirect) {
    'marketplace' => '/integration%20foovia/MVC/View/front_office/MARKETPLACE_MODULE/organic-1.0.0/marketplace.php',
    'support' => 'SUPPORT_MODULE/support_rec_page.php',
    'support_create' => 'SUPPORT_MODULE/add_rec_page.php',
    default => 'foovia.php',
};

$controller = new Controller_user();
$controller->release_expired_bans();

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') == 'POST' && isset($_POST['signin_submit'])) {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = 'Email and password are required.';
    } else {
        try {
            $user = $controller->get_user_by_email($email);

            if (!$user) {
                $error_message = 'Username or password is false';
            } else {
              $banState = $controller->process_ban_countdown((int) $user['id_user']);

              if ($banState['is_banned']) {
                $error_message = 'Your account is banned. Try again in ' . $banState['remaining'] . '.';
              } else {
                
                if ($password === $user['password_user']) {
                  $controller->increment_user_login_count((int) $user['id_user']);
                  $controller->reset_failed_login_attempts((int) $user['id_user']);
                  session_regenerate_id(true);

                  // Password is correct
                  $_SESSION['user_id'] = (int) $user['id_user'];
                  $_SESSION['user_name'] = (string) $user['name_user'];
                  $_SESSION['user_email'] = (string) $user['email_user'];
                  
                  // Redirect immediately instead of using refresh to preserve session
                  header('Location: ' . $redirectUrl, true, 302);
                  exit;
                } else {
                  $attemptState = $controller->register_failed_login_attempt((int) $user['id_user']);

                  if ($attemptState['is_banned']) {
                    $error_message = 'Your account is now banned for 5 minutes. Try again in ' . $attemptState['remaining'] . '.';
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
<title>FOOVIA â€” Sign In</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Boldonse&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="foovia-signin.css">
</head>
<body>

<!-- SUCCESS OVERLAY -->
<div class="success-overlay" id="success-overlay">
  <div class="success-box">
    <div class="success-icon">ðŸŽ‰</div>
    <h2>Welcome back!</h2>
    <p>You've signed in successfully. Let's get back to crushing your goals.</p>
    <a href="foovia.php" class="btn-go">Go to my tracker â†’</a>
  </div>
</div>

<!-- WhatsApp Login Modal -->
<div id="wa-modal" class="wa-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
  <div style="background:var(--panel-bg, #fff); padding:30px; border-radius:16px; width:100%; max-width:400px; text-align:center;">
    <h2 style="font-family:'Syne', sans-serif; margin-bottom:10px;">WhatsApp Login</h2>
    
    <div id="wa-step-1">
      <p style="margin-bottom:20px; color:var(--page-muted, #666);">Enter your phone number to receive a secure login code.</p>
      <input type="text" id="wa-phone" placeholder="+1234567890" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; margin-bottom:20px; font-size:16px;">
      <button onclick="requestWaCode()" style="width:100%; padding:12px; background:#25D366; color:#fff; border:none; border-radius:8px; font-weight:bold; cursor:pointer;">Send Code via WhatsApp</button>
    </div>

    <div id="wa-step-2" style="display:none;">
      <p style="margin-bottom:20px; color:var(--page-muted, #666);">We've sent a 4-digit code to your WhatsApp. Please enter it below.</p>
      <input type="text" id="wa-code" placeholder="0000" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; margin-bottom:20px; font-size:24px; text-align:center; letter-spacing:4px;">
      <button onclick="verifyWaCode()" style="width:100%; padding:12px; background:#25D366; color:#fff; border:none; border-radius:8px; font-weight:bold; cursor:pointer;">Verify Code</button>
    </div>

    <button onclick="document.getElementById('wa-modal').style.display='none'" style="margin-top:20px; background:none; border:none; color:#888; cursor:pointer; text-decoration:underline;">Cancel</button>
  </div>
</div>

<!-- LEFT PANEL -->
<div class="left-panel">
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>
  <div class="blob blob-3"></div>

  <a href="foovia.php" class="left-logo"> FOOVIA</a>

  <div class="left-body">
    <h1>Good to have<br>you <span>back.</span></h1>
    <p>Your nutrition goals, workout plans, and marketplace â€” all waiting for you. Sign in and keep the momentum going.</p>
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
  <p class="form-sub">Don't have an account? <a href="../back_office/USER_MODULE/foovia-signup.php">Create one free â†’</a></p>

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
        <input type="text" id="email" name="email" placeholder="you@example.com" autocomplete="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"/>
        <span class="field-icon">âœ‰</span>
      </div>
      <span class="field-error" id="err-email">Please enter a valid email address (format: example@gmail.com).</span>
    </div>

    <!-- PASSWORD -->
    <div class="field">
      <label for="password">Password</label>
      <div class="field-wrap">
        <input type="password" id="password" name="password" placeholder="Your password" autocomplete="current-password" value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>"/>
        <button class="toggle-pw" type="button" onclick="togglePw('password', this)">Show</button>
      </div>
      <span class="field-error" id="err-password">Password cannot be empty.</span>
    </div>
  </div>

  <div class="forgot-row"><a href="forgot-password.php">Forgot your password?</a></div>

  <button type="submit" name="signin_submit" class="btn-submit">Sign in to my account</button>
  <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
  </form>

  <div class="divider">
    <div class="divider-line"></div>
    <span class="divider-text">or continue with</span>
    <div class="divider-line"></div>
  </div>

  <div class="social-btns">
    <?php if (!empty($googleLoginUrl)): ?>
      <a href="<?php echo htmlspecialchars($googleLoginUrl); ?>" class="social-btn" style="text-decoration: none; color: inherit; display: inline-flex; align-items: center; justify-content: center;">
        <span class="social-icon">G</span> Google
      </a>
    <?php else: ?>
      <button type="button" class="social-btn" disabled title="Google sign-in is not configured" style="opacity: 0.6; cursor: not-allowed;">
        <span class="social-icon">G</span> Google
      </button>
    <?php endif; ?>
    <?php if (!empty($fb_login_url)): ?>
      <a href="<?php echo htmlspecialchars($fb_login_url); ?>" class="social-btn" style="text-decoration: none; color: inherit; display: inline-flex; align-items: center; justify-content: center;">
        <span class="social-icon" style="color:#1877F2; font-weight:bold;">f</span> Facebook
      </a>
    <?php else: ?>
      <button type="button" class="social-btn" disabled title="Facebook sign-in is not configured" style="opacity: 0.6; cursor: not-allowed;">
        <span class="social-icon" style="color:#1877F2; font-weight:bold;">f</span> Facebook
      </button>
    <?php endif; ?>
    <button type="button" class="social-btn" onclick="document.getElementById('wa-modal').style.display='flex'">
      <span class="social-icon" style="color:#25D366;">ðŸ’¬</span> WhatsApp
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

// WhatsApp Flow JS
function requestWaCode() {
  const phone = document.getElementById('wa-phone').value;
  if(!phone) { alert("Please enter a phone number"); return; }
  
  fetch('whatsapp-request.php', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ phone: phone })
  })
  .then(res => res.json())
  .then(data => {
    if(data.success) {
      document.getElementById('wa-step-1').style.display = 'none';
      document.getElementById('wa-step-2').style.display = 'block';
      // Real mode: Code has been sent to WhatsApp
    } else {
      alert("Error: " + data.message);
    }
  });
}

function verifyWaCode() {
  const code = document.getElementById('wa-code').value;
  if(code.length !== 4) { alert("Please enter the 4-digit code"); return; }
  
  fetch('whatsapp-verify.php', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ code: code })
  })
  .then(res => res.json())
  .then(data => {
    if(data.success) {
      window.location.href = data.redirect;
    } else {
      alert(data.message);
    }
  });
}
</script>
</body>
</html>


