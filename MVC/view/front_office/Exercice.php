<?php
require_once __DIR__ . '/../../model/config.php';

$db = config::getConnexion();
$stmt = $db->query("SELECT * FROM exercice ORDER BY id_ex DESC");
$exercises = $stmt->fetchAll();
?>





<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Exercises — FOOVIA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
  .exercise-page {
    min-height: 100vh;
    padding: 120px 24px 40px;
    background: var(--page-bg);
  }

  .exercise-header {
    text-align: center;
    margin-bottom: 28px;
  }

  .exercise-header h1 {
    font-family: 'Boldonse', sans-serif;
    font-size: clamp(2rem, 3vw, 2.8rem);
    line-height: 1.2;
    color: var(--page-text);
    margin-bottom: 10px;
  }

  .exercise-header p {
    font-family: 'DM Sans', sans-serif;
    color: var(--page-muted);
    max-width: 680px;
    margin: 0 auto;
  }

  .anatomy-panel {
    max-width: 1080px;
    margin: 0 auto 28px;
    background: var(--panel-bg);
    border: 1px solid var(--surface-border);
    border-radius: 16px;
    padding: 14px;
    box-shadow: 0 8px 22px rgba(0, 0, 0, 0.08);
  }

  .anatomy-frame {
    width: 100%;
    height: clamp(440px, 62vh, 650px);
    border: 0;
    border-radius: 12px;
    background: transparent;
  }

  .exercise-grid-wrapper {
    overflow-x: auto;
    padding-bottom: 8px;
  }

  .exercise-filter-status {
    max-width: 1080px;
    margin: 0 auto 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.92rem;
    color: var(--page-muted);
  }

  .exercise-filter-status strong {
    color: var(--page-text);
  }

  .exercise-grid {
    display: grid;
    grid-template-columns: repeat(6, 300px);
    gap: 16px;
    width: max-content;
    margin: 0 auto;
    padding: 4px;
  }

  .exercise-card {
    width: 300px;
    height: 300px;
    border-radius: 14px;
    overflow: hidden;
    background: var(--panel-bg);
    border: 1px solid var(--surface-border);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    position: relative;
  }

  .exercise-card.is-hidden {
    display: none;
  }

  .exercise-card-content {
    height: 100%;
    display: flex;
    flex-direction: column;
  }

  .exercise-gif,
  .exercise-image-fallback {
    width: 100%;
    height: 160px;
    object-fit: contain;
    object-position: center;
    display: block;
    background: var(--surface-2);
  }

  .exercise-image-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--panel-muted);
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 0.85rem;
  }

  .exercise-content {
    flex: 1;
    padding: 10px 12px 36px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    overflow: hidden;
    position: relative;
  }

  .exercise-name {
    font-family: 'Syne', sans-serif;
    font-size: 1rem;
    font-weight: 800;
    color: var(--panel-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .exercise-meta {
    font-family: 'DM Sans', sans-serif;
    font-size: 0.85rem;
    color: var(--panel-muted);
    line-height: 1.35;
    max-height: 2.7em;
    overflow: hidden;
  }

  .exercise-calories {
    font-family: 'Syne', sans-serif;
    font-size: 0.78rem;
    color: var(--orange);
    font-weight: 700;
    margin-top: auto;
  }

  .exercise-info-btn {
    position: absolute;
    right: 8px;
    bottom: 8px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: none;
    background: var(--green);
    color: #fff;
    font-family: 'Syne', sans-serif;
    font-size: 0.8rem;
    font-weight: 800;
    line-height: 1;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .exercise-info-btn:hover {
    background: var(--forest);
  }

  .empty-state {
    text-align: center;
    width: 100%;
    padding: 32px 20px;
    color: var(--panel-muted);
    font-family: 'DM Sans', sans-serif;
  }

  .empty-state.filtered {
    display: none;
    max-width: 1080px;
    margin: 0 auto;
    background: var(--panel-bg);
    border: 1px solid var(--surface-border);
    border-radius: 12px;
  }

  .empty-state.filtered.is-visible {
    display: block;
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



<!-- EXERCISE PAGE -->
<section class="exercise-page">
  <div class="exercise-header">
    <h1>Exercise Library</h1>
    <p>Browse our comprehensive collection of exercises to build your perfect workout routine.</p>
  </div>

  <div class="anatomy-panel">
    <iframe
      id="anatomy-frame"
      class="anatomy-frame"
      src="anatomy_man.html"
      title="Interactive anatomy man"
      loading="lazy">
    </iframe>
  </div>

  <div id="exercise-filter-status" class="exercise-filter-status">
    <strong>Showing all exercises</strong>
  </div>

<script>
  // Theme toggle
  (function() {
    const root = document.documentElement;
    const toggle = document.querySelector('.theme-toggle');
    const anatomyFrame = document.getElementById('anatomy-frame');

    const sendThemeToAnatomy = (theme) => {
      if (!anatomyFrame || !anatomyFrame.contentWindow) {
        return;
      }
      anatomyFrame.contentWindow.postMessage({ type: 'foovia-theme', theme: theme }, '*');
    };

    const setTheme = (theme) => {
      const isDark = theme === 'dark';
      root.setAttribute('data-theme', theme);
      root.style.colorScheme = theme;
      toggle.setAttribute('aria-pressed', String(isDark));
      toggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
      sendThemeToAnatomy(theme);
    };

    const stored = localStorage.getItem('theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initialTheme = stored || (prefersDark ? 'dark' : 'light');
    setTheme(initialTheme);

    if (anatomyFrame) {
      anatomyFrame.addEventListener('load', () => {
        sendThemeToAnatomy(root.getAttribute('data-theme') || initialTheme);
      });
    }

    toggle.addEventListener('click', () => {
      const currentTheme = root.getAttribute('data-theme') || 'light';
      const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
      localStorage.setItem('theme', nextTheme);
      setTheme(nextTheme);
    });
  })();

</script>



      <div class="exercise-grid-wrapper">
        <?php if (empty($exercises)): ?>
          <div class="empty-state">No Exercises Yet</div>
        <?php else: ?>
          <div id="exercise-grid" class="exercise-grid">
            <?php foreach ($exercises as $ex): ?>
              <article
                id="card-<?= (int)$ex['id_ex'] ?>"
                class="exercise-card"
                data-muscle="<?= htmlspecialchars((string)$ex['muscle_ex'], ENT_QUOTES) ?>"
                data-type="<?= htmlspecialchars((string)$ex['type_ex'], ENT_QUOTES) ?>"
                data-name="<?= htmlspecialchars((string)$ex['name_ex'], ENT_QUOTES) ?>">
                <div class="exercise-card-content">
                  <?php if (!empty($ex['gif_ex'])): ?>
                    <img src="data:image/gif;base64,<?= base64_encode($ex['gif_ex']) ?>" class="exercise-gif" alt="<?= htmlspecialchars($ex['name_ex']) ?>" />
                  <?php else: ?>
                    <div class="exercise-image-fallback">NO GIF</div>
                  <?php endif; ?>

                  <div class="exercise-content">
                    <div class="exercise-name"><?= htmlspecialchars($ex['name_ex']) ?></div>
                    <div class="exercise-meta"><?= htmlspecialchars($ex['type_ex']) ?> | <?= htmlspecialchars($ex['muscle_ex']) ?></div>
                    <div class="exercise-calories">🔥 <?= (int)$ex['cal_ex'] ?> cal</div>

                    <button
                      type="button"
                      class="exercise-info-btn"
                      title="<?= htmlspecialchars($ex['description_ex']) ?>"
                      aria-label="Exercise info">
                      i
                    </button>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
          <div id="exercise-filter-empty" class="empty-state filtered">No exercises match the selected anatomy muscles.</div>
        <?php endif; ?>
      </div>

      <script>
        (function() {
          const cards = Array.from(document.querySelectorAll('.exercise-card'));
          const status = document.getElementById('exercise-filter-status');
          const emptyState = document.getElementById('exercise-filter-empty');

          const normalize = (text) =>
            String(text || '')
              .toLowerCase()
              .replace(/[^a-z0-9\s]/g, ' ')
              .replace(/\s+/g, ' ')
              .trim();

          const muscleKeywordMap = {
            Hamstrings: ['hamstring'],
            Glutes: ['glute'],
            Lats: ['lats', 'lat', 'back'],
            Traps: ['trap', 'trapez'],
            Triceps: ['tricep'],
            Forearms: ['forearm'],
            Biceps: ['bicep'],
            Obliques: ['oblique'],
            Abs: ['abs', 'abdom', 'core'],
            Neck: ['neck'],
            Delts: ['delt', 'shoulder'],
            Chest: ['chest', 'pect'],
            Quadriceps: ['quadricep', 'quad', 'thigh'],
            Calves: ['calf', 'calves']
          };

          cards.forEach((card) => {
            const combinedText = [card.dataset.name, card.dataset.type, card.dataset.muscle].join(' ');
            card.dataset.search = normalize(combinedText);
          });

          const applyFilter = (selectedMuscles) => {
            const muscles = Array.isArray(selectedMuscles) ? selectedMuscles : [];
            const selected = muscles.filter((m) => muscleKeywordMap[m]);

            if (selected.length === 0) {
              cards.forEach((card) => card.classList.remove('is-hidden'));
              if (status) {
                status.innerHTML = '<strong>Showing all exercises</strong>';
              }
              if (emptyState) {
                emptyState.classList.remove('is-visible');
              }
              return;
            }

            let visibleCount = 0;

            cards.forEach((card) => {
              const searchText = card.dataset.search || '';
              const match = selected.some((muscle) => {
                const keywords = muscleKeywordMap[muscle] || [];
                return keywords.some((word) => searchText.includes(word));
              });

              card.classList.toggle('is-hidden', !match);
              if (match) visibleCount += 1;
            });

            if (status) {
              status.innerHTML = '<strong>' + visibleCount + '</strong> exercise' + (visibleCount === 1 ? '' : 's') + ' matching: ' + selected.join(', ');
            }

            if (emptyState) {
              emptyState.classList.toggle('is-visible', visibleCount === 0);
            }
          };

          window.addEventListener('message', (event) => {
            if (!event || !event.data) return;
            if (event.data.type === 'foovia-muscles') {
              applyFilter(event.data.muscles);
            }
          });
        })();
      </script>

</section>


</body>
</html>