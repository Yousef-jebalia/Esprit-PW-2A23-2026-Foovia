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

  .exercise-search {
    max-width: 1080px;
    margin: 0 auto 16px;
    display: flex;
    gap: 10px;
    align-items: center;
  }

  .exercise-search-input {
    flex: 1;
    height: 44px;
    border: 1px solid var(--surface-border);
    border-radius: 10px;
    padding: 0 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.95rem;
    color: var(--panel-text);
    background: var(--panel-bg);
    outline: none;
  }

  .exercise-search-input:focus {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(17, 121, 90, 0.16);
  }

  .exercise-search-clear {
    height: 44px;
    border: 1px solid var(--surface-border);
    border-radius: 10px;
    padding: 0 14px;
    font-family: 'Syne', sans-serif;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--panel-text);
    background: var(--panel-bg);
    cursor: pointer;
  }

  .exercise-search-clear:hover {
    border-color: var(--green);
    color: var(--green);
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

  .exercise-modal-overlay {
    position: fixed;
    inset: 0;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 18px;
    background: rgba(8, 14, 20, 0.62);
    z-index: 1200;
  }

  .exercise-modal {
    width: min(780px, 96vw);
    max-height: 88vh;
    overflow: hidden;
    border-radius: 18px;
    border: 1px solid var(--surface-border);
    background: var(--panel-bg);
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
    display: flex;
    flex-direction: column;
  }

  .exercise-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 16px 18px;
    border-bottom: 1px solid var(--surface-border);
  }

  .exercise-modal-title {
    font-family: 'Syne', sans-serif;
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--page-text);
  }

  .exercise-modal-close {
    width: 34px;
    height: 34px;
    border: 1px solid var(--surface-border);
    border-radius: 999px;
    background: transparent;
    color: var(--page-text);
    cursor: pointer;
    font-size: 1rem;
    line-height: 1;
  }

  .exercise-modal-body {
    padding: 14px;
    overflow: auto;
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 14px;
  }

  .exercise-modal-media {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .exercise-modal-image,
  .exercise-modal-image-empty {
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 14px;
    border: 1px solid var(--surface-border);
    background: var(--surface-2);
    object-fit: cover;
  }

  .exercise-modal-image-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--page-muted);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.84rem;
  }

  .exercise-modal-anatomy {
    width: 100%;
    height: 200px;
    border: 1px solid var(--surface-border);
    border-radius: 14px;
    overflow: hidden;
    background: var(--surface-2);
  }

  .exercise-modal-anatomy iframe {
    width: 100%;
    height: 100%;
    border: 0;
  }

  .exercise-modal-content {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .exercise-modal-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }

  .exercise-modal-card {
    border: 1px solid var(--surface-border);
    border-radius: 12px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.5);
  }

  .exercise-modal-card h3 {
    margin: 0 0 6px;
    font-family: 'Syne', sans-serif;
    font-size: 0.84rem;
    color: var(--page-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .exercise-modal-card p,
  .exercise-modal-card div {
    margin: 0;
    font-family: 'DM Sans', sans-serif;
    color: var(--page-text);
  }

  .exercise-modal-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    justify-content: center;
  }

  .exercise-info-window-btn {
    padding: 10px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
  }

  .exercise-info-window-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
  }

  .exercise-info-window-btn:active {
    transform: translateY(0);
  }

  .info-window-overlay {
    position: fixed;
    inset: 0;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 18px;
    background: rgba(8, 14, 20, 0.58);
    z-index: 1250;
  }

  .info-window {
    width: min(420px, 96vw);
    max-height: 88vh;
    overflow: hidden;
    border-radius: 18px;
    border: 1px solid var(--surface-border);
    background: var(--panel-bg);
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
    display: flex;
    flex-direction: column;
  }

  .info-window-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--surface-border);
  }

  .info-window-title {
    font-family: 'Syne', sans-serif;
    font-size: 1rem;
    font-weight: 800;
    color: var(--page-text);
  }

  .info-window-close {
    width: 34px;
    height: 34px;
    border: 1px solid var(--surface-border);
    border-radius: 999px;
    background: transparent;
    color: var(--page-text);
    cursor: pointer;
    font-size: 1rem;
    line-height: 1;
  }

  .info-window-body {
    padding: 10px;
  }

  .info-window-frame {
    width: 100%;
    height: 560px;
    border: 0;
    border-radius: 14px;
    background: transparent;
  }

  @media (max-width: 760px) {
    .info-window-frame {
      height: 480px;
    }
  }

  .exercise-muscle-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .exercise-muscle-tag {
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(17, 121, 90, 0.1);
    color: var(--forest);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.82rem;
    font-weight: 700;
  }

  @media (max-width: 760px) {
    .exercise-modal-body {
      grid-template-columns: 1fr;
    }

    .exercise-modal-grid {
      grid-template-columns: 1fr;
    }
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