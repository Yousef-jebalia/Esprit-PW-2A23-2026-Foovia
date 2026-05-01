<?php
require_once __DIR__ . '/../../model/config.php';

$db = config::getConnexion();
$categories = $db->query('SELECT id_cat, name_cat FROM work_categorie ORDER BY name_cat ASC')->fetchAll();
$workouts = $db->query('SELECT id_work, name_work, cal_work, duree_work, id_cat, pic_work FROM workout ORDER BY id_work DESC')->fetchAll();
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
<style>
  .workout-page { min-height: 100vh; padding: 120px 48px 40px; background: var(--page-bg); }
  .workout-header { text-align: center; margin-bottom: 40px; }
  .workout-header h1 { font-family: 'Syne', sans-serif; font-size: 2.5rem; font-weight: 800; color: var(--page-text); margin-bottom: 8px; }
  .category-section { margin-bottom: 48px; max-width: 1200px; margin-left: auto; margin-right: auto; }
  .category-title { font-family: 'Syne', sans-serif; font-size: 1.4rem; font-weight: 700; color: var(--page-text); margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--green); }
  .workout-list { list-style: none; display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
  .workout-item { display: flex; flex-direction: column; background: var(--panel-bg); border: 1px solid var(--surface-border); border-radius: 12px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; }
  .workout-item:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12); }
  .workout-image { width: 100%; height: 180px; object-fit: cover; background: var(--surface-2); }
  .workout-image-empty { width: 100%; height: 180px; background: linear-gradient(135deg, var(--surface-2) 0%, var(--surface-2) 100%); display: flex; align-items: center; justify-content: center; color: var(--page-muted); font-family: 'DM Sans', sans-serif; font-size: 0.85rem; }
  .workout-info { padding: 14px; display: flex; flex-direction: column; gap: 8px; flex: 1; }
  .workout-name { font-family: 'Syne', sans-serif; font-size: 1rem; font-weight: 700; color: var(--page-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .workout-meta { display: flex; gap: 12px; font-family: 'DM Sans', sans-serif; font-size: 0.85rem; color: var(--page-muted); }
  .meta-item { display: flex; align-items: center; gap: 4px; }
  .meta-label { color: var(--green); font-weight: 600; }
  .empty-cat { color: var(--page-muted); font-family: 'DM Sans', sans-serif; padding: 20px; text-align: center; }
  .workout-search {
    max-width: 1200px;
    margin: 0 auto 18px;
    display: flex;
    gap: 10px;
    align-items: center;
    padding: 0 12px;
  }

  .workout-search-input {
    flex: 1;
    height: 44px;
    border: 1px solid var(--surface-border);
    border-radius: 10px;
    padding: 0 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.95rem;
    color: var(--page-text);
    background: var(--panel-bg);
    outline: none;
  }

  .workout-search-input:focus {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(17, 121, 90, 0.12);
  }

  .workout-search-clear {
    height: 44px;
    border: 1px solid var(--surface-border);
    border-radius: 10px;
    padding: 0 14px;
    font-family: 'Syne', sans-serif;
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--page-text);
    background: var(--panel-bg);
    cursor: pointer;
  }
</style>
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
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <div id="workout-filter-empty" class="empty-cat" style="display:none;">No workouts match your search.</div>
</section>

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

  initWorkoutSearch();
</script>

</body>
</html>
