<?php
require_once __DIR__ . '/../../../controle/controle_Menu.php';
require_once __DIR__ . '/../../../controle/controle_categ_rec.php';

$controller = new Controller_menu();
$categoryController = new controle_categ_rec();
$categoryRows = $categoryController->list_categ_rec();
$recipe = null;
$recipeIngredients = [];
$error = '';

function foovia_normalize_image_path($path, $fallback = 'images/product-thumb-1.png') {
  $path = str_replace('\\', '/', trim((string)$path));
  if ($path === '') {
    return $fallback;
  }

  if (!preg_match('~^(https?://|/|\./|\.\./)~i', $path)) {
    return '../../Back Office/' . ltrim($path, '/');
  }

  return $path;
}

function foovia_clean_text($value, $fallback = '') {
  $value = trim((string)$value);
  return $value !== '' ? $value : $fallback;
}

function foovia_number($value) {
  $value = trim((string)$value);
  return is_numeric($value) ? (float)$value : 0.0;
}

function foovia_format_number($value) {
  $value = foovia_number($value);
  if ($value === 0.0) {
    return '0';
  }

  $precision = abs($value - round($value)) < 0.01 ? 0 : 1;
  return rtrim(rtrim(number_format($value, $precision), '0'), '.');
}

function foovia_normalize_hex_color($color) {
  $color = trim((string)$color);
  if ($color === '') {
    return '';
  }

  if ($color[0] === '#') {
    $color = substr($color, 1);
  }

  if (preg_match('/^[0-9a-fA-F]{3}$/', $color)) {
    $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
  }

  if (!preg_match('/^[0-9a-fA-F]{6}$/', $color)) {
    return '';
  }

  return '#' . strtolower($color);
}

function foovia_category_text_color($color) {
  $color = foovia_normalize_hex_color($color);
  if ($color === '') {
    return '#555';
  }

  $hex = ltrim($color, '#');
  $red = hexdec(substr($hex, 0, 2));
  $green = hexdec(substr($hex, 2, 2));
  $blue = hexdec(substr($hex, 4, 2));
  $luminance = (0.299 * $red) + (0.587 * $green) + (0.114 * $blue);

  return $luminance > 160 ? '#111008' : '#ffffff';
}

if (!isset($_GET['id_rec']) || !is_numeric($_GET['id_rec'])) {
  $error = 'Recipe ID is missing or invalid.';
} else {
  $recipe = $controller->get_recipe_by_id((int)$_GET['id_rec']);
  if (!$recipe) {
    $error = 'Recipe not found.';
  } else {
    $recipeIngredients = $controller->get_recipe_ingredients((int)$_GET['id_rec']);
  }
}

$recipeName = $recipe ? foovia_clean_text($recipe['name_rec'] ?? '', 'Recipe') : 'Recipe';
$categoryNames = $recipe ? array_values(array_filter(array_map('trim', explode(',', (string)($recipe['categorie_rec'] ?? ''))))) : [];
$primaryCategory = !empty($categoryNames) ? $categoryNames[0] : 'Recipe';
$categoryColorsByName = [];
foreach ($categoryRows as $categoryRow) {
  $categoryName = isset($categoryRow['nom_categ']) ? trim((string)$categoryRow['nom_categ']) : '';
  if ($categoryName === '') {
    continue;
  }

  $rawColor = $categoryRow['color_categ'] ?? ($categoryRow['color_cat_rec'] ?? '');
  $color = foovia_normalize_hex_color($rawColor);
  if ($color === '') {
    continue;
  }

  $categoryColorsByName[strtolower($categoryName)] = $color;
}
$description = $recipe ? foovia_clean_text($recipe['description_rec'] ?? '', '') : '';
$instructionsRaw = $recipe ? foovia_clean_text($recipe['instruction_rec'] ?? '', '') : '';
$origin = $recipe ? foovia_clean_text($recipe['origin_rec'] ?? '', '') : '';
$calories = $recipe ? foovia_number($recipe['cal_rec'] ?? 0) : 0.0;
$protein = $recipe ? foovia_number($recipe['prot_rec'] ?? 0) : 0.0;
$carbs = $recipe ? foovia_number($recipe['carb_rec'] ?? 0) : 0.0;
$fat = $recipe ? foovia_number($recipe['fat_rec'] ?? 0) : 0.0;
$heroImage = $recipe ? foovia_normalize_image_path($recipe['img_rec'] ?? '', 'images/product-thumb-1.png') : '';

$macroTotal = $protein + $carbs + $fat;
$proteinPct = $macroTotal > 0 ? (int)round(($protein / $macroTotal) * 100) : 0;
$carbPct = $macroTotal > 0 ? (int)round(($carbs / $macroTotal) * 100) : 0;
$fatPct = $macroTotal > 0 ? (int)round(($fat / $macroTotal) * 100) : 0;

$instructionSteps = [];
if ($instructionsRaw !== '') {
  $instructionSteps = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $instructionsRaw))));
}
if (empty($instructionSteps) && $instructionsRaw !== '') {
  $instructionSteps = [$instructionsRaw];
}

$similarRecipes = [];
if ($recipe) {
  $allRecipes = $controller->list_recipe();
  foreach ($allRecipes as $row) {
    if ((int)($row['id_rec'] ?? 0) === (int)($recipe['id_rec'] ?? 0)) {
      continue;
    }
    $similarRecipes[] = $row;
    if (count($similarRecipes) >= 4) {
      break;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA — <?php echo htmlspecialchars($recipeName); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Boldonse&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --yellow:     #F5C842;
    --green:      #4BAE52;
    --orange:     #D94F00;
    --yellow-mid: #F0A830;
    --green-light:#A8C45A;
    --peach:      #F2A98A;
    --forest:     #2E4A28;
    --red:        #C0381A;
    --off-white:  #FDF8EE;
    --dark:       #111008;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html { scroll-behavior: smooth; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--off-white);
    color: var(--dark);
    overflow-x: hidden;
  }

  /* ── NAV ── */
  nav {
    position: fixed; top: 0; left: 0; width: 100%;
    z-index: 100;
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.95rem 1.5rem;
    background: rgba(253,248,238,.88);
    backdrop-filter: blur(14px);
    border-bottom: 1.5px solid rgba(75,174,82,.18);
  }
  .nav-logo {
    font-family: 'Syne', sans-serif;
    font-size: 1.05rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--dark);
    text-decoration: none;
    display: flex; align-items: center; gap: 0.75rem;
  }
  .nav-logo img {
    height: 50px;
    width: auto;
    object-fit: contain;
    display: block;
  }
  .nav-back {
    display: flex; align-items: center; gap: 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: .9rem; font-weight: 500;
    color: #666; text-decoration: none;
    transition: color .2s;
  }
  .nav-back:hover { color: var(--green); }
  .nav-back svg { width: 18px; height: 18px; }
  .nav-cta {
    background: var(--green); color: #fff;
    padding: 9px 22px; border-radius: 100px;
    font-family: 'Boldonse', system-ui;
    font-size: .78rem; text-decoration: none;
    transition: background .2s;
  }
  .nav-cta:hover { background: var(--forest); }

  /* ── HERO RECIPE IMAGE ── */
  .recipe-hero {
    margin-top: 64px;
    position: relative;
    height: 480px;
    overflow: hidden;
  }
  .recipe-hero img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
  }
  /* fallback gradient when no real photo */
  .recipe-hero .hero-bg {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, #2E4A28 0%, #4BAE52 40%, #F5C842 100%);
    display: flex; align-items: center; justify-content: center;
    font-size: 9rem;
  }
  .recipe-hero-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(17,16,8,.85) 0%, rgba(17,16,8,.1) 60%, transparent 100%);
  }
  .recipe-hero-content {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    padding: 40px 64px;
  }
  .recipe-category {
    display: inline-block;
    background: var(--yellow);
    color: var(--dark);
    font-family: 'Boldonse', system-ui;
    font-size: .72rem;
    letter-spacing: .12em; text-transform: uppercase;
    padding: 5px 14px; border-radius: 100px;
    margin-bottom: 14px;
  }
  .recipe-hero h1 {
    font-family: 'Boldonse', system-ui;
    font-size: clamp(2.2rem, 5vw, 3.8rem);
    color: #fff;
    line-height: 1.05;
    margin-bottom: 16px;
    max-width: 700px;
  }
  .recipe-meta {
    display: flex; gap: 24px; flex-wrap: wrap;
  }
  .recipe-meta-item {
    display: flex; align-items: center; gap: 7px;
    color: rgba(255,255,255,.75);
    font-size: .85rem;
  }
  .recipe-meta-item span.icon { font-size: 1rem; }

  /* ── MAIN LAYOUT ── */
  .recipe-body {
    max-width: 1200px;
    margin: 0 auto;
    padding: 56px 64px 80px;
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 56px;
    align-items: start;
  }

  /* ── MACROS CARD ── */
  .macros-card {
    background: var(--dark);
    border-radius: 24px;
    padding: 32px;
    color: #fff;
    margin-bottom: 32px;
  }
  .macros-card h2 {
    font-family: 'Boldonse', system-ui;
    font-size: 1rem;
    color: var(--yellow);
    letter-spacing: .08em; text-transform: uppercase;
    margin-bottom: 24px;
  }
  .macros-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  .macro-item {
    background: rgba(255,255,255,.06);
    border-radius: 16px;
    padding: 18px 16px;
    text-align: center;
    position: relative;
    overflow: hidden;
  }
  .macro-item::before {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
  }
  .macro-item.cal::before  { background: var(--orange); }
  .macro-item.prot::before { background: var(--green); }
  .macro-item.carb::before { background: var(--yellow); }
  .macro-item.fat::before  { background: var(--peach); }

  .macro-val {
    font-family: 'Boldonse', system-ui;
    font-size: 2rem;
    line-height: 1;
    margin-bottom: 4px;
  }
  .macro-item.cal  .macro-val { color: var(--orange); }
  .macro-item.prot .macro-val { color: var(--green); }
  .macro-item.carb .macro-val { color: var(--yellow); }
  .macro-item.fat  .macro-val { color: var(--peach); }

  .macro-label {
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: rgba(255,255,255,.5);
  }

  /* macro bar */
  .macro-bar-wrap { margin-top: 24px; }
  .macro-bar-label {
    display: flex; justify-content: space-between;
    font-size: .78rem; color: rgba(255,255,255,.55);
    margin-bottom: 6px;
  }
  .macro-bar {
    height: 8px; border-radius: 100px;
    background: rgba(255,255,255,.1);
    overflow: hidden;
    margin-bottom: 10px;
  }
  .macro-bar-fill { height: 100%; border-radius: 100px; }

  /* ── DESCRIPTION ── */
  .section-heading {
    font-family: 'Boldonse', system-ui;
    font-size: 1.4rem;
    margin-bottom: 16px;
    display: flex; align-items: center; gap: 10px;
  }
  .section-heading .badge {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
  }

  .description-text {
    font-size: 1rem;
    line-height: 1.8;
    color: #444;
    margin-bottom: 48px;
    padding-bottom: 48px;
    border-bottom: 1.5px solid rgba(0,0,0,.08);
  }

  /* ── INGREDIENTS ── */
  .ingredients-section { margin-bottom: 48px; padding-bottom: 48px; border-bottom: 1.5px solid rgba(0,0,0,.08); }

  .ingredients-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 14px;
    margin-top: 8px;
  }

  .ingredient-card {
    background: #fff;
    border-radius: 18px;
    padding: 16px 12px;
    text-align: center;
    border: 1.5px solid rgba(0,0,0,.06);
    transition: border-color .2s, transform .2s, box-shadow .2s;
    cursor: default;
  }
  .ingredient-card:hover {
    border-color: var(--green);
    transform: translateY(-3px);
    box-shadow: 0 10px 28px rgba(75,174,82,.15);
  }
  .ingredient-photo {
    width: 72px; height: 72px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 10px;
    display: block;
    background: var(--off-white);
    font-size: 2.6rem;
    line-height: 72px;
    text-align: center;
  }
  .ingredient-name {
    font-family: 'Boldonse', system-ui;
    font-size: .8rem;
    margin-bottom: 4px;
    line-height: 1.2;
  }
  .ingredient-qty {
    font-size: .78rem;
    color: var(--green);
    font-weight: 600;
  }
  .ingredient-cal {
    font-size: .7rem;
    color: #aaa;
    margin-top: 2px;
  }

  /* ── INSTRUCTIONS ── */
  .instructions-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-top: 8px;
  }
  .step-card {
    display: flex; gap: 20px; align-items: flex-start;
    background: #fff;
    border-radius: 18px;
    padding: 22px 24px;
    border: 1.5px solid rgba(0,0,0,.06);
    transition: border-color .2s;
  }
  .step-card:hover { border-color: var(--yellow-mid); }
  .step-num-badge {
    width: 40px; height: 40px; border-radius: 12px;
    background: var(--yellow);
    font-family: 'Boldonse', system-ui;
    font-size: 1rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    color: var(--dark);
  }
  .step-content { flex: 1; }
  .step-title {
    font-family: 'Boldonse', system-ui;
    font-size: .95rem;
    margin-bottom: 6px;
    color: var(--dark);
  }
  .step-desc {
    font-size: .88rem;
    line-height: 1.7;
    color: #555;
  }
  .step-timer {
    display: inline-flex; align-items: center; gap: 5px;
    margin-top: 10px;
    background: var(--off-white);
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 100px;
    padding: 4px 12px;
    font-size: .75rem;
    font-weight: 600;
    color: var(--orange);
  }

  /* ── SIDEBAR ── */
  .sidebar { position: sticky; top: 88px; }

  .sidebar-card {
    background: #fff;
    border-radius: 20px;
    padding: 28px;
    border: 1.5px solid rgba(0,0,0,.07);
    margin-bottom: 20px;
  }
  .sidebar-card h3 {
    font-family: 'Boldonse', system-ui;
    font-size: .95rem;
    margin-bottom: 18px;
    color: var(--dark);
  }

  /* tags */
  .tag-cloud { display: flex; flex-wrap: wrap; gap: 8px; }
  .tag {
    background: var(--off-white);
    border: 1.5px solid rgba(0,0,0,.08);
    border-radius: 100px;
    padding: 6px 14px;
    font-size: .78rem; font-weight: 500;
    color: #555;
  }
  .tag.green  { background: rgba(75,174,82,.1);  border-color: var(--green);  color: var(--forest); }
  .tag.yellow { background: rgba(245,200,66,.15); border-color: var(--yellow-mid); color: #7a5800; }
  .tag.orange { background: rgba(217,79,0,.1);    border-color: var(--orange); color: var(--red); }

  /* similar recipes */
  .similar-recipe {
    display: flex; gap: 12px; align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid rgba(0,0,0,.06);
    text-decoration: none; color: var(--dark);
    transition: opacity .2s;
  }
  .similar-recipe:last-child { border-bottom: none; padding-bottom: 0; }
  .similar-recipe:hover { opacity: .75; }
  .similar-thumb {
    width: 54px; height: 54px; border-radius: 12px;
    background: var(--yellow);
    font-size: 1.8rem; text-align: center; line-height: 54px;
    flex-shrink: 0;
  }
  .similar-info strong {
    font-family: 'Boldonse', system-ui;
    font-size: .83rem; display: block; margin-bottom: 2px;
  }
  .similar-info span { font-size: .75rem; color: #888; }

  /* save btn */
  .btn-save {
    width: 100%;
    background: var(--off-white);
    color: var(--dark);
    border: 2px solid var(--dark);
    padding: 16px;
    border-radius: 14px;
    font-family: 'Boldonse', system-ui;
    font-size: .95rem;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: background .2s, color .2s, border-color .2s, transform .15s;
    margin-bottom: 10px;
  }
  .btn-save:hover {
    background: #e8e8e8;
    border-color: #999;
  }
  .btn-save.saved {
    background: var(--red);
    color: #fff;
    border-color: var(--red);
  }
  .btn-log {
    width: 100%;
    background: transparent;
    color: var(--dark);
    border: 2px solid var(--dark);
    padding: 14px;
    border-radius: 14px;
    font-family: 'Boldonse', system-ui;
    font-size: .95rem;
    cursor: pointer;
    transition: background .2s, color .2s;
  }
  .btn-log:hover { background: var(--dark); color: #fff; }
  .btn-log.saved {
    background: var(--green);
    color: #fff;
    border-color: var(--green);
  }

  /* ── RESPONSIVE ── */
  @media (max-width: 960px) {
    .recipe-body { grid-template-columns: 1fr; padding: 36px 28px 60px; }
    .sidebar { position: static; }
    .recipe-hero-content { padding: 28px 28px; }
    nav { padding: 14px 24px; }
    .ingredients-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
  }
  @media (max-width: 600px) {
    .recipe-hero { height: 360px; }
    .recipe-hero h1 { font-size: 2rem; }
    .macros-grid { grid-template-columns: repeat(2, 1fr); }
  }
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <a href="recipe_page.php#recipes" class="nav-logo">
    <img src="../assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo">
    FOOVIA
  </a>
  <a href="recipe_page.php#recipes" class="nav-back">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M5 12l7-7M5 12l7 7"/></svg>
    Back to Recipes
  </a>
  <a href="#" class="nav-cta">Log Meal</a>
</nav>

<!-- HERO -->
<?php if (!empty($recipe)): ?>
  <div class="recipe-hero">
    <?php if ($heroImage !== ''): ?>
      <img src="<?php echo htmlspecialchars($heroImage); ?>" alt="<?php echo htmlspecialchars($recipeName); ?>">
    <?php else: ?>
      <div class="hero-bg">FOOVIA</div>
    <?php endif; ?>
    <div class="recipe-hero-overlay"></div>
    <div class="recipe-hero-content">
      <span class="recipe-category"><?php echo htmlspecialchars($primaryCategory); ?></span>
      <h1><?php echo htmlspecialchars($recipeName); ?></h1>
      <div class="recipe-meta">
        <?php if ($calories > 0): ?>
          <div class="recipe-meta-item"><span class="icon">Cal</span> <?php echo htmlspecialchars(foovia_format_number($calories)); ?> kcal</div>
        <?php endif; ?>
        <?php if ($protein > 0): ?>
          <div class="recipe-meta-item"><span class="icon">Pro</span> <?php echo htmlspecialchars(foovia_format_number($protein)); ?> g</div>
        <?php endif; ?>
        <?php if ($carbs > 0): ?>
          <div class="recipe-meta-item"><span class="icon">Carb</span> <?php echo htmlspecialchars(foovia_format_number($carbs)); ?> g</div>
        <?php endif; ?>
        <?php if ($fat > 0): ?>
          <div class="recipe-meta-item"><span class="icon">Fat</span> <?php echo htmlspecialchars(foovia_format_number($fat)); ?> g</div>
        <?php endif; ?>
        <?php if ($origin !== ''): ?>
          <div class="recipe-meta-item"><span class="icon">Org</span> <?php echo htmlspecialchars($origin); ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="recipe-body">

    <div class="main-col">

      <h2 class="section-heading">
        <span class="badge" style="background:var(--yellow)"></span>
        About this recipe
      </h2>
      <p class="description-text">
        <?php if ($description !== ''): ?>
          <?php echo nl2br(htmlspecialchars($description)); ?>
        <?php else: ?>
          No description available.
        <?php endif; ?>
      </p>

      <div class="ingredients-section">
        <h2 class="section-heading">
          <span class="badge" style="background:var(--green); color:#fff"></span>
          Ingredients
        </h2>

        <?php if (!empty($recipeIngredients)): ?>
          <div class="ingredients-grid">
            <?php foreach ($recipeIngredients as $ingredientRow): ?>
              <?php
                $ingredientName = foovia_clean_text($ingredientRow['name_ing'] ?? '', 'Ingredient');
                $ingredientImagePath = foovia_normalize_image_path($ingredientRow['img_ing'] ?? '', 'images/product-thumb-1.png');
                $ingredientQuantity = foovia_clean_text($ingredientRow['quantity'] ?? '', '');
                $ingredientUnity = foovia_clean_text($ingredientRow['unity'] ?? '', '');
              ?>
              <div class="ingredient-card">
                <img class="ingredient-photo" src="<?php echo htmlspecialchars($ingredientImagePath); ?>" alt="<?php echo htmlspecialchars($ingredientName); ?>">
                <div class="ingredient-name"><?php echo htmlspecialchars($ingredientName); ?></div>
                <?php if ($ingredientQuantity !== '' || $ingredientUnity !== ''): ?>
                  <div class="ingredient-qty"><?php echo htmlspecialchars(trim($ingredientQuantity . ' ' . $ingredientUnity)); ?></div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="description-text" style="margin-top:8px; border-bottom:none; padding-bottom:0;">No ingredients linked to this recipe.</p>
        <?php endif; ?>
      </div>

      <div>
        <h2 class="section-heading">
          <span class="badge" style="background:var(--orange); color:#fff"></span>
          Instructions
        </h2>

        <?php if (!empty($instructionSteps)): ?>
          <div class="instructions-list">
            <?php foreach ($instructionSteps as $index => $stepText): ?>
              <div class="step-card">
                <div class="step-num-badge"><?php echo (int)($index + 1); ?></div>
                <div class="step-content">
                  <div class="step-title">Step <?php echo (int)($index + 1); ?></div>
                  <div class="step-desc"><?php echo htmlspecialchars($stepText); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="description-text" style="margin-top:8px; border-bottom:none; padding-bottom:0;">No instructions available.</p>
        <?php endif; ?>
      </div>

    </div>

    <div class="sidebar">

      <button class="btn-save">Save Recipe</button>
      <button class="btn-log">Log to Daily Tracker</button>

      <div class="macros-card" style="margin-top:20px">
        <h2>Nutrition per serving</h2>
        <div class="macros-grid">
          <div class="macro-item cal">
            <div class="macro-val"><?php echo htmlspecialchars(foovia_format_number($calories)); ?></div>
            <div class="macro-label">Calories</div>
          </div>
          <div class="macro-item prot">
            <div class="macro-val"><?php echo htmlspecialchars(foovia_format_number($protein)); ?>g</div>
            <div class="macro-label">Protein</div>
          </div>
          <div class="macro-item carb">
            <div class="macro-val"><?php echo htmlspecialchars(foovia_format_number($carbs)); ?>g</div>
            <div class="macro-label">Carbs</div>
          </div>
          <div class="macro-item fat">
            <div class="macro-val"><?php echo htmlspecialchars(foovia_format_number($fat)); ?>g</div>
            <div class="macro-label">Fat</div>
          </div>
        </div>

        <div class="macro-bar-wrap">
          <div class="macro-bar-label"><span>Protein</span><span><?php echo htmlspecialchars(foovia_format_number($protein)); ?>g · <?php echo (int)$proteinPct; ?>%</span></div>
          <div class="macro-bar"><div class="macro-bar-fill" style="width:<?php echo (int)$proteinPct; ?>%; background:var(--green)"></div></div>

          <div class="macro-bar-label"><span>Carbs</span><span><?php echo htmlspecialchars(foovia_format_number($carbs)); ?>g · <?php echo (int)$carbPct; ?>%</span></div>
          <div class="macro-bar"><div class="macro-bar-fill" style="width:<?php echo (int)$carbPct; ?>%; background:var(--yellow)"></div></div>

          <div class="macro-bar-label"><span>Fat</span><span><?php echo htmlspecialchars(foovia_format_number($fat)); ?>g · <?php echo (int)$fatPct; ?>%</span></div>
          <div class="macro-bar"><div class="macro-bar-fill" style="width:<?php echo (int)$fatPct; ?>%; background:var(--peach)"></div></div>
        </div>
      </div>

      <div class="sidebar-card">
        <h3>Tags</h3>
        <div class="tag-cloud">
          <?php if (!empty($categoryNames)): ?>
            <?php foreach ($categoryNames as $index => $tagName): ?>
              <?php
                $tagKey = strtolower(trim((string)$tagName));
                $tagColor = $categoryColorsByName[$tagKey] ?? '';
                $tagStyle = '';
                if ($tagColor !== '') {
                  $tagTextColor = foovia_category_text_color($tagColor);
                  $tagStyle = 'background: ' . $tagColor . '; border-color: ' . $tagColor . '; color: ' . $tagTextColor . ';';
                }
              ?>
              <span class="tag"<?php echo $tagStyle !== '' ? ' style="' . htmlspecialchars($tagStyle) . '"' : ''; ?>><?php echo htmlspecialchars($tagName); ?></span>
            <?php endforeach; ?>
          <?php else: ?>
            <span class="tag">No tags</span>
          <?php endif; ?>
        </div>
      </div>

      <div class="sidebar-card">
        <h3>You might also like</h3>
        <?php if (!empty($similarRecipes)): ?>
          <?php foreach ($similarRecipes as $similar): ?>
            <?php
              $similarName = foovia_clean_text($similar['name_rec'] ?? '', 'Recipe');
              $similarCalories = foovia_format_number($similar['cal_rec'] ?? 0);
              $similarInitial = strtoupper(substr($similarName, 0, 1));
            ?>
            <a href="foovia-recipe.php?id_rec=<?php echo (int)($similar['id_rec'] ?? 0); ?>" class="similar-recipe">
              <div class="similar-thumb"><?php echo htmlspecialchars($similarInitial); ?></div>
              <div class="similar-info">
                <strong><?php echo htmlspecialchars($similarName); ?></strong>
                <span><?php echo htmlspecialchars($similarCalories); ?> kcal</span>
              </div>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="font-size:.8rem;color:#888;">No similar recipes found.</p>
        <?php endif; ?>
      </div>

    </div>

  </div>
<?php else: ?>
  <div class="recipe-body">
    <div class="main-col">
      <h2 class="section-heading">
        <span class="badge" style="background:var(--yellow)">!</span>
        Recipe Details
      </h2>
      <p class="description-text"><?php echo htmlspecialchars($error); ?></p>
      <a href="recipe_page.php#recipes" class="nav-cta">Back to Recipes</a>
    </div>
  </div>
<?php endif; ?>


<!-- FOOTER -->
<footer style="background:var(--dark);color:rgba(255,255,255,.45);padding:32px 64px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
  <span style="font-family:'Boldonse',system-ui;color:#F5C842;font-size:1.1rem;">FOOVIA</span>
  <span style="font-size:.82rem;">© 2026 Foovia. All rights reserved.</span>
  <div style="display:flex;gap:20px;">
    <a href="#" style="color:rgba(255,255,255,.4);font-size:.82rem;text-decoration:none;">Privacy</a>
    <a href="#" style="color:rgba(255,255,255,.4);font-size:.82rem;text-decoration:none;">Terms</a>
    <a href="#" style="color:rgba(255,255,255,.4);font-size:.82rem;text-decoration:none;">Support</a>
  </div>
</footer>

<script>
  const saveRecipeBtn = document.querySelector('.btn-save');
  if (saveRecipeBtn) {
    saveRecipeBtn.addEventListener('click', function() {
      if (this.classList.contains('saved')) {
        this.classList.remove('saved');
        this.textContent = 'Save Recipe';
      } else {
        this.classList.add('saved');
        this.textContent = 'Saved';
      }
    });
  }

  const logMealBtn = document.querySelector('.btn-log');
  if (logMealBtn) {
    logMealBtn.addEventListener('click', function() {
      if (this.classList.contains('saved')) {
        this.classList.remove('saved');
        this.textContent = 'Log to Daily Tracker';
      } else {
        this.classList.add('saved');
        this.textContent = 'Logged';
      }
    });
  }
</script>

</body>
</html>
