<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../Model/Marchandise.php';

$marchandiseModel = new Marchandise();
$productId = (int) ($_GET['id'] ?? 0);
$product = $productId > 0 ? $marchandiseModel->findById($productId) : null;
$availability = $productId > 0 ? $marchandiseModel->fetchAvailabilityById($productId) : [];
$recommendedProducts = $productId > 0 ? $marchandiseModel->fetchRecommendedProducts($productId) : [];
$availableStores = array_values(array_filter($availability, static fn (array $store): bool => (int) $store['is_available'] === 1));
$formatPrice = static fn (mixed $price): string => rtrim(rtrim(number_format((float) $price, 3, '.', ''), '0'), '.');

if ($product === null) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product === null ? 'Product Not Found' : htmlspecialchars((string) $product['name_march'], ENT_QUOTES) ?> | Foovia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/vendor.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="../../assets/css/marketplace.css?v=foovia-hero-hover-brand-1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="foovia-detail-body">
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol xmlns="http://www.w3.org/2000/svg" id="cart-icon" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.7"><path d="M8.5 19.5a1.25 1.25 0 1 0 0 2.5a1.25 1.25 0 0 0 0-2.5Zm8 0a1.25 1.25 0 1 0 0 2.5a1.25 1.25 0 0 0 0-2.5ZM3 4h1.2a1 1 0 0 1 .97.757L5.8 7.3m0 0l1.17 6.1a1 1 0 0 0 .98.81h7.96a1 1 0 0 0 .96-.73l1.44-4.92A1 1 0 0 0 17.35 7.3Z"/><path stroke-linecap="round" d="M7.5 17h9.5"/></g></symbol>
    </svg>

    <header class="foovia-topbar">
        <div class="container-lg">
            <div class="row align-items-center g-3">
                <div class="col-lg-3 col-md-4 foovia-brand-col">
                    <a href="marketplace.php" class="foovia-brand">
                        <img src="../../assets/imges-autre/pic_logo.png" class="foovia-logo-img" alt="Foovia logo">
                        <span class="foovia-brand-text">Foovia</span>
                    </a>
                </div>
                <div class="col-lg-6">
                    <nav class="foovia-nav justify-content-lg-center">
                        <a href="marketplace.php">HOME</a>
                        <a href="marketplace.php#products">MARKETPLACE</a>
                    </nav>
                </div>
                <div class="col-lg-3 text-lg-end">
                    <a href="marketplace.php" class="foovia-spotlight-link">Keep shopping</a>
                </div>
            </div>
        </div>
    </header>

    <main class="foovia-detail-page">
        <div class="container-lg">
            <?php if ($product === null): ?>
                <div class="market-empty">
                    <h1 class="h3 mb-3">Product not found</h1>
                    <p class="text-muted">This product does not exist anymore.</p>
                    <a href="marketplace.php" class="btn btn-success rounded-pill px-4">Back to Marketplace</a>
                </div>
            <?php else: ?>
                <?php
                    $imageUrl = '../../../Controller/Marchandise_Controller.php?action=image&id=' . (int) $product['id_march'];
                    $storePayload = array_map(static fn (array $store): array => [
                        'id' => (int) $store['id_mag'],
                        'name' => (string) $store['name_mag'],
                        'phone' => (string) $store['phone_mag'],
                        'address' => (string) $store['adress_mag'],
                        'image' => '../../../Controller/Magasin_Controller.php?action=image&id=' . (int) $store['id_mag'],
                    ], $availableStores);
                ?>
                <div class="foovia-detail-layout">
                    <section class="foovia-detail-image-card">
                        <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES) ?>" alt="<?= htmlspecialchars((string) $product['name_march'], ENT_QUOTES) ?>">
                    </section>

                    <section class="foovia-detail-info">
                        <a href="marketplace.php#products" class="foovia-detail-back">Back to marketplace</a>
                        <h1><?= htmlspecialchars((string) $product['name_march'], ENT_QUOTES) ?></h1>
                        <span class="foovia-arrival">Available in selected Foovia stores</span>
                        <p class="foovia-reference">Reference#: <?= (int) $product['id_march'] ?></p>
                        <p class="foovia-detail-description"><?= htmlspecialchars((string) $product['description_march'], ENT_QUOTES) ?></p>
                        <div class="foovia-detail-specs">
                            <span>Stock: <strong><?= (int) $product['quantity_march'] ?></strong></span>
                            <span>Access point: <strong><?= htmlspecialchars((string) $product['point_acces_march'], ENT_QUOTES) ?></strong></span>
                            <span>Expires: <strong><?= htmlspecialchars((string) $product['date_expiration_march'], ENT_QUOTES) ?></strong></span>
                            <?php if (!empty($product['category_names'])): ?>
                                <span>Categories: <strong><?= htmlspecialchars((string) $product['category_names'], ENT_QUOTES) ?></strong></span>
                            <?php endif; ?>
                        </div>

                        <div class="foovia-availability-card">
                            <h2>Availability</h2>
                            <div class="foovia-availability-list">
                                <?php foreach ($availability as $store): ?>
                                    <div class="foovia-availability-row">
                                        <strong class="foovia-store-hover">
                                            <?php if ((int) ($store['has_image'] ?? 0) === 1): ?>
                                                <img src="../../../Controller/Magasin_Controller.php?action=image&id=<?= (int) $store['id_mag'] ?>" alt="<?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?>">
                                            <?php endif; ?>
                                            <?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?>:
                                            <span class="foovia-store-popover">
                                                <?php if ((int) ($store['has_image'] ?? 0) === 1): ?>
                                                    <img src="../../../Controller/Magasin_Controller.php?action=image&id=<?= (int) $store['id_mag'] ?>" alt="<?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?>">
                                                <?php endif; ?>
                                                <b><?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?></b>
                                                <small><?= htmlspecialchars($store['adress_mag'], ENT_QUOTES) ?></small>
                                                <small><?= htmlspecialchars((string) $store['phone_mag'], ENT_QUOTES) ?></small>
                                                <small><?= htmlspecialchars($store['email_mag'], ENT_QUOTES) ?></small>
                                            </span>
                                        </strong>
                                        <?php if ((int) $store['is_available'] === 1): ?>
                                            <span class="foovia-status-available">Available</span>
                                        <?php else: ?>
                                            <span class="foovia-status-empty">Out of stock</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>

                    <aside class="foovia-purchase-card">
                        <strong><?= htmlspecialchars($formatPrice($product['price_march']), ENT_QUOTES) ?> TND</strong>
                        <div class="foovia-quantity-row">
                            <span>Quantity</span>
                            <input type="text" value="1" data-detail-quantity>
                        </div>
                        <div class="foovia-store-choice-box">
                            <span class="foovia-store-label">Choose store</span>
                            <?php foreach ($availableStores as $store): ?>
                                <label class="foovia-store-choice">
                                    <input
                                        type="radio"
                                        name="detail_store"
                                        value="<?= (int) $store['id_mag'] ?>"
                                        data-store-name="<?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?>"
                                        data-store-image="../../../Controller/Magasin_Controller.php?action=image&id=<?= (int) $store['id_mag'] ?>"
                                        <?= $store === $availableStores[0] ? 'checked' : '' ?>
                                    >
                                    <span><?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <button
                            type="button"
                            class="foovia-cart-btn"
                            data-add-to-cart
                            data-product-id="<?= (int) $product['id_march'] ?>"
                            data-product-name="<?= htmlspecialchars((string) $product['name_march'], ENT_QUOTES) ?>"
                            data-product-price="<?= htmlspecialchars($formatPrice($product['price_march']), ENT_QUOTES) ?>"
                            data-product-image="<?= htmlspecialchars($imageUrl, ENT_QUOTES) ?>"
                        >Add to cart</button>
                        <button
                            type="button"
                            class="foovia-reserve-btn"
                            data-reserve-product
                            data-product-id="<?= (int) $product['id_march'] ?>"
                        >Reserve</button>
                        <p class="foovia-reservation-total" data-reservation-total><?= (int) ($product['reserved_count_march'] ?? 0) ?> reservations</p>
                    </aside>
                </div>

                <?php if ($recommendedProducts !== []): ?>
                    <section class="foovia-recommendations">
                        <p class="foovia-section-eyebrow">Recommended for you</p>
                        <h2>You may also like</h2>
                        <div class="row g-4">
                            <?php foreach ($recommendedProducts as $recommended): ?>
                                <div class="col-md-4">
                                    <article class="market-card">
                                        <div class="market-card-media">
                                            <a href="product-details.php?id=<?= (int) $recommended['id_march'] ?>" class="market-card-image-link">
                                                <img src="../../../Controller/Marchandise_Controller.php?action=image&id=<?= (int) $recommended['id_march'] ?>" alt="<?= htmlspecialchars($recommended['name_march'], ENT_QUOTES) ?>">
                                            </a>
                                        </div>
                                        <div class="market-card-body">
                                            <h3 class="h5"><?= htmlspecialchars($recommended['name_march'], ENT_QUOTES) ?></h3>
                                            <p class="market-meta"><?= htmlspecialchars($recommended['description_march'], ENT_QUOTES) ?></p>
                                            <span class="market-badge"><?= htmlspecialchars($recommended['category_names'] ?? 'Food', ENT_QUOTES) ?></span>
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <span class="market-price"><?= htmlspecialchars($formatPrice($recommended['price_march']), ENT_QUOTES) ?> TND</span>
                                                <a href="product-details.php?id=<?= (int) $recommended['id_march'] ?>" class="btn btn-outline-success rounded-pill px-3">View</a>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <button type="button" class="foovia-cart-toggle foovia-floating-cart" data-cart-toggle aria-label="Open cart">
        <svg width="24" height="24" aria-hidden="true"><use xlink:href="#cart-icon"></use></svg>
        <span data-cart-count>0</span>
    </button>

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

    <script>
        window.FOOVIA_RESERVATION_ENDPOINT = '../../../Controller/Marchandise_Controller.php?action=reserve';
    </script>
    <script src="../../assets/js/foovia-cart.js?v=drag-preview-1"></script>
</body>
</html>
