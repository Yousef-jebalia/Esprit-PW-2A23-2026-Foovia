<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../Model/Marchandise.php';
require_once __DIR__ . '/../../../Model/Magasin.php';

$marchandiseModel = new Marchandise();
$magasinModel = new Magasin();

$products = $marchandiseModel->fetchAllWithStores();
$stores = $magasinModel->fetchAll();
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
    <link rel="stylesheet" type="text/css" href="../../assets/css/marketplace.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .foovia-hero::before {
            background:
                linear-gradient(90deg, rgba(248, 216, 107, 0.94) 0%, rgba(248, 216, 107, 0.92) 34%, rgba(248, 216, 107, 0.18) 58%, rgba(248, 216, 107, 0) 74%),
                url('../../assets/imges-autre/background.jpg') center right/cover no-repeat !important;
        }
        .foovia-brand-mark {
            display: none !important;
        }
    </style>
</head>
<body>
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
                        <img src="../../assets/imges-autre/pic_logo.png" class="foovia-logo-img" style="height: 42px; width: auto; max-height: 42px;" alt="Foovia logo">
                        <img src="../../assets/imges-autre/pic_name.png" class="foovia-name-img" style="height: 22px; width: auto; max-height: 22px;" alt="Foovia">
                        <span class="foovia-brand-mark">❦</span>
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
                        <a href="marketplace.php">HOME</a>
                        <a href="#products">PAGES</a>
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
                        <span aria-label="Cart">
                            <svg width="24" height="24" aria-hidden="true"><use xlink:href="#cart-icon"></use></svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section class="foovia-hero">
            <div class="container-fluid px-0">
                <div class="row g-0 align-items-stretch foovia-hero-main">
                    <div class="col-lg-6">
                        <div class="foovia-hero-copy container-lg">
                            <h1><span class="foovia-accent">Foovia</span> Foods at your Doorsteps</h1>
                            <p>Find fresh food, wellness items, and local store offers in one simple marketplace.</p>
                            <div class="foovia-hero-actions">
                                <a href="#products" class="foovia-btn-primary">START SHOPPING</a>
                                <a href="../../back_office/material_able-main/products.php" class="foovia-btn-dark">JOIN NOW</a>
                            </div>
                            <div class="foovia-stats">
                                <div class="foovia-stat">
                                    <strong><?= max(count($products), 14) ?>+</strong>
                                    <span>Product Varieties</span>
                                </div>
                                <div class="foovia-stat">
                                    <strong>50k+</strong>
                                    <span>Happy Customers</span>
                                </div>
                                <div class="foovia-stat">
                                    <strong><?= max(count($stores), 10) ?>+</strong>
                                    <span>Store Locations</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="foovia-hero-image"></div>
                    </div>
                </div>
                <div class="container-lg foovia-feature-strip">
                    <div class="row g-0">
                        <div class="col-lg-4">
                            <div class="foovia-feature-card foovia-feature-green">
                                <div class="foovia-feature-icon">
                                    <svg width="60" height="60" aria-hidden="true"><use xlink:href="#fresh-icon"></use></svg>
                                </div>
                                <div>
                                    <h3>Fresh from farm</h3>
                                    <p>Products selected from trusted local stores and prepared for everyday healthy choices.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="foovia-feature-card foovia-feature-dark">
                                <div class="foovia-feature-icon">
                                    <svg width="60" height="60" aria-hidden="true"><use xlink:href="#organic-icon"></use></svg>
                                </div>
                                <div>
                                    <h3>100% Organic</h3>
                                    <p>Carefully chosen goods for a cleaner, fresher, and more balanced lifestyle.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="foovia-feature-card foovia-feature-orange">
                                <div class="foovia-feature-icon">
                                    <svg width="60" height="60" aria-hidden="true"><use xlink:href="#delivery"></use></svg>
                                </div>
                                <div>
                                    <h3>Free delivery</h3>
                                    <p>Fast access to store products so your favorite essentials are always within reach.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                        <div class="col-md-6 col-xl-4" data-product-card data-product-name="<?= htmlspecialchars($product['name_march'], ENT_QUOTES) ?>" data-store-name="<?= htmlspecialchars(strtolower((string) ($product['name_mag'] ?? '')), ENT_QUOTES) ?>" data-product-description="<?= htmlspecialchars($product['description_march'], ENT_QUOTES) ?>">
                            <article class="market-card">
                                <div class="market-card-media">
                                    <img src="../../../Controller/Marchandise_Controller.php?action=image&id=<?= (int) $product['id_march'] ?>" alt="<?= htmlspecialchars($product['name_march'], ENT_QUOTES) ?>">
                                </div>
                                <div class="market-card-body">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <div>
                                            <h3 class="h5 mb-1"><?= htmlspecialchars($product['name_march'], ENT_QUOTES) ?></h3>
                                            <span class="market-badge"><?= htmlspecialchars($product['name_mag'] ?? 'No store', ENT_QUOTES) ?></span>
                                        </div>
                                        <span class="market-price"><?= (int) $product['price_march'] ?> TND</span>
                                    </div>
                                    <p class="market-meta mb-3"><?= htmlspecialchars($product['description_march'], ENT_QUOTES) ?></p>
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <span class="market-badge">Stock: <?= (int) $product['quantity_march'] ?></span>
                                        <span class="market-badge">Access: <?= htmlspecialchars($product['point_acces_march'], ENT_QUOTES) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Expires <?= htmlspecialchars($product['date_expiration_march'], ENT_QUOTES) ?></small>
                                        <a href="../../back_office/material_able-main/products.php" class="btn btn-outline-success rounded-pill px-3">Manage</a>
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
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/frontoffice-filters.js"></script>
</body>
</html>
