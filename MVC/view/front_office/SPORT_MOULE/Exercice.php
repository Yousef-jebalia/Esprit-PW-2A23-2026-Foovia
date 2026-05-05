<?php
require_once __DIR__ . '/../../../model/config.php';

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
<link rel="stylesheet" href="exercice_php.css">

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

  <div class="exercise-search">
    <input
      id="exercise-search-input"
      class="exercise-search-input"
      type="search"
      placeholder="Search by exercise, type, or muscle..."
      aria-label="Search exercises" />
    <button id="exercise-search-clear" class="exercise-search-clear" type="button">Clear</button>
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
                data-name="<?= htmlspecialchars((string)$ex['name_ex'], ENT_QUOTES) ?>"
                data-calories="<?= (int)$ex['cal_ex'] ?>"
                data-fatigue="<?= htmlspecialchars((string)$ex['fatigue_ex'], ENT_QUOTES) ?>"
                data-description="<?= htmlspecialchars((string)$ex['description_ex'], ENT_QUOTES) ?>"
                data-gif="<?= !empty($ex['gif_ex']) ? htmlspecialchars(base64_encode($ex['gif_ex']), ENT_QUOTES) : '' ?>">
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

      <div id="exercise-modal-overlay" class="exercise-modal-overlay" aria-hidden="true">
        <div class="exercise-modal" role="dialog" aria-modal="true" aria-labelledby="exercise-modal-title">
          <div class="exercise-modal-header">
            <div id="exercise-modal-title" class="exercise-modal-title">Exercise details</div>
            <button type="button" id="exercise-modal-close" class="exercise-modal-close" aria-label="Close exercise details">&times;</button>
          </div>
          <div class="exercise-modal-body">
            <div class="exercise-modal-media">
              <div id="exercise-modal-image-empty" class="exercise-modal-image-empty">No GIF</div>
              <img id="exercise-modal-image" class="exercise-modal-image" alt="Exercise gif" style="display:none;" />
              <div class="exercise-modal-anatomy">
                <iframe id="exercise-modal-anatomy-frame" src="anatomy_man.html" title="Exercise muscles anatomy" loading="lazy"></iframe>
              </div>
            </div>
            <div class="exercise-modal-content">
              <div class="exercise-modal-grid">
                <div class="exercise-modal-card">
                  <h3>Name</h3>
                  <p id="exercise-modal-name"></p>
                </div>
                <div class="exercise-modal-card">
                  <h3>Type</h3>
                  <p id="exercise-modal-type"></p>
                </div>
                <div class="exercise-modal-card">
                  <h3>Calories</h3>
                  <p id="exercise-modal-calories"></p>
                </div>
                <div class="exercise-modal-card">
                  <h3>Fatigue Ratio</h3>
                  <p id="exercise-modal-fatigue"></p>
                </div>
              </div>

              <div class="exercise-modal-card">
                <h3>Working Muscles</h3>
                <div id="exercise-modal-muscles" class="exercise-muscle-tags"></div>
              </div>

              <div class="exercise-modal-card">
                <h3>Description</h3>
                <p id="exercise-modal-description"></p>
              </div>

              <div class="exercise-modal-actions">
                <button type="button" id="exercise-info-window-btn" class="exercise-info-window-btn">Open in Info Window</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div id="info-window-overlay" class="info-window-overlay" aria-hidden="true">
        <div class="info-window" role="dialog" aria-modal="true" aria-labelledby="info-window-title">
          <div class="info-window-header">
            <div id="info-window-title" class="info-window-title">Info Window</div>
            <button type="button" id="info-window-close" class="info-window-close" aria-label="Close info window">&times;</button>
          </div>
          <div class="info-window-body">
            <iframe id="info-window-frame" class="info-window-frame" src="anatomy_man.html?readonly=1" title="Info Window anatomy" loading="lazy"></iframe>
          </div>
        </div>
      </div>

      <script>
        function initExerciseSearch() {
          const cards = Array.from(document.querySelectorAll('.exercise-card'));
          const status = document.getElementById('exercise-filter-status');
          const emptyState = document.getElementById('exercise-filter-empty');
          const searchInput = document.getElementById('exercise-search-input');
          const clearButton = document.getElementById('exercise-search-clear');
          const overlay = document.getElementById('exercise-modal-overlay');
          const closeButton = document.getElementById('exercise-modal-close');
          const infoWindowBtn = document.getElementById('exercise-info-window-btn');
          const modalTitle = document.getElementById('exercise-modal-title');
          const modalImage = document.getElementById('exercise-modal-image');
          const modalImageEmpty = document.getElementById('exercise-modal-image-empty');
          const modalName = document.getElementById('exercise-modal-name');
          const modalType = document.getElementById('exercise-modal-type');
          const modalCalories = document.getElementById('exercise-modal-calories');
          const modalFatigue = document.getElementById('exercise-modal-fatigue');
          const modalMuscles = document.getElementById('exercise-modal-muscles');
          const modalDescription = document.getElementById('exercise-modal-description');
          const anatomyFrame = document.getElementById('exercise-modal-anatomy-frame');

          let selectedMuscles = [];
          let searchQuery = '';
          let currentExerciseMuscles = [];

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

          const applyFilter = () => {
            const selected = selectedMuscles.filter((m) => muscleKeywordMap[m]);
            const hasMuscleFilter = selected.length > 0;
            const hasSearchFilter = searchQuery.length > 0;

            let visibleCount = 0;

            cards.forEach((card) => {
              const searchText = card.dataset.search || '';
              const muscleMatch = !hasMuscleFilter || selected.some((muscle) => {
                const keywords = muscleKeywordMap[muscle] || [];
                return keywords.some((word) => searchText.includes(word));
              });
              const queryMatch = !hasSearchFilter || searchText.includes(searchQuery);
              const match = muscleMatch && queryMatch;

              card.classList.toggle('is-hidden', !match);
              if (match) visibleCount += 1;
            });

            if (status) {
              if (!hasMuscleFilter && !hasSearchFilter) {
                status.innerHTML = '<strong>Showing all exercises</strong>';
              } else {
                const active = [];
                if (hasMuscleFilter) {
                  active.push('muscles: ' + selected.join(', '));
                }
                if (hasSearchFilter) {
                  active.push('search: "' + searchQuery + '"');
                }
                status.innerHTML = '<strong>' + visibleCount + '</strong> exercise' + (visibleCount === 1 ? '' : 's') + ' matching ' + active.join(' | ');
              }
            }

            if (emptyState) {
              const noResults = visibleCount === 0 && (hasMuscleFilter || hasSearchFilter);
              emptyState.classList.toggle('is-visible', noResults);
            }
          };

          const openExerciseModal = (card) => {
            if (!overlay || !card) {
              return;
            }

            const muscles = String(card.dataset.muscle || '')
              .split(',')
              .map((item) => item.trim())
              .filter(Boolean);
            const uniqueMuscles = Array.from(new Set(muscles));
            
            // Store muscles for info window
            currentExerciseMuscles = uniqueMuscles;

            modalTitle.textContent = (card.dataset.name || 'Exercise') + ' details';
            modalName.textContent = card.dataset.name || 'Unknown';
            modalType.textContent = card.dataset.type || 'Unknown';
            modalCalories.textContent = (card.dataset.calories || '0') + ' cal';
            modalFatigue.textContent = card.dataset.fatigue || 'N/A';
            modalDescription.textContent = card.dataset.description || 'No description available.';

            const gif = card.dataset.gif || '';
            if (gif) {
              modalImage.src = 'data:image/gif;base64,' + gif;
              modalImage.style.display = 'block';
              modalImageEmpty.style.display = 'none';
            } else {
              modalImage.removeAttribute('src');
              modalImage.style.display = 'none';
              modalImageEmpty.style.display = 'flex';
            }

            modalMuscles.innerHTML = '';
            if (uniqueMuscles.length === 0) {
              modalMuscles.innerHTML = '<span class="exercise-muscle-tag">No muscles detected</span>';
            } else {
              uniqueMuscles.forEach((muscle) => {
                const tag = document.createElement('span');
                tag.className = 'exercise-muscle-tag';
                tag.textContent = muscle;
                modalMuscles.appendChild(tag);
              });
            }

            if (anatomyFrame && anatomyFrame.contentWindow) {
              anatomyFrame.contentWindow.postMessage({ type: 'foovia-muscles', muscles: uniqueMuscles }, '*');
            } else if (anatomyFrame) {
              anatomyFrame.addEventListener('load', () => {
                anatomyFrame.contentWindow.postMessage({ type: 'foovia-muscles', muscles: uniqueMuscles }, '*');
              }, { once: true });
            }

            overlay.style.display = 'flex';
            overlay.setAttribute('aria-hidden', 'false');
          };

          const closeExerciseModal = () => {
            if (!overlay) {
              return;
            }
            overlay.style.display = 'none';
            overlay.setAttribute('aria-hidden', 'true');
          };

          if (searchInput) {
            searchInput.addEventListener('input', (event) => {
              searchQuery = normalize(event.target.value);
              applyFilter();
            });
          }

          cards.forEach((card) => {
            const infoButton = card.querySelector('.exercise-info-btn');
            if (infoButton) {
              infoButton.addEventListener('click', () => openExerciseModal(card));
            }
          });

          if (closeButton) {
            closeButton.addEventListener('click', closeExerciseModal);
          }

          if (infoWindowBtn) {
            infoWindowBtn.addEventListener('click', () => {
              if (currentExerciseMuscles && currentExerciseMuscles.length > 0) {
                window.showInfoWindow(currentExerciseMuscles);
              }
            });
          }

          if (overlay) {
            overlay.addEventListener('click', (event) => {
              if (event.target === overlay) {
                closeExerciseModal();
              }
            });
          }

          document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && overlay && overlay.style.display === 'flex') {
              closeExerciseModal();
            }
          });

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

          window.addEventListener('message', (event) => {
            if (!event || !event.data) return;
            if (event.data.type === 'foovia-muscles') {
              selectedMuscles = Array.isArray(event.data.muscles) ? event.data.muscles : [];
              applyFilter();
            }
          });

          applyFilter();
        }

        initExerciseSearch();
      </script>

      <script>
        function showInfoWindow(workingMusclesJson) {
          const overlay = document.getElementById('info-window-overlay');
          const closeButton = document.getElementById('info-window-close');
          const frame = document.getElementById('info-window-frame');

          const normalizeMuscles = (value) => {
            if (Array.isArray(value)) {
              return Array.from(new Set(value.map((item) => String(item || '').trim()).filter(Boolean)));
            }

            if (typeof value === 'string') {
              try {
                return normalizeMuscles(JSON.parse(value));
              } catch (error) {
                return normalizeMuscles([value]);
              }
            }

            if (value && Array.isArray(value.working_muscles)) {
              return normalizeMuscles(value.working_muscles);
            }

            if (value && Array.isArray(value.muscles)) {
              return normalizeMuscles(value.muscles);
            }

            return [];
          };

          const muscles = normalizeMuscles(workingMusclesJson);

          const sendMuscles = () => {
            if (frame && frame.contentWindow) {
              frame.contentWindow.postMessage({ type: 'foovia-info-window', muscles: muscles }, '*');
            }
          };

          if (!overlay || !frame) {
            return;
          }

          overlay.style.display = 'flex';
          overlay.setAttribute('aria-hidden', 'false');

          if (frame.contentWindow) {
            sendMuscles();
          } else {
            frame.addEventListener('load', sendMuscles, { once: true });
          }

          if (closeButton && !closeButton.dataset.bound) {
            closeButton.dataset.bound = '1';
            closeButton.addEventListener('click', () => {
              overlay.style.display = 'none';
              overlay.setAttribute('aria-hidden', 'true');
            });

            overlay.addEventListener('click', (event) => {
              if (event.target === overlay) {
                overlay.style.display = 'none';
                overlay.setAttribute('aria-hidden', 'true');
              }
            });

            document.addEventListener('keydown', (event) => {
              if (event.key === 'Escape' && overlay.style.display === 'flex') {
                overlay.style.display = 'none';
                overlay.setAttribute('aria-hidden', 'true');
              }
            });
          }
        }

        window.showInfoWindow = showInfoWindow;
      </script>

</section>


</body>
</html>