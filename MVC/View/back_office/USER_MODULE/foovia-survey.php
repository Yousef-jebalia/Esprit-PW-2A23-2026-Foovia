<?php
ob_start();
session_start();
include(__DIR__ . '/../../../Controller/Controller_user.php');

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
    header('Location: ../../front_office/foovia-signin.php');
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
            header('refresh:2;url=../../front_office/foovia.php');
            exit;
        }
    } catch (Exception $e) {
        $error_message = 'An error occurred: ' . $e->getMessage();
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../../front_office/assets/Plan de travail 1 no bg (3) (1).png">
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA — Your Health Profile</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Boldonse&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="foovia-survey.css?v=<?php echo time(); ?>">
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">
  <a href="../../front_office/foovia.php" class="topbar-logo">
    <img src="../../front_office/assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" class="topbar-logo-img">
    FOOVIA
  </a>
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

  <!-- STEP 1 — Profile -->
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
      <a href="../../front_office/foovia.php" class="btn-skip">Skip</a>
    </div>
  </div>

  <!-- STEP 2 — Body Metrics -->
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
          <div class="bmi-arrow" id="bmi-arrow">Underweight Â· Normal Â· Overweight Â· Obese Â· Severe</div>
        </div>
      </div>
    </div>

    <div class="nav-btns">
      <button type="button" class="btn-back" onclick="goBack(2)">← Back</button>
      <button type="button" class="btn-next" onclick="goNext(2)">Continue →</button>
      <a href="../../front_office/foovia.php" class="btn-skip">Skip</a>
    </div>
  </div>

  <!-- STEP 3 — Activity -->
  <div class="step" id="step-3">
    <p class="step-eyebrow">Step 3 of 4 — Lifestyle</p>
    <h1 class="step-title">How <em>active</em><br>are you?</h1>
    <p class="step-desc">We use this to calculate your daily calorie needs. Be honest — this is for your benefit!</p>

    <div class="card">
      <div class="card-label">🏃 Activity level</div>
      <div class="activity-grid">
        <div class="activity-tile" onclick="selectActivity(this,'sedentary')">
          <div class="at-icon sed">🛋️</div>
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
            <div class="at-sub">Light exercise 1â€“3 days/week</div>
          </div>
          <div class="at-check"></div>
        </div>
        <div class="activity-tile" onclick="selectActivity(this,'moderate')">
          <div class="at-icon moderate">🏃</div>
          <div class="at-body">
            <div class="at-title">Moderately active</div>
            <div class="at-sub">Moderate exercise 3â€“5 days/week</div>
          </div>
          <div class="at-check"></div>
        </div>
        <div class="activity-tile" onclick="selectActivity(this,'very')">
          <div class="at-icon very">🏋️</div>
          <div class="at-body">
            <div class="at-title">Very active</div>
            <div class="at-sub">Hard exercise 6â€“7 days/week</div>
          </div>
          <div class="at-check"></div>
        </div>
        <div class="activity-tile" onclick="selectActivity(this,'extreme')">
          <div class="at-icon extreme"><svg width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;fill:red;"><path d="M9.75838 1.09929C9.85156 1.13153 9.9852 1.17902 10.1535 1.24207C10.49 1.36812 10.9661 1.55678 11.5355 1.81078C12.6715 2.31752 14.193 3.09073 15.7215 4.15505C18.745 6.26052 22 9.65692 22 14.5393C22 16.6738 21.4305 18.7869 20.1046 20.3856C18.7552 22.0126 16.7095 23 14 23C13.9352 23 13.6752 22.9978 13.4169 22.8125C13.0566 22.5541 12.9699 22.1541 13.0085 21.8667C13.0376 21.6502 13.1305 21.5025 13.1576 21.4602C13.1966 21.3993 13.234 21.3556 13.2534 21.3338C13.293 21.2893 13.3281 21.2581 13.3407 21.247C13.3575 21.2322 13.3716 21.2207 13.3801 21.214C13.4065 21.1929 13.4323 21.1745 13.4402 21.1689L13.4413 21.1681L13.5185 21.1136C13.5762 21.0727 13.6587 21.0131 13.7588 20.9348C13.9606 20.7768 14.2297 20.546 14.4969 20.2526C15.0448 19.6509 15.5 18.8819 15.5 18C15.5 16.3681 14.571 14.8515 13.5067 13.669C12.9869 13.0914 12.4644 12.6267 12.0715 12.3065C12.0471 12.2866 12.0233 12.2674 12 12.2487C11.9767 12.2674 11.9529 12.2866 11.9285 12.3065C11.5356 12.6267 11.0131 13.0914 10.4933 13.669C9.42904 14.8515 8.5 16.3681 8.5 18C8.5 18.8887 8.95405 19.6581 9.49825 20.2564C9.76406 20.5486 10.0319 20.7779 10.2327 20.934C10.3323 21.0114 10.4142 21.0699 10.47 21.1087C10.4933 21.125 10.5115 21.1374 10.5281 21.1487L10.5401 21.1569C10.5471 21.1616 10.5635 21.1728 10.5787 21.1837C10.5832 21.187 10.6139 21.2089 10.6476 21.2376C10.6583 21.2467 10.6772 21.2632 10.6995 21.285C10.7154 21.3005 10.7647 21.3492 10.8157 21.4212C10.8424 21.4607 10.901 21.5658 10.9302 21.6326C10.9668 21.7437 10.9991 22.045 10.9733 22.2301C10.89 22.4562 10.6027 22.798 10.4241 22.9056C10.2979 22.9546 10.0834 22.9965 10 23C7.29045 23 5.24478 22.0126 3.89543 20.3856C2.56953 18.7869 2 16.6738 2 14.5393C2 11.9892 2.88357 10.3815 4.05286 9.15507C4.5965 8.58486 5.19715 8.10224 5.73579 7.66945L5.77852 7.63511C6.34602 7.17903 6.84273 6.7759 7.26778 6.31893C8.30821 5.20037 8.54446 4.18717 8.56055 3.49802C8.56885 3.14245 8.51857 2.85417 8.46943 2.66213C8.44495 2.56644 8.42112 2.49608 8.40592 2.45502C8.39834 2.43455 8.39298 2.42158 8.39089 2.41662C8.22725 2.05872 8.28834 1.6367 8.54841 1.34037C8.86981 0.974175 9.32884 0.950674 9.75838 1.09929Z" fill="red"/></svg></div>
          <div class="at-body">
            <div class="at-title">Extremely active</div>
            <div class="at-sub">Athlete, physical job, or 2Ã— training</div>
          </div>
          <div class="at-check"></div>
        </div>
      </div>
      <div class="step-error" id="err-activity">Please select your activity level.</div>
    </div>

    <div class="nav-btns">
      <button type="button" class="btn-back" onclick="goBack(3)">← Back</button>
      <button type="button" class="btn-next" onclick="goNext(3)">Continue →</button>
      <a href="../../front_office/foovia.php" class="btn-skip">Skip</a>
    </div>
  </div>

  <!-- STEP 4 — Health -->
  <div class="step" id="step-4">
    <p class="step-eyebrow">Step 4 of 4 — Health details</p>
    <h1 class="step-title">Your <em>health</em><br>background</h1>
    <p class="step-desc">This helps us keep your meal and workout plans safe and appropriate. All information is confidential.</p>

    <!-- ILLNESSES -->
    <div class="card">
      <div class="card-label">🩺 Illnesses / Conditions</div>
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
      <a href="../../front_office/foovia.php" class="btn-skip">Skip</a>
    </div>
  </div>

  <!-- â•â• SUCCESS â•â• -->
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

// â”€â”€ GENDER â”€â”€
function selectGender(el, val) {
  document.querySelectorAll('.gender-tile').forEach(t => t.classList.remove('selected'));
  el.classList.add('selected');
  state.gender = val;
  document.getElementById('err-gender').classList.remove('visible');
}

// â”€â”€ ACTIVITY â”€â”€
function selectActivity(el, val) {
  document.querySelectorAll('.activity-tile').forEach(t => t.classList.remove('selected'));
  el.classList.add('selected');
  state.activity = val;
  document.getElementById('err-activity').classList.remove('visible');
}

// â”€â”€ HEIGHT / WEIGHT UNITS â”€â”€
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

// â”€â”€ BMI â”€â”€
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
  else if (bmi < 25)   { label = 'Normal weight'; sub = 'BMI 18.5 â€“ 24.9'; color = '#4BAE52'; }
  else if (bmi < 30)   { label = 'Overweight'; sub = 'BMI 25 â€“ 29.9'; color = '#F0A830'; }
  else if (bmi < 35)   { label = 'Obese'; sub = 'BMI 30 â€“ 34.9'; color = '#D94F00'; }
  else                 { label = 'Severely obese'; sub = 'BMI 35+'; color = '#C0381A'; }

  document.getElementById('bmi-label').textContent = label;
  document.getElementById('bmi-sub').textContent = sub;
  document.getElementById('bmi-val').style.color = color;
}

// â”€â”€ TAGS â”€â”€
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
      <button type="button" class="tag-chip-del" onclick="removeTag('${type}','${t}')">Ã—</button>
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

// â”€â”€ NAVIGATION â”€â”€
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

// â”€â”€ SUCCESS â”€â”€
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

  const aMap = { sedentary:'🛋️ Sedentary', light:'🚶 Lightly active', moderate:'🏃 Moderate', very:'🏋️ Very active', extreme:'<svg width="14" height="14" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;fill:red;margin-right:6px;"><path d="M9.75838 1.09929C9.85156 1.13153 9.9852 1.17902 10.1535 1.24207C10.49 1.36812 10.9661 1.55678 11.5355 1.81078C12.6715 2.31752 14.193 3.09073 15.7215 4.15505C18.745 6.26052 22 9.65692 22 14.5393C22 16.6738 21.4305 18.7869 20.1046 20.3856C18.7552 22.0126 16.7095 23 14 23C13.9352 23 13.6752 22.9978 13.4169 22.8125C13.0566 22.5541 12.9699 22.1541 13.0085 21.8667C13.0376 21.6502 13.1305 21.5025 13.1576 21.4602C13.1966 21.3993 13.234 21.3556 13.2534 21.3338C13.293 21.2893 13.3281 21.2581 13.3407 21.247C13.3575 21.2322 13.3716 21.2207 13.3801 21.214C13.4065 21.1929 13.4323 21.1745 13.4402 21.1689L13.4413 21.1681L13.5185 21.1136C13.5762 21.0727 13.6587 21.0131 13.7588 20.9348C13.9606 20.7768 14.2297 20.546 14.4969 20.2526C15.0448 19.6509 15.5 18.8819 15.5 18C15.5 16.3681 14.571 14.8515 13.5067 13.669C12.9869 13.0914 12.4644 12.6267 12.0715 12.3065C12.0471 12.2866 12.0233 12.2674 12 12.2487C11.9767 12.2674 11.9529 12.2866 11.9285 12.3065C11.5356 12.6267 11.0131 13.0914 10.4933 13.669C9.42904 14.8515 8.5 16.3681 8.5 18C8.5 18.8887 8.95405 19.6581 9.49825 20.2564C9.76406 20.5486 10.0319 20.7779 10.2327 20.934C10.3323 21.0114 10.4142 21.0699 10.47 21.1087C10.4933 21.125 10.5115 21.1374 10.5281 21.1487L10.5401 21.1569C10.5471 21.1616 10.5635 21.1728 10.5787 21.1837C10.5832 21.187 10.6139 21.2089 10.6476 21.2376C10.6583 21.2467 10.6772 21.2632 10.6995 21.285C10.7154 21.3005 10.7647 21.3492 10.8157 21.4212C10.8424 21.4607 10.901 21.5658 10.9302 21.6326C10.9668 21.7437 10.9991 22.045 10.9733 22.2301C10.89 22.4562 10.6027 22.798 10.4241 22.9056C10.2979 22.9546 10.0834 22.9965 10 23C7.29045 23 5.24478 22.0126 3.89543 20.3856C2.56953 18.7869 2 16.6738 2 14.5393C2 11.9892 2.88357 10.3815 4.05286 9.15507C4.5965 8.58486 5.19715 8.10224 5.73579 7.66945L5.77852 7.63511C6.34602 7.17903 6.84273 6.7759 7.26778 6.31893C8.30821 5.20037 8.54446 4.18717 8.56055 3.49802C8.56885 3.14245 8.51857 2.85417 8.46943 2.66213C8.44495 2.56644 8.42112 2.49608 8.40592 2.45502C8.39834 2.43455 8.39298 2.42158 8.39089 2.41662C8.22725 2.05872 8.28834 1.6367 8.54841 1.34037C8.86981 0.974175 9.32884 0.950674 9.75838 1.09929Z" fill="red"/></svg> Extreme' };
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
