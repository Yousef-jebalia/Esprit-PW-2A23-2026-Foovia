<?php
require_once __DIR__ . '/../../../controle/controle_Menu.php';

$controller = new Controller_menu();
$recipe = null;
$recipeIngredients = [];
$error = '';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recipe Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 m-0">Recipe Details</h1>
      <a href="index.php" class="btn btn-outline-primary">Back to Home</a>
    </div>

    <?php if (!empty($recipe)): ?>
      <?php
      $imagePath = isset($recipe['img_rec']) ? $recipe['img_rec'] : '';
      $imagePath = str_replace('\\', '/', $imagePath);
      if (!empty($imagePath) && !preg_match('~^(https?://|/|\./|\.\./)~i', $imagePath)) {
          $imagePath = '../../Back Office/' . ltrim($imagePath, '/');
      }
      if (empty($imagePath)) {
          $imagePath = 'images/product-thumb-1.png';
      }
      ?>
      <div class="card border-0 shadow-sm">
        <div class="row g-0">
          <div class="col-md-3 p-3 d-flex align-items-center justify-content-center">
            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($recipe['name_rec']); ?>" class="img-fluid rounded" style="max-height:200px;object-fit:cover;">
          </div>
          <div class="col-md-9">
            <div class="card-body">
              <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <h2 class="h5 mb-0"><?php echo htmlspecialchars($recipe['name_rec']); ?></h2>
                <span class="badge bg-warning text-dark rounded-0 px-2 py-1"><?php echo htmlspecialchars($recipe['categorie_rec']); ?></span>
              </div>
              <p class="mb-3"><strong>Description:</strong> <?php echo htmlspecialchars($recipe['description_rec']); ?></p>
              <div class="row g-2 mb-3">
                <div class="col-sm-3"><strong>Protein:</strong> <?php echo htmlspecialchars($recipe['prot_rec']); ?></div>
                <div class="col-sm-3"><strong>Fat:</strong> <?php echo htmlspecialchars($recipe['fat_rec']); ?></div>
                <div class="col-sm-3"><strong>Carbs:</strong> <?php echo htmlspecialchars($recipe['carb_rec']); ?></div>
                <div class="col-sm-3"><strong>Calories:</strong> <?php echo htmlspecialchars($recipe['cal_rec']); ?></div>
              </div>
              <p class="mb-2"><strong>Instructions:</strong> <?php echo htmlspecialchars($recipe['instruction_rec']); ?></p>
              <p class="mb-3"><strong>Origin:</strong> <?php echo htmlspecialchars($recipe['origin_rec']); ?></p>

              <div>
                <h3 class="h6 mb-2">Ingredients</h3>
                <?php if (!empty($recipeIngredients)): ?>
                  <ul class="list-group list-group-flush">
                    <?php foreach ($recipeIngredients as $ingredientRow): ?>
                      <?php
                        $ingredientImagePath = isset($ingredientRow['img_ing']) ? str_replace('\\', '/', (string)$ingredientRow['img_ing']) : '';
                        if ($ingredientImagePath !== '' && !preg_match('~^(https?://|/|\./|\.\./)~i', $ingredientImagePath)) {
                          $ingredientImagePath = '../../Back Office/' . ltrim($ingredientImagePath, '/');
                        }
                        if ($ingredientImagePath === '') {
                          $ingredientImagePath = 'images/product-thumb-1.png';
                        }
                      ?>
                      <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span class="d-flex align-items-center gap-2">
                          <img src="<?php echo htmlspecialchars($ingredientImagePath); ?>" alt="<?php echo htmlspecialchars((string)($ingredientRow['name_ing'] ?? 'Ingredient')); ?>" style="width:34px;height:34px;object-fit:cover;border-radius:6px;">
                          <span><?php echo htmlspecialchars((string)($ingredientRow['name_ing'] ?? ('Ingredient #' . (int)($ingredientRow['id_ing'] ?? 0)))); ?></span>
                        </span>
                        <span class="text-muted"><?php echo htmlspecialchars((string)($ingredientRow['quantity'] ?? '')); ?> <?php echo htmlspecialchars((string)($ingredientRow['unity'] ?? '')); ?></span>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <p class="text-muted mb-0">No ingredients linked to this recipe.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-info"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
