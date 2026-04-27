<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../Model/Marchandise.php';
require_once __DIR__ . '/../../../Model/Magasin.php';

$marchandiseModel = new Marchandise();
$magasinModel = new Magasin();

$products = $marchandiseModel->fetchAllWithStores();
$stores = $magasinModel->fetchAll();
$storesById = [];
$storeLogoUrls = [
    'aziza' => '',
    'mg' => '',
    'monoprix' => '',
    'carrefour' => '',
];
$inventoryByBrand = [
    'aziza' => [],
    'mg' => [],
    'monoprix' => [],
    'carrefour' => [],
];
foreach ($stores as $store) {
    $storesById[(int) $store['id_mag']] = $store;
    if ((int) ($store['has_image'] ?? 0) !== 1) {
        continue;
    }

    $storeName = strtolower((string) $store['name_mag']);
    $logoUrl = '../../../Controller/Magasin_Controller.php?action=image&id=' . (int) $store['id_mag'];

    if ($storeLogoUrls['aziza'] === '' && str_contains($storeName, 'aziza')) {
        $storeLogoUrls['aziza'] = $logoUrl;
    }
    if ($storeLogoUrls['mg'] === '' && (str_contains($storeName, 'mg') || str_contains($storeName, 'magasin general'))) {
        $storeLogoUrls['mg'] = $logoUrl;
    }
    if ($storeLogoUrls['monoprix'] === '' && str_contains($storeName, 'monoprix')) {
        $storeLogoUrls['monoprix'] = $logoUrl;
    }
    if ($storeLogoUrls['carrefour'] === '' && str_contains($storeName, 'carrefour')) {
        $storeLogoUrls['carrefour'] = $logoUrl;
    }
}
$formatPrice = static fn (mixed $price): string => rtrim(rtrim(number_format((float) $price, 3, '.', ''), '0'), '.');

$detectStoreBrand = static function (string $name): ?string {
    $normalized = strtolower($name);
    if (str_contains($normalized, 'aziza')) {
        return 'aziza';
    }
    if (str_contains($normalized, 'monoprix')) {
        return 'monoprix';
    }
    if (str_contains($normalized, 'carrefour')) {
        return 'carrefour';
    }
    if (str_contains($normalized, 'mg') || str_contains($normalized, 'magasin general')) {
        return 'mg';
    }

    return null;
};

foreach ($products as $product) {
    $storeIds = array_filter(array_map('intval', explode(',', (string) ($product['store_ids'] ?? ''))));
    foreach ($storeIds as $storeId) {
        $store = $storesById[$storeId] ?? null;
        if ($store === null) {
            continue;
        }

        $brandKey = $detectStoreBrand((string) $store['name_mag']);
        if ($brandKey === null || !isset($inventoryByBrand[$brandKey])) {
            continue;
        }

        $inventoryByBrand[$brandKey][] = [
            'name' => (string) $product['name_march'],
            'quantity' => (int) $product['quantity_march'],
        ];
    }
}

foreach ($inventoryByBrand as $brandKey => $items) {
    $deduplicated = [];
    foreach ($items as $item) {
        $deduplicated[$item['name']] = $item;
    }
    usort($deduplicated, static fn (array $left, array $right): int => $right['quantity'] <=> $left['quantity']);
    $inventoryByBrand[$brandKey] = array_slice(array_values($deduplicated), 0, 5);
}

$categoryBuckets = [];
foreach ($products as $product) {
    $names = array_filter(array_map('trim', explode(',', (string) ($product['category_names'] ?? ''))));
    foreach ($names as $categoryName) {
        if (!isset($categoryBuckets[$categoryName])) {
            $categoryBuckets[$categoryName] = [];
        }
        if (count($categoryBuckets[$categoryName]) < 4) {
            $categoryBuckets[$categoryName][] = $product;
        }
    }
}

$newArrivals = array_slice($products, 0, 4);
$priceFriendly = $products;
usort($priceFriendly, static fn (array $left, array $right): int => ((float) $left['price_march']) <=> ((float) $right['price_march']));
$priceFriendly = array_slice($priceFriendly, 0, 4);

$highStock = $products;
usort($highStock, static fn (array $left, array $right): int => ((int) $right['quantity_march']) <=> ((int) $left['quantity_march']));
$highStock = array_slice($highStock, 0, 4);
$spotlightProducts = $highStock !== [] ? array_slice($highStock, 0, 5) : array_slice($products, 0, 5);
usort(
    $spotlightProducts,
    static function (array $left, array $right): int {
        $leftIsBanana = str_contains(strtolower((string) $left['name_march']), 'banana');
        $rightIsBanana = str_contains(strtolower((string) $right['name_march']), 'banana');

        if ($leftIsBanana === $rightIsBanana) {
            return 0;
        }

        return $leftIsBanana ? -1 : 1;
    }
);

$recommendedPanels = [];
if ($newArrivals !== []) {
    $recommendedPanels[] = [
        'title' => 'New arrivals in Foovia',
        'caption' => 'Recently published marketplace products',
        'products' => $newArrivals,
    ];
}
if ($priceFriendly !== []) {
    $recommendedPanels[] = [
        'title' => 'Best price picks',
        'caption' => 'Affordable items ready to order',
        'products' => $priceFriendly,
    ];
}
foreach ($categoryBuckets as $categoryName => $bucket) {
    if (count($recommendedPanels) >= 4) {
        break;
    }
    if ($bucket !== []) {
        $recommendedPanels[] = [
            'title' => $categoryName . ' recommendations',
            'caption' => 'Products selected from this food category',
            'products' => array_slice($bucket, 0, 4),
        ];
    }
}
if (count($recommendedPanels) < 4 && $highStock !== []) {
    $recommendedPanels[] = [
        'title' => 'Stocked and ready',
        'caption' => 'Products with the highest available quantity',
        'products' => $highStock,
    ];
}
$recommendedPanels = array_slice($recommendedPanels, 0, 3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace Front Office</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/vendor.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" type="text/css" href="../../assets/css/marketplace.css?v=foovia-hero-hover-brand-1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="foovia-page-loader" data-page-loader>
        <div class="foovia-page-loader__panel">
            <span class="foovia-page-loader__dot"></span>
            <span class="foovia-page-loader__dot"></span>
            <span class="foovia-page-loader__dot"></span>
            <p>Loading Foovia marketplace</p>
        </div>
    </div>
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <defs>
            <symbol xmlns="http://www.w3.org/2000/svg" id="search-icon" viewBox="0 0 24 24"><path fill="currentColor" d="M21.71 20.29L18 16.61A9 9 0 1 0 16.61 18l3.68 3.68a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.39ZM11 18a7 7 0 1 1 7-7a7 7 0 0 1-7 7Z"/></symbol>
            <symbol xmlns="http://www.w3.org/2000/svg" id="user-icon" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8.5" r="3.25"/><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M17.5 19c-.23-2.46-1.43-4.25-5.5-4.25S6.73 16.54 6.5 19"/></g></symbol>
            <symbol xmlns="http://www.w3.org/2000/svg" id="bookmark-icon" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.7"><path d="M18.5 20.2c0 1.03-1.16 1.63-2 1.03l-3.63-2.58a1.5 1.5 0 0 0-1.74 0L7.5 21.23c-.84.6-2-.01-2-1.03V6.2C5.5 3.9 6.4 3 8.7 3h6.6c2.3 0 3.2.9 3.2 3.2Z"/><path stroke-linecap="round" d="M9 8h6"/></g></symbol>
            <symbol xmlns="http://www.w3.org/2000/svg" id="cart-icon" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.7"><path d="M8.5 19.5a1.25 1.25 0 1 0 0 2.5a1.25 1.25 0 0 0 0-2.5Zm8 0a1.25 1.25 0 1 0 0 2.5a1.25 1.25 0 0 0 0-2.5ZM3 4h1.2a1 1 0 0 1 .97.757L5.8 7.3m0 0l1.17 6.1a1 1 0 0 0 .98.81h7.96a1 1 0 0 0 .96-.73l1.44-4.92A1 1 0 0 0 17.35 7.3Z"/><path stroke-linecap="round" d="M7.5 17h9.5"/></g></symbol>
            <symbol xmlns="http://www.w3.org/2000/svg" id="delivery" viewBox="0 0 32 32"><path fill="currentColor" d="m29.92 16.61l-3-7A1 1 0 0 0 26 9h-3V7a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v17a1 1 0 0 0 1 1h2.14a4 4 0 0 0 7.72 0h6.28a4 4 0 0 0 7.72 0H29a1 1 0 0 0 1-1v-7a1 1 0 0 0-.08-.39M23 11h2.34l2.14 5H23ZM9 26a2 2 0 1 1 2-2a2 2 0 0 1-2 2m10.14-3h-6.28a4 4 0 0 0-7.72 0H4V8h17v12.56A4 4 0 0 0 19.14 23M23 26a2 2 0 1 1 2-2a2 2 0 0 1-2 2m5-3h-1.14A4 4 0 0 0 23 20v-2h5Z"/></symbol>
            <symbol xmlns="http://www.w3.org/2000/svg" id="organic-icon" viewBox="0 0 24 24"><path fill="currentColor" d="M0 2.84c1.402 2.71 1.445 5.241 2.977 10.4c1.855 5.341 8.703 5.701 9.21 5.711c.46.726 1.513 1.704 3.926 2.21l.268-1.272c-2.082-.436-2.844-1.239-3.106-1.68l-.005.006c.087-.484 1.523-5.377-1.323-9.352C7.182 3.583 0 2.84 0 2.84m24 .84c-3.898.611-4.293-.92-11.473 3.093a11.879 11.879 0 0 1 2.625 10.05c3.723-1.486 5.166-3.976 5.606-6.466c0 0 1.27-4.716 3.242-6.677M12.527 6.773l-.002-.002v.004zM2.643 5.22s5.422 1.426 8.543 11.543c-2.945-.889-4.203-3.796-4.63-5.168h.006a15.863 15.863 0 0 0-3.92-6.375z"/></symbol>
            <symbol xmlns="http://www.w3.org/2000/svg" id="fresh-icon" viewBox="0 0 24 24"><g fill="none"><path d="M24 0v24H0V0zM12.594 23.258l-.012.002l-.071.035l-.02.004l-.014-.004l-.071-.036c-.01-.003-.019 0-.024.006l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427c-.002-.01-.009-.017-.016-.018m.264-.113l-.014.002l-.184.093l-.01.01l-.003.011l.018.43l.005.012l.008.008l.201.092c.012.004.023 0 .029-.008l.004-.014l-.034-.614c-.003-.012-.01-.02-.02-.022m-.715.002a.023.023 0 0 0-.027.006l-.006.014l-.034.614c0 .012.007.02.017.024l.015-.002l.201-.093l.01-.008l.003-.011l.018-.43l-.003-.012l-.01-.01z"/><path fill="currentColor" d="M20 9a1 1 0 0 1 1 1v1a8 8 0 0 1-8 8H9.414l.793.793a1 1 0 0 1-1.414 1.414l-2.496-2.496a.997.997 0 0 1-.287-.567L6 17.991a.996.996 0 0 1 .237-.638l.056-.06l2.5-2.5a1 1 0 0 1 1.414 1.414L9.414 17H13a6 6 0 0 0 6-6v-1a1 1 0 0 1 1-1m-4.793-6.207l2.5 2.5a1 1 0 0 1 0 1.414l-2.5 2.5a1 1 0 1 1-1.414-1.414L14.586 7H11a6 6 0 0 0-6 6v1a1 1 0 1 1-2 0v-1a8 8 0 0 1 8-8h3.586l-.793-.793a1 1 0 0 1 1.414-1.414"/></g></symbol>
        </defs>
    </svg>

    <header class="foovia-topbar">
        <div class="container-lg">
            <div class="row align-items-center g-3">
                <div class="col-lg-2 col-md-4 foovia-brand-col">
                    <a href="marketplace.php" class="foovia-brand">
                        <img src="../../assets/imges-autre/pic_logo.png" class="foovia-logo-img" alt="Foovia logo">
                        <span class="foovia-brand-text">Foovia</span>
                    </a>
                </div>
                <div class="col-lg-4 col-md-8">
                    <div class="foovia-searchbar">
                        <select class="form-select">
                            <option>All Categories</option>
                            <option>Healthy Food</option>
                            <option>Supplements</option>
                            <option>Snacks</option>
                        </select>
                        <input type="text" class="form-control" placeholder="Search for more than 20,000 products">
                        <button type="button" aria-label="Search">
                            <svg width="22" height="22" aria-hidden="true"><use xlink:href="#search-icon"></use></svg>
                        </button>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="foovia-nav justify-content-lg-center">
                        <a href="marketplace.php">Marketplace</a>
                        <a href="#products">How it works</a>
                        <a href="#aziza-map">Community</a>
                    </nav>
                </div>
                <div class="col-lg-2">
                    <div class="foovia-icons justify-content-lg-end">
                        <span aria-label="Account">
                            <svg width="24" height="24" aria-hidden="true"><use xlink:href="#user-icon"></use></svg>
                        </span>
                        <span aria-label="Saved">
                            <svg width="24" height="24" aria-hidden="true"><use xlink:href="#bookmark-icon"></use></svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section class="foovia-recommend-section">
            <div class="container-lg">
                <div class="foovia-recommend-hero">
                    <div class="foovia-recommend-copy">
                        <span class="foovia-section-chip">Recommended for you</span>
                        <h1>Discover products the <span>Foovia</span> way</h1>
                        <p>A lighter marketplace top section with a moving product spotlight, calmer spacing, and curated picks that are easier to scan.</p>
                        <div class="foovia-hero-actions-lite">
                            <a href="#products" class="foovia-hero-primary-link">Browse marketplace</a>
                            <a href="#products" class="foovia-hero-secondary-link">How it works</a>
                        </div>
                    </div>
                    <?php if ($spotlightProducts !== []): ?>
                        <section class="foovia-spotlight-shell" data-recommend-spotlight>
                            <div class="foovia-spotlight-stage">
                                <?php foreach ($spotlightProducts as $index => $spotlightProduct): ?>
                                    <article class="foovia-spotlight-card<?= $index === 0 ? ' is-active' : '' ?>" data-spotlight-slide>
                                        <div class="foovia-spotlight-copy">
                                            <span class="foovia-spotlight-kicker"><?= htmlspecialchars($spotlightProduct['category_names'] ?: 'Foovia pick', ENT_QUOTES) ?></span>
                                            <h2><?= htmlspecialchars($spotlightProduct['name_march'], ENT_QUOTES) ?></h2>
                                            <p><?= htmlspecialchars($spotlightProduct['description_march'], ENT_QUOTES) ?></p>
                                            <div class="foovia-spotlight-meta">
                                                <strong><?= htmlspecialchars($formatPrice($spotlightProduct['price_march']), ENT_QUOTES) ?> TND</strong>
                                                <span><?= (int) $spotlightProduct['quantity_march'] ?> in stock</span>
                                                <span><?= htmlspecialchars((string) ($spotlightProduct['store_names'] ?: 'Foovia stores'), ENT_QUOTES) ?></span>
                                            </div>
                                            <div class="foovia-spotlight-actions">
                                                <a href="product-details.php?id=<?= (int) $spotlightProduct['id_march'] ?>" class="foovia-spotlight-btn">View product</a>
                                                <a href="#products" class="foovia-spotlight-link">Browse catalog</a>
                                            </div>
                                        </div>
                                        <a href="product-details.php?id=<?= (int) $spotlightProduct['id_march'] ?>" class="foovia-spotlight-media" aria-label="<?= htmlspecialchars($spotlightProduct['name_march'], ENT_QUOTES) ?>">
                                            <span class="foovia-spotlight-floating foovia-spotlight-floating-top">Market fresh</span>
                                            <span class="foovia-spotlight-floating foovia-spotlight-floating-middle">Workout ready</span>
                                            <span class="foovia-spotlight-floating foovia-spotlight-floating-bottom">Fresh everyday</span>
                                            <img src="../../../Controller/Marchandise_Controller.php?action=image&id=<?= (int) $spotlightProduct['id_march'] ?>" alt="<?= htmlspecialchars($spotlightProduct['name_march'], ENT_QUOTES) ?>">
                                        </a>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                            <div class="foovia-spotlight-nav" data-spotlight-nav>
                                <?php foreach ($spotlightProducts as $index => $spotlightProduct): ?>
                                    <button
                                        type="button"
                                        class="foovia-spotlight-dot<?= $index === 0 ? ' is-active' : '' ?>"
                                        data-spotlight-dot
                                        data-spotlight-index="<?= $index ?>"
                                        aria-label="Show <?= htmlspecialchars($spotlightProduct['name_march'], ENT_QUOTES) ?>"
                                    ></button>
                                <?php endforeach; ?>
                            </div>
                            <div class="foovia-recommend-stats">
                                <div><i></i><strong><?= count($products) ?></strong><span>Products live</span></div>
                                <div><i></i><strong><?= count($stores) ?></strong><span>Stores linked</span></div>
                                <div><i></i><strong><?= max(array_sum(array_map(static fn (array $product): int => (int) $product['quantity_march'], $products)), 0) ?></strong><span>Total stock</span></div>
                            </div>
                        </section>
                    <?php endif; ?>
                </div>

                <?php if ($recommendedPanels !== []): ?>
                    <div class="foovia-recommend-subhead">
                        <div>
                            <span class="foovia-recommend-label">Shopping moods</span>
                            <h2>Quick picks without the clutter</h2>
                        </div>
                        <div class="foovia-recommend-tools">
                            <p>Each block stays focused on just a few items so the page feels lighter and easier to browse.</p>
                            <div class="foovia-recommend-scroll">
                                <button type="button" class="foovia-scroll-btn" data-recommend-prev aria-label="Scroll recommendations left">&larr;</button>
                                <button type="button" class="foovia-scroll-btn" data-recommend-next aria-label="Scroll recommendations right">&rarr;</button>
                            </div>
                        </div>
                    </div>
                    <section class="foovia-recommend-promote" data-recommend-promote>
                        <div class="foovia-recommend-promote-slot" data-recommend-promote-slot>
                            <div class="foovia-recommend-promote-empty" data-recommend-empty>
                                <span class="foovia-recommend-empty-chip">Interactive preview</span>
                                <strong>Press a recommendation once to bring it here.</strong>
                                <p>Press the same promoted item again and Foovia will open its product page.</p>
                            </div>
                        </div>
                    </section>
                    <div class="foovia-recommend-grid" data-recommend-track>
                        <?php foreach ($recommendedPanels as $panel): ?>
                            <section class="foovia-recommend-card">
                                <div class="foovia-recommend-card-head">
                                    <h2><?= htmlspecialchars($panel['title'], ENT_QUOTES) ?></h2>
                                    <p><?= htmlspecialchars($panel['caption'], ENT_QUOTES) ?></p>
                                </div>
                                <div class="foovia-mini-grid">
                                    <?php foreach (array_slice($panel['products'], 0, 3) as $recommendedProduct): ?>
                                        <a
                                            href="product-details.php?id=<?= (int) $recommendedProduct['id_march'] ?>"
                                            class="foovia-mini-item"
                                            data-recommend-item
                                            data-product-id="<?= (int) $recommendedProduct['id_march'] ?>"
                                            data-product-name="<?= htmlspecialchars($recommendedProduct['name_march'], ENT_QUOTES) ?>"
                                            data-product-price="<?= htmlspecialchars($formatPrice($recommendedProduct['price_march']), ENT_QUOTES) ?>"
                                            data-product-description="<?= htmlspecialchars($recommendedProduct['description_march'], ENT_QUOTES) ?>"
                                            data-product-category="<?= htmlspecialchars((string) ($recommendedProduct['category_names'] ?: 'Foovia pick'), ENT_QUOTES) ?>"
                                            data-product-stock="<?= (int) $recommendedProduct['quantity_march'] ?>"
                                            data-product-stores="<?= htmlspecialchars((string) ($recommendedProduct['store_names'] ?? 'Selected Foovia stores'), ENT_QUOTES) ?>"
                                            data-product-image="../../../Controller/Marchandise_Controller.php?action=image&id=<?= (int) $recommendedProduct['id_march'] ?>"
                                        >
                                            <span class="foovia-mini-thumb">
                                                <img src="../../../Controller/Marchandise_Controller.php?action=image&id=<?= (int) $recommendedProduct['id_march'] ?>" alt="<?= htmlspecialchars($recommendedProduct['name_march'], ENT_QUOTES) ?>">
                                            </span>
                                            <span class="foovia-mini-name"><?= htmlspecialchars($recommendedProduct['name_march'], ENT_QUOTES) ?></span>
                                            <span class="foovia-mini-meta"><?= htmlspecialchars($formatPrice($recommendedProduct['price_march']), ENT_QUOTES) ?> TND</span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                                <a href="#products" class="foovia-mini-link">Explore all products</a>
                            </section>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="pb-5 foovia-catalog-section" id="products">
            <div class="container-lg">
                <div class="row justify-content-between align-items-end mb-4">
                    <div class="col-lg-6">
                        <h2 class="market-title fw-bold mb-2">Marketplace Catalog</h2>
                        <p class="text-muted mb-0">Search products, filter by store, and verify the publication flow between both interfaces.</p>
                    </div>
                </div>

                <div class="market-search mb-5">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-8">
                            <input type="text" class="form-control" placeholder="Search by product, description, or store" data-product-search>
                        </div>
                        <div class="col-lg-4">
                            <select class="form-select" data-store-filter>
                                <option value="">All stores</option>
                                <?php foreach ($stores as $store): ?>
                                    <option value="<?= htmlspecialchars(strtolower($store['name_mag']), ENT_QUOTES) ?>"><?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row market-grid">
                    <?php foreach ($products as $product): ?>
                        <?php
                            $storeIds = array_filter(explode(',', (string) ($product['store_ids'] ?? '')));
                            $storeNames = array_filter(array_map('trim', explode(',', (string) ($product['store_names'] ?? ''))));
                            $storePayload = [];
                            foreach ($storeIds as $index => $storeId) {
                                $store = $storesById[(int) $storeId] ?? null;
                                $storePayload[] = [
                                    'id' => (int) $storeId,
                                    'name' => $storeNames[$index] ?? (string) ($store['name_mag'] ?? 'Store'),
                                    'address' => (string) ($store['adress_mag'] ?? ''),
                                    'phone' => (string) ($store['phone_mag'] ?? ''),
                                    'email' => (string) ($store['email_mag'] ?? ''),
                                    'image' => '../../../Controller/Magasin_Controller.php?action=image&id=' . (int) $storeId,
                                ];
                            }
                        ?>
                        <div class="col-sm-6 col-lg-4 col-xl-3" data-product-card data-product-name="<?= htmlspecialchars($product['name_march'], ENT_QUOTES) ?>" data-store-name="<?= htmlspecialchars(strtolower((string) ($product['store_names'] ?? '')), ENT_QUOTES) ?>" data-product-description="<?= htmlspecialchars($product['description_march'], ENT_QUOTES) ?>">
                            <article
                                class="market-card"
                                draggable="true"
                                data-drag-product
                                data-product-id="<?= (int) $product['id_march'] ?>"
                                data-product-name="<?= htmlspecialchars($product['name_march'], ENT_QUOTES) ?>"
                                data-product-price="<?= htmlspecialchars($formatPrice($product['price_march']), ENT_QUOTES) ?>"
                                data-product-image="../../../Controller/Marchandise_Controller.php?action=image&id=<?= (int) $product['id_march'] ?>"
                                data-product-stores="<?= htmlspecialchars(json_encode($storePayload), ENT_QUOTES) ?>"
                            >
                                <div class="market-card-media">
                                    <a href="product-details.php?id=<?= (int) $product['id_march'] ?>" class="market-card-image-link">
                                        <img src="../../../Controller/Marchandise_Controller.php?action=image&id=<?= (int) $product['id_march'] ?>" alt="<?= htmlspecialchars($product['name_march'], ENT_QUOTES) ?>">
                                    </a>
                                </div>
                                <div class="market-card-body">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <div>
                                            <h3 class="h5 mb-1"><a href="product-details.php?id=<?= (int) $product['id_march'] ?>" class="market-product-link"><?= htmlspecialchars($product['name_march'], ENT_QUOTES) ?></a></h3>
                                            <span class="market-store-badges">
                                                <?php if ($storePayload === []): ?>
                                                    <span class="market-badge">No store</span>
                                                <?php endif; ?>
                                                <?php foreach ($storePayload as $store): ?>
                                                    <span class="market-badge market-store-hover">
                                                        <?= htmlspecialchars($store['name'], ENT_QUOTES) ?>
                                                        <span class="market-store-popover">
                                                            <img src="<?= htmlspecialchars($store['image'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($store['name'], ENT_QUOTES) ?>">
                                                            <b><?= htmlspecialchars($store['name'], ENT_QUOTES) ?></b>
                                                            <?php if ($store['address'] !== ''): ?>
                                                                <small>Address: <?= htmlspecialchars($store['address'], ENT_QUOTES) ?></small>
                                                            <?php endif; ?>
                                                            <?php if ($store['phone'] !== ''): ?>
                                                                <small>Phone: <?= htmlspecialchars($store['phone'], ENT_QUOTES) ?></small>
                                                            <?php endif; ?>
                                                            <?php if ($store['email'] !== ''): ?>
                                                                <small>Email: <?= htmlspecialchars($store['email'], ENT_QUOTES) ?></small>
                                                            <?php endif; ?>
                                                        </span>
                                                    </span>
                                                <?php endforeach; ?>
                                            </span>
                                        </div>
                                        <span class="market-price"><?= htmlspecialchars($formatPrice($product['price_march']), ENT_QUOTES) ?> TND</span>
                                    </div>
                                    <p class="market-meta mb-3"><?= htmlspecialchars($product['description_march'], ENT_QUOTES) ?></p>
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <span class="market-badge">Stock: <?= (int) $product['quantity_march'] ?></span>
                                        <span class="market-badge">Access: <?= htmlspecialchars($product['point_acces_march'], ENT_QUOTES) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Expires <?= htmlspecialchars($product['date_expiration_march'], ENT_QUOTES) ?></small>
                                        <div class="d-flex gap-2">
                                            <button
                                                type="button"
                                                class="btn btn-success rounded-pill px-3"
                                                data-open-cart-picker
                                                data-product-id="<?= (int) $product['id_march'] ?>"
                                                data-product-name="<?= htmlspecialchars($product['name_march'], ENT_QUOTES) ?>"
                                                data-product-price="<?= htmlspecialchars($formatPrice($product['price_march']), ENT_QUOTES) ?>"
                                                data-product-image="../../../Controller/Marchandise_Controller.php?action=image&id=<?= (int) $product['id_march'] ?>"
                                                data-product-stores="<?= htmlspecialchars(json_encode($storePayload), ENT_QUOTES) ?>"
                                            >Add</button>
                                            <a href="product-details.php?id=<?= (int) $product['id_march'] ?>" class="btn btn-outline-success rounded-pill px-3">Details</a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="market-empty mt-4" data-empty-state style="display: none;">
                    <h3 class="h4 mb-2">No products match this filter</h3>
                    <p class="text-muted mb-0">Try another store or search term.</p>
                </div>

                <?php if ($products === []): ?>
                    <div class="market-empty">
                        <h3 class="h4 mb-2">No marketplace products yet</h3>
                        <p class="text-muted mb-4">Use the back office page to add your first product and it will appear here.</p>
                        <a href="../../back_office/material_able-main/products.php" class="btn btn-success rounded-pill px-4">Open Back Office</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="foovia-map-section" id="aziza-map">
            <div class="container-lg">
                <div class="foovia-map-card">
                    <div class="foovia-map-copy">
                        <p class="foovia-section-eyebrow">Near you</p>
                        <h2>Markets around your location</h2>
                        <p>Allow location access or choose a nearby city. Foovia will place pins for nearby markets, and stores named عزيزة are shown as Aziza.</p>
                        <div class="foovia-map-controls">
                            <select data-aziza-city-select>
                                <option value="auto">Use my location</option>
                                <option value="tunis">Tunis</option>
                                <option value="ariana">Ariana</option>
                                <option value="manouba">Manouba</option>
                                <option value="ben_arous">Ben Arous</option>
                                <option value="sousse">Sousse</option>
                                <option value="sfax">Sfax</option>
                            </select>
                            <button type="button" data-aziza-city-search>Search</button>
                        </div>
                        <span class="foovia-map-status" data-aziza-map-status>Waiting for your location...</span>
                    </div>
                    <div
                        class="foovia-map-canvas"
                        data-aziza-map
                        data-aziza-logo="<?= htmlspecialchars($storeLogoUrls['aziza'], ENT_QUOTES) ?>"
                        data-mg-logo="<?= htmlspecialchars($storeLogoUrls['mg'], ENT_QUOTES) ?>"
                        data-monoprix-logo="<?= htmlspecialchars($storeLogoUrls['monoprix'], ENT_QUOTES) ?>"
                        data-carrefour-logo="<?= htmlspecialchars($storeLogoUrls['carrefour'], ENT_QUOTES) ?>"
                        data-store-inventory="<?= htmlspecialchars(json_encode($inventoryByBrand), ENT_QUOTES) ?>"
                    ></div>
                </div>
            </div>
        </section>

        <footer class="foovia-site-footer">
            <div class="container-lg">
                <div class="foovia-footer-inner">
                    <a href="marketplace.php" class="foovia-footer-brand">
                        <img src="../../assets/imges-autre/pic_logo.png" alt="Foovia logo">
                        <img src="../../assets/imges-autre/pic_name.png" alt="Foovia">
                    </a>
                    <p class="foovia-footer-copy">© 2026 Foovia. All rights reserved.</p>
                    <nav class="foovia-footer-nav" aria-label="Footer">
                        <a href="#!">Privacy</a>
                        <a href="#!">Terms</a>
                        <a href="#!">Support</a>
                        <a href="#!">Contact</a>
                    </nav>
                </div>
            </div>
        </footer>
    </main>

    <button type="button" class="foovia-cart-toggle foovia-floating-cart" data-cart-toggle aria-label="Open cart">
        <svg width="24" height="24" aria-hidden="true"><use xlink:href="#cart-icon"></use></svg>
        <span data-cart-count>0</span>
    </button>

    <div class="foovia-cart-picker" data-cart-picker hidden>
        <div class="foovia-cart-picker-panel">
            <button type="button" class="foovia-picker-close" data-picker-close aria-label="Close">x</button>
            <strong class="foovia-picker-price" data-picker-price>0 TND</strong>
            <p class="foovia-picker-product" data-picker-product-name></p>
            <div class="foovia-quantity-row">
                <span>Quantity</span>
                <input type="text" value="1" data-picker-quantity>
            </div>
            <div class="foovia-store-choice-box">
                <span class="foovia-store-label">Choose store</span>
                <div data-picker-store></div>
            </div>
            <button type="button" class="foovia-cart-btn" data-picker-confirm>Add to cart</button>
            <button type="button" class="foovia-reserve-btn" data-picker-reserve>Reserve</button>
            <p class="foovia-reservation-total" data-picker-reservation-total>0 reservations</p>
        </div>
    </div>

    <div class="foovia-cart-modal" data-cart-modal hidden>
        <div class="foovia-cart-panel">
            <div class="foovia-cart-header">
                <h2>Your cart</h2>
                <button type="button" data-cart-close aria-label="Close cart">x</button>
            </div>
            <div class="foovia-cart-items" data-cart-items></div>
            <div class="foovia-cart-footer">
                <strong>Total: <span data-cart-total>0 TND</span></strong>
                <button type="button" class="foovia-cart-btn" data-cart-checkout>Checkout</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.FOOVIA_RESERVATION_ENDPOINT = '../../../Controller/Marchandise_Controller.php?action=reserve';
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="../../assets/js/frontoffice-filters.js"></script>
    <script src="../../assets/js/frontoffice-recommendations.js?v=spotlight-3"></script>
    <script src="../../assets/js/foovia-cart.js?v=drag-preview-1"></script>
    <script src="../../assets/js/aziza-map.js?v=stock-click-1"></script>
</body>
</html>
