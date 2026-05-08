<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Model/MARKETPLACE_MODULE/config.php';

$db = Database::getConnection();

$products = [
    [
        'name' => 'Oranges',
        'description' => 'Fresh Tunisian oranges sold by the kilo.',
        'price' => 3.200,
        'quantity' => 36,
        'expires' => '2026-05-08',
        'point' => 'F1',
        'image' => __DIR__ . '/../../View/front_office/MARKETPLACE_MODULE/assets/products/oranges.jpg',
        'stores' => [1, 2, 3, 4, 5],
        'categories' => ['Fruits'],
    ],
    [
        'name' => 'Potatoes',
        'description' => 'Everyday cooking potatoes for fries, tajine, and oven dishes.',
        'price' => 2.800,
        'quantity' => 52,
        'expires' => '2026-05-20',
        'point' => 'V2',
        'image' => __DIR__ . '/../../View/front_office/MARKETPLACE_MODULE/assets/products/potatoes.webp',
        'stores' => [1, 2, 4, 5],
        'categories' => ['Vegetables'],
    ],
    [
        'name' => 'Bananas',
        'description' => 'Sweet bananas ready for breakfast, snacks, and smoothies.',
        'price' => 6.900,
        'quantity' => 28,
        'expires' => '2026-05-06',
        'point' => 'F2',
        'image' => __DIR__ . '/../../View/front_office/MARKETPLACE_MODULE/assets/products/banana.jpg',
        'stores' => [1, 3, 4, 5],
        'categories' => ['Fruits', 'Breakfast Food'],
    ],
    [
        'name' => 'Beef Steak',
        'description' => 'Fresh beef steak cut for grilling and pan-searing.',
        'price' => 42.900,
        'quantity' => 18,
        'expires' => '2026-05-02',
        'point' => 'M1',
        'image' => __DIR__ . '/../../View/front_office/MARKETPLACE_MODULE/assets/products/steak.jpg',
        'stores' => [2, 3, 5],
        'categories' => ['Meat'],
    ],
    [
        'name' => 'Tomatoes',
        'description' => 'Red tomatoes for salads, sauces, and Tunisian market cooking.',
        'price' => 1.600,
        'quantity' => 44,
        'expires' => '2026-05-07',
        'point' => 'V1',
        'image' => __DIR__ . '/../../View/front_office/MARKETPLACE_MODULE/assets/products/tomato.jpg',
        'stores' => [1, 2, 3, 4, 5],
        'categories' => ['Vegetables'],
    ],
];

$findStatement = $db->prepare('SELECT id_march FROM marchandise WHERE LOWER(name_march) = LOWER(:name) LIMIT 1');
$insertStatement = $db->prepare(
    'INSERT INTO marchandise
        (name_march, description_march, price_march, quantity_march, date_expiration_march, point_acces_march, img_march)
     VALUES
        (:name_march, :description_march, :price_march, :quantity_march, :date_expiration_march, :point_acces_march, :img_march)'
);
$updateStatement = $db->prepare(
    'UPDATE marchandise
     SET name_march = :name_march,
         description_march = :description_march,
         price_march = :price_march,
         quantity_march = :quantity_march,
         date_expiration_march = :date_expiration_march,
         point_acces_march = :point_acces_march,
         img_march = :img_march
     WHERE id_march = :id_march'
);
$deleteStoreLinks = $db->prepare('DELETE FROM vendre WHERE id_march = :id_march');
$insertStoreLink = $db->prepare('INSERT INTO vendre (id_march, id_mag) VALUES (:id_march, :id_mag)');
$deleteCategoryLinks = $db->prepare('DELETE FROM marchandise_categorie WHERE id_march = :id_march');
$insertCategoryLink = $db->prepare('INSERT INTO marchandise_categorie (id_march, id_categ) VALUES (:id_march, :id_categ)');
$findCategory = $db->prepare('SELECT id_categ FROM categorie WHERE LOWER(name_categ) = LOWER(:name_categ) LIMIT 1');

$db->beginTransaction();

try {
    foreach ($products as $product) {
        $imageBinary = file_get_contents($product['image']);
        if ($imageBinary === false) {
            throw new RuntimeException('Unable to read image: ' . $product['image']);
        }

        $findStatement->execute(['name' => $product['name']]);
        $productId = $findStatement->fetchColumn();

        $params = [
            'name_march' => $product['name'],
            'description_march' => $product['description'],
            'price_march' => $product['price'],
            'quantity_march' => $product['quantity'],
            'date_expiration_march' => $product['expires'],
            'point_acces_march' => $product['point'],
            'img_march' => $imageBinary,
        ];

        if ($productId === false) {
            $insertStatement->execute($params);
            $productId = (int) $db->lastInsertId();
        } else {
            $params['id_march'] = (int) $productId;
            $updateStatement->execute($params);
            $productId = (int) $productId;
        }

        $deleteStoreLinks->execute(['id_march' => $productId]);
        foreach ($product['stores'] as $storeId) {
            $insertStoreLink->execute([
                'id_march' => $productId,
                'id_mag' => $storeId,
            ]);
        }

        $deleteCategoryLinks->execute(['id_march' => $productId]);
        foreach ($product['categories'] as $categoryName) {
            $findCategory->execute(['name_categ' => $categoryName]);
            $categoryId = $findCategory->fetchColumn();
            if ($categoryId === false) {
                throw new RuntimeException('Missing marketplace category: ' . $categoryName);
            }

            $insertCategoryLink->execute([
                'id_march' => $productId,
                'id_categ' => (int) $categoryId,
            ]);
        }
    }

    $db->commit();
    echo "Selected products imported successfully.\n";
} catch (Throwable $exception) {
    $db->rollBack();
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
