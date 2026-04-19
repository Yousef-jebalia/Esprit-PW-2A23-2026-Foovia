<?php
session_start();
include_once(__DIR__ . '/../../model/config.php');

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signin_submit'])) {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = 'Email and password are required.';
    } else {
        try {
            $db = config::getConnexion();
            
            $sql = "SELECT id_user, name_user, email_user, password_user FROM user WHERE LOWER(email_user) = :email";
            $query = $db->prepare($sql);
            $query->execute(['email' => $email]);
            $user = $query->fetch();

            if (!$user) {
                $error_message = 'Username or password is false';
            } else {
                
                if ($password === $user['password_user']) {
                    // Password is correct
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['user_name'] = $user['name_user'];
                    $_SESSION['user_email'] = $user['email_user'];
                    $success_message = 'Connected successfully! Redirecting...';
                    header('refresh:2;url=foovia.php');
                    exit;
                } else {
                    $error_message = 'Username or password is false';
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
<style>
  :root {
    --yellow:    #F5C842;
    --green:     #4BAE52;
    --orange:    #D94F00;
    --yellow-mid:#F0A830;
    --forest:    #2E4A28;
    --peach:     #F2A98A;
    --red:       #C0381A;
    --off-white: #FDF8EE;
    --dark:      #111008;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { height: 100%; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--dark);
    color: var(--dark);
    display: flex;
    min-height: 100vh;
    overflow-x: hidden;
  }

  /* ── LEFT PANEL ── */
  .left-panel {
    flex: 1;
    background: var(--dark);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 48px 56px;
    position: relative;
    overflow: hidden;
    min-height: 100vh;
  }

  /* decorative blobs */
  .blob {
    position: absolute;
    border-radius: 50%;
    opacity: .18;
  }
  .blob-1 {
    width: 500px; height: 500px;
    background: var(--green);
    top: -120px; left: -140px;
    animation: drift 8s ease-in-out infinite alternate;
  }
  .blob-2 {
    width: 380px; height: 380px;
    background: var(--yellow);
    bottom: -80px; right: -80px;
    animation: drift 10s ease-in-out infinite alternate-reverse;
  }
  .blob-3 {
    width: 200px; height: 200px;
    background: var(--orange);
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    animation: drift 7s ease-in-out infinite alternate;
  }
  @keyframes drift {
    from { transform: translate(0, 0) scale(1); }
    to   { transform: translate(20px, 30px) scale(1.06); }
  }
  .blob-2 { animation: drift2 10s ease-in-out infinite alternate-reverse; }
  @keyframes drift2 {
    from { transform: translate(0, 0) scale(1); }
    to   { transform: translate(-20px, -20px) scale(1.04); }
  }

  .left-logo {
    font-family: 'Boldonse', system-ui;
    font-size: 1.6rem;
    color: var(--yellow);
    text-decoration: none;
    position: relative; z-index: 2;
  }

  .left-body {
    position: relative; z-index: 2;
  }
  .left-body h1 {
    font-family: 'Boldonse', system-ui;
    font-size: clamp(2.4rem, 4vw, 3.6rem);
    color: #fff;
    line-height: 1.05;
    margin-bottom: 20px;
  }
  .left-body h1 span { color: var(--yellow); }
  .left-body p {
    font-size: 1rem;
    color: rgba(255,255,255,.55);
    line-height: 1.7;
    max-width: 380px;
  }

  /* feature pills */
  .left-pills {
    display: flex;
    flex-direction: column;
    gap: 12px;
    position: relative; z-index: 2;
  }
  .pill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 100px;
    padding: 10px 18px;
    color: rgba(255,255,255,.75);
    font-size: .85rem;
    width: fit-content;
  }
  .pill-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
  }

  /* ── RIGHT PANEL (form) ── */
  .right-panel {
    width: 520px;
    background: var(--off-white);
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 64px 56px;
    position: relative;
    min-height: 100vh;
  }

  .form-eyebrow {
    font-family: 'Boldonse', system-ui;
    font-size: .7rem;
    letter-spacing: .16em;
    text-transform: uppercase;
    color: var(--green);
    margin-bottom: 10px;
  }
  .form-title {
    font-family: 'Boldonse', system-ui;
    font-size: 2.2rem;
    line-height: 1.05;
    margin-bottom: 8px;
    color: var(--dark);
  }
  .form-sub {
    font-size: .9rem;
    color: #666;
    margin-bottom: 36px;
  }
  .form-sub a { color: var(--green); text-decoration: none; font-weight: 500; }
  .form-sub a:hover { text-decoration: underline; }

  /* fields */
  .field-group { display: flex; flex-direction: column; gap: 16px; margin-bottom: 22px; }

  .field {
    display: flex;
    flex-direction: column;
    gap: 7px;
  }
  .field label {
    font-family: 'Boldonse', system-ui;
    font-size: .75rem;
    letter-spacing: .06em;
    color: #444;
  }
  .field-wrap {
    position: relative;
    display: flex;
    align-items: center;
  }
  .field-wrap input {
    width: 100%;
    border: 1.5px solid rgba(0,0,0,.12);
    border-radius: 14px;
    padding: 14px 46px 14px 16px;
    font-family: 'DM Sans', sans-serif;
    font-size: .95rem;
    background: #fff;
    color: var(--dark);
    outline: none;
    transition: border-color .2s, box-shadow .2s;
  }
  .field-wrap input:focus {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(75,174,82,.12);
  }
  .field-wrap input.error {
    border-color: var(--red);
    box-shadow: 0 0 0 3px rgba(192,56,26,.1);
  }
  .field-icon {
    position: absolute;
    right: 14px;
    color: #bbb;
    font-size: 1rem;
    pointer-events: none;
    line-height: 1;
  }
  .toggle-pw {
    position: absolute;
    right: 14px;
    background: none; border: none;
    cursor: pointer;
    color: #bbb;
    font-size: .8rem;
    font-family: 'Boldonse', system-ui;
    padding: 2px 4px;
    transition: color .2s;
  }
  .toggle-pw:hover { color: var(--green); }

  .field-error {
    font-size: .75rem;
    color: var(--red);
    display: none;
    align-items: center;
    gap: 4px;
  }
  .field-error.visible { display: flex; }

  /* divider */
  .divider {
    display: flex; align-items: center; gap: 12px;
    margin: 6px 0 22px;
  }
  .divider-line { flex: 1; height: 1px; background: rgba(0,0,0,.1); }
  .divider-text { font-size: .78rem; color: #aaa; white-space: nowrap; }

  /* forgot */
  .forgot-row {
    display: flex; justify-content: flex-end;
    margin-top: -8px; margin-bottom: 24px;
  }
  .forgot-row a {
    font-size: .82rem; color: var(--orange);
    text-decoration: none; font-weight: 500;
    transition: color .2s;
  }
  .forgot-row a:hover { color: var(--red); }

  /* submit */
  .btn-submit {
    width: 100%;
    background: var(--dark);
    color: #fff;
    border: none;
    border-radius: 14px;
    padding: 16px;
    font-family: 'Boldonse', system-ui;
    font-size: 1rem;
    cursor: pointer;
    transition: background .2s, transform .15s;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
  }
  .btn-submit:hover { background: var(--forest); transform: scale(1.01); }
  .btn-submit:active { transform: scale(.99); }

  /* social */
  .social-btns { display: flex; gap: 10px; }
  .social-btn {
    flex: 1;
    border: 1.5px solid rgba(0,0,0,.1);
    background: #fff;
    border-radius: 12px;
    padding: 12px;
    font-family: 'DM Sans', sans-serif;
    font-size: .85rem;
    font-weight: 500;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    color: var(--dark);
    transition: background .15s, border-color .15s;
  }
  .social-btn:hover { background: var(--off-white); border-color: rgba(0,0,0,.2); }
  .social-icon { font-size: 1.1rem; }

  /* success overlay */
  .success-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(17,16,8,.6);
    z-index: 200;
    align-items: center; justify-content: center;
  }
  .success-overlay.show { display: flex; }
  .success-box {
    background: #fff;
    border-radius: 24px;
    padding: 48px 40px;
    text-align: center;
    max-width: 360px;
    width: 90%;
    animation: popIn .35s cubic-bezier(.34,1.56,.64,1) both;
  }
  @keyframes popIn {
    from { opacity: 0; transform: scale(.8); }
    to   { opacity: 1; transform: scale(1); }
  }
  .success-icon { font-size: 3rem; margin-bottom: 16px; }
  .success-box h2 {
    font-family: 'Boldonse', system-ui;
    font-size: 1.5rem; margin-bottom: 10px; color: var(--dark);
  }
  .success-box p { font-size: .9rem; color: #666; margin-bottom: 28px; line-height: 1.6; }
  .success-box .btn-go {
    display: inline-block;
    background: var(--green); color: #fff;
    padding: 13px 32px; border-radius: 100px;
    font-family: 'Boldonse', system-ui; font-size: .9rem;
    text-decoration: none;
    transition: background .2s;
  }
  .success-box .btn-go:hover { background: var(--forest); }

  /* responsive */
  @media (max-width: 860px) {
    body { flex-direction: column; }
    .left-panel { min-height: auto; padding: 36px 28px 48px; }
    .left-body h1 { font-size: 2rem; }
    .right-panel { width: 100%; padding: 48px 28px 60px; min-height: auto; }
  }
</style>
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

  <a href="foovia.html" class="left-logo">🌿 FOOVIA</a>

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

  <div class="forgot-row"><a href="#">Forgot your password?</a></div>

  <button type="submit" name="signin_submit" class="btn-submit">Sign in to my account</button>
  </form>

  <div class="divider">
    <div class="divider-line"></div>
    <span class="divider-text">or continue with</span>
    <div class="divider-line"></div>
  </div>

  <div class="social-btns">
    <button class="social-btn">
      <span class="social-icon">G</span> Google
    </button>
    <button class="social-btn">
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
