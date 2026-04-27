<?php

declare(strict_types=1);
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
    <link rel="stylesheet" type="text/css" href="../../assets/css/marketplace.css?v=foovia-checkout-1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body class="foovia-checkout-body">
    <header class="foovia-topbar">
        <div class="container-lg">
            <div class="row align-items-center g-3">
                <div class="col-lg-3 col-md-4 foovia-brand-col">
                    <a href="marketplace.php" class="foovia-brand">
                        <img src="../../assets/imges-autre/pic_logo.png" class="foovia-logo-img" alt="Foovia logo">
                        <img src="../../assets/imges-autre/pic_name.png" class="foovia-name-img" alt="Foovia">
                    </a>
                </div>
                <div class="col-lg-6">
                    <nav class="foovia-nav justify-content-lg-center">
                        <a href="marketplace.php">HOME</a>
                        <a href="marketplace.php#products">MARKETPLACE</a>
                        <a href="checkout.php">CHECKOUT</a>
                    </nav>
                </div>
                <div class="col-lg-3 text-lg-end">
                    <a href="marketplace.php" class="foovia-spotlight-link">Back to shopping</a>
                </div>
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
            <div class="foovia-payment-success-details">
                <span>Reference</span>
                <strong data-success-reference>FV-0000</strong>
            </div>
            <a href="marketplace.php" class="foovia-checkout-submit foovia-checkout-submit--link">Return to marketplace</a>
        </div>
    </div>

    <script src="../../assets/js/checkout.js?v=foovia-checkout-1"></script>
</body>
</html>
