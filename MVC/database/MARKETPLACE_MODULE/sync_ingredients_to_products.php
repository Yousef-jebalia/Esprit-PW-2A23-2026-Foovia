<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__, 3);
$db = new PDO('mysql:host=127.0.0.1;dbname=foovia_db;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

function resolveIngredientImagePath(string $rootDir, string $imagePath): ?string
{
    $imagePath = trim($imagePath);
    if ($imagePath === '') {
        return null;
    }

    $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $imagePath);
    $candidates = [
        $rootDir . DIRECTORY_SEPARATOR . 'MVC' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'back_office' . DIRECTORY_SEPARATOR . $normalized,
        $rootDir . DIRECTORY_SEPARATOR . 'MVC' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'back_office' . DIRECTORY_SEPARATOR . 'menu_module' . DIRECTORY_SEPARATOR . $normalized,
        $rootDir . DIRECTORY_SEPARATOR . 'MVC' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'front_office' . DIRECTORY_SEPARATOR . 'menu_module' . DIRECTORY_SEPARATOR . $normalized,
        $rootDir . DIRECTORY_SEPARATOR . $normalized,
    ];

    foreach ($candidates as $candidate) {
        $resolved = realpath($candidate);
        if ($resolved !== false && is_file($resolved)) {
            return $resolved;
        }
    }

    return null;
}

function nextTableId(PDO $db, string $table, string $column): int
{
    return (int) $db->query("SELECT COALESCE(MAX($column), 0) + 1 FROM $table")->fetchColumn();
}

function ensureIngredientCategory(PDO $db): ?int
{
    $tableExists = (bool) $db->query("SHOW TABLES LIKE 'categorie'")->fetchColumn();
    if (!$tableExists) {
        return null;
    }

    $statement = $db->prepare('SELECT id_categ FROM categorie WHERE LOWER(name_categ) = LOWER(:name) LIMIT 1');
    $statement->execute(['name' => 'Ingredients']);
    $existingId = $statement->fetchColumn();
    if ($existingId !== false) {
        return (int) $existingId;
    }

    $categoryId = nextTableId($db, 'categorie', 'id_categ');
    $insert = $db->prepare('INSERT INTO categorie (id_categ, name_categ) VALUES (:id_categ, :name_categ)');
    $insert->execute([
        'id_categ' => $categoryId,
        'name_categ' => 'Ingredients',
    ]);

    return $categoryId;
}

function foodCategoryNames(): array
{
    return [
        'Fruits',
        'Vegetables',
        'Leafy Greens',
        'Dairy',
        'Eggs',
        'Meat',
        'Poultry',
        'Fish and Seafood',
        'Plant-Based Protein',
        'Grains and Cereals',
        'Pasta and Rice',
        'Bakery and Baking',
        'Sweeteners',
        'Oils and Fats',
        'Spices and Condiments',
        'Legumes',
        'Nuts and Seeds',
        'Breakfast Food',
        'Sauces and Marinades',
        'Beverages',
        'Snacks',
        'Canned Food',
        'Frozen Food',
    ];
}

function ensureFoodCategories(PDO $db): array
{
    $categoryIds = [];
    $findCategory = $db->prepare('SELECT id_categ FROM categorie WHERE LOWER(name_categ) = LOWER(:name_categ) LIMIT 1');
    $insertCategory = $db->prepare('INSERT INTO categorie (id_categ, name_categ) VALUES (:id_categ, :name_categ)');

    foreach (foodCategoryNames() as $categoryName) {
        $findCategory->execute(['name_categ' => $categoryName]);
        $categoryId = $findCategory->fetchColumn();

        if ($categoryId === false) {
            $categoryId = nextTableId($db, 'categorie', 'id_categ');
            $insertCategory->execute([
                'id_categ' => $categoryId,
                'name_categ' => $categoryName,
            ]);
        }

        $categoryIds[strtolower($categoryName)] = (int) $categoryId;
    }

    return $categoryIds;
}

function classifyFoodProduct(string $name): array
{
    $normalizedName = strtolower(trim($name));
    $normalizedName = str_replace(['_', '-'], ' ', $normalizedName);
    $normalizedName = preg_replace('/\s+/', ' ', $normalizedName) ?? $normalizedName;

    $categoriesByProduct = [
        'apple' => ['Fruits'],
        'apples' => ['Fruits'],
        'banana' => ['Fruits', 'Breakfast Food'],
        'blueberries' => ['Fruits', 'Breakfast Food'],
        'lemon' => ['Fruits', 'Spices and Condiments'],
        'avocado' => ['Fruits', 'Oils and Fats'],
        'tomato' => ['Vegetables'],
        'cucumber' => ['Vegetables'],
        'potatoes' => ['Vegetables'],
        'potato' => ['Vegetables'],
        'carrot' => ['Vegetables'],
        'bell pepper' => ['Vegetables'],
        'onion' => ['Vegetables', 'Spices and Condiments'],
        'garlic' => ['Vegetables', 'Spices and Condiments'],
        'lettuce' => ['Vegetables', 'Leafy Greens'],
        'spinach' => ['Vegetables', 'Leafy Greens'],
        'milk' => ['Dairy', 'Beverages'],
        'yogurt' => ['Dairy', 'Breakfast Food'],
        'butter' => ['Dairy', 'Oils and Fats'],
        'parmesan' => ['Dairy'],
        'cheddar' => ['Dairy'],
        'eggs' => ['Eggs', 'Breakfast Food'],
        'chicken breast' => ['Poultry', 'Meat'],
        'bacon' => ['Meat'],
        'ground beef' => ['Meat'],
        'salmon' => ['Fish and Seafood'],
        'shrimp' => ['Fish and Seafood'],
        'tofu' => ['Plant-Based Protein'],
        'chickpeas' => ['Legumes', 'Plant-Based Protein'],
        'black beans' => ['Legumes', 'Plant-Based Protein'],
        'lentils' => ['Legumes', 'Plant-Based Protein'],
        'almonds' => ['Nuts and Seeds', 'Snacks'],
        'walnuts' => ['Nuts and Seeds', 'Snacks'],
        'rice' => ['Pasta and Rice', 'Grains and Cereals'],
        'rice white' => ['Pasta and Rice', 'Grains and Cereals'],
        'rice (white)' => ['Pasta and Rice', 'Grains and Cereals'],
        'pasta' => ['Pasta and Rice', 'Grains and Cereals'],
        'oats' => ['Grains and Cereals', 'Breakfast Food'],
        'flour' => ['Bakery and Baking', 'Grains and Cereals'],
        'sugar' => ['Sweeteners', 'Bakery and Baking'],
        'honey' => ['Sweeteners', 'Breakfast Food'],
        'olive oil' => ['Oils and Fats'],
        'salt' => ['Spices and Condiments'],
        'paprika' => ['Spices and Condiments'],
        'soy sauce' => ['Sauces and Marinades', 'Spices and Condiments'],
    ];

    return $categoriesByProduct[$normalizedName] ?? ['Vegetables'];
}

function replaceProductCategoryLinks(PDO $db, int $productId, array $categoryNames, array $categoryIds): void
{
    $deleteLinks = $db->prepare('DELETE FROM marchandise_categorie WHERE id_march = :id_march');
    $insertLink = $db->prepare('INSERT INTO marchandise_categorie (id_march, id_categ) VALUES (:id_march, :id_categ)');

    $deleteLinks->execute(['id_march' => $productId]);

    foreach (array_unique($categoryNames) as $categoryName) {
        $key = strtolower($categoryName);
        if (!isset($categoryIds[$key])) {
            continue;
        }

        $insertLink->execute([
            'id_march' => $productId,
            'id_categ' => $categoryIds[$key],
        ]);
    }
}

function firstStoreId(PDO $db): ?int
{
    $tableExists = (bool) $db->query("SHOW TABLES LIKE 'magasin'")->fetchColumn();
    if (!$tableExists) {
        return null;
    }

    $storeId = $db->query('SELECT id_mag FROM magasin ORDER BY id_mag LIMIT 1')->fetchColumn();

    return $storeId === false ? null : (int) $storeId;
}

function availableStoreIds(PDO $db): array
{
    $tableExists = (bool) $db->query("SHOW TABLES LIKE 'magasin'")->fetchColumn();
    if (!$tableExists) {
        return [];
    }

    return array_map('intval', $db->query('SELECT id_mag FROM magasin ORDER BY id_mag')->fetchAll(PDO::FETCH_COLUMN));
}

function normalizeProductName(string $name): string
{
    $normalized = strtolower(trim($name));
    $normalized = str_replace(['_', '-', '(', ')'], ' ', $normalized);

    return preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
}

function realisticStoreIdsForProduct(string $name, array $availableStoreIds): array
{
    if ($availableStoreIds === []) {
        return [];
    }

    $available = array_flip($availableStoreIds);
    $selectStores = static function (array $preferred) use ($available, $availableStoreIds): array {
        $storeIds = array_values(array_filter($preferred, static fn (int $storeId): bool => isset($available[$storeId])));

        return $storeIds !== [] ? array_values(array_unique($storeIds)) : array_slice($availableStoreIds, 0, 1);
    };

    $assignments = [
        'apple' => [1, 3, 5, 8],
        'apples' => [1, 3, 5, 8],
        'orange' => [1, 3, 4, 5],
        'oranges' => [1, 3, 4, 5],
        'banana' => [1, 2, 3, 5],
        'blueberries' => [3, 5, 7],
        'lemon' => [1, 2, 4, 8],
        'avocado' => [3, 5, 7],
        'tomato' => [1, 2, 4, 8],
        'cucumber' => [1, 2, 4, 8],
        'lettuce' => [1, 3, 4],
        'spinach' => [2, 3, 5],
        'potatoes' => [1, 2, 4, 8],
        'potato' => [1, 2, 4, 8],
        'carrot' => [1, 2, 4, 8],
        'bell pepper' => [1, 3, 4, 5],
        'garlic' => [1, 2, 4, 8],
        'onion' => [1, 2, 4, 8],
        'milk' => [2, 3, 5, 7],
        'yogurt' => [2, 3, 5],
        'butter' => [2, 3, 5, 7],
        'eggs' => [1, 2, 3, 8],
        'parmesan' => [3, 5, 7],
        'cheddar' => [2, 3, 5],
        'chicken breast' => [2, 5, 7],
        'ground beef' => [2, 5, 7],
        'bacon' => [3, 5, 7],
        'salmon' => [3, 5, 7],
        'shrimp' => [3, 5, 7],
        'tofu' => [3, 5, 7],
        'chickpeas' => [1, 2, 5, 8],
        'black beans' => [2, 4, 5, 8],
        'lentils' => [1, 2, 4, 8],
        'rice white' => [1, 2, 4, 5, 8],
        'rice' => [1, 2, 4, 5, 8],
        'pasta' => [1, 2, 3, 5],
        'flour' => [1, 2, 4, 8],
        'sugar' => [1, 2, 4, 8],
        'salt' => [1, 2, 4, 8],
        'olive oil' => [2, 3, 5, 7],
        'soy sauce' => [2, 3, 5],
        'paprika' => [1, 2, 5],
        'honey' => [1, 3, 5, 7],
        'almonds' => [2, 3, 5, 7],
        'walnuts' => [2, 3, 5, 7],
        'oats' => [1, 2, 3, 5],
    ];

    $normalizedName = normalizeProductName($name);

    return $selectStores($assignments[$normalizedName] ?? [1, 2, 3, 5]);
}

function replaceProductStoreLinks(PDO $db, int $productId, array $storeIds): void
{
    $deleteLinks = $db->prepare('DELETE FROM vendre WHERE id_march = :id_march');
    $insertLink = $db->prepare('INSERT INTO vendre (id_march, id_mag) VALUES (:id_march, :id_mag)');

    $deleteLinks->execute(['id_march' => $productId]);

    foreach ($storeIds as $storeId) {
        $insertLink->execute([
            'id_march' => $productId,
            'id_mag' => $storeId,
        ]);
    }
}

function buildMarketplaceDescription(string $name, int $protein, int $fat, int $carbs, int $calories): string
{
    $lowerName = strtolower($name);

    $customDescriptions = [
        'chicken breast' => 'Lean chicken breast, ready for grilling, meal prep, salads, and high-protein bowls. A clean everyday choice for balanced meals.',
        'salmon' => 'Fresh salmon with a rich texture and naturally savory flavor. Great for oven baking, pan searing, or pairing with rice and vegetables.',
        'tofu' => 'Soft tofu with a mild taste that absorbs marinades beautifully. Ideal for stir-fries, bowls, soups, and vegetarian protein meals.',
        'eggs' => 'Versatile fresh eggs for breakfast plates, baking, salads, and quick protein-rich snacks.',
        'milk' => 'Fresh milk for smoothies, cereals, coffee, sauces, and everyday cooking.',
        'yogurt' => 'Creamy yogurt that works well for breakfast bowls, sauces, marinades, and light snacks.',
        'flour' => 'All-purpose flour for homemade bread, pastries, pancakes, sauces, and everyday baking.',
        'sugar' => 'Fine sugar for baking, desserts, drinks, and pantry preparation.',
        'salt' => 'Kitchen salt for seasoning, preserving, and bringing out flavor in everyday dishes.',
        'olive oil' => 'Smooth olive oil for salads, marinades, roasting, and light cooking.',
        'butter' => 'Creamy butter for baking, sauteing, spreading, and adding richness to recipes.',
        'rice' => 'Pantry rice that pairs easily with vegetables, proteins, stews, and meal-prep dishes.',
        'pasta' => 'Classic pasta for quick family meals, sauces, salads, and comforting dinner plates.',
        'tomato' => 'Fresh tomatoes with bright flavor for salads, sauces, sandwiches, and cooked dishes.',
        'onion' => 'Fresh onions for soups, sauces, stews, grilled plates, and daily cooking bases.',
        'garlic' => 'Aromatic garlic for marinades, sauces, roasted dishes, and stronger savory flavor.',
        'carrot' => 'Crunchy carrots for salads, soups, juices, roasting, and healthy snacks.',
        'potato' => 'Versatile potatoes for roasting, boiling, fries, mash, soups, and hearty meals.',
        'broccoli' => 'Fresh broccoli for steaming, stir-frying, roasting, and nutrient-rich side dishes.',
        'spinach' => 'Tender spinach leaves for salads, omelets, smoothies, pasta, and quick sauteed sides.',
        'lettuce' => 'Crisp lettuce for fresh salads, wraps, sandwiches, and lighter meals.',
        'apple' => 'Fresh apples for snacks, lunch boxes, desserts, smoothies, and fruit bowls.',
        'banana' => 'Naturally sweet bananas for smoothies, breakfast bowls, baking, and quick energy snacks.',
        'orange' => 'Juicy oranges for fresh snacks, breakfast plates, desserts, and homemade juice.',
        'lemon' => 'Bright lemons for dressings, marinades, tea, desserts, and fresh seasoning.',
        'strawberry' => 'Sweet strawberries for desserts, smoothies, yogurt bowls, and fresh fruit plates.',
        'avocado' => 'Creamy avocado for toast, salads, sandwiches, bowls, and healthy spreads.',
        'cucumber' => 'Crisp cucumbers for salads, sandwiches, dips, and refreshing side dishes.',
        'bell pepper' => 'Colorful bell peppers for salads, stir-fries, stuffing, roasting, and fresh snacks.',
        'mushroom' => 'Fresh mushrooms with earthy flavor for omelets, pasta, sauces, soups, and sauteed dishes.',
        'cheese' => 'Flavorful cheese for sandwiches, pasta, salads, gratins, and snack plates.',
        'chickpeas' => 'Hearty chickpeas for salads, hummus, stews, bowls, and plant-based protein meals.',
        'black beans' => 'Nutritious black beans for wraps, rice bowls, soups, salads, and vegetarian dishes.',
        'lentils' => 'Protein-rich lentils for soups, stews, salads, and filling plant-based meals.',
        'parmesan' => 'Aged parmesan with a sharp, savory flavor for pasta, risotto, salads, and gratins.',
        'cheddar' => 'Rich cheddar cheese for burgers, sandwiches, sauces, baked dishes, and snacks.',
        'bacon' => 'Smoky bacon for breakfast plates, sandwiches, pasta, salads, and savory toppings.',
        'ground beef' => 'Fresh ground beef for burgers, pasta sauces, tacos, casseroles, and quick family meals.',
        'shrimp' => 'Tender shrimp for pasta, rice dishes, salads, skewers, and fast seafood meals.',
        'soy sauce' => 'Savory soy sauce for marinades, stir-fries, noodles, rice dishes, and dipping sauces.',
        'paprika' => 'A warm paprika spice for marinades, roasted vegetables, stews, sauces, and grilled dishes.',
    ];

    $base = $customDescriptions[$lowerName] ?? sprintf(
        'Fresh %s selected for everyday cooking, meal prep, and balanced homemade dishes.',
        $name
    );

    if ($calories <= 0) {
        return $base;
    }

    return sprintf(
        '%s Approximate nutrition per 100g: %dg protein, %dg carbs, %dg fat, %d kcal.',
        $base,
        $protein,
        $carbs,
        $fat,
        $calories
    );
}

$ingredients = $db->query(
    'SELECT id_ing, name_ing, prot_ing, fat_ing, carb_ing, cal_ing, img_ing
     FROM ingrediant
     ORDER BY id_ing'
)->fetchAll();

$findProduct = $db->prepare('SELECT id_march FROM marchandise WHERE LOWER(name_march) = LOWER(:name_march) LIMIT 1');
$insertProduct = $db->prepare(
    'INSERT INTO marchandise
        (id_march, name_march, description_march, price_march, quantity_march, date_expiration_march, point_acces_march, img_march, reserved_count_march)
     VALUES
        (:id_march, :name_march, :description_march, :price_march, :quantity_march, :date_expiration_march, :point_acces_march, :img_march, 0)'
);
$updateProduct = $db->prepare(
    'UPDATE marchandise
     SET description_march = :description_march,
         price_march = :price_march,
         quantity_march = :quantity_march,
         date_expiration_march = :date_expiration_march,
         point_acces_march = :point_acces_march,
         img_march = :img_march
     WHERE id_march = :id_march'
);
$availableStoreIds = availableStoreIds($db);
$inserted = 0;
$updated = 0;
$skippedImages = [];
$recategorized = 0;

$db->beginTransaction();

try {
    ensureIngredientCategory($db);
    $categoryIds = ensureFoodCategories($db);

    foreach ($ingredients as $ingredient) {
        $name = trim((string) $ingredient['name_ing']);
        if ($name === '') {
            continue;
        }

        $imagePath = resolveIngredientImagePath($rootDir, (string) $ingredient['img_ing']);
        if ($imagePath === null) {
            $skippedImages[] = $name;
            continue;
        }

        $imageBinary = file_get_contents($imagePath);
        if ($imageBinary === false) {
            $skippedImages[] = $name;
            continue;
        }

        $protein = (int) $ingredient['prot_ing'];
        $fat = (int) $ingredient['fat_ing'];
        $carbs = (int) $ingredient['carb_ing'];
        $calories = (int) $ingredient['cal_ing'];
        $price = max(1.000, round(($calories > 0 ? $calories / 100 : 1.5) + (($protein + $fat + $carbs) / 200), 3));

        $payload = [
            'name_march' => $name,
            'description_march' => buildMarketplaceDescription($name, $protein, $fat, $carbs, $calories),
            'price_march' => $price,
            'quantity_march' => 30,
            'date_expiration_march' => (new DateTimeImmutable('+30 days'))->format('Y-m-d'),
            'point_acces_march' => 'Foovia ingredient shelf',
            'img_march' => $imageBinary,
        ];

        $findProduct->execute(['name_march' => $name]);
        $productId = $findProduct->fetchColumn();

        if ($productId === false) {
            $productId = nextTableId($db, 'marchandise', 'id_march');
            $insertProduct->execute($payload + ['id_march' => $productId]);
            $inserted++;
        } else {
            $productId = (int) $productId;
            $updateProduct->execute([
                'description_march' => $payload['description_march'],
                'price_march' => $payload['price_march'],
                'quantity_march' => $payload['quantity_march'],
                'date_expiration_march' => $payload['date_expiration_march'],
                'point_acces_march' => $payload['point_acces_march'],
                'img_march' => $payload['img_march'],
                'id_march' => $productId,
            ]);
            $updated++;
        }

        replaceProductStoreLinks($db, $productId, realisticStoreIdsForProduct($name, $availableStoreIds));

        replaceProductCategoryLinks($db, $productId, classifyFoodProduct($name), $categoryIds);
        $recategorized++;
    }

    $products = $db->query('SELECT id_march, name_march FROM marchandise ORDER BY id_march')->fetchAll();
    foreach ($products as $product) {
        replaceProductStoreLinks(
            $db,
            (int) $product['id_march'],
            realisticStoreIdsForProduct((string) $product['name_march'], $availableStoreIds)
        );
        replaceProductCategoryLinks(
            $db,
            (int) $product['id_march'],
            classifyFoodProduct((string) $product['name_march']),
            $categoryIds
        );
    }

    $db->commit();
} catch (Throwable $exception) {
    $db->rollBack();
    throw $exception;
}

echo "Ingredients synced to marketplace products.\n";
echo "Inserted: $inserted\n";
echo "Updated: $updated\n";
echo "Recategorized synced products: $recategorized\n";
if ($skippedImages !== []) {
    echo "Skipped missing images: " . implode(', ', $skippedImages) . "\n";
}
