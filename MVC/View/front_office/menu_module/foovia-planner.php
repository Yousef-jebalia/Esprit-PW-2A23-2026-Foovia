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

include_once(__DIR__ . '/../../../Controller/Controller_user.php');
$userController = new Controller_user();
$userData = $userController->get_user($userId);
$user_subscription = $userData['subscription_user'] ?? 'free';

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
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/Plan de travail 1 no bg (3) (1).png">
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA — Meal Planner</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Boldonse&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="foovia-planner.css?v=<?php echo time(); ?>">
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
</head>
<body>

<!-- NAV -->
<nav class="foovia-nav" data-theme="light" aria-label="Main navigation">
  <div style="display:flex;align-items:center;gap:2px;margin-left:0;flex-shrink:0;">
    <button class="nav-sidebar-toggle" type="button" aria-label="Open page list" aria-controls="navSidebar" aria-expanded="false" style="width:54px;height:54px;border-radius:12px;gap:4px;padding:0;display:inline-flex;flex-direction:column;align-items:center;justify-content:center;background:rgba(255,255,255,.72);border-color:rgba(17,16,8,.18);margin-right:8px;">
      <span style="width:26px;height:4px;border-radius:999px;display:block;background:#111008;"></span>
      <span style="width:26px;height:4px;border-radius:999px;display:block;background:#111008;"></span>
      <span style="width:26px;height:4px;border-radius:999px;display:block;background:#111008;"></span>
    </button>
    <a href="../foovia.php" class="nav-logo">
      <img src="../assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo">
      FOOVIA
    </a>
  </div>
  <ul class="nav-links">
    <li><a href="../foovia.php">Home</a></li>
    <li><a href="recipe_page.php">Recipes</a></li>
    <li><a href="#" class="active">Meal Plan</a></li>
    <li><a href="../TRACK_MODULE/tracking.php">Tracker</a></li>
  </ul>
  <div class="nav-actions">
    <button class="btn-nav outline" onclick="clearDay()">Clear day</button>
    <button class="btn-nav" onclick="autoFillDay()">✨ Auto-fill day</button>
    <?php if ((isset($_SESSION['role_user']) && strtolower(trim($_SESSION['role_user'])) === 'admin') || (isset($userData) && strtolower(trim($userData['role_user'] ?? '')) === 'admin')): ?>
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
      <a href="../foovia-signup.php" class="nav-btn nav-signup">Sign Up</a>
    <?php endif; ?>
    <?php if (isset($_SESSION['user_id']) && ($user_subscription === 'premium' || $user_subscription === 'elite')): ?>
      <div class="premium-badge-nav" title="Premium Member" onclick="window.location.href='../foovia-premium.php'">
        <img src="../assets/crown-svgrepo-com%20(1).svg" class="premium-icon-nav" alt="Premium">
      </div>
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
          <div class="scard-title"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> Week at a glance</div>
        <div class="week-mini" id="week-mini"></div>
      </div>

      <!-- GOALS CARD -->
      <div class="scard">
        <div class="scard-title"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/><path d="M21 3l-6.6 6.6"/></svg> Daily goals</div>
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
        <div class="scard-title"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 3H2l8 9v7l4 2v-9l8-9z"/></svg> Dietary filters</div>
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
const LOG_MEAL_ENDPOINT = <?php echo json_encode(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))) . '/../../../Controller/menu_module/log_meal_handler.php'); ?>;
const RECIPE_MAP = RECIPES.reduce((acc, recipe) => {
  acc[recipe.id] = recipe;
  return acc;
}, {});
const USER_CONDITIONS = (USER_PROFILE.illnesses || []).map(item => String(item).toLowerCase());

const SLOTS = [
  { key:'breakfast', label:'Breakfast', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M4 12h16"/></svg>', time:'7:00 – 9:00 AM' },
  { key:'morning-snack', label:'Morning Snack', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h14v7a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7z"/><path d="M16 8a3 3 0 0 1 0 6"/></svg>', time:'10:30 – 11:00 AM' },
  { key:'lunch', label:'Lunch', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>', time:'12:30 – 1:30 PM' },
  { key:'afternoon-snack', label:'Afternoon Snack', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 11c1.5-2 2-6 0-8-2 0-3 2-4 2s-2-2-4-2c-2 2-1.5 6 0 8 1.5 2 3 3 4 3s2.5-1 4-3z"/><path d="M15 4s1 1 0 2"/></svg>', time:'3:30 – 4:00 PM' },
  { key:'dinner', label:'Dinner', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>', time:'7:00 – 8:30 PM' },
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

  const handleJsonResponse = (res) => {
    if (!res.ok) {
      return res.text().then(text => {
        throw new Error(text || `HTTP ${res.status}`);
      });
    }
    return res.json();
  };

  if (loggedMeals[key]) {
    fetch(LOG_MEAL_ENDPOINT, {
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
      showToast(`⚠️ Request failed: ${err.message || 'network error'}`);
      console.error(err);
    });
    return;
  }

  fetch(LOG_MEAL_ENDPOINT, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      date: dayKey,
      meals: [{ id_rec: item.id, meal_type: SLOTS.find(s => s.key === slotKey)?.label, qty: item.qty || 100 }]
    })
  })
  .then(handleJsonResponse)
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
    showToast(`⚠️ Request failed: ${err.message || 'network error'}`);
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

  fetch(LOG_MEAL_ENDPOINT, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      date: dayKey,
      meals: mealsToLog
    })
  })
  .then(res => {
    if (!res.ok) {
      return res.text().then(text => {
        throw new Error(text || `HTTP ${res.status}`);
      });
    }
    return res.json();
  })
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
    showToast(`⚠️ Request failed: ${err.message || 'network error'}`);
    console.error(err);
  });
}

// Persist logged meals
let lastLoggedFetchOffset = null;
function loadLogsForWeek() {
  if (lastLoggedFetchOffset === weekOffset) return;
  lastLoggedFetchOffset = weekOffset;

  const dates = getWeekDates(weekOffset).map(d => getDayKey(d));
  fetch(`${LOG_MEAL_ENDPOINT}?dates=${dates.join(',')}`)
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
          <span class="meal-macro mm-kcal"><svg width="14" height="14" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;margin-right:6px;"><path d="M9.75838 1.09929C9.85156 1.13153 9.9852 1.17902 10.1535 1.24207C10.49 1.36812 10.9661 1.55678 11.5355 1.81078C12.6715 2.31752 14.193 3.09073 15.7215 4.15505C18.745 6.26052 22 9.65692 22 14.5393C22 16.6738 21.4305 18.7869 20.1046 20.3856C18.7552 22.0126 16.7095 23 14 23C13.9352 23 13.6752 22.9978 13.4169 22.8125C13.0566 22.5541 12.9699 22.1541 13.0085 21.8667C13.0376 21.6502 13.1305 21.5025 13.1576 21.4602C13.1966 21.3993 13.234 21.3556 13.2534 21.3338C13.293 21.2893 13.3281 21.2581 13.3407 21.247C13.3575 21.2322 13.3716 21.2207 13.3801 21.214C13.4065 21.1929 13.4323 21.1745 13.4402 21.1689L13.4413 21.1681L13.5185 21.1136C13.5762 21.0727 13.6587 21.0131 13.7588 20.9348C13.9606 20.7768 14.2297 20.546 14.4969 20.2526C15.0448 19.6509 15.5 18.8819 15.5 18C15.5 16.3681 14.571 14.8515 13.5067 13.669C12.9869 13.0914 12.4644 12.6267 12.0715 12.3065C12.0471 12.2866 12.0233 12.2674 12 12.2487C11.9767 12.2674 11.9529 12.2866 11.9285 12.3065C11.5356 12.6267 11.0131 13.0914 10.4933 13.669C9.42904 14.8515 8.5 16.3681 8.5 18C8.5 18.8887 8.95405 19.6581 9.49825 20.2564C9.76406 20.5486 10.0319 20.7779 10.2327 20.934C10.3323 21.0114 10.4142 21.0699 10.47 21.1087C10.4933 21.125 10.5115 21.1374 10.5281 21.1487L10.5401 21.1569C10.5471 21.1616 10.5635 21.1728 10.5787 21.1837C10.5832 21.187 10.6139 21.2089 10.6476 21.2376C10.6583 21.2467 10.6772 21.2632 10.6995 21.285C10.7154 21.3005 10.7647 21.3492 10.8157 21.4212C10.8424 21.4607 10.901 21.5658 10.9302 21.6326C10.9668 21.7437 10.9991 22.045 10.9733 22.2301C10.89 22.4562 10.6027 22.798 10.4241 22.9056C10.2979 22.9546 10.0834 22.9965 10 23C7.29045 23 5.24478 22.0126 3.89543 20.3856C2.56953 18.7869 2 16.6738 2 14.5393C2 11.9892 2.88357 10.3815 4.05286 9.15507C4.5965 8.58486 5.19715 8.10224 5.73579 7.66945L5.77852 7.63511C6.34602 7.17903 6.84273 6.7759 7.26778 6.31893C8.30821 5.20037 8.54446 4.18717 8.56055 3.49802C8.56885 3.14245 8.51857 2.85417 8.46943 2.66213C8.44495 2.56644 8.42112 2.49608 8.40592 2.45502C8.39834 2.43455 8.39298 2.42158 8.39089 2.41662C8.22725 2.05872 8.28834 1.6367 8.54841 1.34037C8.86981 0.974175 9.32884 0.950674 9.75838 1.09929Z" fill="red"/></svg> ${r.kcal}</span>
          <span class="meal-macro mm-prot"><svg width="18" height="14" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;margin-right:6px;">
<path style="fill:#666666;" d="M431.197,121.41C540.2,192.013,533.727,340.034,442.92,413.628c-1.704,1.375-3.432,2.713-5.199,4.025
  c-56.306,41.744-167.656,46.375-226.814-43.485c-50.854-77.266-103.236-76.534-132.954-78.276
  c-29.73-1.741-98.554-3.483-71.966-85.241C32.589,128.906,168.672,22.793,353.263,83.679
  C384.747,94.065,409.96,107.643,431.197,121.41z"/>
<path style="fill:#F95428;" d="M480.247,260.623c2.625,49.315-18.764,97.343-57.189,128.486c-1.363,1.098-2.751,2.183-4.126,3.205
  c-19.004,14.083-46.766,22.5-74.237,22.5c-0.013,0-0.013,0-0.013,0c-25.869,0-74.212-7.546-107.425-57.997
  c-24.758-37.617-52.444-62.969-84.635-77.506c-27.749-12.518-52.495-13.83-68.862-14.701c-1.363-0.076-6.347-0.353-6.347-0.353
  c-11.244-0.631-37.567-2.12-43.75-11.168c-3.521-5.149-2.65-17.364,2.335-32.671c9.048-27.812,34.21-57.921,67.297-80.547
  c28.506-19.471,76.307-42.69,142.216-42.69c31.838,0,64.773,5.54,97.86,16.455c24.771,8.164,47.22,19.055,70.679,34.248
  C454.252,173.93,477.761,213.97,480.247,260.623z"/>
<path style="fill:#F2F2F2;" d="M361.023,228.924c27.169,0,49.201,22.033,49.201,49.214s-22.033,49.214-49.201,49.214
  c-27.181,0-49.214-22.033-49.214-49.214S333.842,228.924,361.023,228.924z"/>
<g>
  <polygon style="fill:#E54728;" points="448.88,420.972 448.879,420.973 448.878,420.974 "/>
  <path style="fill:#E54728;" d="M187.847,129.885c-4.519-2.621-10.312-1.083-12.934,3.439l-54.89,94.637
    c-2.622,4.521-1.083,10.312,3.439,12.934c1.494,0.868,3.128,1.28,4.74,1.28c3.263,0,6.441-1.691,8.196-4.717l54.89-94.637
    C193.907,138.298,192.369,132.508,187.847,129.885z"/>
  <path style="fill:#E54728;" d="M267.8,131.778c-4.518-2.621-10.312-1.083-12.934,3.439l-72.424,124.869
    c-2.622,4.521-1.083,10.312,3.439,12.934c1.494,0.868,3.128,1.28,4.74,1.28c3.263,0,6.441-1.691,8.196-4.717l72.424-124.869
    C273.861,140.191,272.323,134.401,267.8,131.778z"/>
  <path style="fill:#E54728;" d="M347.778,149.593c-4.511-2.639-10.307-1.118-12.947,3.393l-95.137,162.724
    c-2.639,4.511-1.119,10.308,3.393,12.947c1.502,0.878,3.145,1.295,4.767,1.295c3.252,0,6.418-1.678,8.178-4.689l95.137-162.724
    C353.81,158.028,352.291,152.231,347.778,149.593z"/>
  <path style="fill:#E54728;" d="M334.234,341.832c-4.511-2.641-10.308-1.119-12.947,3.393l-9.353,15.998
    c-2.639,4.511-1.119,10.308,3.393,12.947c1.502,0.878,3.146,1.295,4.769,1.295c3.252,0,6.418-1.678,8.178-4.689l9.353-15.998
    C340.265,350.268,338.746,344.471,334.234,341.832z"/>
  <path style="fill:#E54728;" d="M424.268,187.414c-4.51-2.641-10.307-1.119-12.947,3.393l-11.724,20.054
    c-2.639,4.511-1.119,10.308,3.393,12.947c1.502,0.878,3.145,1.295,4.767,1.295c3.252,0,6.418-1.678,8.178-4.689l11.724-20.054
    C430.3,195.849,428.781,190.052,424.268,187.414z"/>
</g>
</svg> ${r.prot}g</span>
          <span class="meal-macro mm-carb"><svg width="18" height="14" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;margin-right:6px;"><path fill="#D99E82" d="M36 13.5c0-4.558-4.435-8.267-10-8.479V5H10v.021C4.435 5.233 0 8.942 0 13.5c0 1.861.747 3.576 2 4.976V31a4 4 0 0 0 4 4h24a4 4 0 0 0 4-4V18.476c1.253-1.4 2-3.115 2-4.976z"></path><path fill="#CC927A" d="M19 18.476h15v1.5H19z"></path><path fill="#FFE8B6" d="M21 13.5c0-3.461-3.538-6.291-8-6.489C12.835 7.004 10.668 7 10.5 7C5.806 7 2 9.91 2 13.5c0 1.595.754 3.053 2 4.184V30a3 3 0 0 0 3 3h9a3 3 0 0 0 3-3V17.679c1.244-1.131 2-2.586 2-4.179z"></path></svg> ${r.carb}g</span>
          <span class="meal-macro mm-fat"><svg width="18" height="14" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;margin-right:6px;">
        <path d="M51.87 55.06s-22.76 8.59-28.21 10.49c-4.19 1.47-14.04 5.32-16.49 6.54s-3.3 2.17-3.57 4.08c-.27 1.91-.11 4.37.85 5.59s6.54 6.95 14.04 12.4s15.26 11.58 22.35 11.72c7.09.14 27.67-10.36 47.7-19.49s29.98-13.76 31.35-14.58s4.67-2.29 4.56-6.78c-.09-3.68-7.99-6.93-7.99-6.93l-27.64-13l-36.95 9.96z" fill="#6ca4ae"></path>
        <path d="M55.96 62.56s-8.72 3.41-11.86 5.04c-3.13 1.64-27.39 10.63-27.39 10.63s-6.13 2.45-6.81 3.27s.55 2.73 1.36 3.82c.82 1.09 11.74 9.26 15.81 11.72c4.35 2.64 8.99 4.91 10.63 5.04c1.64.14 9.81-1.5 9.81-1.5l35.98-14.62c4.03-1.73 16.46-7.68 22.22-10.33c5.76-2.65 11.2-5.53 13.14-6.77c1.89-1.2 4.07-2.59 4.01-5.35c-.05-2.11-1.84-2.99-4.14-4.38c-2.3-1.38-11.28-5.41-11.28-5.41l-51.48 8.84z" fill="#a8cfd7"></path>
        <path d="M97.55 58.87L49.4 66.09s-2.22.7-1.7 2.16s3.06 2.55 5.99 4.33c2.93 1.78 21.28 12.72 23.89 12.76c.82.01 3.51-1.29 5.86-2.27c5.46-2.26 13.63-6.25 17.97-8.39c6.31-3.12 13.71-5.65 13.5-7.95c-.21-2.3-4.61-3.77-4.61-3.77l-12.75-4.09z" fill="#6ba3ac"></path>
        <path d="M51.82 78.34c3.69 1.01 9.88 3.1 9.88 3.1l11.73 6.11l-13.99 7.87l-7.96 2.26s-5.36 1.17-8.63 1.26c-3.27.08-7.87-1.93-7.87-1.93l16.84-18.67z" fill="#6ba3ac"></path>
        <path d="M5.98 72.24s-1.53.7-2.16 2.26c-.94 2.32.81 3.9 1.43 4.79c1.28 1.83 7.04 7.03 7.04 7.03s-2.11-2.34-2.07-4.02c.02-.69.43-1.83.43-1.83s-2.84-3.16-3.33-3.9s-1.38-2.02-.84-2.91c.54-.89 1.84-1.51 1.58-1.95c-.13-.24-1.29.18-2.08.53z" fill="#dcedf6"></path>
        <path d="M37.28 101.6c-.65.45 3.05 1.35 8.29.69c5.76-.72 40.35-17.41 40.35-17.41s-.24-.94-.84-1.41c-.69-.54-1.65-.39-1.65-.39s-28.62 12.54-32.1 14.04c-1.96.84-7.1 2.86-8.41 3.27c-2.09.64-5.36 1.02-5.64 1.21z" fill="#dcedf6"></path>
        <path d="M53.23 48.43s-4.62-12.14-3.49-13.05c1.13-.9 12.09-5.65 20.38-8.44s13.19-4.15 14.55-4.07c1.36.08 6.86 1.81 11.91 3.54s16.21 6.33 16.28 9.05c.08 2.71-17.26 22.99-17.26 22.99L53.23 48.43z" fill="#ffe265"></path>
        <path d="M77.95 47.96c.74-1.24 8.71-4.17 18.61-7.89c9.41-3.53 15.34-5.59 16.14-5.1c1.23.76 1.02 6.46.91 16.2c-.08 7.1-.27 12.73-1.32 13.87c-.99 1.07-11.06 5.61-16.76 8.34c-5.7 2.72-16.31 7.83-18.18 8.06c-2.33.29-4.83-1.07-4.83-1.07l5.43-32.41z" fill="#feb502"></path>
        <path d="M48.81 66.37c.92 2.02 6.89 5.21 11.97 8.01c5.7 3.14 13.46 7.92 15.68 6.93c1.56-.69 1.49-15.27 1.49-21.13s.21-11.85-.08-12.26c-.57-.81-10.57-5.16-13.95-6.81c-3.38-1.65-13.2-6.88-14.28-5.61c-1.08 1.27-.74 8.75-.83 17.33s-.83 11.73 0 13.54z" fill="#ffebc9"></path>
        <path d="M36.36 89.08L12.81 74.24s-1.81-.39-2.03 1.01c-.22 1.4-.7 5.78-.25 6.59c.44.81 6.5 5.83 9.67 8.12s12.69 9.44 13.58 9.44c.89 0 7.17-6.48 7.17-6.48l-4.59-3.84z" fill="#ffe265"></path>
        <path d="M33.85 91l7.68-7.9l20.18-1.66s1.75.25 1.75 1.8s.37 5.09-.52 5.83s-8.11 2.92-13.73 4.87c-6.79 2.36-14.56 5.65-15.5 5.46c-.38-.06.14-8.4.14-8.4z" fill="#feb502"></path>
        <path d="M37.84 67.96c-2.58-.11-11.48 3.06-15.2 3.83c-3.89.81-10.27 1.86-11.08 2.59s2.11 2.85 4.2 4.37c2.49 1.81 14.28 9.99 15.61 11.03c1.33 1.03 2.51 1.7 4.36.96c1.85-.74 7.64-3.01 13.84-4.71c6.2-1.7 13.66-3.69 13.36-4.06c-.3-.37-8.99-5.44-14.15-8.47c-3.55-2.07-7.55-5.39-10.94-5.54z" fill="#ffebc9"></path>
        </svg> ${r.fat}g</span>
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

    const slotMacros = r ? `<span class="meal-macro mm-kcal"><svg width="14" height="14" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;margin-right:6px;"><path d="M9.75838 1.09929C9.85156 1.13153 9.9852 1.17902 10.1535 1.24207C10.49 1.36812 10.9661 1.55678 11.5355 1.81078C12.6715 2.31752 14.193 3.09073 15.7215 4.15505C18.745 6.26052 22 9.65692 22 14.5393C22 16.6738 21.4305 18.7869 20.1046 20.3856C18.7552 22.0126 16.7095 23 14 23C13.9352 23 13.6752 22.9978 13.4169 22.8125C13.0566 22.5541 12.9699 22.1541 13.0085 21.8667C13.0376 21.6502 13.1305 21.5025 13.1576 21.4602C13.1966 21.3993 13.234 21.3556 13.2534 21.3338C13.293 21.2893 13.3281 21.2581 13.3407 21.247C13.3575 21.2322 13.3716 21.2207 13.3801 21.214C13.4065 21.1929 13.4323 21.1745 13.4402 21.1689L13.4413 21.1681L13.5185 21.1136C13.5762 21.0727 13.6587 21.0131 13.7588 20.9348C13.9606 20.7768 14.2297 20.546 14.4969 20.2526C15.0448 19.6509 15.5 18.8819 15.5 18C15.5 16.3681 14.571 14.8515 13.5067 13.669C12.9869 13.0914 12.4644 12.6267 12.0715 12.3065C12.0471 12.2866 12.0233 12.2674 12 12.2487C11.9767 12.2674 11.9529 12.2866 11.9285 12.3065C11.5356 12.6267 11.0131 13.0914 10.4933 13.669C9.42904 14.8515 8.5 16.3681 8.5 18C8.5 18.8887 8.95405 19.6581 9.49825 20.2564C9.76406 20.5486 10.0319 20.7779 10.2327 20.934C10.3323 21.0114 10.4142 21.0699 10.47 21.1087C10.4933 21.125 10.5115 21.1374 10.5281 21.1487L10.5401 21.1569C10.5471 21.1616 10.5635 21.1728 10.5787 21.1837C10.5832 21.187 10.6139 21.2089 10.6476 21.2376C10.6583 21.2467 10.6772 21.2632 10.6995 21.285C10.7154 21.3005 10.7647 21.3492 10.8157 21.4212C10.8424 21.4607 10.901 21.5658 10.9302 21.6326C10.9668 21.7437 10.9991 22.045 10.9733 22.2301C10.89 22.4562 10.6027 22.798 10.4241 22.9056C10.2979 22.9546 10.0834 22.9965 10 23C7.29045 23 5.24478 22.0126 3.89543 20.3856C2.56953 18.7869 2 16.6738 2 14.5393C2 11.9892 2.88357 10.3815 4.05286 9.15507C4.5965 8.58486 5.19715 8.10224 5.73579 7.66945L5.77852 7.63511C6.34602 7.17903 6.84273 6.7759 7.26778 6.31893C8.30821 5.20037 8.54446 4.18717 8.56055 3.49802C8.56885 3.14245 8.51857 2.85417 8.46943 2.66213C8.44495 2.56644 8.42112 2.49608 8.40592 2.45502C8.39834 2.43455 8.39298 2.42158 8.39089 2.41662C8.22725 2.05872 8.28834 1.6367 8.54841 1.34037C8.86981 0.974175 9.32884 0.950674 9.75838 1.09929Z" fill="red"/></svg> ${sKcal} kcal</span>
      <span class="meal-macro mm-prot"><svg width="18" height="14" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;margin-right:6px;">
<path style="fill:#666666;" d="M431.197,121.41C540.2,192.013,533.727,340.034,442.92,413.628c-1.704,1.375-3.432,2.713-5.199,4.025
  c-56.306,41.744-167.656,46.375-226.814-43.485c-50.854-77.266-103.236-76.534-132.954-78.276
  c-29.73-1.741-98.554-3.483-71.966-85.241C32.589,128.906,168.672,22.793,353.263,83.679
  C384.747,94.065,409.96,107.643,431.197,121.41z"/>
<path style="fill:#F95428;" d="M480.247,260.623c2.625,49.315-18.764,97.343-57.189,128.486c-1.363,1.098-2.751,2.183-4.126,3.205
  c-19.004,14.083-46.766,22.5-74.237,22.5c-0.013,0-0.013,0-0.013,0c-25.869,0-74.212-7.546-107.425-57.997
  c-24.758-37.617-52.444-62.969-84.635-77.506c-27.749-12.518-52.495-13.83-68.862-14.701c-1.363-0.076-6.347-0.353-6.347-0.353
  c-11.244-0.631-37.567-2.12-43.75-11.168c-3.521-5.149-2.65-17.364,2.335-32.671c9.048-27.812,34.21-57.921,67.297-80.547
  c28.506-19.471,76.307-42.69,142.216-42.69c31.838,0,64.773,5.54,97.86,16.455c24.771,8.164,47.22,19.055,70.679,34.248
  C454.252,173.93,477.761,213.97,480.247,260.623z"/>
<path style="fill:#F2F2F2;" d="M361.023,228.924c27.169,0,49.201,22.033,49.201,49.214s-22.033,49.214-49.201,49.214
  c-27.181,0-49.214-22.033-49.214-49.214S333.842,228.924,361.023,228.924z"/>
<g>
  <polygon style="fill:#E54728;" points="448.88,420.972 448.879,420.973 448.878,420.974 "/>
  <path style="fill:#E54728;" d="M187.847,129.885c-4.519-2.621-10.312-1.083-12.934,3.439l-54.89,94.637
    c-2.622,4.521-1.083,10.312,3.439,12.934c1.494,0.868,3.128,1.28,4.74,1.28c3.263,0,6.441-1.691,8.196-4.717l54.89-94.637
    C193.907,138.298,192.369,132.508,187.847,129.885z"/>
  <path style="fill:#E54728;" d="M267.8,131.778c-4.518-2.621-10.312-1.083-12.934,3.439l-72.424,124.869
    c-2.622,4.521-1.083,10.312,3.439,12.934c1.494,0.868,3.128,1.28,4.74,1.28c3.263,0,6.441-1.691,8.196-4.717l72.424-124.869
    C273.861,140.191,272.323,134.401,267.8,131.778z"/>
  <path style="fill:#E54728;" d="M347.778,149.593c-4.511-2.639-10.307-1.118-12.947,3.393l-95.137,162.724
    c-2.639,4.511-1.119,10.308,3.393,12.947c1.502,0.878,3.145,1.295,4.767,1.295c3.252,0,6.418-1.678,8.178-4.689l95.137-162.724
    C353.81,158.028,352.291,152.231,347.778,149.593z"/>
  <path style="fill:#E54728;" d="M334.234,341.832c-4.511-2.641-10.308-1.119-12.947,3.393l-9.353,15.998
    c-2.639,4.511-1.119,10.308,3.393,12.947c1.502,0.878,3.146,1.295,4.769,1.295c3.252,0,6.418-1.678,8.178-4.689l9.353-15.998
    C340.265,350.268,338.746,344.471,334.234,341.832z"/>
  <path style="fill:#E54728;" d="M424.268,187.414c-4.51-2.641-10.307-1.119-12.947,3.393l-11.724,20.054
    c-2.639,4.511-1.119,10.308,3.393,12.947c1.502,0.878,3.145,1.295,4.767,1.295c3.252,0,6.418-1.678,8.178-4.689l11.724-20.054
    C430.3,195.849,428.781,190.052,424.268,187.414z"/>
</g>
</svg> ${sProt}g P</span>
      <span class="meal-macro mm-carb"><svg width="18" height="14" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;margin-right:6px;"><path fill="#D99E82" d="M36 13.5c0-4.558-4.435-8.267-10-8.479V5H10v.021C4.435 5.233 0 8.942 0 13.5c0 1.861.747 3.576 2 4.976V31a4 4 0 0 0 4 4h24a4 4 0 0 0 4-4V18.476c1.253-1.4 2-3.115 2-4.976z"></path><path fill="#CC927A" d="M19 18.476h15v1.5H19z"></path><path fill="#FFE8B6" d="M21 13.5c0-3.461-3.538-6.291-8-6.489C12.835 7.004 10.668 7 10.5 7C5.806 7 2 9.91 2 13.5c0 1.595.754 3.053 2 4.184V30a3 3 0 0 0 3 3h9a3 3 0 0 0 3-3V17.679c1.244-1.131 2-2.586 2-4.179z"></path></svg> ${sCarb}g C</span>
      <span class="meal-macro mm-fat"><svg width="18" height="14" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;margin-right:6px;"><path d="M51.87 55.06s-22.76 8.59-28.21 10.49c-4.19 1.47-14.04 5.32-16.49 6.54s-3.3 2.17-3.57 4.08c-.27 1.91-.11 4.37.85 5.59s6.54 6.95 14.04 12.4s15.26 11.58 22.35 11.72c7.09.14 27.67-10.36 47.7-19.49s29.98-13.76 31.35-14.58s4.67-2.29 4.56-6.78c-.09-3.68-7.99-6.93-7.99-6.93l-27.64-13l-36.95 9.96z" fill="#6ca4ae"></path>
    <path d="M55.96 62.56s-8.72 3.41-11.86 5.04c-3.13 1.64-27.39 10.63-27.39 10.63s-6.13 2.45-6.81 3.27s.55 2.73 1.36 3.82c.82 1.09 11.74 9.26 15.81 11.72c4.35 2.64 8.99 4.91 10.63 5.04c1.64.14 9.81-1.5 9.81-1.5l35.98-14.62c4.03-1.73 16.46-7.68 22.22-10.33c5.76-2.65 11.2-5.53 13.14-6.77c1.89-1.2 4.07-2.59 4.01-5.35c-.05-2.11-1.84-2.99-4.14-4.38c-2.3-1.38-11.28-5.41-11.28-5.41l-51.48 8.84z" fill="#a8cfd7"></path>
    <path d="M97.55 58.87L49.4 66.09s-2.22.7-1.7 2.16s3.06 2.55 5.99 4.33c2.93 1.78 21.28 12.72 23.89 12.76c.82.01 3.51-1.29 5.86-2.27c5.46-2.26 13.63-6.25 17.97-8.39c6.31-3.12 13.71-5.65 13.5-7.95c-.21-2.3-4.61-3.77-4.61-3.77l-12.75-4.09z" fill="#6ba3ac"></path>
    <path d="M51.82 78.34c3.69 1.01 9.88 3.1 9.88 3.1l11.73 6.11l-13.99 7.87l-7.96 2.26s-5.36 1.17-8.63 1.26c-3.27.08-7.87-1.93-7.87-1.93l16.84-18.67z" fill="#6ba3ac"></path>
    <path d="M5.98 72.24s-1.53.7-2.16 2.26c-.94 2.32.81 3.9 1.43 4.79c1.28 1.83 7.04 7.03 7.04 7.03s-2.11-2.34-2.07-4.02c.02-.69.43-1.83.43-1.83s-2.84-3.16-3.33-3.9s-1.38-2.02-.84-2.91c.54-.89 1.84-1.51 1.58-1.95c-.13-.24-1.29.18-2.08.53z" fill="#dcedf6"></path>
    <path d="M37.28 101.6c-.65.45 3.05 1.35 8.29.69c5.76-.72 40.35-17.41 40.35-17.41s-.24-.94-.84-1.41c-.69-.54-1.65-.39-1.65-.39s-28.62 12.54-32.1 14.04c-1.96.84-7.1 2.86-8.41 3.27c-2.09.64-5.36 1.02-5.64 1.21z" fill="#dcedf6"></path>
    <path d="M53.23 48.43s-4.62-12.14-3.49-13.05c1.13-.9 12.09-5.65 20.38-8.44s13.19-4.15 14.55-4.07c1.36.08 6.86 1.81 11.91 3.54s16.21 6.33 16.28 9.05c.08 2.71-17.26 22.99-17.26 22.99L53.23 48.43z" fill="#ffe265"></path>
    <path d="M77.95 47.96c.74-1.24 8.71-4.17 18.61-7.89c9.41-3.53 15.34-5.59 16.14-5.1c1.23.76 1.02 6.46.91 16.2c-.08 7.1-.27 12.73-1.32 13.87c-.99 1.07-11.06 5.61-16.76 8.34c-5.7 2.72-16.31 7.83-18.18 8.06c-2.33.29-4.83-1.07-4.83-1.07l5.43-32.41z" fill="#feb502"></path>
    <path d="M48.81 66.37c.92 2.02 6.89 5.21 11.97 8.01c5.7 3.14 13.46 7.92 15.68 6.93c1.56-.69 1.49-15.27 1.49-21.13s.21-11.85-.08-12.26c-.57-.81-10.57-5.16-13.95-6.81c-3.38-1.65-13.2-6.88-14.28-5.61c-1.08 1.27-.74 8.75-.83 17.33s-.83 11.73 0 13.54z" fill="#ffebc9"></path>
    <path d="M36.36 89.08L12.81 74.24s-1.81-.39-2.03 1.01c-.22 1.4-.7 5.78-.25 6.59c.44.81 6.5 5.83 9.67 8.12s12.69 9.44 13.58 9.44c.89 0 7.17-6.48 7.17-6.48l-4.59-3.84z" fill="#ffe265"></path>
    <path d="M33.85 91l7.68-7.9l20.18-1.66s1.75.25 1.75 1.8s.37 5.09-.52 5.83s-8.11 2.92-13.73 4.87c-6.79 2.36-14.56 5.65-15.5 5.46c-.38-.06.14-8.4.14-8.4z" fill="#feb502"></path>
    <path d="M37.84 67.96c-2.58-.11-11.48 3.06-15.2 3.83c-3.89.81-10.27 1.86-11.08 2.59s2.11 2.85 4.2 4.37c2.49 1.81 14.28 9.99 15.61 11.03c1.33 1.03 2.51 1.7 4.36.96c1.85-.74 7.64-3.01 13.84-4.71c6.2-1.7 13.66-3.69 13.36-4.06c-.3-.37-8.99-5.44-14.15-8.47c-3.55-2.07-7.55-5.39-10.94-5.54z" fill="#ffebc9"></path>
    </svg> ${sFat}g F</span>` : '';
    return `<div class="meal-slot ${isDisabled ? 'disabled-slot' : ''}" style="${isDisabled ? 'opacity:0.5; filter:grayscale(1);' : ''}">
      <div class="slot-header">
        <div class="slot-title" style="display:flex; align-items:center; gap:8px;">
          <span class="slot-icon">${slot.icon}</span>
          ${slot.label}
          <button onclick="toggleSlot('${activeDay}', '${slot.key}')" aria-pressed="${isDisabled ? 'true' : 'false'}" style="background:none; border:none; cursor:pointer; font-size:1.1rem; padding:0; margin-left:8px; display:inline-flex; align-items:center;" title="${isDisabled ? 'Enable' : 'Disable'} this slot">
            ${isDisabled ? `
              <svg width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;fill:currentColor;">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M15.9202 12.7988C15.9725 12.5407 16 12.2736 16 12C16 9.79086 14.2091 8 12 8C11.7264 8 11.4593 8.02746 11.2012 8.07977L12.1239 9.00251C13.6822 9.06583 14.9342 10.3178 14.9975 11.8761L15.9202 12.7988ZM9.39311 10.5143C9.14295 10.9523 9 11.4595 9 12C9 13.6569 10.3431 15 12 15C12.5405 15 13.0477 14.857 13.4857 14.6069L14.212 15.3332C13.5784 15.7545 12.8179 16 12 16C9.79086 16 8 14.2091 8 12C8 11.1821 8.24547 10.4216 8.66676 9.78799L9.39311 10.5143Z" fill="currentColor"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M16.1537 17.2751L15.4193 16.5406C14.3553 17.1196 13.1987 17.5 12 17.5C10.3282 17.5 8.73816 16.7599 7.36714 15.7735C6.00006 14.79 4.89306 13.5918 4.19792 12.7478C3.77356 12.2326 3.72974 12.1435 3.72974 12C3.72974 11.8565 3.77356 11.7674 4.19792 11.2522C4.86721 10.4396 5.9183 9.29863 7.21572 8.33704L6.50139 7.62271C5.16991 8.63072 4.10383 9.79349 3.42604 10.6164L3.36723 10.6876C3.03671 11.087 2.72974 11.4579 2.72974 12C2.72974 12.5421 3.0367 12.913 3.36723 13.3124L3.42604 13.3836C4.15099 14.2638 5.32014 15.5327 6.78312 16.5853C8.24216 17.635 10.0361 18.5 12 18.5C13.5101 18.5 14.9196 17.9886 16.1537 17.2751ZM9.18993 6.06861C10.0698 5.71828 11.0135 5.5 12 5.5C13.9639 5.5 15.7579 6.365 17.2169 7.41472C18.6799 8.46727 19.849 9.73623 20.574 10.6164L20.6328 10.6876C20.9633 11.087 21.2703 11.4579 21.2703 12C21.2703 12.5421 20.9633 12.913 20.6328 13.3124L20.574 13.3836C20.0935 13.9669 19.418 14.721 18.5911 15.4697L17.883 14.7617C18.6787 14.0456 19.3338 13.3164 19.8021 12.7478C20.2265 12.2326 20.2703 12.1435 20.2703 12C20.2703 11.8565 20.2265 11.7674 19.8021 11.2522C19.107 10.4082 18 9.21001 16.6329 8.22646C15.2619 7.24007 13.6718 6.5 12 6.5C11.3056 6.5 10.6253 6.62768 9.96897 6.84765L9.18993 6.06861Z" fill="currentColor"/>
                <path d="M5 2L21 18" stroke="currentColor"/>
              </svg>
            ` : `
              <svg width="16" height="16" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;fill:currentColor;"><path d="M512 160c320 0 512 352 512 352S832 864 512 864 0 512 0 512s192-352 512-352zm0 64c-225.28 0-384.128 208.064-436.8 288 52.608 79.872 211.456 288 436.8 288 225.28 0 384.128-208.064 436.8-288-52.608-79.872-211.456-288-436.8-288zm0 64a224 224 0 1 1 0 448 224 224 0 0 1 0-448zm0 64a160.192 160.192 0 0 0-160 160c0 88.192 71.744 160 160 160s160-71.808 160-160-71.744-160-160-160z"/></svg>
            `}
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
            <button class="meal-act-btn swap-btn" style="color:#1e88e5;" onclick="openSwapModal('${activeDay}','${slot.key}')" ${isPast ? 'disabled' : ''}>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M18.7153 1.71609C18.3241 1.32351 18.3241 0.687013 18.7153 0.294434C19.1066 -0.0981448 19.7409 -0.0981448 20.1321 0.294434L22.4038 2.57397L22.417 2.58733C23.1935 3.37241 23.1917 4.64056 22.4116 5.42342L20.1371 7.70575C19.7461 8.09808 19.1122 8.09808 18.7213 7.70575C18.3303 7.31342 18.3303 6.67733 18.7213 6.285L20.0018 5L4.99998 5C4.4477 5 3.99998 5.44772 3.99998 6V13C3.99998 13.5523 3.55227 14 2.99998 14C2.4477 14 1.99998 13.5523 1.99998 13V6C1.99998 4.34315 3.34313 3 4.99998 3H19.9948L18.7153 1.71609Z" fill="currentColor"/><path d="M22 11C22 10.4477 21.5523 10 21 10C20.4477 10 20 10.4477 20 11V18C20 18.5523 19.5523 19 19 19L4.00264 19L5.28213 17.7161C5.67335 17.3235 5.67335 16.687 5.28212 16.2944C4.8909 15.9019 4.2566 15.9019 3.86537 16.2944L1.59369 18.574L1.58051 18.5873C0.803938 19.3724 0.805727 20.6406 1.58588 21.4234L3.86035 23.7058C4.25133 24.0981 4.88523 24.0981 5.2762 23.7058C5.66718 23.3134 5.66718 22.6773 5.2762 22.285L3.99563 21L19 21C20.6568 21 22 19.6569 22 18L22 11Z" fill="currentColor"/></svg>
              Swap
            </button>
            <button class="meal-act-btn" style="color:#ffb300;" onclick="window.location.href='foovia-recipe.php?id_rec=${r.id}'">
              <svg width="16" height="16" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill="currentColor" d="M512 160c320 0 512 352 512 352S832 864 512 864 0 512 0 512s192-352 512-352zm0 64c-225.28 0-384.128 208.064-436.8 288 52.608 79.872 211.456 288 436.8 288 225.28 0 384.128-208.064 436.8-288-52.608-79.872-211.456-288-436.8-288zm0 64a224 224 0 1 1 0 448 224 224 0 0 1 0-448zm0 64a160.192 160.192 0 0 0-160 160c0 88.192 71.744 160 160 160s160-71.808 160-160-71.744-160-160-160z"/></svg>
              View
            </button>
            <button class="meal-act-btn log-btn ${isLogged ? 'logged' : ''}" style="color:#2e7d32;" onclick="logMeal('${activeDay}','${slot.key}')" ${isPast ? 'disabled' : ''}>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 8L16 12M16 12L12 16M16 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              ${isLogged ? 'Logged' : 'Log'}
            </button>
            <button class="meal-act-btn remove-btn" style="color:#e53935;" onclick="removeMeal('${activeDay}','${slot.key}')" ${isPast ? 'disabled' : ''}>
              <svg width="16" height="16" viewBox="0 0 1920 1920" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill="currentColor" d="M614.742 795.026c61.932.36 119.783 24.965 163.11 69.373L605.98 1032.071c1.44 1.32 4.081 3 7.322 3l-369.67 373.392 267.89 267.77 375.312-375.31 168.752-158.79c44.288 43.207 68.893 101.178 69.257 162.99.356 62.412-23.889 121.103-68.297 165.512l-381.793 381.912c-43.568 43.448-101.54 67.453-163.23 67.453-61.813 0-119.784-24.005-163.232-67.453L67.438 1571.694c-89.897-90.017-89.897-236.445-.12-326.462L449.23 863.319c44.049-44.048 102.26-68.293 164.192-68.293Zm764.305 706.118v360.069h-120.023v-360.069h120.023Zm222.402 17.56 240.046 240.045-84.856 84.856-240.046-240.045 84.856-84.856Zm257.69-257.605v120.023h-360.07v-120.023h360.07ZM1245.497 67.184c89.177-89.177 235.725-89.657 326.582-.96l280.733 280.733c89.537 91.938 89.177 238.245 0 327.542L1470.9 1056.172c-43.329 43.448-101.42 67.573-163.231 67.573-61.692 0-119.663-24.125-163.111-67.573L1314.27 886.46c-3.12-3.121-10.082-3.121-13.083 0L1683.1 504.667l-279.893-267.77-369.55 381.552c3.48-3.841 3.36-9.122-.24-12.843L863.704 775.318c-90.017-90.017-90.017-236.324 0-326.342ZM408.543 540.962v120.023H68.998V540.962h339.545ZM658.91 71.072v339.425H538.767V71.073H658.91ZM161.092 78.43l240.165 240.046-84.856 84.856L76.356 163.286l84.736-84.856Z"/></svg>
              Remove
            </button>
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
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="7" width="18" height="12" rx="2"/><path d="M3 7l9-4 9 4"/></svg> Extra logged meals
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
<script src="../js/sidebar.js"></script>
</body>
</html>
