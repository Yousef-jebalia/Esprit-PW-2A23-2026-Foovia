<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../../../Model/MARKETPLACE_MODULE/url_helper.php';
require_once __DIR__ . '/../../../../Model/MARKETPLACE_MODULE/pricing_helper.php';
$appBaseUrl = foovia_app_base_url();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../foovia-signin.php?redirect=marketplace');
    exit;
}

$userName = $_SESSION['user_name'] ?? '';
$subscriptionUser = 'normal';
try {
    $userDb = new PDO('mysql:host=localhost;dbname=foovia_db;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $userStatement = $userDb->prepare('SELECT subscription_user FROM user WHERE id_user = :id_user LIMIT 1');
    $userStatement->execute(['id_user' => (int) $_SESSION['user_id']]);
    $subscriptionUser = strtolower(trim((string) ($userStatement->fetchColumn() ?: 'normal')));
} catch (Throwable $exception) {
    $subscriptionUser = 'normal';
}
$canUseDelivery = str_contains($subscriptionUser, 'premium') || str_contains($subscriptionUser, 'premuim');

require_once __DIR__ . '/../../../../Model/MARKETPLACE_MODULE/Marchandise.php';

$marchandiseModel = new Marchandise();
$productId = (int) ($_GET['id'] ?? 0);
$product = $productId > 0 ? $marchandiseModel->findById($productId) : null;
$availability = $productId > 0 ? $marchandiseModel->fetchAvailabilityById($productId) : [];
$recommendedProducts = $productId > 0 ? $marchandiseModel->fetchRecommendedProducts($productId) : [];
$availableStores = array_values(array_filter($availability, static fn (array $store): bool => (int) $store['is_available'] === 1));
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/MARKETPLACE_MODULE/assets/css/marketplace.css?v=quantity-total-1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="foovia-detail-body">
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol xmlns="http://www.w3.org/2000/svg" id="cart-icon" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.7"><path d="M8.5 19.5a1.25 1.25 0 1 0 0 2.5a1.25 1.25 0 0 0 0-2.5Zm8 0a1.25 1.25 0 1 0 0 2.5a1.25 1.25 0 0 0 0-2.5ZM3 4h1.2a1 1 0 0 1 .97.757L5.8 7.3m0 0l1.17 6.1a1 1 0 0 0 .98.81h7.96a1 1 0 0 0 .96-.73l1.44-4.92A1 1 0 0 0 17.35 7.3Z"/><path stroke-linecap="round" d="M7.5 17h9.5"/></g></symbol>
    </svg>

    <header class="foovia-topbar">
        <a href="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/foovia.php" class="foovia-brand">
            <img src="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/assets/Plan%20de%20travail%201%20no%20bg%20(3)%20(1).png" alt="FOOVIA Logo" class="foovia-logo-img">
            FOOVIA
        </a>
        <nav class="foovia-nav" aria-label="Primary">
            <a href="marketplace.php#recommended">recommanded</a>
            <a href="food-waste-awareness.php">food waste</a>
            <a href="marketplace.php#products">marketplace</a>
            <a href="marketplace.php#aziza-map">around you</a>
        </nav>
        <div class="foovia-nav-actions">
            <a href="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/foovia-backoffice.php" class="foovia-nav-btn foovia-nav-backoffice">Backoffice</a>
            <button class="foovia-theme-toggle" type="button" aria-label="Switch display mode">
                <svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"></path>
                </svg>
                <svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M21 14.5A8.5 8.5 0 1 1 9.5 3a7 7 0 1 0 11.5 11.5z"></path>
                </svg>
            </button>
            <div class="dropdown">
                <a href="#" class="foovia-nav-btn foovia-nav-user dropdown-toggle" role="button" id="marketUserMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    Welcome, <?php echo htmlspecialchars($userName !== '' ? $userName : 'User'); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="marketUserMenu">
                    <li><a class="dropdown-item" href="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/profile.php">My Account</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/logout.php">Logout</a></li>
                </ul>
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
                    $imageUrl = foovia_url('MVC/Controller/MARKETPLACE_MODULE/Marchandise_Controller.php?action=image&id=' . (int) $product['id_march']);
                    $storePayload = array_map(static fn (array $store): array => [
                        'id' => (int) $store['id_mag'],
                        'name' => (string) $store['name_mag'],
                        'phone' => (string) $store['phone_mag'],
                        'address' => (string) $store['adress_mag'],
                        'image' => foovia_url('MVC/Controller/MARKETPLACE_MODULE/Magasin_Controller.php?action=image&id=' . (int) $store['id_mag']),
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
                                                <img src="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/Controller/MARKETPLACE_MODULE/Magasin_Controller.php?action=image&id=<?= (int) $store['id_mag'] ?>" alt="<?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?>">
                                            <?php endif; ?>
                                            <?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?>:
                                            <span class="foovia-store-popover">
                                                <?php if ((int) ($store['has_image'] ?? 0) === 1): ?>
                                                    <img src="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/Controller/MARKETPLACE_MODULE/Magasin_Controller.php?action=image&id=<?= (int) $store['id_mag'] ?>" alt="<?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?>">
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
                        <strong data-detail-price><?= htmlspecialchars(foovia_format_unit_price($product), ENT_QUOTES) ?></strong>
                        <div class="foovia-quantity-row">
                            <span>Quantity</span>
                            <input type="number" value="1" min="0.1" step="0.1" data-detail-quantity>
                        </div>
                        <small class="foovia-quantity-total" data-detail-total>Total <?= htmlspecialchars(foovia_format_price(foovia_product_unit_price($product)), ENT_QUOTES) ?> TND for 1 <?= htmlspecialchars(foovia_product_unit($product), ENT_QUOTES) ?></small>
                        <div class="foovia-store-choice-box">
                            <span class="foovia-store-label">Choose store</span>
                            <?php foreach ($availableStores as $store): ?>
                                <label class="foovia-store-choice">
                                    <input
                                        type="radio"
                                        name="detail_store"
                                        value="<?= (int) $store['id_mag'] ?>"
                                        data-store-name="<?= htmlspecialchars($store['name_mag'], ENT_QUOTES) ?>"
                                        data-store-image="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/Controller/MARKETPLACE_MODULE/Magasin_Controller.php?action=image&id=<?= (int) $store['id_mag'] ?>"
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
                            data-product-price="<?= htmlspecialchars(foovia_format_price(foovia_product_unit_price($product)), ENT_QUOTES) ?>"
                            data-product-unit="<?= htmlspecialchars(foovia_product_unit($product), ENT_QUOTES) ?>"
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
                                                <img src="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/Controller/MARKETPLACE_MODULE/Marchandise_Controller.php?action=image&id=<?= (int) $recommended['id_march'] ?>" alt="<?= htmlspecialchars($recommended['name_march'], ENT_QUOTES) ?>">
                                            </a>
                                        </div>
                                        <div class="market-card-body">
                                            <h3 class="h5"><?= htmlspecialchars($recommended['name_march'], ENT_QUOTES) ?></h3>
                                            <p class="market-meta"><?= htmlspecialchars($recommended['description_march'], ENT_QUOTES) ?></p>
                                            <span class="market-badge"><?= htmlspecialchars($recommended['category_names'] ?? 'Food', ENT_QUOTES) ?></span>
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <span class="market-price"><?= htmlspecialchars(foovia_format_unit_price($recommended), ENT_QUOTES) ?></span>
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

    <div class="foovia-delivery-planner" data-delivery-planner hidden>
        <div class="foovia-delivery-planner-panel">
            <div class="foovia-cart-header">
                <h2>Delivery planner</h2>
                <button type="button" data-delivery-close aria-label="Close delivery planner">x</button>
            </div>
            <p class="foovia-delivery-copy">Choose the store point that will dispatch your order. Foovia will estimate the arrival time automatically from the map.</p>
            <div class="foovia-delivery-map" data-delivery-map></div>
            <div class="foovia-delivery-summary">
                <div><span>Dispatch point</span><strong data-delivery-point>Choose a point on the map</strong></div>
                <div><span>Destination</span><strong data-delivery-destination>Your current location</strong></div>
                <div><span>Estimated arrival</span><strong data-delivery-estimate>Waiting for selection</strong></div>
            </div>
            <div class="foovia-weather-compact">
                <strong data-delivery-weather-badge>Standard delivery conditions · 7.5 TND</strong>
                <small data-delivery-weather-note>No weather surcharge applied.</small>
            </div>
            <p class="foovia-delivery-contact-note">Allow browser notifications to get your delivery ping when the timer finishes.</p>
            <div class="foovia-delivery-payment">
                <span class="foovia-store-label">Payment method</span>
                <div class="foovia-delivery-methods">
                    <button type="button" class="foovia-delivery-method" data-delivery-method="cash">Cash on delivery</button>
                    <button type="button" class="foovia-delivery-method" data-delivery-method="card">Pay by card</button>
                </div>
            </div>
            <div class="foovia-delivery-actions">
                <button type="button" class="foovia-reserve-btn" data-delivery-cash hidden>Confirm cash order</button>
                <button type="button" class="foovia-cart-btn" data-delivery-card hidden>Continue to card checkout</button>
            </div>
            <p class="foovia-delivery-feedback" data-delivery-feedback></p>
        </div>
    </div>

    <script>
        window.FOOVIA_APP_BASE = <?= json_encode($appBaseUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        window.FOOVIA_RESERVATION_ENDPOINT = '<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/Controller/MARKETPLACE_MODULE/Marchandise_Controller.php?action=reserve';
        window.FOOVIA_USER_SUBSCRIPTION = <?= json_encode($subscriptionUser, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        window.FOOVIA_CAN_DELIVER = <?= $canUseDelivery ? 'true' : 'false' ?>;
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/MARKETPLACE_MODULE/assets/js/foovia-cart.js?v=cart-count-lines-1"></script>
    <script src="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/MARKETPLACE_MODULE/assets/js/marketplace-delivery-tracker.js?v=push-notify-3"></script>
    <script src="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/MARKETPLACE_MODULE/assets/js/foovia-market-theme.js"></script>
</body>
</html>
