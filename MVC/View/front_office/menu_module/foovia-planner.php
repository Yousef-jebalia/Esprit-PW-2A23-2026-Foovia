<?php
require_once __DIR__ . '/../../../Model/config.php';
require_once __DIR__ . '/../../../Controller/menu_module/controle_Menu.php';

session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: ../foovia-signin.php');
  exit;
}

$userId = (int) $_SESSION['user_id'];
$userName = (string) ($_SESSION['user_name'] ?? 'User');
$db = config::getConnexion();

function foovia_split_list(string $value): array {
  $value = strtolower(trim($value));
  if ($value === '' || $value === 'none' || $value === 'n/a') {
    return [];
  }

  $parts = preg_split('/[,;|]+/', $value) ?: [];
  $clean = [];
  foreach ($parts as $part) {
    $part = trim((string) $part);
    if ($part !== '') {
      $clean[] = $part;
    }
  }

  return array_values(array_unique($clean));
}

function foovia_guess_category(array $categories, string $name): string {
  $text = strtolower(trim($name . ' ' . implode(' ', $categories)));
  if (preg_match('/\b(breakfast|petit|dejeuner|morning|brunch)\b/', $text)) {
    return 'breakfast';
  }
  if (preg_match('/\b(lunch|dejeuner|noon|midday)\b/', $text)) {
    return 'lunch';
  }
  if (preg_match('/\b(dinner|diner|supper|evening)\b/', $text)) {
    return 'dinner';
  }
  if (preg_match('/\b(snack|collation)\b/', $text)) {
    return 'snack';
  }
  return 'lunch';
}

function foovia_pick_emoji(string $category): string {
  switch ($category) {
    case 'breakfast':
      return '🍳';
    case 'lunch':
      return '🥗';
    case 'dinner':
      return '🍲';
    case 'snack':
      return '🥜';
    default:
      return '🍽️';
  }
}

function foovia_pick_bg(string $category): string {
  switch ($category) {
    case 'breakfast':
      return '#fff8e1';
    case 'lunch':
      return '#e8f5e9';
    case 'dinner':
      return '#fde8d8';
    case 'snack':
      return '#fff3e0';
    default:
      return '#fdf3dc';
  }
}

function foovia_normalize_image_path($path, $fallback = 'images/product-thumb-1.png') {
  $path = str_replace('\\', '/', trim((string)$path));
  if ($path === '') {
    return $fallback;
  }

  if (!preg_match('~^(https?://|/|\./|\.\./)~i', $path)) {
    return '../../back_office/' . ltrim($path, '/');
  }

  return $path;
}

function foovia_is_gluten_free(array $ingredients): bool {
  if (empty($ingredients)) {
    return true;
  }
  $glutenSources = ['wheat', 'flour', 'pasta', 'bread', 'semolina', 'barley', 'rye'];
  foreach ($ingredients as $ingredient) {
    $name = strtolower($ingredient);
    foreach ($glutenSources as $source) {
      if (strpos($name, $source) !== false) {
        return false;
      }
    }
  }
  return true;
}

function foovia_recipe_has_allergen(array $ingredients, array $allergies, string $recipeName): bool {
  if (empty($allergies)) {
    return false;
  }
  $recipeName = strtolower($recipeName);
  foreach ($allergies as $allergy) {
    $needle = strtolower($allergy);
    if ($needle === '') {
      continue;
    }
    if (strpos($recipeName, $needle) !== false) {
      return true;
    }
    foreach ($ingredients as $ingredient) {
      if (strpos(strtolower($ingredient), $needle) !== false) {
        return true;
      }
    }
  }
  return false;
}

function foovia_format_number($value, int $precision = 0): string {
  if (!is_numeric($value)) {
    return '0';
  }

  $value = (float) $value;
  if (abs($value - round($value)) < 0.01) {
    $precision = 0;
  }

  return rtrim(rtrim(number_format($value, $precision), '0'), '.');
}

$userRow = [];
try {
  $userQuery = $db->prepare('SELECT allergie_user, illness_user FROM user WHERE id_user = :id_user LIMIT 1');
  $userQuery->execute(['id_user' => $userId]);
  $userRow = $userQuery->fetch() ?: [];
} catch (Exception $e) {
  $userRow = [];
}

$allergies = foovia_split_list((string) ($userRow['allergie_user'] ?? ''));
$illnesses = foovia_split_list((string) ($userRow['illness_user'] ?? ''));

$goals = [
  'kcal' => 2000,
  'prot' => 150,
  'carb' => 200,
  'fat' => 65,
];
$hasGoal = false;
try {
  $goalQuery = $db->prepare("SELECT obj_cal_obj, obj_prot_obj, obj_carb_obj, obj_fat_obj FROM objectiflongterme WHERE id_user = :id_user ORDER BY (status_obj = 'en_cours') DESC, date_deb_obj DESC, id_obj DESC LIMIT 1");
  $goalQuery->execute(['id_user' => $userId]);
  $goalRow = $goalQuery->fetch();
  if (is_array($goalRow)) {
    $goals = [
      'kcal' => (float) ($goalRow['obj_cal_obj'] ?? $goals['kcal']),
      'prot' => (float) ($goalRow['obj_prot_obj'] ?? $goals['prot']),
      'carb' => (float) ($goalRow['obj_carb_obj'] ?? $goals['carb']),
      'fat' => (float) ($goalRow['obj_fat_obj'] ?? $goals['fat']),
    ];
    $hasGoal = true;
  }
} catch (Exception $e) {
  $hasGoal = false;
}

$controller = new Controller_menu();
$recipesRaw = $controller->list_recipe();

$ingredientsByRecipe = [];
try {
  $ingredientQuery = $db->query('SELECT ct.id_rec, i.name_ing FROM contenir ct LEFT JOIN ingrediant i ON i.id_ing = ct.id_ing ORDER BY ct.id_rec ASC');
  foreach ($ingredientQuery as $row) {
    $idRec = (int) ($row['id_rec'] ?? 0);
    $nameIng = trim((string) ($row['name_ing'] ?? ''));
    if ($idRec > 0 && $nameIng !== '') {
      $ingredientsByRecipe[$idRec][] = $nameIng;
    }
  }
} catch (Exception $e) {
  $ingredientsByRecipe = [];
}

$recipesForJs = [];
foreach ($recipesRaw as $row) {
  $id = (int) ($row['id_rec'] ?? 0);
  $name = trim((string) ($row['name_rec'] ?? ''));
  if ($id <= 0 || $name === '') {
    continue;
  }
  $ingredients = $ingredientsByRecipe[$id] ?? [];
  if (foovia_recipe_has_allergen($ingredients, $allergies, $name)) {
    continue;
  }
  $categoryList = array_filter(array_map('trim', explode(',', (string) ($row['categorie_rec'] ?? ''))));
  $category = foovia_guess_category($categoryList, $name);
  $tags = [];
  $prot = (float) ($row['prot_rec'] ?? 0);
  $carb = (float) ($row['carb_rec'] ?? 0);
  $fat = (float) ($row['fat_rec'] ?? 0);
  if ($prot >= 25) {
    $tags[] = 'high-protein';
  }
  if ($carb <= 20) {
    $tags[] = 'low-carb';
  }
  if (foovia_is_gluten_free($ingredients)) {
    $tags[] = 'gluten-free';
  }
  $categoryText = strtolower(implode(' ', $categoryList));
  if (strpos($categoryText, 'vegan') !== false) {
    $tags[] = 'vegan';
  }
  if (strpos($categoryText, 'vegetarian') !== false) {
    $tags[] = 'vegetarian';
  }

  $recipesForJs[] = [
    'id' => $id,
    'name' => $name,
    'emoji' => foovia_pick_emoji($category),
    'bg' => foovia_pick_bg($category),
    'image' => foovia_normalize_image_path($row['img_rec'] ?? '', ''),
    'origin' => trim((string) ($row['origin_rec'] ?? '')),
    'cat' => $category,
    'tags' => array_values(array_unique($tags)),
    'kcal' => (float) ($row['cal_rec'] ?? 0),
    'prot' => $prot,
    'carb' => $carb,
    'fat' => $fat,
    'ingredients' => array_values(array_unique($ingredients)),
  ];
}

$allowedIds = array_column($recipesForJs, 'id');
$quickAddIds = [];
try {
  $favoriteQuery = $db->prepare('SELECT id_rec FROM choisir WHERE id_user = :id_user ORDER BY created_at DESC LIMIT 5');
  $favoriteQuery->execute(['id_user' => $userId]);
  $quickAddIds = array_map('intval', $favoriteQuery->fetchAll(PDO::FETCH_COLUMN));
} catch (Exception $e) {
  $quickAddIds = [];
}
$quickAddIds = array_values(array_filter($quickAddIds, function ($id) use ($allowedIds) {
  return in_array((int) $id, $allowedIds, true);
}));
if (empty($quickAddIds)) {
  $sorted = $recipesForJs;
  usort($sorted, function ($a, $b) {
    return ($b['prot'] <=> $a['prot']) ?: ($b['kcal'] <=> $a['kcal']);
  });
  $quickAddIds = array_slice(array_column($sorted, 'id'), 0, 5);
}

$allergyLabel = $allergies ? implode(', ', $allergies) : 'None';
$illnessLabel = $illnesses ? implode(', ', $illnesses) : 'None';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA — Meal Planner</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Boldonse&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="foovia-planner.css?v=<?php echo time(); ?>">
</head>
<body>

<!-- NAV -->
<nav class="foovia-nav" data-theme="light" aria-label="Main navigation">
  <a href="../foovia.php" class="nav-logo">
    <img src="../assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo">
    FOOVIA
  </a>
  <ul class="nav-links">
    <li><a href="../foovia.php">Home</a></li>
    <li><a href="recipe_page.php">Recipes</a></li>
    <li><a href="#" class="active">Meal Plan</a></li>
    <li><a href="../TRACK_MODULE/tracking.php">Tracker</a></li>
  </ul>
  <div class="nav-actions">
    <button class="btn-nav outline" onclick="clearDay()">Clear day</button>
    <button class="btn-nav" onclick="autoFillDay()">✨ Auto-fill day</button>
    <a href="../foovia-backoffice.php" class="nav-btn nav-backoffice">Backoffice</a>
    <button class="theme-toggle" type="button" aria-label="Switch to dark mode" aria-pressed="false">
      <svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true">
        <circle cx="12" cy="12" r="4"></circle>
        <path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"></path>
      </svg>
      <svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M21 14.5A8.5 8.5 0 1 1 9.5 3a7 7 0 1 0 11.5 11.5z"></path>
      </svg>
    </button>
    <?php if (isset($_SESSION['user_id'])): ?>
      <div class="dropdown">
        <a href="#" class="nav-btn dropdown-toggle" role="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
          Welcome, <?php echo htmlspecialchars($userName); ?>
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
  </div>
</nav>

<div class="page">

  <!-- HEADER -->
  <div class="page-header">
    <p class="header-eyebrow">📋 Weekly Planner</p>
    <h1 class="header-title"><?php echo htmlspecialchars($userName); ?>'s <span>meal plan</span></h1>
    <p class="header-sub">Drag to rearrange · Click swap to change · Auto-fill for quick suggestions</p>
    <p class="header-meta">Goal: <?php echo htmlspecialchars(foovia_format_number($goals['kcal'])); ?> kcal · Allergies: <?php echo htmlspecialchars($allergyLabel); ?> · Conditions: <?php echo htmlspecialchars($illnessLabel); ?></p>
    <div class="header-stats">
      <div class="hstat"><div class="hstat-val" id="hs-kcal">0</div><div class="hstat-lbl">kcal today</div></div>
      <div class="hstat"><div class="hstat-val" id="hs-prot">0g</div><div class="hstat-lbl">protein</div></div>
      <div class="hstat"><div class="hstat-val" id="hs-meals">0</div><div class="hstat-lbl">meals planned</div></div>
      <div class="hstat"><div class="hstat-val" id="hs-complete">0%</div><div class="hstat-lbl">day complete</div></div>
    </div>
  </div>

  <!-- WEEK NAV -->
  <div class="week-nav">
    <button class="week-arrow" onclick="changeWeek(-1)">← Prev</button>
    <span class="week-label" id="week-label"></span>
    <button class="week-today-btn" onclick="goToToday()">Today</button>
    <button class="week-arrow" onclick="changeWeek(1)">Next →</button>
  </div>

  <!-- DAY TABS -->
  <div class="day-tabs" id="day-tabs"></div>

  <!-- MAIN -->
  <div class="main-layout">

    <!-- DAY PANEL -->
    <div class="day-panel" id="day-panel"></div>

    <!-- SIDEBAR -->
    <div class="sidebar">

      <!-- WEEK OVERVIEW -->
      <div class="scard">
        <div class="scard-title">📅 Week at a glance</div>
        <div class="week-mini" id="week-mini"></div>
      </div>

      <!-- GOALS CARD -->
      <div class="scard">
        <div class="scard-title">🎯 Daily goals</div>
        <div class="goal-stack">
          <div>
            <div class="goal-row">
              <span class="goal-label">Calories</span>
              <span id="goal-kcal-txt" class="goal-value"></span>
            </div>
            <div class="goal-track">
              <div id="goal-kcal-bar" class="goal-bar kcal"></div>
            </div>
          </div>
          <div>
            <div class="goal-row">
              <span class="goal-label">Protein</span>
              <span id="goal-prot-txt" class="goal-value"></span>
            </div>
            <div class="goal-track">
              <div id="goal-prot-bar" class="goal-bar prot"></div>
            </div>
          </div>
          <div>
            <div class="goal-row">
              <span class="goal-label">Carbs</span>
              <span id="goal-carb-txt" class="goal-value"></span>
            </div>
            <div class="goal-track">
              <div id="goal-carb-bar" class="goal-bar carb"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="scard">
        <div class="scard-title">🧩 Dietary filters</div>
        <div class="dietary-list">
          <div class="dietary-item">
            <span class="dietary-label">Allergies</span>
            <span class="dietary-value<?php echo $allergies ? '' : ' dietary-empty'; ?>"><?php echo htmlspecialchars($allergyLabel); ?></span>
          </div>
          <div class="dietary-item">
            <span class="dietary-label">Conditions</span>
            <span class="dietary-value<?php echo $illnesses ? '' : ' dietary-empty'; ?>"><?php echo htmlspecialchars($illnessLabel); ?></span>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- SWAP MODAL -->
<div class="modal-overlay" id="swap-modal" onclick="handleModalOverlay(event)">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-head-title">Swap <span>meal</span></div>
      <button class="modal-close" onclick="closeSwapModal()">✕</button>
    </div>
    <p class="modal-sub">Choose a replacement from our recipe library</p>

    <div class="swap-current" id="swap-current-display">
      <div class="swap-current-emoji" id="swap-cur-emoji">🍽️</div>
      <div>
        <div class="swap-current-label">Currently planned</div>
        <div class="swap-current-name" id="swap-cur-name">—</div>
      </div>
    </div>
    <div class="swap-arrow-row">↕ swap with</div>

    <div class="modal-search-wrap">
      <span class="modal-search-icon">🔍</span>
      <input type="text" id="swap-search" placeholder="Search recipes…" oninput="filterSwapList(this.value)"/>
    </div>
    <div class="modal-tabs" id="swap-tabs">
      <button class="modal-tab active" onclick="setSwapTab('all',this)">All</button>
      <button class="modal-tab" onclick="setSwapTab('breakfast',this)">Breakfast</button>
      <button class="modal-tab" onclick="setSwapTab('lunch',this)">Lunch</button>
      <button class="modal-tab" onclick="setSwapTab('dinner',this)">Dinner</button>
      <button class="modal-tab" onclick="setSwapTab('snack',this)">Snack</button>
      <button class="modal-tab" onclick="setSwapTab('high-protein',this)">High Protein</button>
      <button class="modal-tab" onclick="setSwapTab('low-carb',this)">Low Carb</button>
    </div>
    <div class="swap-list" id="swap-list"></div>
    <div class="modal-qty-wrap" id="modal-qty-wrap" style="display:none; text-align:center; margin-bottom: 12px; margin-top: 12px;">
      <label for="swap-qty-input" style="font-size:0.9rem; font-weight:500;">Quantity (g): </label>
      <input type="number" id="swap-qty-input" min="1" step="1" style="width: 80px; padding: 6px; border: 1px solid #ccc; border-radius: 6px; text-align: center; font-size: 0.9rem; font-family: inherit;">
      <div id="swap-qty-hint" style="font-size:0.75rem; color:#888; margin-top:4px;">Auto-calculated to fit goals. You can adjust it.</div>
    </div>
    <div class="modal-confirm">
      <button class="btn-confirm-swap" id="btn-confirm-swap" onclick="confirmSwap()" disabled>Select a recipe to swap</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast">✅ Done</div>

<script>
// ── RECIPE LIBRARY ──
const RECIPES = <?php echo json_encode($recipesForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const QUICK_ADD_IDS = <?php echo json_encode($quickAddIds, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const USER_PROFILE = <?php echo json_encode([
  'id' => $userId,
  'name' => $userName,
  'allergies' => $allergies,
  'illnesses' => $illnesses,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const GOALS = <?php echo json_encode($goals, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const HAS_GOAL = <?php echo $hasGoal ? 'true' : 'false'; ?>;
const AI_ENDPOINT = '../../../Controller/menu_module/generate_meal_plan_ai.php';
const RECIPE_MAP = RECIPES.reduce((acc, recipe) => {
  acc[recipe.id] = recipe;
  return acc;
}, {});
const USER_CONDITIONS = (USER_PROFILE.illnesses || []).map(item => String(item).toLowerCase());

const SLOTS = [
  { key:'breakfast', label:'Breakfast', icon:'🌅', time:'7:00 – 9:00 AM' },
  { key:'morning-snack', label:'Morning Snack', icon:'☕', time:'10:30 – 11:00 AM' },
  { key:'lunch', label:'Lunch', icon:'☀️', time:'12:30 – 1:30 PM' },
  { key:'afternoon-snack', label:'Afternoon Snack', icon:'🍎', time:'3:30 – 4:00 PM' },
  { key:'dinner', label:'Dinner', icon:'🌙', time:'7:00 – 8:30 PM' },
];
const SLOT_CAT = { breakfast:'breakfast', 'morning-snack':'snack', lunch:'lunch', 'afternoon-snack':'snack', dinner:'dinner' };

const DAYS = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
const SLOT_RATIOS = {
  breakfast: 0.25,
  'morning-snack': 0.1,
  lunch: 0.3,
  'afternoon-snack': 0.1,
  dinner: 0.25,
};

// plan[dayKey][slotKey] = recipeId | null
const plan = {};
let disabledSlots = {};
let weekOffset = 0;
let activeDay  = null;
let swapContext = null; // {dayKey, slotKey}
let swapSelectedId = null;
let swapTabFilter  = 'all';
let swapSearchQ    = '';
const loggedMeals  = {}; // State to track logged meals in session

function logMeal(dayKey, slotKey) {
  const item = plan[dayKey][slotKey];
  if (!item || !item.id) return;
  const r = RECIPE_MAP[item.id];
  const key = `${dayKey}-${slotKey}`;
  
  if (loggedMeals[key]) {
    fetch('../../../Controller/menu_module/log_meal_handler.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        date: dayKey,
        action: 'delete',
        meals: [{ id_rec: item.id }]
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        delete loggedMeals[key];
        showToast(`📝 Unlogged ${r.name}`);
        render();
      } else {
        showToast(`⚠️ Error: ${data.error || 'Could not unlog meal'}`);
      }
    })
    .catch(err => {
      showToast('⚠️ Request failed');
      console.error(err);
    });
    return;
  }

  fetch('../../../Controller/menu_module/log_meal_handler.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      date: dayKey,
      meals: [{ id_rec: item.id, meal_type: SLOTS.find(s => s.key === slotKey)?.label, qty: item.qty || 100 }]
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      loggedMeals[key] = {
        id: item.id,
        qty: item.qty || 100,
        time: new Date().toLocaleTimeString()
      };
      showToast(`✅ Logged ${r.name} to tracker`);
      render();
    } else {
      showToast(`⚠️ Error: ${data.error || 'Could not log meal'}`);
    }
  })
  .catch(err => {
    showToast('⚠️ Request failed');
    console.error(err);
  });
}

function logAllMeals(dayKey) {
  initDay(dayKey);
  const mealsToLog = [];
  SLOTS.forEach(slot => {
    const item = plan[dayKey][slot.key];
    if (item && item.id && !loggedMeals[`${dayKey}-${slot.key}`]) {
      mealsToLog.push({ id_rec: item.id, meal_type: slot.label, qty: item.qty || 100 });
    }
  });

  if (mealsToLog.length === 0) {
    showToast('💡 All meals already logged or no meals to log');
    return;
  }

  fetch('../../../Controller/menu_module/log_meal_handler.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      date: dayKey,
      meals: mealsToLog
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      mealsToLog.forEach(m => {
        const slotKey = SLOTS.find(s => s.label === m.meal_type)?.key;
        if (slotKey) {
          loggedMeals[`${dayKey}-${slotKey}`] = {
            id: m.id_rec,
            qty: m.qty || 100,
            time: new Date().toLocaleTimeString()
          };
        }
      });
      showToast(`✅ Logged ${data.logged_count || mealsToLog.length} meals to tracker`);
      render();
    } else {
      showToast(`⚠️ Error: ${data.error || 'Could not log meals'}`);
    }
  })
  .catch(err => {
    showToast('⚠️ Request failed');
    console.error(err);
  });
}

// Persist logged meals
let lastLoggedFetchOffset = null;
function loadLogsForWeek() {
  if (lastLoggedFetchOffset === weekOffset) return;
  lastLoggedFetchOffset = weekOffset;

  const dates = getWeekDates(weekOffset).map(d => getDayKey(d));
  fetch(`../../../Controller/menu_module/log_meal_handler.php?dates=${dates.join(',')}`)
    .then(res => res.json())
    .then(data => {
      if (data.success && data.logs) {
        data.logs.forEach(log => {
          // 1. Try exact slot label match
          let slot = SLOTS.find(s => s.label === log.meal_type);
          
          // 2. If no match, try to find a planned slot with this recipe ID for this day
          if (!slot && plan[log.date]) {
            const slotKey = Object.keys(plan[log.date]).find(sk => plan[log.date][sk] && plan[log.date][sk].id === log.id_rec);
            if (slotKey) slot = SLOTS.find(s => s.key === slotKey);
          }

          if (slot) {
            loggedMeals[`${log.date}-${slot.key}`] = {
              id: log.id_rec,
              qty: log.quantity || 100,
              time: log.meal_time
            };
          } else {
            // Handle "Extra" logs that don't fit a slot (e.g. random snacks from recipe page)
            if (!loggedMeals[log.date + '-extras']) loggedMeals[log.date + '-extras'] = [];
            loggedMeals[log.date + '-extras'].push(log);
          }
        });
        render();
      }
    })
    .catch(err => console.error('Failed to load logs:', err));
}
function getWeekDates(offset=0) {
  const now = new Date();
  const monday = new Date(now);
  monday.setDate(now.getDate() - ((now.getDay() + 6) % 7) + offset * 7);
  return Array.from({length:7}, (_,i) => {
    const d = new Date(monday);
    d.setDate(monday.getDate() + i);
    return d;
  });
}

function getDayKey(date) { return date.toISOString().split('T')[0]; }

function getRecentRecipes(currentDayKey, daysToLookBack = 3) {
  const currentDate = new Date(currentDayKey);
  const recentIds = new Set();
  for (let i = 1; i <= daysToLookBack; i++) {
    const d = new Date(currentDate);
    d.setDate(currentDate.getDate() - i);
    const dayKey = getDayKey(d);
    if (plan[dayKey]) {
      Object.values(plan[dayKey]).forEach(item => {
        if (item && item.id) recentIds.add(item.id);
      });
    }
  }
  return Array.from(recentIds);
}

function initDay(dayKey) {
  if (!plan[dayKey]) plan[dayKey] = {};
  if (!disabledSlots[dayKey]) disabledSlots[dayKey] = [];
  SLOTS.forEach(s => { if (!(s.key in plan[dayKey])) plan[dayKey][s.key] = null; });
}

// ── INIT ──
window.onload = () => {
  const today = getDayKey(new Date());
  initDay(today);
  activeDay = today;

  if (RECIPES.length > 0) {
    seedInitialPlan(today);
  }

  render();
  renderQuickAdd();
};

// ── WEEK NAV ──
function changeWeek(dir) { weekOffset += dir; render(); }
function goToToday() { weekOffset = 0; activeDay = getDayKey(new Date()); render(); }

// ── SELECT DAY ──
function selectDay(dayKey) { activeDay = dayKey; initDay(dayKey); render(); }

// ── CLEAR / AUTO-FILL ──
function clearDay() {
  if (!activeDay) return;
  SLOTS.forEach(s => { plan[activeDay][s.key] = null; });
  render(); showToast('🗑️ Day cleared');
}

function seedInitialPlan(dayKey) {
  fillDayHeuristic(dayKey);
}

function buildAutoPools() {
  const pool = { breakfast: [], lunch: [], dinner: [], snack: [] };
  RECIPES.forEach(recipe => {
    const cat = recipe.cat || 'lunch';
    if (!pool[cat]) {
      pool[cat] = [];
    }
    pool[cat].push(recipe.id);
  });
  return pool;
}

const AUTO_POOL = buildAutoPools();

function hasCondition(matchers) {
  return USER_CONDITIONS.some(condition => matchers.some(matcher => condition.includes(matcher)));
}

function getSlotTargets(dayKey) {
  const targets = {};
  const disabled = disabledSlots[dayKey] || [];
  
  let activeRatioSum = 0;
  Object.keys(SLOT_RATIOS).forEach(slotKey => {
    if (!disabled.includes(slotKey)) {
      activeRatioSum += SLOT_RATIOS[slotKey] || 0;
    }
  });

  Object.keys(SLOT_RATIOS).forEach(slotKey => {
    if (disabled.includes(slotKey)) {
      targets[slotKey] = { kcal: 0, prot: 0, carb: 0, fat: 0 };
    } else {
      const originalRatio = SLOT_RATIOS[slotKey] || 0;
      const effectiveRatio = activeRatioSum > 0 ? (originalRatio / activeRatioSum) : 0;
      targets[slotKey] = {
        kcal: GOALS.kcal * effectiveRatio,
        prot: GOALS.prot * effectiveRatio,
        carb: GOALS.carb * effectiveRatio,
        fat: GOALS.fat * effectiveRatio,
      };
    }
  });
  return targets;
}

function scoreRecipe(recipe, target) {
  const safe = value => (value && value > 0 ? value : 1);
  const M = (target.kcal > 0 && recipe.kcal > 0) ? target.kcal / recipe.kcal : 0;
  const qty = Math.max(0, Math.round(M * 100));
  const actualM = qty / 100;

  const scaled = {
    kcal: recipe.kcal * actualM,
    prot: recipe.prot * actualM,
    carb: recipe.carb * actualM,
    fat: recipe.fat * actualM,
  };

  let score = 0;
  if (scaled.kcal > target.kcal) {
    score += ((scaled.kcal - target.kcal) / safe(target.kcal)) * 10;
  } else {
    score += Math.abs(scaled.kcal - target.kcal) / safe(target.kcal);
  }
  score += Math.abs(scaled.prot - target.prot) / safe(target.prot) * 0.7;
  score += Math.abs(scaled.carb - target.carb) / safe(target.carb) * 0.6;
  score += Math.abs(scaled.fat - target.fat) / safe(target.fat) * 0.5;

  if (hasCondition(['diab'])) {
    score += Math.max(0, scaled.carb - target.carb) / safe(target.carb) * 1.2;
  }
  if (hasCondition(['cholest', 'lipid'])) {
    score += Math.max(0, scaled.fat - target.fat) / safe(target.fat) * 1.1;
  }
  if (hasCondition(['hyperten', 'tension'])) {
    score += Math.max(0, scaled.kcal - target.kcal) / safe(target.kcal) * 0.9;
  }
  if (hasCondition(['kidney', 'renal'])) {
    score += Math.max(0, scaled.prot - target.prot) / safe(target.prot) * 1.0;
  }

  return { score, qty, scaled };
}

function pickBestRecipe(slotKey, usedIds, targets, strictCap = false) {
  const cat = SLOT_CAT[slotKey] || 'lunch';
  const pool = (AUTO_POOL[cat] || []).filter(id => !usedIds.has(id));
  const candidates = pool.length ? pool : RECIPES.map(r => r.id).filter(id => !usedIds.has(id));
  if (!candidates.length) {
    return null;
  }

  const target = targets[slotKey] || targets.lunch;
  let bestId = null;
  let bestQty = 0;
  let bestScore = Number.POSITIVE_INFINITY;
  candidates.forEach(id => {
    const recipe = RECIPE_MAP[id];
    if (!recipe) return;

    const { score, qty, scaled } = scoreRecipe(recipe, target);

    if (strictCap && scaled.kcal > target.kcal * 1.1 + 30) {
      return; // strict limit to not exceed daily cap
    }

    if (score < bestScore) {
      bestScore = score;
      bestId = id;
      bestQty = qty;
    }
  });

  if (bestId === null && !strictCap) {
    bestId = candidates[0];
    const { qty } = scoreRecipe(RECIPE_MAP[bestId], target);
    bestQty = qty;
  }

  return bestId !== null ? { id: bestId, qty: bestQty } : null;
}

function fillDayHeuristic(dayKey) {
  initDay(dayKey);
  const used = new Set(getRecentRecipes(dayKey, 3));
  const targets = getSlotTargets(dayKey);
  const disabled = disabledSlots[dayKey] || [];
  SLOTS.forEach(slot => {
    if (disabled.includes(slot.key)) {
      plan[dayKey][slot.key] = null;
      return;
    }
    const pick = pickBestRecipe(slot.key, used, targets);
    if (pick) {
      plan[dayKey][slot.key] = { id: pick.id, qty: pick.qty };
      used.add(pick.id);
    } else {
      plan[dayKey][slot.key] = null;
    }
  });
}

function recalculateNextMeals(dayKey, changedSlotKey) {
  const changedIndex = SLOTS.findIndex(s => s.key === changedSlotKey);
  if (changedIndex === -1 || changedIndex === SLOTS.length - 1) return;

  let accum = { kcal: 0, prot: 0, carb: 0, fat: 0 };
  for (let i = 0; i <= changedIndex; i++) {
    const item = plan[dayKey][SLOTS[i].key];
    if (item && item.id) {
      const r = RECIPE_MAP[item.id];
      if (r) {
        const M = item.qty / 100;
        accum.kcal += r.kcal * M;
        accum.prot += r.prot * M;
        accum.carb += r.carb * M;
        accum.fat += r.fat * M;
      }
    }
  }

  const disabled = disabledSlots[dayKey] || [];
  let remainingRatioSum = 0;
  for (let i = changedIndex + 1; i < SLOTS.length; i++) {
    if (!disabled.includes(SLOTS[i].key)) {
      remainingRatioSum += SLOT_RATIOS[SLOTS[i].key] || 0;
    }
  }

  if (remainingRatioSum <= 0) return;

  const remainingGoals = {
    kcal: Math.max(0, GOALS.kcal - accum.kcal),
    prot: Math.max(0, GOALS.prot - accum.prot),
    carb: Math.max(0, GOALS.carb - accum.carb),
    fat:  Math.max(0, GOALS.fat  - accum.fat),
  };

  const used = new Set(getRecentRecipes(dayKey, 3));
  for (let i = 0; i <= changedIndex; i++) {
    const item = plan[dayKey][SLOTS[i].key];
    if (item && item.id) used.add(item.id);
  }

  for (let i = changedIndex + 1; i < SLOTS.length; i++) {
    const slotKey = SLOTS[i].key;
    if (disabled.includes(slotKey)) {
      plan[dayKey][slotKey] = null;
      continue;
    }
    const ratio = SLOT_RATIOS[slotKey] || 0;
    const proportion = ratio / remainingRatioSum;
    
    const target = {
      kcal: remainingGoals.kcal * proportion,
      prot: remainingGoals.prot * proportion,
      carb: remainingGoals.carb * proportion,
      fat:  remainingGoals.fat  * proportion,
    };

    const pick = pickBestRecipe(slotKey, used, { [slotKey]: target }, true);
    if (pick) {
      plan[dayKey][slotKey] = { id: pick.id, qty: pick.qty };
      used.add(pick.id);
    } else {
      plan[dayKey][slotKey] = null;
    }
  }
}

async function requestAiPlan() {
  if (!AI_ENDPOINT || RECIPES.length === 0) {
    return null;
  }

  const avoidIds = getRecentRecipes(activeDay, 3);

  try {
    const response = await fetch(AI_ENDPOINT, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        slots: SLOTS.filter(s => !(disabledSlots[activeDay] || []).includes(s.key)).map(slot => slot.key),
        avoid_ids: avoidIds
      }),
    });
    const data = await response.json();
    if (data && data.success && data.plan) {
      return data.plan;
    } else if (data && !data.success && data.error) {
      showToast('⚠️ AI Error: ' + data.error);
    }
  } catch (error) {
    showToast('⚠️ AI Request Failed');
    return null;
  }

  return null;
}

function applyPlan(dayKey, planMap) {
  const allowedIds = new Set(Object.keys(RECIPE_MAP).map(id => Number(id)));
  const targets = getSlotTargets(dayKey);
  const disabled = disabledSlots[dayKey] || [];
  SLOTS.forEach(slot => {
    if (disabled.includes(slot.key)) {
      plan[dayKey][slot.key] = null;
      return;
    }
    const id = Number(planMap?.[slot.key] || 0);
    if (allowedIds.has(id)) {
      const r = RECIPE_MAP[id];
      const target = targets[slot.key] || targets.lunch;
      const M = (target.kcal > 0 && r.kcal > 0) ? target.kcal / r.kcal : 0;
      const qty = Math.max(0, Math.round(M * 100));
      plan[dayKey][slot.key] = { id: id, qty: qty };
    } else {
      plan[dayKey][slot.key] = null;
    }
  });
}

async function autoFillDay() {
  if (!activeDay) return;
  initDay(activeDay);

  const btn = document.querySelector('.nav-actions .btn-nav:not(.outline)');
  let oldText = '✨ Auto-fill day';
  if (btn) {
    oldText = btn.innerHTML;
    btn.innerHTML = '⏳ Thinking...';
    btn.disabled = true;
  }

  const aiPlan = await requestAiPlan();
  
  if (btn) {
    btn.innerHTML = oldText;
    btn.disabled = false;
  }

  if (aiPlan) {
    applyPlan(activeDay, aiPlan);
    render();
    showToast('✨ AI personalized plan ready!');
    return;
  }

  fillDayHeuristic(activeDay);
  render();
  showToast('✨ Day auto-filled (Standard)');
}

// ── REMOVE MEAL ──
function removeMeal(dayKey, slotKey) { 
  plan[dayKey][slotKey] = null; 
  recalculateNextMeals(dayKey, slotKey);
  render(); 
  showToast('🗑️ Meal removed'); 
}

function toggleSlot(dayKey, slotKey) {
  if (!disabledSlots[dayKey]) disabledSlots[dayKey] = [];
  const idx = disabledSlots[dayKey].indexOf(slotKey);
  if (idx !== -1) {
    disabledSlots[dayKey].splice(idx, 1);
  } else {
    disabledSlots[dayKey].push(slotKey);
    plan[dayKey][slotKey] = null;
  }
  recalculateNextMeals(dayKey, slotKey);
  render();
}

// ── ADD MEAL (empty slot click) ──
function addMeal(dayKey, slotKey) { openSwapModal(dayKey, slotKey, true); }

// ── SWAP MODAL ──
let isAddMode = false;
function openSwapModal(dayKey, slotKey, addMode=false) {
  swapContext = { dayKey, slotKey };
  swapSelectedId = null;
  swapTabFilter = 'all';
  swapSearchQ = '';
  isAddMode = addMode;
  document.getElementById('swap-search').value = '';
  document.querySelectorAll('.modal-tab').forEach(t => t.classList.remove('active'));
  document.querySelector('.modal-tab').classList.add('active');

  const curId = plan[dayKey]?.[slotKey];
  const cur = curId ? RECIPE_MAP[curId] : null;
  document.getElementById('swap-cur-emoji').textContent = cur ? cur.emoji : '➕';
  document.getElementById('swap-cur-name').textContent = cur ? cur.name : 'Empty slot';

  if (addMode) {
    document.querySelector('.modal-head-title').innerHTML = 'Add <span>meal</span>';
    document.getElementById('swap-current-display').style.display = 'none';
    document.querySelector('.swap-arrow-row').style.display = 'none';
  } else {
    document.querySelector('.modal-head-title').innerHTML = 'Swap <span>meal</span>';
    document.getElementById('swap-current-display').style.display = '';
    document.querySelector('.swap-arrow-row').style.display = '';
  }

  document.getElementById('btn-confirm-swap').disabled = true;
  document.getElementById('btn-confirm-swap').textContent = 'Select a recipe to swap';
  document.getElementById('modal-qty-wrap').style.display = 'none';
  renderSwapList();
  document.getElementById('swap-modal').classList.add('open');
}
function closeSwapModal() { document.getElementById('swap-modal').classList.remove('open'); }
function handleModalOverlay(e) { if (e.target === document.getElementById('swap-modal')) closeSwapModal(); }

function setSwapTab(tab, el) {
  swapTabFilter = tab;
  document.querySelectorAll('.modal-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  renderSwapList();
}
function filterSwapList(q) { swapSearchQ = q.toLowerCase(); renderSwapList(); }

function renderSwapList() {
  let list = RECIPES.filter(r => {
    const currentItem = swapContext ? plan[swapContext.dayKey]?.[swapContext.slotKey] : null;
    return r.id !== (currentItem ? currentItem.id : null);
  });
  if (swapTabFilter !== 'all') list = list.filter(r => r.cat === swapTabFilter || r.tags.includes(swapTabFilter));
  if (swapSearchQ) list = list.filter(r => r.name.toLowerCase().includes(swapSearchQ));

  if (!list.length) {
    document.getElementById('swap-list').innerHTML = '<div class="empty-state">No recipes match your filters.<small>Try another tag or clear the search.</small></div>';
    return;
  }

  document.getElementById('swap-list').innerHTML = list.map(r => `
    <div class="swap-option ${swapSelectedId===r.id?'selected':''}" onclick="selectSwapRecipe(${r.id})">
      <div class="swap-option-emoji" data-bg="${r.bg}" style="overflow:hidden;">
        ${r.image ? `<img src="${r.image}" alt="${r.name}" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">` : r.emoji}
      </div>
      <div class="swap-option-info">
        <div class="swap-option-name">${r.name}</div>
        ${r.origin ? `<div style="font-size:0.7rem; color:#888; margin-bottom:2px;">📍 ${r.origin}</div>` : ''}
        <div class="swap-option-macros">
          <span class="meal-macro mm-kcal">🔥 ${r.kcal}</span>
          <span class="meal-macro mm-prot">💪 ${r.prot}g</span>
          <span class="meal-macro mm-carb">🌾 ${r.carb}g</span>
          <span class="meal-macro mm-fat">🥑 ${r.fat}g</span>
        </div>
      </div>
      <div class="swap-check">${swapSelectedId===r.id?'✓':''}</div>
    </div>
  `).join('');
  applyDynamicStyles(document.getElementById('swap-list'));
}

function selectSwapRecipe(id) {
  swapSelectedId = id;
  renderSwapList();
  const r = RECIPE_MAP[id];
  
  if (swapContext) {
    const target = getSlotTargets()[swapContext.slotKey];
    const M = (target && target.kcal > 0 && r.kcal > 0) ? target.kcal / r.kcal : 0;
    const optimalQty = Math.max(0, Math.round(M * 100));
    document.getElementById('swap-qty-input').value = optimalQty;
  }
  
  document.getElementById('modal-qty-wrap').style.display = 'block';

  const btn = document.getElementById('btn-confirm-swap');
  btn.disabled = false;
  btn.textContent = `Use "${r.name}"`;
}

function confirmSwap() {
  if (!swapSelectedId || !swapContext) return;
  
  const r = RECIPE_MAP[swapSelectedId];
  if (!r) return;
  
  let qty = parseInt(document.getElementById('swap-qty-input').value, 10);
  if (isNaN(qty) || qty <= 0) {
    const target = getSlotTargets()[swapContext.slotKey];
    const M = (target && target.kcal > 0 && r.kcal > 0) ? target.kcal / r.kcal : 0;
    qty = Math.max(0, Math.round(M * 100));
  }

  plan[swapContext.dayKey][swapContext.slotKey] = { id: swapSelectedId, qty: qty };
  closeSwapModal();
  
  recalculateNextMeals(swapContext.dayKey, swapContext.slotKey);
  
  render();
  showToast(`🔄 Swapped to ${r.name}`);
}

// ── DRAG & DROP ──
let dragData = null;

function onDragStart(e, dayKey, slotKey) {
  dragData = { dayKey, slotKey };
  e.dataTransfer.effectAllowed = 'move';
  setTimeout(() => { e.target.closest('.meal-card')?.classList.add('dragging'); }, 0);
}
function onDragEnd(e) {
  document.querySelectorAll('.meal-card, .empty-slot').forEach(el => el.classList.remove('dragging','drag-over'));
  dragData = null;
}
function onDragOver(e, dayKey, slotKey) {
  e.preventDefault(); e.dataTransfer.dropEffect = 'move';
  document.querySelectorAll('.meal-card, .empty-slot').forEach(el => el.classList.remove('drag-over'));
  document.querySelector(`[data-slot="${dayKey}-${slotKey}"]`)?.classList.add('drag-over');
}
function onDrop(e, dayKey, slotKey) {
  e.preventDefault();
  if (!dragData) return;
  if (dragData.dayKey === dayKey && dragData.slotKey === slotKey) return;
  const fromItem = plan[dragData.dayKey][dragData.slotKey];
  const toItem   = plan[dayKey][slotKey];
  plan[dayKey][slotKey] = fromItem;
  plan[dragData.dayKey][dragData.slotKey] = toItem;
  dragData = null;
  document.querySelectorAll('.meal-card, .empty-slot').forEach(el => el.classList.remove('drag-over'));
  render();
  showToast('🔄 Meals rearranged');
}

// ── QUICK ADD ──
function renderQuickAdd() {
  const container = document.getElementById('quick-add-list');
  const ids = QUICK_ADD_IDS.length ? QUICK_ADD_IDS : RECIPES.slice(0, 5).map(r => r.id);
  if (!ids.length) {
    container.innerHTML = '<div class="empty-state">No recipes to add yet.<small>Add recipes in the library first.</small></div>';
    return;
  }

  container.innerHTML = ids.map(id => {
    const r = RECIPE_MAP[id];
    if (!r) {
      return '';
    }
    return `<div class="qa-item" onclick="quickAdd(${r.id})">
      <div class="qa-emoji">${r.emoji}</div>
      <div class="qa-info"><div class="qa-name">${r.name}</div><div class="qa-kcal">${r.kcal} kcal</div></div>
      <button class="qa-add" title="Add to first empty slot">+</button>
    </div>`;
  }).join('');
}
function quickAdd(id) {
  initDay(activeDay);
  const slot = SLOTS.find(s => !plan[activeDay][s.key]);
  if (!slot) { showToast('⚠️ No empty slots today'); return; }
  
  const r = RECIPE_MAP[id];
  if (!r) return;
  
  const target = getSlotTargets()[slot.key];
  const M = (target && target.kcal > 0 && r.kcal > 0) ? target.kcal / r.kcal : 0;
  const qty = Math.max(0, Math.round(M * 100));

  plan[activeDay][slot.key] = { id: id, qty: qty };
  render();
  showToast(`➕ Added ${r.name}`);
}

function applyDynamicStyles(root = document) {
  if (!root) {
    return;
  }
  root.querySelectorAll('[data-bg]').forEach(el => {
    const value = el.getAttribute('data-bg');
    if (value) {
      el.style.backgroundColor = value;
    }
  });

  root.querySelectorAll('[data-width]').forEach(el => {
    const raw = parseFloat(el.getAttribute('data-width'));
    const width = Number.isFinite(raw) ? Math.min(Math.max(raw, 0), 100) : 0;
    el.style.width = `${width}%`;
  });
}

// ── RENDER ──
function render() {
  loadLogsForWeek();
  const dates = getWeekDates(weekOffset);
  const todayKey = getDayKey(new Date());
  if (!activeDay || !dates.find(d => getDayKey(d) === activeDay)) {
    activeDay = getDayKey(dates.find(d => getDayKey(d) === todayKey) || dates[0]);
  }
  
  const isPast = activeDay < todayKey;
  const isToday = activeDay === todayKey;

  // week label
  const fmt = d => d.toLocaleDateString('en-GB',{day:'numeric',month:'short'});
  document.getElementById('week-label').textContent = `${fmt(dates[0])} – ${fmt(dates[6])}`;

  // day tabs
  const tabsEl = document.getElementById('day-tabs');
  tabsEl.innerHTML = dates.map(d => {
    const key = getDayKey(d);
    initDay(key);
    const hasMeals = SLOTS.some(s => plan[key][s.key] && plan[key][s.key].id);
    return `<div class="day-tab ${key===activeDay?'active':''} ${key===todayKey?'today':''} ${hasMeals?'has-meals':''}" onclick="selectDay('${key}')">
      <div class="day-tab-name">${DAYS[d.getDay()]}</div>
      <div class="day-tab-num">${d.getDate()}</div>
      <div class="day-tab-dot"></div>
    </div>`;
  }).join('');

  // day panel
  initDay(activeDay);
  const dayTotals = getDayTotals(activeDay);
  const panel = document.getElementById('day-panel');
  if (!RECIPES.length) {
    panel.innerHTML = '<div class="empty-state">No recipes found.<small>Add recipes to start planning.</small></div>';
    document.getElementById('hs-kcal').textContent = '0';
    document.getElementById('hs-prot').textContent = '0g';
    document.getElementById('hs-meals').textContent = '0';
    document.getElementById('hs-complete').textContent = '0%';
    ['kcal','prot','carb'].forEach(k => {
      document.getElementById(`goal-${k}-bar`).style.width = '0%';
      document.getElementById(`goal-${k}-txt`).textContent = `0${k === 'kcal' ? ' kcal' : 'g'} / ${GOALS[k]}${k === 'kcal' ? ' kcal' : 'g'}`;
    });
    document.getElementById('week-mini').innerHTML = '<div class="empty-state">No recipes yet.<small>Add recipes to see weekly stats.</small></div>';
    return;
  }

  const disabled = disabledSlots[activeDay] || [];
  panel.innerHTML = SLOTS.map(slot => {
    const isDisabled = disabled.includes(slot.key);
    const planItem = plan[activeDay][slot.key];
    const logged = loggedMeals[activeDay + '-' + slot.key];

    // Reality check: Show logged meal if exists, otherwise show planned
    const item = logged || planItem;
    const rid = item ? item.id : null;
    const qty = item ? item.qty : 0;
    const r   = rid ? RECIPE_MAP[rid] : null;
    const isLogged = !!logged;
    const isDeviation = logged && planItem && logged.id !== planItem.id;
    
    let sKcal = 0, sProt = 0, sCarb = 0, sFat = 0;
    if (r) {
      const M = qty / 100;
      sKcal = Math.round(r.kcal * M);
      sProt = Math.round(r.prot * M);
      sCarb = Math.round(r.carb * M);
      sFat  = Math.round(r.fat * M);
    }

    const slotMacros = r ? `<span class="meal-macro mm-kcal">🔥 ${sKcal} kcal</span><span class="meal-macro mm-prot">💪 ${sProt}g P</span><span class="meal-macro mm-carb">🌾 ${sCarb}g C</span><span class="meal-macro mm-fat">🥑 ${sFat}g F</span>` : '';
    return `<div class="meal-slot ${isDisabled ? 'disabled-slot' : ''}" style="${isDisabled ? 'opacity:0.5; filter:grayscale(1);' : ''}">
      <div class="slot-header">
        <div class="slot-title" style="display:flex; align-items:center; gap:8px;">
          <span class="slot-icon">${slot.icon}</span>${slot.label}
          <button onclick="toggleSlot('${activeDay}', '${slot.key}')" style="background:none; border:none; cursor:pointer; font-size:1.1rem; padding:0; margin-left:8px;" title="${isDisabled ? 'Enable' : 'Disable'} this slot">
            ${isDisabled ? '👁️‍🗨️' : '👁️'}
          </button>
        </div>
        <div class="slot-meta">
          ${r && !isDisabled ? `<div class="slot-macro-sum">⚖️ ${qty}g · ${sKcal} kcal</div>` : ''}
          <div class="slot-time">${slot.time}</div>
        </div>
      </div>
      ${isDisabled ? `
      <div class="empty-slot" style="background:transparent; border-style:dashed; cursor:default;">
        <span class="empty-slot-text" style="color:#888;">Slot disabled</span>
      </div>
      ` : (r ? `
      <div class="meal-card" draggable="${!isPast}"
        data-slot="${activeDay}-${slot.key}"
        ${!isPast ? `
        ondragstart="onDragStart(event,'${activeDay}','${slot.key}')"
        ondragend="onDragEnd(event)"
        ondragover="onDragOver(event,'${activeDay}','${slot.key}')"
        ondrop="onDrop(event,'${activeDay}','${slot.key}')"
        ` : ''}>
        <div class="meal-card-inner">
          <div class="drag-handle" title="${isPast ? 'Cannot reorder past meals' : 'Drag to reorder'}" style="${isPast ? 'cursor:default; opacity:0.3;' : ''}">⠿</div>
          <div class="meal-thumb" data-bg="${r.bg}">
            ${r.image ? `<img src="${r.image}" alt="${r.name}" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">` : r.emoji}
            ${isDeviation ? `<div style="position:absolute; top:-5px; right:-5px; background:var(--orange); color:#fff; font-size:0.6rem; padding:2px 6px; border-radius:100px; border:2px solid #fff; font-weight:700; box-shadow:0 2px 4px rgba(0,0,0,0.1);" title="Different from original plan">ALT</div>` : ''}
            ${isLogged ? `<div style="position:absolute; bottom:-5px; right:-5px; background:var(--blue); color:#fff; font-size:0.65rem; padding:2px 6px; border-radius:100px; border:2px solid #fff; font-weight:700; box-shadow:0 2px 4px rgba(0,0,0,0.1);" title="Logged to tracker">✓</div>` : ''}
          </div>
          <div class="meal-info">
            <div class="meal-name">${r.name}</div>
            ${r.origin ? `<div class="meal-origin" style="font-size:0.75rem; color:#888; margin-bottom:4px;">📍 ${r.origin}</div>` : ''}
            <div class="meal-macros-row">${slotMacros}</div>
            <div class="meal-tags">${r.tags.map(t=>`<span class="meal-tag">${t}</span>`).join('')}</div>
          </div>
          <div class="meal-actions">
            <button class="meal-act-btn swap-btn" onclick="openSwapModal('${activeDay}','${slot.key}')" ${isPast ? 'disabled' : ''}>🔄 Swap</button>
            <button class="meal-act-btn" onclick="window.location.href='foovia-recipe.php?id_rec=${r.id}'">👁 View</button>
            <button class="meal-act-btn log-btn ${isLogged ? 'logged' : ''}" onclick="logMeal('${activeDay}','${slot.key}')" ${isPast ? 'disabled' : ''}>
              ${isLogged ? '✅ Logged' : '📝 Log'}
            </button>
            <button class="meal-act-btn remove-btn" onclick="removeMeal('${activeDay}','${slot.key}')" ${isPast ? 'disabled' : ''}>✕ Remove</button>
          </div>
        </div>
      </div>
      ` : `
      <div class="empty-slot"
        data-slot="${activeDay}-${slot.key}"
        ${!isPast ? `
        onclick="addMeal('${activeDay}','${slot.key}')"
        ondragover="onDragOver(event,'${activeDay}','${slot.key}')"
        ondrop="onDrop(event,'${activeDay}','${slot.key}')"
        ` : 'style="cursor:default; opacity:0.6;"'}>
        <span class="empty-slot-icon">➕</span>
        <span class="empty-slot-text">${isPast ? 'No meal planned' : `Add ${slot.label.toLowerCase()}`}</span>
      </div>
      `)}
    </div>`;
  }).join('') + (function() {
    const extras = loggedMeals[activeDay + '-extras'] || [];
    if (!extras.length) return '';
    return `
    <div class="extras-section" style="margin-top:24px;">
      <h3 style="font-family:'Boldonse',sans-serif; font-size:1.1rem; margin-bottom:12px; color:var(--dark); display:flex; align-items:center; gap:8px;">
        <span>🍱</span> Extra logged meals
      </h3>
      <div style="display:flex; flex-direction:column; gap:12px;">
        ${extras.map(log => {
          const r = RECIPE_MAP[log.id_rec];
          if (!r) return '';
          return `
          <div class="meal-card" style="border-style:dashed; opacity:0.9;">
            <div class="meal-card-inner">
              <div class="meal-thumb" data-bg="${r.bg}">
                ${r.image ? `<img src="${r.image}" alt="${r.name}" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">` : r.emoji}
                <div style="position:absolute; bottom:-5px; right:-5px; background:var(--blue); color:#fff; font-size:0.65rem; padding:2px 6px; border-radius:100px; border:2px solid #fff; font-weight:700; box-shadow:0 2px 4px rgba(0,0,0,0.1);">✓</div>
              </div>
              <div class="meal-info">
                <div class="meal-name">${r.name}</div>
                <div style="font-size:0.75rem; color:#888;">Logged at ${log.meal_time} · ${log.quantity || 100}g</div>
              </div>
            </div>
          </div>`;
        }).join('')}
      </div>
    </div>`;
  })() + `
  <div class="day-summary-card">
    <div class="dscard-title">Day totals</div>
    <div class="ds-macros">
      <div class="ds-macro"><div class="ds-val ds-val-orange">${dayTotals.kcal}</div><div class="ds-lbl">kcal</div></div>
      <div class="ds-macro"><div class="ds-val ds-val-green">${dayTotals.prot}g</div><div class="ds-lbl">protein</div></div>
      <div class="ds-macro"><div class="ds-val ds-val-yellow">${dayTotals.carb}g</div><div class="ds-lbl">carbs</div></div>
      <div class="ds-macro"><div class="ds-val ds-val-peach">${dayTotals.fat}g</div><div class="ds-lbl">fat</div></div>
    </div>
    <div class="ds-bars">
      ${[['Calories',dayTotals.kcal,GOALS.kcal,'bar-orange'],['Protein',dayTotals.prot,GOALS.prot,'bar-green'],['Carbs',dayTotals.carb,GOALS.carb,'bar-yellow'],['Fat',dayTotals.fat,GOALS.fat,'bar-peach']].map(([lbl,val,goal,colorClass]) => {
        const safeGoal = goal > 0 ? goal : 1;
        return `
      <div class="ds-bar-row">
        <div class="ds-bar-hd"><span>${lbl}</span><span>${val} / ${goal}</span></div>
        <div class="ds-bar-track"><div class="ds-bar-fill ${colorClass}" data-width="${Math.min((val/safeGoal)*100,100)}"></div></div>
      </div>`;
      }).join('')}
    </div>
    <div style="margin-top:22px; padding-top:18px; border-top:1px solid rgba(255,255,255,0.1);">
      <button class="btn-nav" style="width:100%; justify-content:center; gap:8px; display:flex; align-items:center;" onclick="logAllMeals('${activeDay}')" ${isPast ? 'disabled style="opacity:0.6; cursor:not-allowed;"' : ''}>
        📝 ${isPast ? 'Daily log completed' : 'Log all meals for today'}
      </button>
    </div>
  </div>`;
  applyDynamicStyles(panel);

  // header stats
  const meals = SLOTS.filter(s => plan[activeDay][s.key] && plan[activeDay][s.key].id).length;
  document.getElementById('hs-kcal').textContent   = dayTotals.kcal;
  document.getElementById('hs-prot').textContent   = dayTotals.prot + 'g';
  document.getElementById('hs-meals').textContent  = meals;
  document.getElementById('hs-complete').textContent = Math.round((meals/SLOTS.length)*100) + '%';

  // goal bars sidebar
  ['kcal','prot','carb'].forEach(k => {
    const val = dayTotals[k];
    const goal = GOALS[k];
    const safeGoal = goal > 0 ? goal : 1;
    const pct = Math.min((val/safeGoal)*100,100);
    const unit = k==='kcal' ? ' kcal' : 'g';
    document.getElementById(`goal-${k}-bar`).style.width = pct + '%';
    document.getElementById(`goal-${k}-txt`).textContent = `${val}${unit} / ${goal}${unit}`;
  });

  // week mini sidebar
  const wm = document.getElementById('week-mini');
  wm.innerHTML = dates.map(d => {
    const key = getDayKey(d);
    initDay(key);
    const t = getDayTotals(key);
    const kcalGoal = GOALS.kcal > 0 ? GOALS.kcal : 1;
    const protGoal = GOALS.prot > 0 ? GOALS.prot : 1;
    const pct = Math.min((t.kcal/kcalGoal)*100,100);
    return `<div class="wm-row ${key===activeDay?'active':''}" onclick="selectDay('${key}')">
      <div class="wm-day">${DAYS[d.getDay()]}</div>
      <div class="wm-bars">
        <div class="wm-bar"><div class="wm-bar-fill wm-bar-kcal" data-width="${pct}"></div></div>
        <div class="wm-bar"><div class="wm-bar-fill wm-bar-prot" data-width="${Math.min((t.prot/protGoal)*100,100)}"></div></div>
      </div>
      <div class="wm-kcal">${t.kcal || '—'}</div>
      <div class="wm-dot"></div>
    </div>`;
  }).join('');
  applyDynamicStyles(wm);
}

function getDayTotals(dayKey) {
  initDay(dayKey);
  const totals = SLOTS.reduce((acc, s) => {
    const planItem = plan[dayKey][s.key];
    const logged = loggedMeals[`${dayKey}-${s.key}`];
    
    // Prioritize logged meal for totals
    const item = logged || planItem;
    if (item && item.id) {
      const r = RECIPE_MAP[item.id];
      if (r) {
        const M = (item.qty || 100) / 100;
        acc.kcal += Math.round(r.kcal * M);
        acc.prot += Math.round(r.prot * M);
        acc.carb += Math.round(r.carb * M);
        acc.fat += Math.round(r.fat * M);
      }
    }
    return acc;
  }, { kcal:0, prot:0, carb:0, fat:0 });

  // Add extras (logged but not in a specific slot)
  Object.values(loggedMeals).forEach(log => {
    // Check if this log belongs to this day and is an "Extra" (no slotKey mapping in loggedMeals for extras usually)
    // Actually, extras are stored differently in my mapping logic sometimes.
    // Let's refine the check:
    if (log.date === dayKey && log.isExtra) {
       const r = RECIPE_MAP[log.id];
       if (r) {
         const M = (log.qty || 100) / 100;
         totals.kcal += Math.round(r.kcal * M);
         totals.prot += Math.round(r.prot * M);
         totals.carb += Math.round(r.carb * M);
         totals.fat += Math.round(r.fat * M);
       }
    }
  });

  return totals;
}

// ── TOAST ──
let toastTimer;
function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 2600);
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  (function() {
    const root = document.documentElement;
    const nav = document.querySelector('.foovia-nav');
    const toggle = document.querySelector('.theme-toggle');

    if (!nav || !toggle) {
      return;
    }

    const themeKey = 'theme';
    const legacyThemeKey = 'foovia-theme';
    const stored = localStorage.getItem(themeKey) || localStorage.getItem(legacyThemeKey);
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initialTheme = stored || (prefersDark ? 'dark' : 'light');

    const setTheme = (theme) => {
      const isDark = theme === 'dark';
      root.setAttribute('data-theme', theme);
      root.style.colorScheme = theme;
      nav.setAttribute('data-theme', theme);
      toggle.setAttribute('aria-pressed', String(isDark));
      toggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
    };

    setTheme(initialTheme);
    localStorage.setItem(themeKey, initialTheme);
    localStorage.setItem(legacyThemeKey, initialTheme);

    toggle.addEventListener('click', () => {
      const currentTheme = root.getAttribute('data-theme') || 'light';
      const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
      localStorage.setItem(themeKey, nextTheme);
      localStorage.setItem(legacyThemeKey, nextTheme);
      setTheme(nextTheme);
    });
  })();
</script>
</body>
</html>
