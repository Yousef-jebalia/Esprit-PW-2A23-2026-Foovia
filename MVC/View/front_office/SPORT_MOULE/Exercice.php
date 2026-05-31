<?php
require_once __DIR__ . '/../../../Model/config.php';

$db = config::getConnexion();
$stmt = $db->query("SELECT * FROM exercice ORDER BY id_ex DESC");
$exercises = $stmt->fetchAll();
?>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: ../foovia-signin.php');
  exit;
}
include_once(__DIR__ . '/../../../Controller/Controller_user.php');
$userId = $_SESSION['user_id'];
$is_logged_in = true;
$user_name = $_SESSION['user_name'] ?? 'User';

$userController = new Controller_user();
$userData = $userController->get_user($userId);
$userSubscription = $userData['subscription_user'] ?? 'free';
$isAdmin = isset($_SESSION['role_user']) && strtolower(trim((string) $_SESSION['role_user'])) === 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/Plan de travail 1 no bg (3) (1).png">
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Exercises — FOOVIA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="exercice_php.css">
<link rel="stylesheet" href="../foovia.css">
<style>
  /* Premium Badge Navigation Component */
  .premium-badge-nav {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #E8B84B 0%, #F0A830 100%);
    border-radius: 50%;
    color: #fff;
    box-shadow: 0 4px 12px rgba(232, 184, 75, 0.3);
    margin-left: 10px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 2px solid #fff;
    flex-shrink: 0;
  }
  .premium-badge-nav:hover {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 6px 16px rgba(232, 184, 75, 0.4);
  }
  .premium-icon-nav {
    width: 22px;
    height: 22px;
    filter: brightness(0) invert(1);
  }
</style>
 
<!-- Skeleton loader styles -->
<style>
  .skeleton-overlay {
    position: fixed;
    inset: 0;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1200;
    transition: opacity 0.5s ease, visibility 0.5s ease;
  }
  .skeleton-hidden { opacity: 0; visibility: hidden; pointer-events: none; }
  .skeleton-inner { width: 90%; max-width: 1100px; }
  .skeleton-block { background: linear-gradient(90deg, #eee 25%, #f7f7f7 50%, #eee 75%); background-size: 200% 100%; animation: shimmer 1.2s infinite; border-radius: 8px; }
  .sk-nav { height: 56px; margin-bottom: 18px; }
  .sk-title { height: 48px; width: 50%; margin: 14px 0 24px; }
  .sk-anatomy { height: 360px; border-radius: 12px; margin-bottom: 18px; }
  .sk-search { height: 44px; width: 60%; margin-bottom: 18px; }
  .sk-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(220px,1fr)); gap: 16px; }
  .sk-card { height: 160px; border-radius: 10px; }

  @keyframes shimmer {
    0% { background-position: 200% 0 }
    100% { background-position: -200% 0 }
  }
</style>
</head>
<body>
  <!-- Skeleton overlay shown while page resources load -->
  <div id="skeleton-overlay" class="skeleton-overlay" aria-hidden="false">
    <div class="skeleton-inner">
      <div class="skeleton-block sk-nav"></div>
      <div class="skeleton-block sk-title"></div>
      <div class="skeleton-block sk-anatomy"></div>
      <div class="skeleton-block sk-search"></div>
      <div class="sk-grid">
        <div class="skeleton-block sk-card"></div>
        <div class="skeleton-block sk-card"></div>
        <div class="skeleton-block sk-card"></div>
        <div class="skeleton-block sk-card"></div>
        <div class="skeleton-block sk-card"></div>
        <div class="skeleton-block sk-card"></div>
      </div>
    </div>
  </div>

<!-- NAV -->
<nav>
  <div style="display:flex;align-items:center;gap:2px;margin-left:0;flex-shrink:0;">
    <button class="nav-sidebar-toggle" type="button" aria-label="Open page list" aria-controls="navSidebar" aria-expanded="false" style="width:54px;height:54px;border-radius:12px;gap:4px;padding:0;display:inline-flex;flex-direction:column;align-items:center;justify-content:center;background:rgba(255,255,255,.72);border-color:rgba(17,16,8,.18);margin-right:8px;">
      <span style="width:26px;height:4px;border-radius:999px;display:block;background:#111008;"></span>
      <span style="width:26px;height:4px;border-radius:999px;display:block;background:#111008;"></span>
      <span style="width:26px;height:4px;border-radius:999px;display:block;background:#111008;"></span>
    </button>
    <a href="../foovia.php" class="nav-logo">
      <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" style="height: 50px; width: auto;">
      FOOVIA
    </a>
  </div>
  <ul class="nav-links">
     <li><a href="Exercice.php">Exercice</a></li>
    <li><a href="Workout.php">Workouts</a></li>
    <li><a href="<?php echo ($userSubscription === 'premium' || $userSubscription === 'elite') ? 'custome_workout.php' : '../foovia-premium.php'; ?>">Custom Workouts</a></li>
  </ul>
  <div class="nav-actions">
    <?php if ($isAdmin): ?>
      <a href="../foovia-backoffice.php" class="nav-btn nav-backoffice">Backoffice</a>
    <?php endif; ?>
    <button class="theme-toggle" type="button" aria-label="Switch to dark mode" aria-pressed="false">
      <svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true">
        <circle cx="12" cy="12" r="4"></circle>
        <path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"></path>
      </svg>
      <svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M21 14.5A8.5 8.5 0 1 1 9.5 3a7 7 0 1 0 11.5 11.5z"></path>
      </svg>
    </button>
    <?php if ($is_logged_in): ?>
      <div class="dropdown">
        <a href="#" class="nav-btn dropdown-toggle" role="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
          Welcome, <?php echo htmlspecialchars($user_name); ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
          <li><a class="dropdown-item" href="../profile.php">My Account</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
        </ul>
      </div>
    <?php else: ?>
      <a href="../foovia-signin.php" class="nav-btn nav-signin">Sign In</a>
      <a href="../../back_office/USER_MODULE/foovia-signup.php" class="nav-btn nav-signup">Sign Up</a>
    <?php endif; ?>
    <?php if ($is_logged_in && ($userSubscription === 'premium' || $userSubscription === 'elite')): ?>
      <div class="premium-badge-nav" title="Premium Member" onclick="window.location.href='../foovia-premium.php'">
        <img src="../assets/crown-svgrepo-com%20(1).svg" class="premium-icon-nav" alt="Premium">
      </div>
    <?php endif; ?>
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
                    <div class="exercise-calories"><svg width="14" height="14" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;fill:red;margin-right:6px;"><path d="M9.75838 1.09929C9.85156 1.13153 9.9852 1.17902 10.1535 1.24207C10.49 1.36812 10.9661 1.55678 11.5355 1.81078C12.6715 2.31752 14.193 3.09073 15.7215 4.15505C18.745 6.26052 22 9.65692 22 14.5393C22 16.6738 21.4305 18.7869 20.1046 20.3856C18.7552 22.0126 16.7095 23 14 23C13.9352 23 13.6752 22.9978 13.4169 22.8125C13.0566 22.5541 12.9699 22.1541 13.0085 21.8667C13.0376 21.6502 13.1305 21.5025 13.1576 21.4602C13.1966 21.3993 13.234 21.3556 13.2534 21.3338C13.293 21.2893 13.3281 21.2581 13.3407 21.247C13.3575 21.2322 13.3716 21.2207 13.3801 21.214C13.4065 21.1929 13.4323 21.1745 13.4402 21.1689L13.4413 21.1681L13.5185 21.1136C13.5762 21.0727 13.6587 21.0131 13.7588 20.9348C13.9606 20.7768 14.2297 20.546 14.4969 20.2526C15.0448 19.6509 15.5 18.8819 15.5 18C15.5 16.3681 14.571 14.8515 13.5067 13.669C12.9869 13.0914 12.4644 12.6267 12.0715 12.3065C12.0471 12.2866 12.0233 12.2674 12 12.2487C11.9767 12.2674 11.9529 12.2866 11.9285 12.3065C11.5356 12.6267 11.0131 13.0914 10.4933 13.669C9.42904 14.8515 8.5 16.3681 8.5 18C8.5 18.8887 8.95405 19.6581 9.49825 20.2564C9.76406 20.5486 10.0319 20.7779 10.2327 20.934C10.3323 21.0114 10.4142 21.0699 10.47 21.1087C10.4933 21.125 10.5115 21.1374 10.5281 21.1487L10.5401 21.1569C10.5471 21.1616 10.5635 21.1728 10.5787 21.1837C10.5832 21.187 10.6139 21.2089 10.6476 21.2376C10.6583 21.2467 10.6772 21.2632 10.6995 21.285C10.7154 21.3005 10.7647 21.3492 10.8157 21.4212C10.8424 21.4607 10.901 21.5658 10.9302 21.6326C10.9668 21.7437 10.9991 22.045 10.9733 22.2301C10.89 22.4562 10.6027 22.798 10.4241 22.9056C10.2979 22.9546 10.0834 22.9965 10 23C7.29045 23 5.24478 22.0126 3.89543 20.3856C2.56953 18.7869 2 16.6738 2 14.5393C2 11.9892 2.88357 10.3815 4.05286 9.15507C4.5965 8.58486 5.19715 8.10224 5.73579 7.66945L5.77852 7.63511C6.34602 7.17903 6.84273 6.7759 7.26778 6.31893C8.30821 5.20037 8.54446 4.18717 8.56055 3.49802C8.56885 3.14245 8.51857 2.85417 8.46943 2.66213C8.44495 2.56644 8.42112 2.49608 8.40592 2.45502C8.39834 2.43455 8.39298 2.42158 8.39089 2.41662C8.22725 2.05872 8.28834 1.6367 8.54841 1.34037C8.86981 0.974175 9.32884 0.950674 9.75838 1.09929Z" fill="red"/></svg> <?= (int)$ex['cal_ex'] ?> cal</div>

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

      <script>
        // Hide skeleton overlay when page finishes loading or on timeout fallback
        (function() {
          const overlay = document.getElementById('skeleton-overlay');
          const hideOverlay = () => {
            if (!overlay) return;
            overlay.classList.add('skeleton-hidden');
            setTimeout(() => { if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); }, 600);
          };

          // Prefer full load event to ensure iframes/images are ready
          window.addEventListener('load', hideOverlay);

          // Fallback: hide after 2s in case load doesn't fire quickly
          setTimeout(hideOverlay, 2000);
        })();
      </script>

</section>

<script src="../js/sidebar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>