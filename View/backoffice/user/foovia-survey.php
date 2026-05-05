<?php
session_start();
include(__DIR__ . '/../../../controller/Controller_user.php');

$error_message = '';
$success_message = '';
$userEmail = $_SESSION['signup_email'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

if (!$userEmail && $userId) {
    try {
        $db = config::getConnexion();
        $sql = "SELECT email_user FROM user WHERE id_user = :id";
        $query = $db->prepare($sql);
        $query->execute(['id' => $userId]);
        $row = $query->fetch();
        if ($row) {
            $userEmail = $row['email_user'];
        }
    } catch (Exception $e) {
        // ignore here; the redirect below will handle a missing user
    }
}

if (!$userEmail) {
    header('Location: ../../frontoffice/user/foovia-signin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['survey_submit'])) {
    try {
        $controller = new Controller_user();
        $db = config::getConnexion();

        $sql = "SELECT id_user, name_user, password_user, phone_user FROM user WHERE email_user = :email";
        $query = $db->prepare($sql);
        $query->execute(['email' => $userEmail]);
        $result = $query->fetch();

        if (!$result) {
            $error_message = 'User not found.';
        } else {
            $user_id    = $result['id_user'];
            $user_pw    = $result['password_user'];
            $user_phone = $result['phone_user'];

            $birthday = $_POST['birthday'] ?? '';
            $height   = intval($_POST['height'] ?? 0);
            $weight   = intval($_POST['weight'] ?? 0);
            $bmi      = floatval($_POST['bmi'] ?? 0);

            $user = new User(
                $user_id,
                $result['name_user'],
                $_POST['lastname'] ?? $result['name_user'],
                $userEmail,
                $user_pw,
                $user_phone,
                $_POST['gender'] ?? '',
                $birthday,
                $height,
                $weight,
                $bmi,
                $_POST['activity'] ?? '',
                $_POST['illness'] ?? '',
                $_POST['allergie'] ?? '',
                $_POST['medicament'] ?? '',
                date('Y-m-d H:i:s'),
                'user',
                'normal',
                'active',
                '00:00:00'
            );

            $controller->update_user($user, $user_id);
            $success_message = 'Survey completed successfully! Redirecting to login...';
            unset($_SESSION['signup_email']);
            header('refresh:2;url=../frontoffice/foovia.php');
            exit;
        }
    } catch (Exception $e) {
        $error_message = 'An error occurred: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA — Your Health Profile</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Boldonse&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="foovia-survey.css">
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">
  <a href="../frontoffice/foovia.php" class="topbar-logo">🌿 FOOV<span>IA</span></a>
  <div class="progress-wrap">
    <div class="progress-labels">
      <span id="pl-1" class="active">Profile</span>
      <span id="pl-2">Body</span>
      <span id="pl-3">Activity</span>
      <span id="pl-4">Health</span>
      <span id="pl-5">Done</span>
    </div>
    <div class="progress-track">
      <div class="progress-fill" id="progress-fill"></div>
    </div>
  </div>
  <div class="topbar-step">Step <span id="step-num">1</span> / 4</div>
</div>

<main>
<form method="POST" action="" id="surveyForm" class="survey-wrap">
  <input type="hidden" name="gender" id="gender-hidden">
  <input type="hidden" name="birthday" id="birthday-hidden">
  <input type="hidden" name="height" id="height-hidden">
  <input type="hidden" name="weight" id="weight-hidden">
  <input type="hidden" name="bmi" id="bmi-hidden">
  <input type="hidden" name="activity" id="activity-hidden">
  <input type="hidden" name="illness" id="illness-hidden">
  <input type="hidden" name="allergie" id="allergie-hidden">
  <input type="hidden" name="medicament" id="medicament-hidden">
  <input type="hidden" name="survey_submit" value="1">
  <?php if (!empty($error_message)): ?>
    <div class="card alert-card">
      <div class="step-error visible alert-message">
        <?php echo htmlspecialchars($error_message); ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- ══ STEP 1 — Profile ══ -->
  <div class="step active" id="step-1">
    <p class="step-eyebrow">Step 1 of 4 — About you</p>
    <h1 class="step-title">Tell us about<br><em>yourself</em></h1>
    <p class="step-desc">Help us personalise your experience. This takes less than 2 minutes.</p>

    <!-- GENDER -->
    <div class="card">
      <div class="card-label">👤 Gender</div>
      <div class="gender-grid">
        <div class="gender-tile" onclick="selectGender(this,'male')">
          <span class="gt-icon">♂️</span>
          <span class="gt-label">Male</span>
        </div>
        <div class="gender-tile" onclick="selectGender(this,'female')">
          <span class="gt-icon">♀️</span>
          <span class="gt-label">Female</span>
        </div>
        <div class="gender-tile" onclick="selectGender(this,'other')">
          <span class="gt-icon">⚧️</span>
          <span class="gt-label">Other</span>
        </div>
      </div>
      <div class="step-error" id="err-gender">Please select your gender.</div>
    </div>

    <!-- BIRTHDAY -->
    <div class="card">
      <div class="card-label">🎂 Date of birth</div>
      <div class="field-row">
        <div class="field">
          <label>Day</label>
          <input type="number" id="dob-day" placeholder="DD"/>
        </div>
        <div class="field">
          <label>Month</label>
          <select id="dob-month">
            <option value="">Month</option>
            <option>January</option><option>February</option><option>March</option>
            <option>April</option><option>May</option><option>June</option>
            <option>July</option><option>August</option><option>September</option>
            <option>October</option><option>November</option><option>December</option>
          </select>
        </div>
        <div class="field field-span-2">
          <label>Year</label>
          <input type="number" id="dob-year" placeholder="YYYY"/>
        </div>
      </div>
      <div class="step-error" id="err-dob">You must be at least 15 years old.</div>
    </div>

    <div class="nav-btns">
      <button type="button" class="btn-next" onclick="goNext(1)">Continue →</button>
      <a href="../frontoffice/foovia.php" class="btn-skip">Skip</a>
    </div>
  </div>

  <!-- ══ STEP 2 — Body Metrics ══ -->
  <div class="step" id="step-2">
    <p class="step-eyebrow">Step 2 of 4 — Body metrics</p>
    <h1 class="step-title">Your <em>body</em><br>stats</h1>
    <p class="step-desc">Used to calculate your BMI and personalise your nutrition targets. All data stays private.</p>

    <!-- HEIGHT -->
    <div class="card">
      <div class="card-label space-between">
        <span>📏 Height</span>
        <div class="unit-toggle">
          <button type="button" class="unit-btn active" id="h-cm" onclick="setHeightUnit('cm')">cm</button>
          <button type="button" class="unit-btn" id="h-ft" onclick="setHeightUnit('ft')">ft/in</button>
        </div>
      </div>
      <div id="height-cm-wrap">
        <div class="field">
          <label>Height (cm)</label>
          <input type="number" id="height-cm" placeholder="e.g. 175" oninput="recalcBMI()"/>
        </div>
      </div>
      <div id="height-ft-wrap" class="hidden">
        <div class="field-row">
          <div class="field">
            <label>Feet</label>
            <input type="number" id="height-ft" placeholder="5" oninput="recalcBMI()"/>
          </div>
          <div class="field">
            <label>Inches</label>
            <input type="number" id="height-in" placeholder="9" oninput="recalcBMI()"/>
          </div>
        </div>
      </div>
      <div class="step-error" id="err-height">Please enter your height.</div>
    </div>

    <!-- WEIGHT -->
    <div class="card">
      <div class="card-label space-between">
        <span>⚖️ Weight</span>
        <div class="unit-toggle">
          <button type="button" class="unit-btn active" id="w-kg" onclick="setWeightUnit('kg')">kg</button>
          <button type="button" class="unit-btn" id="w-lb" onclick="setWeightUnit('lb')">lb</button>
        </div>
      </div>
      <div class="field">
        <label id="weight-label">Weight (kg)</label>
        <input type="number" id="weight" placeholder="e.g. 70" oninput="recalcBMI()"/>
      </div>
      <div class="step-error" id="err-weight">Please enter your weight.</div>
    </div>

    <!-- BMI -->
    <div class="card">
      <div class="card-label">📊 BMI — Body Mass Index</div>
      <div class="bmi-display">
        <div class="bmi-num" id="bmi-val">—</div>
        <div class="bmi-info">
          <div class="bmi-label" id="bmi-label">Enter height & weight</div>
          <div class="bmi-sub" id="bmi-sub">Your BMI will appear automatically</div>
          <div class="bmi-scale">
            <div class="bmi-seg seg-1"></div>
            <div class="bmi-seg seg-2"></div>
            <div class="bmi-seg seg-3"></div>
            <div class="bmi-seg seg-4"></div>
            <div class="bmi-seg seg-5"></div>
          </div>
          <div class="bmi-arrow" id="bmi-arrow">Underweight · Normal · Overweight · Obese · Severe</div>
        </div>
      </div>
    </div>

    <div class="nav-btns">
      <button type="button" class="btn-back" onclick="goBack(2)">← Back</button>
      <button type="button" class="btn-next" onclick="goNext(2)">Continue →</button>
      <a href="../frontoffice/foovia.php" class="btn-skip">Skip</a>
    </div>
  </div>

  <!-- ══ STEP 3 — Activity ══ -->
  <div class="step" id="step-3">
    <p class="step-eyebrow">Step 3 of 4 — Lifestyle</p>
    <h1 class="step-title">How <em>active</em><br>are you?</h1>
    <p class="step-desc">We use this to calculate your daily calorie needs. Be honest — this is for your benefit!</p>

    <div class="card">
      <div class="card-label">🏃 Activity level</div>
      <div class="activity-grid">
        <div class="activity-tile" onclick="selectActivity(this,'sedentary')">
          <div class="at-icon sed">🪑</div>
          <div class="at-body">
            <div class="at-title">Sedentary</div>
            <div class="at-sub">Little or no exercise, desk job</div>
          </div>
          <div class="at-check"></div>
        </div>
        <div class="activity-tile" onclick="selectActivity(this,'light')">
          <div class="at-icon light">🚶</div>
          <div class="at-body">
            <div class="at-title">Lightly active</div>
            <div class="at-sub">Light exercise 1–3 days/week</div>
          </div>
          <div class="at-check"></div>
        </div>
        <div class="activity-tile" onclick="selectActivity(this,'moderate')">
          <div class="at-icon moderate">🚴</div>
          <div class="at-body">
            <div class="at-title">Moderately active</div>
            <div class="at-sub">Moderate exercise 3–5 days/week</div>
          </div>
          <div class="at-check"></div>
        </div>
        <div class="activity-tile" onclick="selectActivity(this,'very')">
          <div class="at-icon very">🏋️</div>
          <div class="at-body">
            <div class="at-title">Very active</div>
            <div class="at-sub">Hard exercise 6–7 days/week</div>
          </div>
          <div class="at-check"></div>
        </div>
        <div class="activity-tile" onclick="selectActivity(this,'extreme')">
          <div class="at-icon extreme">🔥</div>
          <div class="at-body">
            <div class="at-title">Extremely active</div>
            <div class="at-sub">Athlete, physical job, or 2× training</div>
          </div>
          <div class="at-check"></div>
        </div>
      </div>
      <div class="step-error" id="err-activity">Please select your activity level.</div>
    </div>

    <div class="nav-btns">
      <button type="button" class="btn-back" onclick="goBack(3)">← Back</button>
      <button type="button" class="btn-next" onclick="goNext(3)">Continue →</button>
      <a href="../frontoffice/foovia.php" class="btn-skip">Skip</a>
    </div>
  </div>

  <!-- ══ STEP 4 — Health ══ -->
  <div class="step" id="step-4">
    <p class="step-eyebrow">Step 4 of 4 — Health details</p>
    <h1 class="step-title">Your <em>health</em><br>background</h1>
    <p class="step-desc">This helps us keep your meal and workout plans safe and appropriate. All information is confidential.</p>

    <!-- ILLNESSES -->
    <div class="card">
      <div class="card-label">🏥 Illnesses / Conditions</div>
      <div class="tag-input-wrap" onclick="focusInput('illness-input')">
        <div id="illness-tags"></div>
        <input class="tag-real-input" id="illness-input" placeholder="Type and press Enter…" onkeydown="handleTag(event,'illness')"/>
      </div>
      <div class="tag-hint">Press Enter or comma to add. <strong>Suggestions:</strong></div>
      <div class="tag-suggestions">
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('illness','Diabetes')">Diabetes</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('illness','Hypertension')">Hypertension</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('illness','Asthma')">Asthma</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('illness','Celiac')">Celiac</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('illness','PCOS')">PCOS</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('illness','Hypothyroidism')">Hypothyroidism</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('illness','IBS')">IBS</button>
      </div>
      <div class="none-toggle" id="none-illness" onclick="toggleNone('illness')">
        <div class="none-box" id="none-illness-box"></div>
        No known illnesses or conditions
      </div>
    </div>

    <!-- ALLERGIES -->
    <div class="card">
      <div class="card-label">⚠️ Food allergies & intolerances</div>
      <div class="tag-input-wrap" onclick="focusInput('allergy-input')">
        <div id="allergy-tags"></div>
        <input class="tag-real-input" id="allergy-input" placeholder="Type and press Enter…" onkeydown="handleTag(event,'allergy')"/>
      </div>
      <div class="tag-hint">Press Enter or comma to add. <strong>Suggestions:</strong></div>
      <div class="tag-suggestions">
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('allergy','Gluten')">Gluten</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('allergy','Dairy')">Dairy</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('allergy','Nuts')">Nuts</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('allergy','Eggs')">Eggs</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('allergy','Shellfish')">Shellfish</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('allergy','Soy')">Soy</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('allergy','Sesame')">Sesame</button>
      </div>
      <div class="none-toggle" id="none-allergy" onclick="toggleNone('allergy')">
        <div class="none-box" id="none-allergy-box"></div>
        No known allergies or intolerances
      </div>
    </div>

    <!-- MEDICATIONS -->
    <div class="card">
      <div class="card-label">💊 Current medications</div>
      <div class="tag-input-wrap" onclick="focusInput('medic-input')">
        <div id="medic-tags"></div>
        <input class="tag-real-input" id="medic-input" placeholder="Type and press Enter…" onkeydown="handleTag(event,'medic')"/>
      </div>
      <div class="tag-hint">Press Enter or comma to add. <strong>Suggestions:</strong></div>
      <div class="tag-suggestions">
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('medic','Metformin')">Metformin</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('medic','Levothyroxine')">Levothyroxine</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('medic','Ibuprofen')">Ibuprofen</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('medic','Omeprazole')">Omeprazole</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('medic','Vitamin D')">Vitamin D</button>
        <button type="button" class="tag-sug-btn" onclick="addTagDirect('medic','Omega-3')">Omega-3</button>
      </div>
      <div class="none-toggle" id="none-medic" onclick="toggleNone('medic')">
        <div class="none-box" id="none-medic-box"></div>
        Not currently taking any medication
      </div>
    </div>

    <div class="nav-btns">
      <button type="button" class="btn-back" onclick="goBack(4)">← Back</button>
      <button type="button" class="btn-next finish" onclick="goNext(4)">Complete my profile ✓</button>
      <a href="../frontoffice/foovia.php" class="btn-skip">Skip</a>
    </div>
  </div>

  <!-- ══ SUCCESS ══ -->
  <div class="success-screen" id="success-screen">
    <span class="success-big-icon">🎉</span>
    <h1>Profile <span>complete!</span></h1>
    <p>We've built your personalised plan based on your answers. Your nutrition targets, recipe suggestions, and workout plan are all ready.</p>
    <div class="success-chips" id="summary-chips"></div>
    <a href="foovia-tracker.html" class="btn-go">Start tracking today →</a>
  </div>
</form>

<script>
const state = {
  gender: null,
  activity: null,
  heightUnit: 'cm',
  weightUnit: 'kg',
  tags: { illness: [], allergy: [], medic: [] },
  none: { illness: false, allergy: false, medic: false }
};

const STEPS = 4;
const PROGRESS_LABELS = ['pl-1','pl-2','pl-3','pl-4','pl-5'];

function setProgress(step) {
  const pct = ((step - 1) / STEPS) * 100;
  document.getElementById('progress-fill').style.width = pct + '%';
  document.getElementById('step-num').textContent = step > STEPS ? STEPS : step;
  PROGRESS_LABELS.forEach((id, i) => {
    document.getElementById(id).classList.toggle('active', i < step);
  });
}

// ── GENDER ──
function selectGender(el, val) {
  document.querySelectorAll('.gender-tile').forEach(t => t.classList.remove('selected'));
  el.classList.add('selected');
  state.gender = val;
  document.getElementById('err-gender').classList.remove('visible');
}

// ── ACTIVITY ──
function selectActivity(el, val) {
  document.querySelectorAll('.activity-tile').forEach(t => t.classList.remove('selected'));
  el.classList.add('selected');
  state.activity = val;
  document.getElementById('err-activity').classList.remove('visible');
}

// ── HEIGHT / WEIGHT UNITS ──
function setHeightUnit(unit) {
  state.heightUnit = unit;
  document.getElementById('height-cm-wrap').style.display = unit === 'cm' ? '' : 'none';
  document.getElementById('height-ft-wrap').style.display = unit === 'ft' ? '' : 'none';
  document.getElementById('h-cm').classList.toggle('active', unit === 'cm');
  document.getElementById('h-ft').classList.toggle('active', unit === 'ft');
  recalcBMI();
}
function setWeightUnit(unit) {
  state.weightUnit = unit;
  document.getElementById('weight-label').textContent = 'Weight (' + unit + ')';
  document.getElementById('w-kg').classList.toggle('active', unit === 'kg');
  document.getElementById('w-lb').classList.toggle('active', unit === 'lb');
  recalcBMI();
}

// ── BMI ──
function recalcBMI() {
  let heightM = null, weightKg = null;

  if (state.heightUnit === 'cm') {
    const cm = parseFloat(document.getElementById('height-cm').value);
    if (cm > 0) heightM = cm / 100;
  } else {
    const ft = parseFloat(document.getElementById('height-ft').value) || 0;
    const inch = parseFloat(document.getElementById('height-in').value) || 0;
    const totalIn = ft * 12 + inch;
    if (totalIn > 0) heightM = totalIn * 0.0254;
  }

  const wRaw = parseFloat(document.getElementById('weight').value);
  if (wRaw > 0) weightKg = state.weightUnit === 'kg' ? wRaw : wRaw * 0.453592;

  if (!heightM || !weightKg) {
    document.getElementById('bmi-val').textContent = '—';
    document.getElementById('bmi-label').textContent = 'Enter height & weight';
    document.getElementById('bmi-sub').textContent = 'Your BMI will appear automatically';
    return;
  }

  const bmi = weightKg / (heightM * heightM);
  const rounded = Math.round(bmi * 10) / 10;
  document.getElementById('bmi-val').textContent = rounded;

  let label, sub, color;
  if (bmi < 18.5)      { label = 'Underweight'; sub = 'BMI below 18.5'; color = '#5ab5f5'; }
  else if (bmi < 25)   { label = 'Normal weight'; sub = 'BMI 18.5 – 24.9'; color = '#4BAE52'; }
  else if (bmi < 30)   { label = 'Overweight'; sub = 'BMI 25 – 29.9'; color = '#F0A830'; }
  else if (bmi < 35)   { label = 'Obese'; sub = 'BMI 30 – 34.9'; color = '#D94F00'; }
  else                 { label = 'Severely obese'; sub = 'BMI 35+'; color = '#C0381A'; }

  document.getElementById('bmi-label').textContent = label;
  document.getElementById('bmi-sub').textContent = sub;
  document.getElementById('bmi-val').style.color = color;
}

// ── TAGS ──
function focusInput(id) { document.getElementById(id).focus(); }

function handleTag(e, type) {
  if (e.key === 'Enter' || e.key === ',') {
    e.preventDefault();
    const input = e.target;
    const val = input.value.replace(',','').trim();
    if (val) addTag(type, val);
    input.value = '';
  }
}

function addTag(type, val) {
  if (state.none[type]) return;
  if (state.tags[type].includes(val)) return;
  state.tags[type].push(val);
  renderTags(type);
}

function addTagDirect(type, val) {
  if (state.none[type]) {
    toggleNone(type);
  }
  addTag(type, val);
}

function removeTag(type, val) {
  state.tags[type] = state.tags[type].filter(t => t !== val);
  renderTags(type);
}

function renderTags(type) {
  const wrap = document.getElementById(type + '-tags');
  wrap.innerHTML = state.tags[type].map(t => `
    <span class="tag-chip ${type}">
      ${t}
      <button type="button" class="tag-chip-del" onclick="removeTag('${type}','${t}')">×</button>
    </span>
  `).join('');
}

function toggleNone(type) {
  state.none[type] = !state.none[type];
  const row = document.getElementById('none-' + type);
  const box = document.getElementById('none-' + type + '-box');
  row.classList.toggle('active', state.none[type]);
  box.textContent = state.none[type] ? '✓' : '';
  if (state.none[type]) {
    state.tags[type] = [];
    renderTags(type);
    document.getElementById(type + '-input').value = '';
  }
}

// ── NAVIGATION ──
function goNext(step) {
  if (!validateStep(step)) return;
  document.getElementById('step-' + step).classList.remove('active');

  if (step < STEPS) {
    document.getElementById('step-' + (step + 1)).classList.add('active');
    setProgress(step + 1);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } else {
    if (prepareSubmission()) {
      document.getElementById('surveyForm').submit();
    }
  }
}

function goBack(step) {
  document.getElementById('step-' + step).classList.remove('active');
  document.getElementById('step-' + (step - 1)).classList.add('active');
  setProgress(step - 1);
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function validateStep(step) {
  if (step === 1) {
    let ok = true;
    if (!state.gender) {
      document.getElementById('err-gender').classList.add('visible');
      ok = false;
    }
    const day  = parseInt(document.getElementById('dob-day').value, 10);
    const mon  = document.getElementById('dob-month').value;
    const year = parseInt(document.getElementById('dob-year').value, 10);
    const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const monthIndex = monthNames.indexOf(mon);
    const selected = (day && monthIndex >= 0 && year) ? new Date(year, monthIndex, day) : null;
    const cutoff = new Date();
    cutoff.setHours(0, 0, 0, 0);
    cutoff.setFullYear(cutoff.getFullYear() - 15);
    if (!selected || day < 1 || day > 31 || year < 1920 || selected > cutoff) {
      document.getElementById('err-dob').classList.add('visible');
      ok = false;
    } else {
      document.getElementById('err-dob').classList.remove('visible');
    }
    return ok;
  }
  if (step === 2) {
    let ok = true;
    const hasHeight = state.heightUnit === 'cm'
      ? (() => {
          const cm = parseFloat(document.getElementById('height-cm').value);
          return !isNaN(cm) && cm >= 100 && cm <= 250;
        })()
      : (() => {
          const ft = parseFloat(document.getElementById('height-ft').value);
          const inches = parseFloat(document.getElementById('height-in').value || '0');
          return !isNaN(ft) && ft >= 3 && ft <= 8 && !isNaN(inches) && inches >= 0 && inches <= 11;
        })();
    if (!hasHeight) {
      document.getElementById('err-height').classList.add('visible'); ok = false;
    } else { document.getElementById('err-height').classList.remove('visible'); }

    const weight = parseFloat(document.getElementById('weight').value);
    const weightOk = !isNaN(weight) && weight >= 30 && weight <= 300;
    if (!weightOk) {
      document.getElementById('err-weight').classList.add('visible'); ok = false;
    } else { document.getElementById('err-weight').classList.remove('visible'); }
    return ok;
  }
  if (step === 3) {
    if (!state.activity) {
      document.getElementById('err-activity').classList.add('visible');
      return false;
    }
    return true;
  }
  return true;
}

function prepareSubmission() {
  document.getElementById('gender-hidden').value = state.gender || '';

  const day = document.getElementById('dob-day').value;
  const mon = document.getElementById('dob-month').value;
  const year = document.getElementById('dob-year').value;
  const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  const monthIndex = monthNames.indexOf(mon);
  if (!day || monthIndex === -1 || !year) return false;
  document.getElementById('birthday-hidden').value = year + '-' + String(monthIndex + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');

  let height = 0;
  if (state.heightUnit === 'cm') {
    height = parseFloat(document.getElementById('height-cm').value) || 0;
  } else {
    const ft = parseFloat(document.getElementById('height-ft').value) || 0;
    const inch = parseFloat(document.getElementById('height-in').value) || 0;
    height = Math.round((ft * 12 + inch) * 2.54);
  }
  document.getElementById('height-hidden').value = height;

  const rawWeight = parseFloat(document.getElementById('weight').value) || 0;
  const weight = state.weightUnit === 'kg' ? rawWeight : Math.round(rawWeight * 0.453592);
  document.getElementById('weight-hidden').value = weight;

  const bmiRaw = parseFloat(document.getElementById('bmi-val').textContent);
  document.getElementById('bmi-hidden').value = isNaN(bmiRaw) ? '' : bmiRaw;

  document.getElementById('activity-hidden').value = state.activity || '';
  document.getElementById('illness-hidden').value = state.none.illness ? '' : state.tags.illness.join(', ');
  document.getElementById('allergie-hidden').value = state.none.allergy ? '' : state.tags.allergy.join(', ');
  document.getElementById('medicament-hidden').value = state.none.medic ? '' : state.tags.medic.join(', ');

  return true;
}

// ── SUCCESS ──
function showSuccess() {
  document.getElementById('survey-wrap') && null;
  document.querySelector('.survey-wrap').querySelector('.step.active')?.classList.remove('active');
  document.getElementById('success-screen').classList.add('active');
  document.getElementById('progress-fill').style.width = '100%';
  PROGRESS_LABELS.forEach(id => document.getElementById(id).classList.add('active'));
  document.getElementById('step-num').textContent = '4';

  // build summary chips
  const chips = [];
  const gMap = { male:'♂️ Male', female:'♀️ Female', other:'⚧️ Other' };
  if (state.gender) chips.push({ label: gMap[state.gender], color: '#4BAE52' });

  const bmi = document.getElementById('bmi-val').textContent;
  if (bmi !== '—') chips.push({ label: 'BMI ' + bmi, color: '#F0A830' });

  const aMap = { sedentary:'🪑 Sedentary', light:'🚶 Lightly active', moderate:'🚴 Moderate', very:'🏋️ Very active', extreme:'🔥 Extreme' };
  if (state.activity) chips.push({ label: aMap[state.activity], color: '#D94F00' });

  const totalHealth = state.tags.illness.length + state.tags.allergy.length + state.tags.medic.length;
  chips.push({ label: `${totalHealth} health note${totalHealth !== 1 ? 's' : ''} recorded`, color: '#5ab5f5' });

  const dotClasses = { '#4BAE52':'dot-green', '#F0A830':'dot-yellow', '#D94F00':'dot-orange', '#5ab5f5':'dot-blue' };
  document.getElementById('summary-chips').innerHTML = chips.map(c => {
    const dotClass = dotClasses[c.color] || '';
    return `
      <div class="success-chip">
        <div class="dot ${dotClass}"></div>
        ${c.label}
      </div>
    `;
  }).join('');

  window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
</body>
</html>

