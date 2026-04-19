<!DOCTYPE html>
<html lang="en">
<?php
session_start();
include_once(__DIR__ . '/../../controller/Controller_user.php');

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup_submit'])) {
    $name     = trim($_POST['name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';
    $lastname  = trim($_POST['lastname'] ?? $name);
    $gender    = $_POST['gender'] ?? 'Not specified';
    $birthday  = $_POST['birthday'] ?? '';
    $height    = intval($_POST['height'] ?? 0);
    $weight    = intval($_POST['weight'] ?? 0);
    $bmi       = intval($_POST['bmi'] ?? 0);
    $activity  = $_POST['activity'] ?? '';
    $illness   = $_POST['illness'] ?? '';
    $allergie  = $_POST['allergie'] ?? '';
    $medicament = $_POST['medicament'] ?? '';

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (!isset($_POST['terms_accepted'])) {
        $error_message = 'You must accept the Terms & Conditions.';
    } else {
        try {
            $user = new User(
                0,
                $name,
                $lastname,
                $email,
                $password,
                $phone,
                $gender,
                $birthday,
                $height,
                $weight,
                $bmi,
                $activity,
                $illness,
                $allergie,
                $medicament,
                date('Y-m-d H:i:s'),
                'user'
            );

            $controller = new Controller_user();
            $result = $controller->add_user($user);

            $success_message = 'Account created successfully! Redirecting to survey...';
            $_SESSION['signup_email'] = $email;
            header('Location: survey.php');
            exit;
        } catch (Exception $e) {
            $error_message = 'An error occurred: ' . $e->getMessage();
        }
    }
}
?>
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA — Create Account</title>
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

  .blob {
    position: absolute;
    border-radius: 50%;
    opacity: .16;
    pointer-events: none;
  }
  .blob-1 {
    width: 460px; height: 460px;
    background: var(--orange);
    top: -100px; right: -100px;
    animation: driftA 9s ease-in-out infinite alternate;
  }
  .blob-2 {
    width: 340px; height: 340px;
    background: var(--green);
    bottom: -60px; left: -80px;
    animation: driftB 11s ease-in-out infinite alternate-reverse;
  }
  .blob-3 {
    width: 180px; height: 180px;
    background: var(--yellow);
    top: 55%; left: 55%;
    animation: driftA 6s ease-in-out infinite alternate;
  }
  @keyframes driftA {
    from { transform: translate(0,0) scale(1); }
    to   { transform: translate(-18px, 28px) scale(1.07); }
  }
  @keyframes driftB {
    from { transform: translate(0,0) scale(1); }
    to   { transform: translate(18px, -22px) scale(1.05); }
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
    font-size: clamp(2.2rem, 3.8vw, 3.4rem);
    color: #fff;
    line-height: 1.05;
    margin-bottom: 20px;
  }
  .left-body h1 span.gr { color: var(--green); }
  .left-body h1 span.ye { color: var(--yellow); }
  .left-body p {
    font-size: 1rem;
    color: rgba(255,255,255,.52);
    line-height: 1.7;
    max-width: 380px;
  }

  /* step indicators */
  .steps-preview {
    position: relative; z-index: 2;
    display: flex; flex-direction: column; gap: 14px;
  }
  .step-item {
    display: flex; align-items: center; gap: 14px;
  }
  .step-num {
    width: 32px; height: 32px; border-radius: 10px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Boldonse', system-ui;
    font-size: .8rem;
    color: var(--yellow);
    flex-shrink: 0;
  }
  .step-label { font-size: .85rem; color: rgba(255,255,255,.6); }

  /* ── RIGHT PANEL ── */
  .right-panel {
    width: 540px;
    background: var(--off-white);
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 52px 52px;
    position: relative;
    min-height: 100vh;
    overflow-y: auto;
  }

  .form-eyebrow {
    font-family: 'Boldonse', system-ui;
    font-size: .7rem;
    letter-spacing: .16em;
    text-transform: uppercase;
    color: var(--orange);
    margin-bottom: 10px;
  }
  .form-title {
    font-family: 'Boldonse', system-ui;
    font-size: 2rem;
    line-height: 1.08;
    margin-bottom: 8px;
    color: var(--dark);
  }
  .form-sub {
    font-size: .88rem;
    color: #666;
    margin-bottom: 30px;
  }
  .form-sub a { color: var(--green); text-decoration: none; font-weight: 500; }
  .form-sub a:hover { text-decoration: underline; }

  /* fields */
  .field-group { display: flex; flex-direction: column; gap: 14px; margin-bottom: 18px; }

  .field { display: flex; flex-direction: column; gap: 6px; }
  .field label {
    font-family: 'Boldonse', system-ui;
    font-size: .72rem; letter-spacing: .06em; color: #444;
  }
  .field-wrap { position: relative; display: flex; align-items: center; }
  .field-wrap input {
    width: 100%;
    border: 1.5px solid rgba(0,0,0,.12);
    border-radius: 14px;
    padding: 13px 46px 13px 16px;
    font-family: 'DM Sans', sans-serif;
    font-size: .93rem;
    background: #fff;
    color: var(--dark);
    outline: none;
    transition: border-color .2s, box-shadow .2s;
  }
  .field-wrap input:focus {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(75,174,82,.12);
  }
  .field-wrap input.invalid {
    border-color: var(--red);
    box-shadow: 0 0 0 3px rgba(192,56,26,.1);
  }
  .field-wrap input.valid {
    border-color: var(--green);
  }
  .field-icon {
    position: absolute; right: 14px;
    color: #ccc; font-size: 1rem;
    pointer-events: none;
  }
  .toggle-pw {
    position: absolute; right: 14px;
    background: none; border: none;
    cursor: pointer;
    color: #bbb;
    font-size: .75rem;
    font-family: 'Boldonse', system-ui;
    padding: 2px 4px;
    transition: color .2s;
  }
  .toggle-pw:hover { color: var(--green); }

  .field-error {
    font-size: .73rem; color: var(--red);
    display: none; align-items: center; gap: 4px;
  }
  .field-error.visible { display: flex; }

  /* password strength */
  .pw-strength { margin-top: 8px; }
  .pw-strength-bars {
    display: flex; gap: 4px; margin-bottom: 4px;
  }
  .pw-bar {
    flex: 1; height: 4px; border-radius: 100px;
    background: rgba(0,0,0,.1);
    transition: background .3s;
  }
  .pw-strength-label {
    font-size: .72rem; color: #aaa;
    transition: color .3s;
  }

  /* phone */
  .phone-code {
    border: 1.5px solid rgba(0,0,0,.12);
    border-right: none;
    border-radius: 14px 0 0 14px;
    padding: 13px 10px 13px 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: .88rem;
    background: #fff;
    color: var(--dark);
    outline: none;
    cursor: pointer;
    flex-shrink: 0;
    transition: border-color .2s;
  }
  .field-wrap:focus-within .phone-code { border-color: var(--green); }
  .field-wrap input.invalid ~ select,
  .field-wrap select.invalid { border-color: var(--red); }

  /* checkbox */
  .checkbox-row {
    display: flex; align-items: flex-start; gap: 12px;
    margin-bottom: 22px;
    padding: 16px;
    background: #fff;
    border-radius: 14px;
    border: 1.5px solid rgba(0,0,0,.08);
    cursor: pointer;
    transition: border-color .2s;
  }
  .checkbox-row:hover { border-color: var(--green); }
  .checkbox-row.err-check { border-color: var(--red); }

  .custom-check {
    width: 20px; height: 20px; border-radius: 6px;
    border: 2px solid rgba(0,0,0,.2);
    background: #fff;
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    transition: background .2s, border-color .2s;
    margin-top: 1px;
    cursor: pointer;
  }
  .custom-check.checked {
    background: var(--green);
    border-color: var(--green);
  }
  .check-mark { color: #fff; font-size: .75rem; display: none; }
  .custom-check.checked .check-mark { display: block; }

  .checkbox-text {
    font-size: .83rem;
    line-height: 1.5;
    color: #555;
  }
  .checkbox-text a { color: var(--green); font-weight: 500; text-decoration: none; }
  .checkbox-text a:hover { text-decoration: underline; }
  .check-error {
    font-size: .72rem; color: var(--red);
    margin-top: -16px; margin-bottom: 16px;
    display: none;
  }
  .check-error.visible { display: block; }

  /* submit */
  .btn-submit {
    width: 100%;
    background: var(--orange);
    color: #fff;
    border: none;
    border-radius: 14px;
    padding: 16px;
    font-family: 'Boldonse', system-ui;
    font-size: 1rem;
    cursor: pointer;
    transition: background .2s, transform .15s;
    margin-bottom: 16px;
  }
  .btn-submit:hover { background: var(--red); transform: scale(1.01); }
  .btn-submit:active { transform: scale(.99); }

  /* divider & social */
  .divider {
    display: flex; align-items: center; gap: 12px;
    margin: 2px 0 18px;
  }
  .divider-line { flex: 1; height: 1px; background: rgba(0,0,0,.1); }
  .divider-text { font-size: .78rem; color: #aaa; white-space: nowrap; }

  .social-btns { display: flex; gap: 10px; }
  .social-btn {
    flex: 1;
    border: 1.5px solid rgba(0,0,0,.1);
    background: #fff;
    border-radius: 12px;
    padding: 11px;
    font-family: 'DM Sans', sans-serif;
    font-size: .83rem;
    font-weight: 500;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 7px;
    color: var(--dark);
    transition: background .15s, border-color .15s;
  }
  .social-btn:hover { background: rgba(0,0,0,.03); border-color: rgba(0,0,0,.2); }

  /* success overlay */
  .success-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(17,16,8,.65);
    z-index: 200;
    align-items: center; justify-content: center;
  }
  .success-overlay.show { display: flex; }
  .success-box {
    background: #fff;
    border-radius: 24px;
    padding: 48px 40px;
    text-align: center;
    max-width: 380px; width: 90%;
    animation: popIn .35s cubic-bezier(.34,1.56,.64,1) both;
  }
  @keyframes popIn {
    from { opacity: 0; transform: scale(.8); }
    to   { opacity: 1; transform: scale(1); }
  }
  .success-icon { font-size: 3rem; margin-bottom: 16px; }
  .success-box h2 {
    font-family: 'Boldonse', system-ui;
    font-size: 1.6rem; margin-bottom: 10px; color: var(--dark);
  }
  .success-box h2 span { color: var(--green); }
  .success-box p { font-size: .88rem; color: #666; margin-bottom: 28px; line-height: 1.65; }
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
  @media (max-width: 900px) {
    body { flex-direction: column; }
    .left-panel { min-height: auto; padding: 36px 28px 44px; }
    .right-panel { width: 100%; padding: 44px 24px 60px; min-height: auto; }
    .left-body h1 { font-size: 2rem; }
  }
</style>
</head>
<body>

<!-- SUCCESS OVERLAY -->
<div class="success-overlay" id="success-overlay">
  <div class="success-box">
    <div class="success-icon">🌱</div>
    <h2>You're in, <span id="welcome-name">friend</span>!</h2>
    <p>Your Foovia account is ready. Let's set up your nutrition goals and get you started on your health journey.</p>
    <a href="foovia-tracker.html" class="btn-go">Start tracking →</a>
  </div>
</div>

<!-- LEFT PANEL -->
<div class="left-panel">
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>
  <div class="blob blob-3"></div>

  <a href="foovia.html" class="left-logo">🌿 FOOVIA</a>

  <div class="left-body">
    <h1>Your health<br>journey<br>starts <span class="gr">here.</span></h1>
    <p>Join thousands of people using Foovia to eat smarter, train better, and reduce food waste — one day at a time.</p>
  </div>

  <div class="steps-preview">
    <div class="step-item">
      <div class="step-num">1</div>
      <span class="step-label">Create your free account</span>
    </div>
    <div class="step-item">
      <div class="step-num">2</div>
      <span class="step-label">Complete your health profile</span>
    </div>
    <div class="step-item">
      <div class="step-num">3</div>
      <span class="step-label">Get your personalised plan</span>
    </div>
    <div class="step-item">
      <div class="step-num">4</div>
      <span class="step-label">Start eating & training smarter</span>
    </div>
  </div>
</div>

<!-- RIGHT PANEL -->
<div class="right-panel">
  <p class="form-eyebrow">Free forever</p>
  <h1 class="form-title">Create your<br>Foovia account</h1>
  <p class="form-sub">Already have one? <a href="foovia-signin.html">Sign in instead →</a></p>

  <?php if (!empty($error_message)): ?>
    <div style="background: #fee; color: #c33; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem;">
      <?php echo htmlspecialchars($error_message); ?>
    </div>
  <?php endif; ?>

  <form id="signupForm" action="" method="POST" novalidate>
  <div class="field-group">

    <!-- NAME -->
    <div class="field">
      <label for="name">Full name</label>
      <div class="field-wrap">
        <input type="text" id="name" name="name" placeholder="Amine Trabelsi" autocomplete="name"/>
        <span class="field-icon">👤</span>
      </div>
      <span class="field-error" id="err-name">Please enter your full name.</span>
    </div>

    <!-- EMAIL -->
    <div class="field">
      <label for="email">Email address</label>
      <div class="field-wrap">
        <input type="email" id="email" name="email" placeholder="you@gmail.com" autocomplete="email"/>
        <span class="field-icon">✉</span>
      </div>
      <span class="field-error" id="err-email">Email must end with @gmail.com</span>
    </div>

    <!-- PHONE -->
    <div class="field">
      <label for="phone">Phone number</label>
      <div class="field-wrap">
        <select id="phone-code" class="phone-code">
          <option value="+216">🇹🇳 +216</option>
          <option value="+1">🇺🇸 +1</option>
          <option value="+44">🇬🇧 +44</option>
          <option value="+33">🇫🇷 +33</option>
          <option value="+49">🇩🇪 +49</option>
          <option value="+212">🇲🇦 +212</option>
          <option value="+213">🇩🇿 +213</option>
          <option value="+20">🇪🇬 +20</option>
          <option value="+966">🇸🇦 +966</option>
          <option value="+971">🇦🇪 +971</option>
        </select>
        <input type="tel" id="phone" name="phone" placeholder="XX XXX XXX" autocomplete="tel" style="padding-left:0; border-left:none; border-radius:0 14px 14px 0;"/>
      </div>
      <span class="field-error" id="err-phone">Phone must be exactly 8 digits.</span>
    </div>

    <!-- PASSWORD -->
    <div class="field">
      <label for="password">Password</label>
      <div class="field-wrap">
        <input type="password" id="password" name="password" placeholder="At least 8 characters" autocomplete="new-password" oninput="checkStrength(this.value)"/>
        <button class="toggle-pw" type="button" onclick="togglePw('password', this)">Show</button>
      </div>
      <div class="pw-strength" id="pw-strength" style="display:none">
        <div class="pw-strength-bars">
          <div class="pw-bar" id="bar1"></div>
          <div class="pw-bar" id="bar2"></div>
          <div class="pw-bar" id="bar3"></div>
          <div class="pw-bar" id="bar4"></div>
        </div>
        <span class="pw-strength-label" id="pw-label">Too short</span>
      </div>
      <span class="field-error" id="err-password">Password must be at least 8 characters.</span>
    </div>

    <!-- CONFIRM PASSWORD -->
    <div class="field">
      <label for="confirm">Confirm password</label>
      <div class="field-wrap">
        <input type="password" id="confirm" name="confirm-password" placeholder="Repeat your password" autocomplete="new-password"/>
        <button class="toggle-pw" type="button" onclick="togglePw('confirm', this)">Show</button>
      </div>
      <span class="field-error" id="err-confirm">Passwords do not match.</span>
    </div>

  </div>

  <!-- TERMS CHECKBOX -->
  <div class="checkbox-row" id="terms-row" onclick="toggleCheck()">
    <div class="custom-check" id="custom-check">
      <span class="check-mark">✓</span>
    </div>
    <div class="checkbox-text">
      I agree to Foovia's <a href="#" onclick="event.stopPropagation()">Terms of Service</a> and <a href="#" onclick="event.stopPropagation()">Privacy Policy</a>. I understand my data is used to personalise my experience.
    </div>
  </div>
  <div class="check-error" id="err-terms">You must agree to the terms to continue.</div>

  <button type="submit" name="signup_submit" class="btn-submit">Create my free account</button>

  <!-- Hidden fields for additional user data -->
  <input type="hidden" name="lastname" value="">
  <input type="hidden" name="gender" value="Not specified">
  <input type="hidden" name="birthday" value="">
  <input type="hidden" name="height" value="0">
  <input type="hidden" name="weight" value="0">
  <input type="hidden" name="bmi" value="0">
  <input type="hidden" name="activity" value="">
  <input type="hidden" name="illness" value="">
  <input type="hidden" name="allergie" value="">
  <input type="hidden" name="medicament" value="">
  <input type="hidden" name="terms_accepted" id="terms_hidden" value="0">

  </form>

<script>
let termsChecked = false;

// Add validation functions from auth-sign-up.php
function showError(fieldId, errorId) {
    const field = document.getElementById(fieldId);
    const error = document.getElementById(errorId);
    field.classList.add('invalid');
    field.classList.remove('valid');
    error.style.display = 'block';
    return false;
}
function showValid(fieldId, errorId) {
    const field = document.getElementById(fieldId);
    const error = document.getElementById(errorId);
    field.classList.remove('invalid');
    field.classList.add('valid');
    error.style.display = 'none';
    return true;
}

function toggleCheck() {
  termsChecked = !termsChecked;
  const chk = document.getElementById('custom-check');
  const row  = document.getElementById('terms-row');
  chk.classList.toggle('checked', termsChecked);
  row.classList.remove('err-check');
  document.getElementById('err-terms').classList.remove('visible');
  document.getElementById('terms_hidden').value = termsChecked ? '1' : '0';
}

function togglePw(id, btn) {
  const input = document.getElementById(id);
  const hidden = input.type === 'password';
  input.type   = hidden ? 'text' : 'password';
  btn.textContent = hidden ? 'Hide' : 'Show';
}

const STRENGTH_COLORS = ['#C0381A','#F0A830','#4BAE52','#2E4A28'];
const STRENGTH_LABELS = ['Too short','Weak','Good','Strong'];

function checkStrength(val) {
  const wrap = document.getElementById('pw-strength');
  if (!val) { wrap.style.display = 'none'; return; }
  wrap.style.display = 'block';

  let score = 0;
  if (val.length >= 8)  score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  score = Math.max(1, score);

  const color = STRENGTH_COLORS[score - 1];
  for (let i = 1; i <= 4; i++) {
    document.getElementById('bar' + i).style.background = i <= score ? color : 'rgba(0,0,0,.1)';
  }
  const label = document.getElementById('pw-label');
  label.textContent = STRENGTH_LABELS[score - 1];
  label.style.color = color;
}

function validate(id, check, errId) {
  const input = document.getElementById(id);
  const err   = document.getElementById(errId);
  const ok    = check(input.value);
  input.classList.toggle('invalid', !ok);
  if (ok) input.classList.add('valid'); else input.classList.remove('valid');
  err.classList.toggle('visible', !ok);
  return ok;
}

function validateName() {
  return validate('name', v => v.trim().length >= 2, 'name-error');
}

function validateEmail() {
  return validate('email', v => /^[a-zA-Z0-9._%+\-]+@gmail\.com$/.test(v), 'email-error');
}

function validatePhone() {
  return validate('phone', v => /^\+\d{3}\d{8}$/.test(v), 'phone-error');
}

function validatePassword() {
  return validate('password', v => v.length >= 8, 'password-error');
}

function validateConfirm() {
  const pw = document.getElementById('password').value;
  return validate('confirm-password', v => v === pw && v.length > 0, 'confirm-password-error');
}

function handleSignUp() {
  // Combine phone code and number
  const phoneCode = document.getElementById('phone-code').value;
  const phoneNumber = document.getElementById('phone').value;
  document.getElementById('phone').value = phoneCode + phoneNumber;

  const nameOk    = validateName();
  const emailOk   = validateEmail();
  const phoneOk   = validatePhone();
  const passOk    = validatePassword();
  const confirmOk = validateConfirm();

  const terms = document.getElementById('terms_accepted');
  const termsError = document.getElementById('terms-error');
  let termsOk = true;
  if (!terms.checked) {
    termsError.style.display = 'block';
    termsOk = false;
  } else {
    termsError.style.display = 'none';
  }

  if (!nameOk || !emailOk || !phoneOk || !passOk || !confirmOk || !termsOk) {
    // Restore original phone value if validation failed
    document.getElementById('phone').value = phoneNumber;
    return false;
  }
  return true;
}
</script>

<script>
// Add input restrictions
document.getElementById('phone').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 8);
});

// Add name input restriction
document.getElementById('name').addEventListener('input', function () {
// Add an eye icon to toggle password visibility
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Add event listeners for password toggle
const passwordToggle = document.createElement('i');
passwordToggle.className = 'fa fa-eye';
passwordToggle.id = 'password-toggle';
passwordToggle.style.cursor = 'pointer';
passwordToggle.style.position = 'absolute';
passwordToggle.style.right = '10px';
passwordToggle.style.top = '50%';
passwordToggle.style.transform = 'translateY(-50%)';

document.getElementById('password').parentNode.style.position = 'relative';
document.getElementById('password').parentNode.appendChild(passwordToggle);

passwordToggle.addEventListener('click', () => togglePasswordVisibility('password', 'password-toggle'));

const confirmPasswordToggle = document.createElement('i');
confirmPasswordToggle.className = 'fa fa-eye';
confirmPasswordToggle.id = 'confirm-password-toggle';
confirmPasswordToggle.style.cursor = 'pointer';
confirmPasswordToggle.style.position = 'absolute';
confirmPasswordToggle.style.right = '10px';
confirmPasswordToggle.style.top = '50%';
confirmPasswordToggle.style.transform = 'translateY(-50%)';

document.getElementById('confirm-password').parentNode.style.position = 'relative';
document.getElementById('confirm-password').parentNode.appendChild(confirmPasswordToggle);

confirmPasswordToggle.addEventListener('click', () => togglePasswordVisibility('confirm-password', 'confirm-password-toggle'));


</body>
</html>
