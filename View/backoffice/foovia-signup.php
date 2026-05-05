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
    } elseif (strlen($name) < 3) {
        $error_message = 'Full name must be at least 3 characters long.';
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
                'user',
                'normal',
                'active',
                '00:00:00'
            );

            $controller = new Controller_user();
            $result = $controller->add_user($user);

            $success_message = 'Account created successfully! Redirecting to survey...';
            $_SESSION['signup_email'] = $email;
            header('Location: foovia-survey.php');
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
<link rel="stylesheet" href="foovia-signup.css">
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

  <a href="../frontoffice/foovia.php" class="left-logo"> FOOVIA</a>

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
  <p class="form-sub">Already have one? <a href="../frontoffice/foovia-signin.php">Sign in instead →</a></p>

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
        <input type="text" id="name" name="name" placeholder="Amine Trabelsi" autocomplete="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"/>
        <span class="field-icon">👤</span>
      </div>
      <span class="field-error" id="err-name">Full name must be at least 3 characters long.</span>
    </div>

    <!-- EMAIL -->
    <div class="field">
      <label for="email">Email address</label>
      <div class="field-wrap">
        <input type="text" id="email" name="email" placeholder="you@gmail.com" autocomplete="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"/>
        <span class="field-icon">✉</span>
      </div>
      <span class="field-error" id="err-email">Email must be in format: example@gmail.com</span>
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
        <input type="text" id="phone" name="phone" placeholder="XX XXX XXX" autocomplete="tel" style="padding-left:0; border-left:none; border-radius:0 14px 14px 0;" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"/>
      </div>
      <span class="field-error" id="err-phone">Phone must be exactly 8 digits.</span>
    </div>

    <!-- PASSWORD -->
    <div class="field">
      <label for="password">Password</label>
      <div class="field-wrap">
        <input type="password" id="password" name="password" placeholder="At least 8 characters" autocomplete="new-password" oninput="checkStrength(this.value)" value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>"/>
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
        <input type="password" id="confirm" name="confirm-password" placeholder="Repeat your password" autocomplete="new-password" value="<?php echo htmlspecialchars($_POST['confirm-password'] ?? ''); ?>"/>
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
  return validate('name', v => v.trim().length >= 3, 'err-name');
}

function validateEmail() {
  return validate('email', v => /^[a-zA-Z0-9._%+\-]+@gmail\.com$/.test(v), 'err-email');
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
// Keep only digits and stop typing after 8 numbers.
document.getElementById('phone')?.addEventListener('input', function () {
  this.value = this.value.replace(/\D/g, '').slice(0, 8);
});
</script>

</body>
</html>
