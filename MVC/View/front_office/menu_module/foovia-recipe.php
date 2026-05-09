<?php
require_once __DIR__ . '/../../../Controller/menu_module/controle_Menu.php';
require_once __DIR__ . '/../../../Controller/menu_module/controle_categ_rec.php';

session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: ../foovia-signin.php');
  exit;
}

$controller = new Controller_menu();
$categoryController = new controle_categ_rec();
$categoryRows = $categoryController->list_categ_rec();
$recipe = null;
$recipeIngredients = [];
$error = '';
$userId = (int)$_SESSION['user_id'];
$is_logged_in = true;
$user_name = $_SESSION['user_name'] ?? 'User';

$recipeId = 0;
if (isset($_GET['id_rec']) && is_numeric($_GET['id_rec'])) {
  $recipeId = (int)$_GET['id_rec'];
} elseif (isset($_POST['id_rec']) && is_numeric($_POST['id_rec'])) {
  $recipeId = (int)$_POST['id_rec'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['favorite_action'] ?? '') === 'toggle' && $recipeId > 0) {
  $isAlreadyFavorite = $controller->is_recipe_favorited_by_user($userId, $recipeId);

  if ($isAlreadyFavorite) {
    if ($controller->remove_recipe_from_user_favorites($userId, $recipeId)) {
      header('Location: foovia-recipe.php?id_rec=' . $recipeId);
      exit;
    }
  } else {
    if ($controller->add_recipe_to_user_favorites($userId, $recipeId)) {
      header('Location: foovia-recipe.php?id_rec=' . $recipeId);
      exit;
    }
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

function foovia_market_ingredient_key($name) {
  $name = strtolower(trim((string)$name));
  $name = str_replace(['_', '-', '(', ')'], ' ', $name);
  return preg_replace('/\s+/', ' ', $name) ?: $name;
}

function foovia_market_product_url_for_ingredient($name) {
  $marketProductIds = [
    'apples' => 1,
    'apple' => 1,
    'chicken breast' => 2,
    'salmon' => 3,
    'tofu' => 4,
    'eggs' => 5,
    'egg' => 5,
    'milk' => 6,
    'yogurt' => 7,
    'flour' => 8,
    'sugar' => 9,
    'salt' => 10,
    'olive oil' => 11,
    'butter' => 12,
    'garlic' => 13,
    'onion' => 14,
    'tomato' => 15,
    'tomatoes' => 15,
    'cucumber' => 16,
    'lettuce' => 17,
    'spinach' => 18,
    'rice white' => 19,
    'rice' => 19,
    'pasta' => 20,
    'potatoes' => 21,
    'potato' => 21,
    'carrot' => 22,
    'bell pepper' => 23,
    'avocado' => 24,
    'lemon' => 25,
    'honey' => 26,
    'almonds' => 27,
    'almond' => 27,
    'walnuts' => 28,
    'walnut' => 28,
    'oats' => 29,
    'banana' => 30,
    'bananas' => 30,
    'blueberries' => 31,
    'blueberry' => 31,
    'chickpeas' => 32,
    'chickpea' => 32,
    'black beans' => 33,
    'black bean' => 33,
    'lentils' => 34,
    'lentil' => 34,
    'parmesan' => 35,
    'cheddar' => 36,
    'bacon' => 37,
    'ground beef' => 38,
    'shrimp' => 39,
    'soy sauce' => 40,
    'paprika' => 41,
    'orange' => 42,
    'oranges' => 42,
  ];

  $key = foovia_market_ingredient_key($name);
  if (!isset($marketProductIds[$key])) {
    return '';
  }

  return '../MARKETPLACE_MODULE/organic-1.0.0/product-details.php?id=' . (int)$marketProductIds[$key];
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

if ($recipeId <= 0) {
  $error = 'Recipe ID is missing or invalid.';
} else {
  $recipe = $controller->get_recipe_by_id($recipeId);
  if (!$recipe) {
    $error = 'Recipe not found.';
  } else {
    $recipeIngredients = $controller->get_recipe_ingredients($recipeId);
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

$isRecipeFavorite = $recipe && $userId > 0 ? $controller->is_recipe_favorited_by_user($userId, (int)$recipe['id_rec']) : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA — <?php echo htmlspecialchars($recipeName); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Boldonse&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="foovia-recipe.css?v=market-badge-size-1">
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
                $marketProductUrl = foovia_market_product_url_for_ingredient($ingredientName);
              ?>
              <?php if ($marketProductUrl !== ''): ?>
                <a class="ingredient-card is-market-available" href="<?php echo htmlspecialchars($marketProductUrl); ?>" aria-label="Open <?php echo htmlspecialchars($ingredientName); ?> in the marketplace">
              <?php else: ?>
                <div class="ingredient-card">
              <?php endif; ?>
                <?php if ($marketProductUrl !== ''): ?>
                  <span class="ingredient-market-badge" title="Available in marketplace">
                    <img src="assets/marketplace-stars-svgrepo-com.svg" alt="">
                  </span>
                <?php endif; ?>
                <img class="ingredient-photo" src="<?php echo htmlspecialchars($ingredientImagePath); ?>" alt="<?php echo htmlspecialchars($ingredientName); ?>">
                <div class="ingredient-name"><?php echo htmlspecialchars($ingredientName); ?></div>
                <?php if ($ingredientQuantity !== '' || $ingredientUnity !== ''): ?>
                  <div class="ingredient-qty"><?php echo htmlspecialchars(trim($ingredientQuantity . ' ' . $ingredientUnity)); ?></div>
                <?php endif; ?>
              <?php if ($marketProductUrl !== ''): ?>
                </a>
              <?php else: ?>
                </div>
              <?php endif; ?>
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

      <form method="post" class="favorite-form">
        <input type="hidden" name="id_rec" value="<?php echo (int)($recipe['id_rec'] ?? $recipeId); ?>">
        <button class="btn-save<?php echo $isRecipeFavorite ? ' saved' : ''; ?>" type="submit" name="favorite_action" value="toggle">
          <?php echo $isRecipeFavorite ? 'Favorited' : 'Favorite'; ?>
        </button>
      </form>
      <div class="log-controls" style="display: flex; gap: 12px; align-items: flex-end; margin-bottom: 24px; background: rgba(0,0,0,0.03); padding: 16px; border-radius: 16px;">
        <div style="flex: 0 0 100px;">
          <label for="log-qty" style="display: block; font-size: 0.75rem; color: #888; font-weight: 600; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Quantity</label>
          <div style="display: flex; align-items: center; gap: 4px; background: #fff; border: 1.5px solid rgba(0,0,0,.08); border-radius: 12px; padding: 8px 12px; transition: border-color 0.2s;">
            <input type="number" id="log-qty" value="100" min="1" step="1" style="width: 100%; border: none; outline: none; font-family: 'DM Sans', sans-serif; font-size: 1rem; font-weight: 600; color: var(--dark);">
            <span style="font-size: 0.85rem; color: #aaa; font-weight: 500;">g</span>
          </div>
        </div>
        <button class="btn-log" style="flex: 1; height: 46px; margin: 0; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; border-radius: 12px;">
          <span>📝</span> Log to Tracker
        </button>
      </div>

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
              $similarImage = foovia_normalize_image_path($similar['img_rec'] ?? '', 'images/product-thumb-1.png');
            ?>
            <a href="foovia-recipe.php?id_rec=<?php echo (int)($similar['id_rec'] ?? 0); ?>" class="similar-recipe">
              <?php if ($similarImage !== ''): ?>
                <img class="similar-thumb" src="<?php echo htmlspecialchars($similarImage); ?>" alt="<?php echo htmlspecialchars($similarName); ?>">
              <?php else: ?>
                <div class="similar-thumb similar-thumb-fallback"><?php echo htmlspecialchars(strtoupper(substr($similarName, 0, 1))); ?></div>
              <?php endif; ?>
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
  const logMealBtn = document.querySelector('.btn-log');
  const qtyInput = document.getElementById('log-qty');
  if (logMealBtn) {
    logMealBtn.addEventListener('click', function() {
      if (this.classList.contains('saved')) return;

      const qty = qtyInput ? qtyInput.value : 100;
      const recipeId = <?php echo (int)($recipe['id_rec'] ?? 0); ?>;
      const today = new Date().toISOString().split('T')[0];

      logMealBtn.innerHTML = '⏳ Logging...';
      logMealBtn.disabled = true;

      fetch('../../../Controller/menu_module/log_meal_handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          date: today,
          meals: [{ id_rec: recipeId, meal_type: 'Recipe Page', qty: qty }]
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          this.classList.add('saved');
          this.innerHTML = '<span>✅</span> Logged';
          this.style.background = 'var(--blue)';
          this.style.borderColor = 'var(--blue)';
          this.style.color = '#fff';
        } else {
          alert('Error: ' + data.error);
          this.innerHTML = '<span>📝</span> Log to Tracker';
          this.disabled = false;
        }
      })
      .catch(err => {
        console.error(err);
        this.innerHTML = '<span>📝</span> Log to Tracker';
        this.disabled = false;
      });
    });
  }
</script>

</body>
</html>
