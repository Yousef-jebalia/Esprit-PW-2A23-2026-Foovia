<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../../Model/MARKETPLACE_MODULE/url_helper.php';

$appBaseUrl = foovia_app_base_url();
$userName = $_SESSION['user_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Waste Impact | Foovia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/vendor.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/MARKETPLACE_MODULE/assets/css/marketplace.css?v=awareness-experience-1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body class="foovia-awareness-body">
    <header class="foovia-topbar">
        <a href="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/foovia.php" class="foovia-brand">
            <img src="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/assets/Plan%20de%20travail%201%20no%20bg%20(3)%20(1).png" alt="FOOVIA Logo" class="foovia-logo-img">
            FOOVIA
        </a>
        <nav class="foovia-nav" aria-label="Primary">
            <a href="marketplace.php">Marketplace</a>
            <a href="#impact">Impact</a>
            <a href="#calculator">Calculator</a>
        </nav>
        <div class="foovia-nav-actions">
            <a href="marketplace.php" class="foovia-nav-btn foovia-nav-backoffice">Back to market</a>
            <button class="foovia-theme-toggle" type="button" aria-label="Switch display mode">
                <svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"></path>
                </svg>
                <svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M21 14.5A8.5 8.5 0 1 1 9.5 3a7 7 0 1 0 11.5 11.5z"></path>
                </svg>
            </button>
            <?php if ($userName !== ''): ?>
                <span class="foovia-nav-btn foovia-nav-user">Welcome, <?= htmlspecialchars((string) $userName, ENT_QUOTES) ?></span>
            <?php endif; ?>
        </div>
    </header>

    <main class="foovia-awareness-page">
        <section class="foovia-awareness-hero">
            <div class="container-lg">
                <div class="foovia-awareness-hero-grid">
                    <div class="foovia-awareness-copy" data-awareness-reveal>
                        <span class="foovia-section-chip">Food waste awareness</span>
                        <h1>Eat smart. Train better. Waste nothing.</h1>
                        <p>Food waste is not only a kitchen problem. It consumes water, soil, energy, fuel, and labor before it ever reaches a bin.</p>
                        <div class="foovia-awareness-actions">
                            <a href="#calculator" class="foovia-hero-primary-link">Estimate your impact</a>
                            <a href="#impact" class="foovia-hero-secondary-link">See the damage</a>
                        </div>
                    </div>
                    <div class="foovia-awareness-visual" data-awareness-reveal>
                        <img src="images/rescued-food.jpg" alt="Discarded vegetables and fruit showing food waste">
                        <div class="foovia-awareness-scanline"></div>
                        <div class="foovia-awareness-float foovia-awareness-float--top">
                            <strong data-count-to="1300000000">0</strong>
                            <span>tons wasted yearly</span>
                        </div>
                        <div class="foovia-awareness-float foovia-awareness-float--bottom">
                            <strong data-count-to="10">0</strong>
                            <span>% of greenhouse gases</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="foovia-awareness-stats" id="impact">
            <div class="container-lg">
                <div class="foovia-awareness-section-head" data-awareness-reveal>
                    <span class="foovia-recommend-label">The hidden cost</span>
                    <h2>Every wasted meal leaves a mark on nature.</h2>
                </div>
                <div class="foovia-awareness-stat-grid">
                    <article data-awareness-reveal>
                        <span>01</span>
                        <strong><b data-count-to="30">0</b>%</strong>
                        <p>About a third of food produced is lost or wasted across farms, transport, shops, restaurants, and homes.</p>
                    </article>
                    <article data-awareness-reveal>
                        <span>02</span>
                        <strong><b data-count-to="25">0</b>x</strong>
                        <p>Methane from rotting food is far more powerful than carbon dioxide over the short term.</p>
                    </article>
                    <article data-awareness-reveal>
                        <span>03</span>
                        <strong><b data-count-to="2">0</b>B</strong>
                        <p>Enough food is wasted yearly to help feed billions of people if handled better.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="foovia-awareness-impact">
            <div class="container-lg">
                <div class="foovia-awareness-impact-grid">
                    <article data-awareness-reveal>
                        <span class="foovia-promoted-chip">Climate</span>
                        <h3>Food in landfills heats the planet.</h3>
                        <p>When food decomposes without oxygen, it releases methane. Reducing food waste is one of the fastest everyday climate actions.</p>
                    </article>
                    <article data-awareness-reveal>
                        <span class="foovia-promoted-chip">Water</span>
                        <h3>Uneaten food still drinks water.</h3>
                        <p>Farms use huge amounts of water to grow food. When the food is thrown away, that water was spent for nothing.</p>
                    </article>
                    <article data-awareness-reveal>
                        <span class="foovia-promoted-chip">Habitats</span>
                        <h3>Demand pushes into wild spaces.</h3>
                        <p>Producing surplus food can pressure forests, wetlands, and grasslands that nature needs to stay balanced.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="foovia-awareness-story">
            <div class="container-lg">
                <div class="foovia-awareness-section-head" data-awareness-reveal>
                    <span class="foovia-recommend-label">Look closer</span>
                    <h2>The problem is visible before the numbers arrive.</h2>
                </div>
                <div class="foovia-awareness-story-grid">
                    <figure data-awareness-reveal>
                        <img src="images/UAE-food-waste.jpg" alt="Large pile of discarded vegetables outdoors">
                        <figcaption>
                            <strong>Waste at scale</strong>
                            <span>When edible produce is discarded in bulk, the environmental cost multiplies quickly.</span>
                        </figcaption>
                    </figure>
                    <figure data-awareness-reveal>
                        <img src="images/Food-waste-1-scaled-e1598419528733-1000x667.jpg" alt="Close-up of wasted fruit and vegetables">
                        <figcaption>
                            <strong>Small losses add up</strong>
                            <span>Every spoiled item carries hidden water, transport, packaging, and storage costs.</span>
                        </figcaption>
                    </figure>
                </div>
            </div>
        </section>

        <section class="foovia-awareness-calculator" id="calculator">
            <div class="container-lg">
                <div class="foovia-awareness-calc-shell" data-awareness-reveal>
                    <div>
                        <span class="foovia-section-chip">Impact calculator</span>
                        <h2>What if your household wasted less food this week?</h2>
                        <p>Move the slider and Foovia will estimate the yearly food rescued from the bin. It is a demo estimate, but it makes the problem easier to feel.</p>
                    </div>
                    <div class="foovia-awareness-calc-panel">
                        <label for="wasteInput">Food saved each week</label>
                        <div class="foovia-awareness-slider-row">
                            <input id="wasteInput" type="range" min="1" max="20" value="5" data-waste-slider>
                            <strong><span data-waste-weekly>5</span> kg</strong>
                        </div>
                        <div class="foovia-awareness-calc-results">
                            <div><span>Yearly food rescued</span><strong><b data-waste-yearly>260</b> kg</strong></div>
                            <div><span>Meals protected</span><strong><b data-waste-meals>520</b></strong></div>
                            <div><span>Nature score</span><strong data-waste-score>Strong start</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="foovia-awareness-actions-panel">
            <div class="container-lg">
                <div class="foovia-awareness-action-shell" data-awareness-reveal>
                    <div>
                        <span class="foovia-section-chip">Start with Foovia</span>
                        <h2>Buy with intention, reserve what you will use, and make waste harder to happen.</h2>
                        <p>Foovia can help users choose focused products, compare options calmly, and avoid impulse buying that expires untouched.</p>
                    </div>
                    <div class="foovia-awareness-action-list">
                        <div><strong>Plan</strong><span>Check your fridge before shopping.</span></div>
                        <div><strong>Store</strong><span>Keep older products visible and easy to use.</span></div>
                        <div><strong>Share</strong><span>Offer extra food before it expires.</span></div>
                    </div>
                    <a href="marketplace.php" class="foovia-checkout-submit foovia-checkout-submit--link">Return to marketplace</a>
                </div>
            </div>
        </section>
    </main>

    <script src="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/MARKETPLACE_MODULE/assets/js/foovia-market-theme.js"></script>
    <script src="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES) ?>/MVC/View/front_office/MARKETPLACE_MODULE/assets/js/food-waste-awareness.js?v=awareness-experience-1"></script>
</body>
</html>
