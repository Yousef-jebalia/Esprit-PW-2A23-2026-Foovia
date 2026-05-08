<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../../../Model/MARKETPLACE_MODULE/url_helper.php';
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
if (!$canUseDelivery) {
    header('Location: marketplace.php?delivery=upgrade');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Foovia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/vendor.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/MARKETPLACE_MODULE/assets/css/marketplace.css?v=premium-dark-checkout-2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body class="foovia-checkout-body">
    <header class="foovia-topbar">
        <a href="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/foovia.php" class="foovia-brand">
            <img src="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/assets/Plan%20de%20travail%201%20no%20bg%20(3)%20(1).png" alt="FOOVIA Logo" class="foovia-logo-img">
            FOOVIA
        </a>
        <nav class="foovia-nav" aria-label="Primary">
            <a href="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/foovia.php#features">Features</a>
            <a href="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/foovia.php#how">How it works</a>
            <a href="marketplace.php">Marketplace</a>
            <a href="marketplace.php#aziza-map">Community</a>
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

    <main class="foovia-checkout-page">
        <div class="container-lg">
            <div class="foovia-checkout-hero">
                <span class="foovia-section-chip">Secure checkout</span>
                <h1>Complete your Foovia order</h1>
                <p>Review your selected stores, verify your billing details, and finish your payment in one place.</p>
            </div>

            <div class="foovia-checkout-layout">
                <section class="foovia-checkout-form-card">
                    <div class="foovia-checkout-card-preview">
                        <div class="foovia-bank-chip"></div>
                        <div class="foovia-card-brand" data-card-brand>VISA</div>
                        <strong data-card-preview-number>•••• •••• •••• ••••</strong>
                        <div class="foovia-card-preview-row">
                            <span>
                                <small>Card holder</small>
                                <b data-card-preview-name>Foovia Customer</b>
                            </span>
                            <span>
                                <small>Expires</small>
                                <b data-card-preview-expiry>MM/YY</b>
                            </span>
                        </div>
                    </div>

                    <div class="foovia-checkout-head">
                        <h2>Payment details</h2>
                        <p>Use a realistic test card style form directly inside the project.</p>
                    </div>

                    <div class="foovia-checkout-delivery-card" data-checkout-delivery-card hidden>
                        <span class="foovia-section-chip">Delivery plan</span>
                        <div class="foovia-checkout-delivery-grid">
                            <div><span>Dispatch point</span><strong data-checkout-delivery-point></strong></div>
                            <div><span>Destination</span><strong data-checkout-destination></strong></div>
                            <div><span>Estimated time</span><strong data-checkout-estimate></strong></div>
                            <div><span>Payment</span><strong data-checkout-payment></strong></div>
                            <div><span>Weather</span><strong data-checkout-weather></strong></div>
                        </div>
                    </div>

                    <form class="foovia-checkout-form" data-checkout-form>
                        <div class="foovia-checkout-grid">
                            <label class="foovia-checkout-field">
                                <span>Cardholder name</span>
                                <input type="text" data-field="holder_name" placeholder="Amina Ben Salah">
                                <small data-error-for="holder_name"></small>
                            </label>
                            <label class="foovia-checkout-field">
                                <span>Email address</span>
                                <input type="text" data-field="email" placeholder="amina@example.com">
                                <small data-error-for="email"></small>
                            </label>
                        </div>

                        <label class="foovia-checkout-field">
                            <span>Card number</span>
                            <input type="text" data-field="card_number" placeholder="4242 4242 4242 4242">
                            <small data-error-for="card_number"></small>
                        </label>

                        <div class="foovia-checkout-grid foovia-checkout-grid--triple">
                            <label class="foovia-checkout-field">
                                <span>Expiry</span>
                                <input type="text" data-field="expiry" placeholder="MM/YY">
                                <small data-error-for="expiry"></small>
                            </label>
                            <label class="foovia-checkout-field">
                                <span>CVV</span>
                                <input type="text" data-field="cvv" placeholder="123">
                                <small data-error-for="cvv"></small>
                            </label>
                            <label class="foovia-checkout-field">
                                <span>Phone number</span>
                                <input type="text" data-field="phone" placeholder="+216 20 000 000">
                                <small data-error-for="phone"></small>
                            </label>
                        </div>

                        <div class="foovia-checkout-grid">
                            <label class="foovia-checkout-field">
                                <span>Billing address</span>
                                <input type="text" data-field="address" placeholder="12 Avenue Habib Bourguiba, Tunis">
                                <small data-error-for="address"></small>
                            </label>
                            <label class="foovia-checkout-field">
                                <span>City</span>
                                <input type="text" data-field="city" placeholder="Tunis">
                                <small data-error-for="city"></small>
                            </label>
                        </div>

                        <div class="foovia-checkout-grid">
                            <label class="foovia-checkout-field">
                                <span>Postal code</span>
                                <input type="text" data-field="postal_code" placeholder="1000">
                                <small data-error-for="postal_code"></small>
                            </label>
                            <label class="foovia-checkout-field">
                                <span>Country</span>
                                <input type="text" data-field="country" placeholder="Tunisia">
                                <small data-error-for="country"></small>
                            </label>
                        </div>

                        <div class="foovia-checkout-note">
                            <strong>Accepted cards</strong>
                            <p>Visa, Mastercard, and local test-style debit cards. This screen is a realistic Foovia checkout simulation for your project demo.</p>
                        </div>

                        <div class="foovia-checkout-actions">
                            <a href="marketplace.php" class="foovia-checkout-back">Continue shopping</a>
                            <button type="submit" class="foovia-checkout-submit">Pay now</button>
                        </div>
                    </form>
                </section>

                <aside class="foovia-checkout-summary-card">
                    <div class="foovia-checkout-head">
                        <h2>Order summary</h2>
                        <p>Your selected marketplace products and stores.</p>
                    </div>
                    <div class="foovia-checkout-summary-list" data-checkout-items></div>
                    <div class="foovia-checkout-totals">
                        <div><span>Subtotal</span><strong data-checkout-subtotal>0 TND</strong></div>
                        <div><span>Delivery</span><strong data-checkout-delivery>7.5 TND</strong></div>
                        <div><span>Service fee</span><strong data-checkout-fee>1.9 TND</strong></div>
                        <div class="foovia-checkout-total-line"><span>Total</span><strong data-checkout-total>0 TND</strong></div>
                    </div>
                    <div class="foovia-checkout-summary-note" data-checkout-empty hidden>
                        Your cart is empty right now. Go back to the marketplace and add products first.
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <div class="foovia-payment-processing" data-payment-processing hidden>
        <div class="foovia-payment-processing-panel">
            <span class="foovia-page-loader__dot"></span>
            <span class="foovia-page-loader__dot"></span>
            <span class="foovia-page-loader__dot"></span>
            <p>Authorizing your Foovia payment...</p>
        </div>
    </div>

    <div class="foovia-payment-success" data-payment-success hidden>
        <div class="foovia-payment-success-panel">
            <span class="foovia-payment-success-badge">Paid</span>
            <h2>Payment approved</h2>
            <p>Your order has been confirmed and the checkout simulation is complete.</p>
            <div class="foovia-payment-success-details" data-success-delivery-block hidden>
                <span>Estimated delivery</span>
                <strong data-success-delivery></strong>
            </div>
            <div class="foovia-payment-success-details">
                <span>Reference</span>
                <strong data-success-reference>FV-0000</strong>
            </div>
            <a href="marketplace.php" class="foovia-checkout-submit foovia-checkout-submit--link">Return to marketplace</a>
        </div>
    </div>

    <script>
        window.FOOVIA_APP_BASE = <?= json_encode($appBaseUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    </script>
    <script src="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/MARKETPLACE_MODULE/assets/js/checkout.js?v=checkout-units-1"></script>
    <script src="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/MARKETPLACE_MODULE/assets/js/marketplace-delivery-tracker.js?v=push-notify-3"></script>
    <script src="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/MARKETPLACE_MODULE/assets/js/foovia-market-theme.js"></script>
</body>
</html>
