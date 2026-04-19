<?php
require_once '../../controller/ObjectifLongTerme_Controller.php';

$controller = new ObjectifLongTerme_Controller();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id_obj'])) {
  $controller->delete_objectif((int) $_POST['delete_id_obj']);
  header('Location: tracking.php#long-term-goals');
  exit;
}

$objectifs = $controller->list_objectifs();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA Tracking Module</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link id="foovia-style" rel="stylesheet" href="./style.css?v=20260419">
<script>
  (function () {
    const styleLink = document.getElementById('foovia-style');
    const candidates = [
      './style.css?v=20260419',
      'style.css?v=20260419',
      '/foovia/Esprit-PW-2A23-2526-Foovia-/view/front_office/style.css?v=20260419'
    ];
    let idx = 0;

    styleLink.addEventListener('error', function () {
      idx += 1;
      if (idx < candidates.length) {
        styleLink.href = candidates[idx];
      }
    });
  })();
</script>
</head>
<body>

<nav>
  <a href="index.html" class="nav-logo">
    <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" style="height: 50px; width: auto;">
    FOOVIA
  </a>
  <ul class="nav-links">
    <li><a href="#long-term-goals">Long Term Goals</a></li>
    <li><a href="#weekly-tracking">Weekly Tracking</a></li>
    <li><a href="#progress">Progress</a></li>
    <li><a href="#history">History</a></li>
  </ul>
  <div class="nav-actions">
    <a href="backoffice.html" class="nav-btn nav-backoffice">Backoffice</a>
    <button class="theme-toggle" type="button" aria-label="Switch to dark mode" aria-pressed="false">
      <svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true">
        <circle cx="12" cy="12" r="4"></circle>
        <path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"></path>
      </svg>
      <svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M21 14.5A8.5 8.5 0 1 1 9.5 3a7 7 0 1 0 11.5 11.5z"></path>
      </svg>
    </button>
    <a href="signin.html" class="nav-btn nav-signin">Sign In</a>
    <a href="signup.html" class="nav-btn nav-signup">Sign Up</a>
  </div>
</nav>

<section class="hero hero-tracking">
  <div class="hero-text">
    <h1 class="hero-title">
      Track smarter.<br>
      Improve <span class="accent">daily.</span><br>
      Stay <span class="accent2">consistent.</span>
    </h1>

    <div class="hero-actions">
      <a href="#weekly-tracking" class="btn-primary">Open tracker</a>
      <a href="index.html" class="btn-secondary">Back to welcome</a>
    </div>
  </div>
</section>

<div class="features-strip">
  <div class="marquee-track">
    <span>Meal Logging</span><span class="sep">*</span>
    <span>Macro Tracking</span><span class="sep">*</span>
    <span>Hydration Goals</span><span class="sep">*</span>
    <span>Workout Consistency</span><span class="sep">*</span>
    <span>Daily Reminders</span><span class="sep">*</span>
    <span>Weekly Reports</span><span class="sep">*</span>
    <span>Meal Logging</span><span class="sep">*</span>
    <span>Macro Tracking</span><span class="sep">*</span>
    <span>Hydration Goals</span><span class="sep">*</span>
    <span>Workout Consistency</span><span class="sep">*</span>
    <span>Daily Reminders</span><span class="sep">*</span>
    <span>Weekly Reports</span><span class="sep">*</span>
  </div>
</div>

<section class="section" id="long-term-goals">
  <p class="section-label">Long Term Goals</p>
  <h2 class="section-title features-title">Manage your long-term goals in one place.</h2>

  <div class="ltg-shell">
    <div class="ltg-head">
      <div>
        <h3 class="ltg-headline">Long term goals list</h3>
      </div>
      <div class="ltg-actions">
        <button
          type="button"
          class="btn-primary ltg-open-survey"
          data-survey-url="../back_office/form-elements-component.php"
          aria-controls="ltg-survey-panel"
          aria-expanded="false"
        >
          Add Goal
        </button>
      </div>
    </div>

    <div class="ltg-table-wrap">
      <table class="ltg-table" aria-label="Long term goals table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Target</th>
            <th>Initial</th>
            <th>Start</th>
            <th>End</th>
            <th>Status</th>
            <th>Reminder</th>
            <th>Sport</th>
            <th>Diet</th>
            <th>Calories</th>
            <th>Fat</th>
            <th>Protein</th>
            <th>Carbs</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($objectifs)): ?>
            <?php foreach ($objectifs as $objectif): ?>
              <?php
                $status = str_replace(
                  ['en_attente', 'en_cours', 'termine'],
                  ['pending', 'in progress', 'completed'],
                  (string) $objectif['status_obj']
                );
                $statusClass = 'ltg-status-pending';
                if ($status === 'in progress') {
                  $statusClass = 'ltg-status-progress';
                } elseif ($status === 'completed') {
                  $statusClass = 'ltg-status-completed';
                }
              ?>
              <tr>
                <td><?php echo htmlspecialchars((string) $objectif['id_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['type_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['val_cible_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['val_init_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['date_deb_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['date_fin_obj']); ?></td>
                <td><span class="ltg-status <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($status); ?></span></td>
                <td><?php echo htmlspecialchars((string) $objectif['frequency_rappel_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['consistancy_sport_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['consistency_alim_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['obj_cal_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['obj_fat_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['obj_prot_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['obj_carb_obj']); ?></td>
                <td>
                  <div class="ltg-row-actions">
                    <a href="../back_office/edit-objectif-long-terme.php?id_obj=<?php echo urlencode((string) $objectif['id_obj']); ?>" class="ltg-action ltg-edit">Edit</a>
                    <form method="post" action="" onsubmit="return window.confirm('Delete this goal?');">
                      <input type="hidden" name="delete_id_obj" value="<?php echo htmlspecialchars((string) $objectif['id_obj']); ?>">
                      <button type="submit" class="ltg-action ltg-delete">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="15" class="ltg-empty">No long-term goals found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</section>

<section class="section ltg-survey-section" id="ltg-survey-panel" hidden>
  <div class="ltg-survey-shell">
    <div class="ltg-survey-head">
      <h3 class="ltg-headline">Long term goal survey</h3>
      <button type="button" class="ltg-close-survey" aria-label="Close survey">Close</button>
    </div>
    <iframe
      class="ltg-survey-frame"
      title="Long term goal survey"
      data-src="../back_office/form-elements-component.php"
    ></iframe>
  </div>
</section>

<section class="section" id="weekly-tracking">
  <p class="section-label">Weekly tracking</p>
  <h2 class="section-title features-title">Everything you need to stay on track this week.</h2>

  <div class="feat-grid">
    <div class="feat-row">
      <div class="feat-card photo-card photo-recipes">
        <div class="feat-num">01 - Meals</div>
        <h3>Photo based meal logging</h3>
        <p>Upload a meal picture and get fast nutritional estimates with auto-filled logging fields.</p>
        <a href="#" class="feat-btn">Log meal</a>
      </div>

      <div class="feat-card photo-card photo-macro-tracking">
        <div class="feat-num">02 - Macros</div>
        <h3>Protein carbs fat split</h3>
        <p>Follow your target macro split in real time and receive actionable recommendations.</p>
        <a href="#" class="feat-btn">Check macros</a>
      </div>

      <div class="feat-card photo-card photo-sport">
        <div class="feat-num">03 - Workouts</div>
        <h3>Exercise completion tracking</h3>
        <p>Track completed sessions, effort level, and weekly streaks linked to your plan.</p>
        <a href="#" class="feat-btn">Update workout</a>
      </div>
    </div>

    <div class="feat-row">
      <div class="feat-card photo-card photo-marketplace">
        <div class="feat-num">04 - Hydration</div>
        <h3>Water and supplement reminders</h3>
        <p>Set smart reminders for hydration and supplements based on your routine and weather.</p>
        <a href="#" class="feat-btn">Set reminders</a>
      </div>

      <div class="feat-card photo-card photo-survey-user">
        <div class="feat-num">05 - Goals</div>
        <h3>Weekly objective review</h3>
        <p>Compare planned versus completed targets and adjust your next week strategy quickly.</p>
        <a href="#" class="feat-btn">Review goals</a>
      </div>

      <div class="feat-card photo-card photo-support">
        <div class="feat-num">06 - Insights</div>
        <h3>Progress summary and support</h3>
        <p>See trend summaries, receive chatbot coaching, and ask for support when blocked.</p>
        <a href="#" class="feat-btn">View insights</a>
      </div>
    </div>
  </div>
</section>

<section class="how" id="progress">
  <p class="section-label">Progress</p>
  <h2 class="section-title how-title">A simple flow to measure and improve your progress.</h2>
  <div class="steps">
    <div class="step">
      <div class="step-dot"></div>
      <div class="step-num">01</div>
      <h3>Log your meals</h3>
      <p>Capture breakfast, lunch, dinner, and snacks in seconds from text or photo.</p>
    </div>
    <div class="step">
      <div class="step-dot" style="background:var(--yellow)"></div>
      <div class="step-num">02</div>
      <h3>Track habits</h3>
      <p>Mark workouts, hydration, and supplements to keep your habit chain complete.</p>
    </div>
    <div class="step">
      <div class="step-dot" style="background:var(--orange)"></div>
      <div class="step-num">03</div>
      <h3>Review trends</h3>
      <p>See daily and weekly trend cards that highlight wins and missed targets.</p>
    </div>
    <div class="step">
      <div class="step-dot" style="background:var(--peach)"></div>
      <div class="step-num">04</div>
      <h3>Adapt your plan</h3>
      <p>Use the recommended adjustments to keep progress steady and sustainable.</p>
    </div>
  </div>
</section>

<div class="stats">
  <div class="stat">
    <div class="stat-num">24h</div>
    <div class="stat-label">Daily tracking cycle</div>
  </div>
  <div class="stat">
    <div class="stat-num">3x</div>
    <div class="stat-label">Faster meal logging</div>
  </div>
  <div class="stat">
    <div class="stat-num">7d</div>
    <div class="stat-label">Weekly progress reports</div>
  </div>
  <div class="stat">
    <div class="stat-num">1</div>
    <div class="stat-label">Single health dashboard</div>
  </div>
</div>

<section class="cta-section" id="history">
  <p class="section-label">History</p>
  <h2 class="cta-title">Your previous weeks,<br><em>clearly organized.</em></h2>
  <p>Review your completed logs, compare weekly snapshots, and learn from your history.</p>
  <a href="#weekly-tracking" class="btn-primary">Review Weekly Tracking</a>
</section>

<footer>
  <div class="footer-brand">
    <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" style="height: 36px; width: auto;">
    FOOVIA
  </div>
  <p>© 2026 Foovia. All rights reserved.</p>
  <ul class="footer-links">
    <li><a href="#">Privacy</a></li>
    <li><a href="#">Terms</a></li>
    <li><a href="#">Support</a></li>
    <li><a href="#">Contact</a></li>
  </ul>
</footer>

<script>
  (function() {
    const root = document.documentElement;
    const toggle = document.querySelector('.theme-toggle');

    const setTheme = (theme) => {
      const isDark = theme === 'dark';
      root.setAttribute('data-theme', theme);
      root.style.colorScheme = theme;
      toggle.setAttribute('aria-pressed', String(isDark));
      toggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
    };

    const stored = localStorage.getItem('theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initialTheme = stored || (prefersDark ? 'dark' : 'light');
    setTheme(initialTheme);

    toggle.addEventListener('click', () => {
      const currentTheme = root.getAttribute('data-theme') || 'light';
      const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
      localStorage.setItem('theme', nextTheme);
      setTheme(nextTheme);
    });

    const openSurveyButton = document.querySelector('.ltg-open-survey');
    const surveyPanel = document.getElementById('ltg-survey-panel');
    const closeSurveyButton = document.querySelector('.ltg-close-survey');
    const surveyFrame = document.querySelector('.ltg-survey-frame');

    if (openSurveyButton && surveyPanel && surveyFrame) {
      openSurveyButton.addEventListener('click', () => {
        if (!surveyFrame.src) {
          surveyFrame.src = surveyFrame.getAttribute('data-src') || '';
        }
        surveyPanel.hidden = false;
        openSurveyButton.setAttribute('aria-expanded', 'true');
        surveyPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    }

    if (closeSurveyButton && surveyPanel && openSurveyButton) {
      closeSurveyButton.addEventListener('click', () => {
        surveyPanel.hidden = true;
        openSurveyButton.setAttribute('aria-expanded', 'false');
      });
    }
  })();
</script>

</body>
</html>
