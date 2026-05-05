<?php
require_once __DIR__ . '/../../../model/config.php';

$db = config::getConnexion();
$categories = $db->query('SELECT id_cat, name_cat FROM work_categorie ORDER BY name_cat ASC')->fetchAll();
$workouts = $db->query(
  'SELECT w.id_work, w.name_work, w.cal_work, w.duree_work, w.id_cat, w.pic_work, wc.name_cat
   FROM workout w
   LEFT JOIN work_categorie wc ON wc.id_cat = w.id_cat
   ORDER BY w.id_work DESC'
)->fetchAll();

$workoutExerciseRows = $db->query(
  'SELECT b.id_work, e.id_ex, e.name_ex, e.type_ex, e.muscle_ex, e.cal_ex, e.fatigue_ex, e.description_ex, e.gif_ex
   FROM belong b
   INNER JOIN exercice e ON e.id_ex = b.id_ex
   ORDER BY b.id_work DESC, e.name_ex ASC'
)->fetchAll(PDO::FETCH_ASSOC);

$workoutDetailsById = [];
foreach ($workouts as $workout) {
  $workoutId = (int)$workout['id_work'];
  $exerciseItems = [];
  $muscleTags = [];
  $fatigueValues = [];

  foreach ($workoutExerciseRows as $row) {
    if ((int)$row['id_work'] !== $workoutId) {
      continue;
    }

    $type = strtolower(trim((string)$row['type_ex']));
    $muscles = array_values(array_filter(array_map('trim', explode(',', (string)$row['muscle_ex']))));

    foreach ($muscles as $muscle) {
      if (!in_array($muscle, $muscleTags, true)) {
        $muscleTags[] = $muscle;
      }
    }

    $fatigueValues[] = (float)$row['fatigue_ex'];
    $exerciseItems[] = [
      'id_ex' => (int)$row['id_ex'],
      'name' => (string)$row['name_ex'],
      'type_ex' => $type,
      'muscle_ex' => (string)$row['muscle_ex'],
      'description_ex' => (string)$row['description_ex'],
      'gif_ex' => !empty($row['gif_ex']) ? base64_encode($row['gif_ex']) : null,
    ];
  }

  $avgFatigue = count($fatigueValues) > 0 ? array_sum($fatigueValues) / count($fatigueValues) : null;
  $exerciseNames = array_map(fn($item) => $item['name'], $exerciseItems);

  $workoutDetailsById[$workoutId] = [
    'id_work' => $workoutId,
    'name_work' => (string)$workout['name_work'],
    'category' => (string)($workout['name_cat'] ?? ''),
    'cal_work' => (int)$workout['cal_work'],
    'duree_work' => (int)$workout['duree_work'],
    'pic_work' => !empty($workout['pic_work']) ? base64_encode($workout['pic_work']) : null,
    'muscles' => $muscleTags,
    'fatigue_avg' => $avgFatigue,
    'description' => count($exerciseNames) > 0
      ? 'Exercises: ' . implode(', ', $exerciseNames)
      : 'No exercises linked to this workout yet.',
    'exercises' => $exerciseItems,
  ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Workouts — FOOVIA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="workout_php.css">

</head>
<body>

<!-- NAV -->
<nav>
  <a href="#" class="nav-logo">
    <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" style="height: 50px; width: auto;">
    FOOVIA
  </a>
  <ul class="nav-links">
    <li><a href="Exercice.php">Exercice</a></li>
    <li><a href="Workout.php">Workouts</a></li>
    <li><a href="custome_workout.php">Custom Workouts</a></li>
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


<!-- WORKOUT PAGE -->
<section class="workout-page">
  <div class="workout-header">
    <h1>Workouts by Category</h1>
  </div>

  <div class="workout-search">
    <input id="workout-search-input" class="workout-search-input" type="search" placeholder="Search workouts by name, time, or calories..." aria-label="Search workouts" />
    <button id="workout-search-clear" class="workout-search-clear" type="button">Clear</button>
  </div>

  <?php foreach ($categories as $category): ?>
    <?php $catWorkouts = array_filter($workouts, fn($w) => (int)$w['id_cat'] === (int)$category['id_cat']); ?>
    <div class="category-section">
      <h2 class="category-title"><?php echo htmlspecialchars($category['name_cat']); ?></h2>
      
      <?php if (empty($catWorkouts)): ?>
        <div class="empty-cat">No workouts in this category</div>
      <?php else: ?>
        <ul class="workout-list">
          <?php foreach ($catWorkouts as $workout): ?>
            <li class="workout-item">
              <?php if (!empty($workout['pic_work'])): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($workout['pic_work']); ?>" alt="<?php echo htmlspecialchars($workout['name_work']); ?>" class="workout-image">
              <?php else: ?>
                <div class="workout-image-empty">No Image</div>
              <?php endif; ?>
              <div class="workout-info">
                <span class="workout-name"><?php echo htmlspecialchars($workout['name_work']); ?></span>
                <div class="workout-meta">
                  <div class="meta-item">
                    <span class="meta-label">Cal:</span>
                    <span><?php echo (int)$workout['cal_work']; ?></span>
                  </div>
                  <div class="meta-item">
                    <span class="meta-label">Time:</span>
                    <span><?php echo (int)$workout['duree_work']; ?> min</span>
                  </div>
                </div>
                <button type="button" class="workout-info-btn" data-workout-id="<?php echo (int)$workout['id_work']; ?>" aria-label="Open workout details">i</button>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <div id="workout-filter-empty" class="empty-cat" style="display:none;">No workouts match your search.</div>
</section>

<div id="workout-info-overlay" class="workout-modal-overlay" aria-hidden="true">
  <div class="workout-modal" role="dialog" aria-modal="true" aria-labelledby="workout-modal-title">
    <div class="workout-modal-header">
      <div id="workout-modal-title" class="workout-modal-title">Workout details</div>
      <button type="button" class="workout-modal-close" id="workout-info-close" aria-label="Close workout details">&times;</button>
    </div>
    <div class="workout-modal-body">
      <div class="workout-modal-media">
        <div id="workout-modal-image-empty" class="workout-modal-image-empty">No Image</div>
        <img id="workout-modal-image" class="workout-modal-image" alt="Workout image" style="display:none;" />
        <div class="workout-modal-anatomy">
          <iframe id="workout-modal-anatomy-frame" src="anatomy_man.html" title="Workout muscles anatomy" loading="lazy"></iframe>
        </div>
      </div>
      <div class="workout-modal-content">
        <div class="workout-modal-grid">
          <div class="workout-modal-card">
            <h3>Name</h3>
            <p id="workout-modal-name"></p>
          </div>
          <div class="workout-modal-card">
            <h3>Category / Type</h3>
            <p id="workout-modal-type"></p>
          </div>
          <div class="workout-modal-card">
            <h3>Calories</h3>
            <p id="workout-modal-calories"></p>
          </div>
          <div class="workout-modal-card">
            <h3>Fatigue Ratio</h3>
            <p id="workout-modal-fatigue"></p>
          </div>
        </div>

        <div class="workout-modal-card">
          <h3>Working Muscles</h3>
          <div id="workout-modal-muscles" class="workout-muscle-tags"></div>
        </div>

        <div class="workout-modal-card">
          <h3>Description</h3>
          <p id="workout-modal-description"></p>
        </div>

        <div class="workout-modal-card">
          <h3>Exercises</h3>
          <ul id="workout-modal-exercises" class="workout-exercise-list"></ul>
        </div>
      </div>
    </div>
  </div>
</div>

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
  })();
</script>

<script>
  const workoutDetailsMap = <?php echo json_encode($workoutDetailsById, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>

<script>
  function initWorkoutInfoModal() {
    const overlay = document.getElementById('workout-info-overlay');
    const closeButton = document.getElementById('workout-info-close');
    const modalTitle = document.getElementById('workout-modal-title');
    const modalImage = document.getElementById('workout-modal-image');
    const modalImageEmpty = document.getElementById('workout-modal-image-empty');
    const modalName = document.getElementById('workout-modal-name');
    const modalType = document.getElementById('workout-modal-type');
    const modalCalories = document.getElementById('workout-modal-calories');
    const modalFatigue = document.getElementById('workout-modal-fatigue');
    const modalMuscles = document.getElementById('workout-modal-muscles');
    const modalDescription = document.getElementById('workout-modal-description');
    const modalExercises = document.getElementById('workout-modal-exercises');
    const anatomyFrame = document.getElementById('workout-modal-anatomy-frame');
    const buttons = Array.from(document.querySelectorAll('.workout-info-btn'));

    if (!overlay || !closeButton) {
      return;
    }

    const normalizeMusclesForAnatomy = (muscles) => {
      return Array.from(new Set((muscles || []).map((muscle) => String(muscle || '').trim()).filter(Boolean)));
    };

    const sendMusclesToAnatomy = (muscles) => {
      if (anatomyFrame && anatomyFrame.contentWindow) {
        anatomyFrame.contentWindow.postMessage({ type: 'foovia-muscles', muscles: muscles }, '*');
      }
    };

    const openModal = (workoutId) => {
      const workout = workoutDetailsMap[String(workoutId)] || workoutDetailsMap[Number(workoutId)];
      if (!workout) {
        return;
      }

      const muscles = normalizeMusclesForAnatomy(workout.muscles || []);

      modalTitle.textContent = workout.name_work + ' details';
      modalName.textContent = workout.name_work;
      modalType.textContent = workout.category || 'Uncategorized';
      modalCalories.textContent = workout.cal_work + ' cal';
      modalFatigue.textContent = workout.fatigue_avg === null ? 'N/A' : workout.fatigue_avg.toFixed(1) + ' / 10';
      modalDescription.textContent = workout.description || 'No description available.';

      if (workout.pic_work) {
        modalImage.src = 'data:image/jpeg;base64,' + workout.pic_work;
        modalImage.style.display = 'block';
        modalImageEmpty.style.display = 'none';
      } else {
        modalImage.removeAttribute('src');
        modalImage.style.display = 'none';
        modalImageEmpty.style.display = 'flex';
      }

      modalMuscles.innerHTML = '';
      if (muscles.length === 0) {
        modalMuscles.innerHTML = '<span class="workout-muscle-tag">No muscles detected</span>';
      } else {
        muscles.forEach((muscle) => {
          const tag = document.createElement('span');
          tag.className = 'workout-muscle-tag';
          tag.textContent = muscle;
          modalMuscles.appendChild(tag);
        });
      }

      modalExercises.innerHTML = '';
      (workout.exercises || []).forEach((exercise) => {
        const li = document.createElement('li');
        li.textContent = exercise.name + ' (' + exercise.type_ex + ')';
        modalExercises.appendChild(li);
      });

      overlay.style.display = 'flex';
      overlay.setAttribute('aria-hidden', 'false');

      if (anatomyFrame && anatomyFrame.contentWindow) {
        sendMusclesToAnatomy(muscles);
      } else if (anatomyFrame) {
        anatomyFrame.addEventListener('load', function() {
          sendMusclesToAnatomy(muscles);
        }, { once: true });
      }
    };

    const closeModal = () => {
      overlay.style.display = 'none';
      overlay.setAttribute('aria-hidden', 'true');
    };

    buttons.forEach((button) => {
      button.addEventListener('click', () => {
        openModal(button.dataset.workoutId);
      });
    });

    closeButton.addEventListener('click', closeModal);
    overlay.addEventListener('click', (event) => {
      if (event.target === overlay) {
        closeModal();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && overlay.style.display === 'flex') {
        closeModal();
      }
    });
  }
</script>

<script>
  function initWorkoutSearch() {
    const sections = Array.from(document.querySelectorAll('.category-section'));
    const items = Array.from(document.querySelectorAll('.workout-item'));
    const searchInput = document.getElementById('workout-search-input');
    const clearButton = document.getElementById('workout-search-clear');
    const emptyState = document.getElementById('workout-filter-empty');

    let searchQuery = '';

    const normalize = (text) =>
      String(text || '')
        .toLowerCase()
        .replace(/[^a-z0-9\s]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    items.forEach((item) => {
      const name = item.querySelector('.workout-name')?.textContent || '';
      const meta = item.querySelector('.workout-meta')?.textContent || '';
      const category = item.closest('.category-section')?.querySelector('.category-title')?.textContent || '';
      item.dataset.search = normalize([name, meta, category].join(' '));
    });

    const applyFilter = () => {
      const hasSearch = searchQuery.length > 0;
      let totalVisible = 0;

      sections.forEach((section) => {
        const listItems = Array.from(section.querySelectorAll('.workout-item'));
        let visibleInSection = 0;

        listItems.forEach((it) => {
          const text = it.dataset.search || '';
          const match = !hasSearch || text.includes(searchQuery);
          it.style.display = match ? '' : 'none';
          if (match) visibleInSection += 1;
        });

        section.style.display = visibleInSection > 0 ? '' : 'none';
        totalVisible += visibleInSection;
      });

      if (emptyState) {
        emptyState.style.display = totalVisible === 0 && hasSearch ? '' : 'none';
      }
    };

    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        searchQuery = normalize(e.target.value);
        applyFilter();
      });
    }

    if (clearButton) {
      clearButton.addEventListener('click', () => {
        searchQuery = '';
        if (searchInput) {
          searchInput.value = '';
          searchInput.focus();
        }
        applyFilter();
      });
    }

    applyFilter();
  }

  initWorkoutInfoModal();
  initWorkoutSearch();
</script>

</body>
</html>
