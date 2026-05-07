<?php
session_start();
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA — Eat Smart. Live Bold.</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">

</head><link rel="stylesheet" href="foovia.css">
<body>

<!-- NAV -->
<nav>
  <a href="#" class="nav-logo">
    <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" class="nav-logo-img">
    FOOVIA
  </a>
  <ul class="nav-links">
    <li><a href="#features">Features</a></li>
    <li><a href="#how">How it works</a></li>
    <li><a href="marketplace-gateway.php">Marketplace</a></li>
    <li><a href="SUPPORT_MODULE/support_rec_page.php">Support & Community</a></li>
  </ul>
  <div class="nav-actions">
    <a href="foovia-backoffice.php" class="nav-btn nav-backoffice">Backoffice</a>
    <button class="theme-toggle" type="button" aria-label="Switch to dark mode" aria-pressed="false">
      <svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true">
        <circle cx="12" cy="12" r="4"></circle>
        <path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"></path>
      </svg>
      <svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M21 14.5A8.5 8.5 0 1 1 9.5 3a7 7 0 1 0 11.5 11.5z"></path>
      </svg>
    </button>
    <?php if ($is_logged_in): ?>
      <div class="dropdown">
        <a href="#" class="nav-btn dropdown-toggle" role="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
          Welcome, <?php echo htmlspecialchars($user_name); ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
          <li><a class="dropdown-item" href="profile.php">My Account</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="logout.php">Logout</a></li>
        </ul>
      </div>
    <?php else: ?>
      <a href="foovia-signin.php" class="nav-btn nav-signin">Sign In</a>
      <a href="../back_office/USER_MODULE/foovia-signup.php" class="nav-btn nav-signup">Sign Up</a>
    <?php endif; ?>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-text">
    
    <h1 class="hero-title">
      Eat smart.<br>
      Train <span class="accent">better.</span><br>
      Waste <span class="accent2">nothing.</span>
    </h1>
    
    <div class="hero-actions">
      <a href="#" class="btn-primary">Start for free</a>
      <a href="#features" class="btn-secondary">Explore features ↓</a>
    </div>
  </div>

  <div class="hero-visual">
    <div class="hero-card-stack">
      <div class="hcard hcard-pill pill-1">
        <div class="dot"></div>
        Macros tracked ✓
      </div>
      <div class="hcard hcard-main">
        <div class="logo-in-card">
          <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA logo">
        </div>
        <h3>FOOVIA</h3>
        <p>Your personalised nutrition & fitness guide, available 24/7.</p>
      </div>
      <div class="hcard hcard-pill pill-2">
        <div class="dot"></div>
        Workout ready 💪
      </div>
      <div class="hcard hcard-pill pill-3">
        <div class="dot"></div>
        Market fresh 🛒
      </div>
    </div>
  </div>
</section>

<!-- MARQUEE -->
<div class="features-strip">
  <div class="marquee-track">
    <span>Recipes & Ingredients</span><span class="sep">✦</span>
    <span>Macro Tracking</span><span class="sep">✦</span>
    <span>AI Workout Plans</span><span class="sep">✦</span>
    <span>Fresh Marketplace</span><span class="sep">✦</span>
    <span>Personalised Goals</span><span class="sep">✦</span>
    <span>Community Support</span><span class="sep">✦</span>
    <span>Zero Food Waste</span><span class="sep">✦</span>
    <span>Smart Reminders</span><span class="sep">✦</span>
    <!-- duplicate for seamless loop -->
    <span>Recipes & Ingredients</span><span class="sep">✦</span>
    <span>Macro Tracking</span><span class="sep">✦</span>
    <span>AI Workout Plans</span><span class="sep">✦</span>
    <span>Fresh Marketplace</span><span class="sep">✦</span>
    <span>Personalised Goals</span><span class="sep">✦</span>
    <span>Community Support</span><span class="sep">✦</span>
    <span>Zero Food Waste</span><span class="sep">✦</span>
    <span>Smart Reminders</span><span class="sep">✦</span>
  </div>
</div>

<!-- FEATURES -->
<section class="section" id="features">
  <p class="section-label">What we offer</p>
  <h2 class="section-title features-title">Every tool you need,  in one <br>plate.</h2>

  <div class="feat-grid">

    <div class="feat-row">
      <div class="feat-card photo-card photo-recipes">
        <span class="feat-icon">🍽️</span>
        <div class="feat-num">01 — Recipes</div>
        <h3>Recipes from your fridge</h3>
        <p>Snap a photo of your ingredients and Foovia generates personalised recipes instantly. Meals adapted to your dietary goals, allergies, and preferences, no guesswork needed.</p>
        <span class="feat-tag">AI-powered</span>
         <a href="recipes.html" class="feat-btn">Explore Recipes</a>
      </div>

      <div class="feat-card photo-card photo-macro-tracking">
        <span class="feat-icon">📊</span>
        <div class="feat-num">02 — Track</div>
        <h3>Goals & Macros</h3>
        <p>Track macros, exercises, and supplements. Photograph your meal for instant nutritional estimates. Get hydration and medicine reminders.</p>
        <span class="feat-tag">Smart reminders</span>
         <a href="TRACK_MODULE/tracking.php" class="feat-btn">Start Tracking</a>
      </div>

      <div class="feat-card photo-card photo-sport">
        <span class="feat-icon">🏋️</span>
        <div class="feat-num">03 — Sport</div>
        <h3>Body-mapped workouts</h3>
        <p>Select the body part you want to train on an interactive mannequin. Get plans tailored to your injuries, fitness level, and ambitions.</p>
        <span class="feat-tag">Injury-aware</span>
         <a href="SPORT_MOULE/Exercice.php" class="feat-btn">Build Workout</a> 
      </div>
    </div>

    <div class="feat-row">
      <div class="feat-card photo-card photo-marketplace">
        <span class="feat-icon">🛒</span>
        <div class="feat-num">04 — Marketplace</div>
        <h3>Fresh. Local. Zero-waste.</h3>
        <p>Connect directly with local producers. Buy surplus food before it's wasted, win for your wallet, win for the planet.</p>
        <span class="feat-tag">Eco-friendly</span>
         <a href="marketplace-gateway.php" class="feat-btn">Browse Market</a>
      </div>

      <div class="feat-card photo-card photo-survey-user">
        <span class="feat-icon">👤</span>
        <div class="feat-num">05 — Onboarding</div>
        <h3>Built around you</h3>
        <p>Our intake survey understands your goals, health challenges, and lifestyle before your first session. Secured with 2FA from day one.</p>
        <span class="feat-tag">Personalised</span>
         <a href="../back_office/USER_MODULE/foovia-survey.php" class="feat-btn">Get Started</a>
      </div>

      <div class="feat-card photo-card photo-support">
        <span class="feat-icon">💬</span>
        <div class="feat-num">06 — Community</div>
        <h3>Support that never sleeps</h3>
        <p>Ticket-based issue tracking, AI chatbot, and user-led discussion threads. Earn rewards for being a helpful community member.</p>
        <span class="feat-tag">Hybrid support</span>
         <a href="community.html" class="feat-btn">Join Community</a>
      </div>
    </div>

  </div>
</section>

<!-- HOW IT WORKS -->
<section class="how" id="how">
  <p class="section-label">How it works</p>
  <h2 class="section-title how-title">From signup to your first meal in<br> minutes.</h2>
  <div class="steps">
    <div class="step">
      <div class="step-dot"></div>
      <div class="step-num">01</div>
      <h3>Create profile</h3>
      <p>Complete a short health survey. We map your goals, restrictions, and challenges to build your personalised plan.</p>
    </div>
    <div class="step">
      <div class="step-dot step-dot-yellow"></div>
      <div class="step-num">02</div>
      <h3>Snap & track</h3>
      <p>Photograph ingredients or meals. Foovia calculates macros and suggests what to cook next based on your goals.</p>
    </div>
    <div class="step">
      <div class="step-dot step-dot-orange"></div>
      <div class="step-num">03</div>
      <h3>Train smarter</h3>
      <p>Tap the mannequin, pick your target muscles, and get a workout plan built for your body and today's energy level.</p>
    </div>
    <div class="step">
      <div class="step-dot step-dot-peach"></div>
      <div class="step-num">04</div>
      <h3>Shop & connect</h3>
      <p>Browse the marketplace for fresh, local, and surplus produce — reducing waste while fuelling your healthy lifestyle.</p>
    </div>
  </div>
</section>

<!-- MARKETPLACE -->
<section class="marketplace" id="marketplace">
  <div class="mkt-text">
    <p class="section-label">Marketplace</p>
    <h2 class="section-title">Fresh from producer to plate.</h2>
    <p>Our marketplace connects you directly with local farmers and food producers. Buy surplus items at great prices — tackling food waste while keeping your kitchen stocked with quality ingredients.</p>
    <a href="marketplace-gateway.php" class="btn-primary btn-primary-start">Browse the market</a>
  </div>
  <div class="mkt-visual">
    <div class="mkt-cards">
      <div class="mkt-item">
        <div class="mkt-item-icon">🥦</div>
        <div class="mkt-item-body">
          <strong>Organic Broccoli</strong>
          <span>Local Farm — 800g</span>
        </div>
        <div class="mkt-item-price">0.90 DT</div>
      </div>
      <div class="mkt-item">
        <div class="mkt-item-icon">🍅</div>
        <div class="mkt-item-body">
          <strong>Sun-dried Tomatoes</strong>
          <span>Surplus — 500g</span>
        </div>
        <div class="mkt-item-price">1.20 DT</div>
      </div>
      <div class="mkt-item">
        <div class="mkt-item-icon">🥚</div>
        <div class="mkt-item-body">
          <strong>Free-range Eggs</strong>
          <span>Ferme Nahli — 12 pcs</span>
        </div>
        <div class="mkt-item-price">4.50 DT</div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="stats">
  <div class="stat">
    <div class="stat-num">6+</div>
    <div class="stat-label">Core features</div>
  </div>
  <div class="stat">
    <div class="stat-num">AI</div>
    <div class="stat-label">Macro scan from photo</div>
  </div>
  <div class="stat">
    <div class="stat-num">0</div>
    <div class="stat-label">Food waste goal</div>
  </div>
  <div class="stat">
    <div class="stat-num">24/7</div>
    <div class="stat-label">Community support</div>
  </div>
</div>

<!-- CTA -->
<section class="cta-section" id="community">
  <p class="section-label">Ready to start?</p>
  <h2 class="cta-title">Your healthiest chapter<br><em>starts here.</em></h2>
  <p>Join Foovia and get a personalised nutrition, fitness, and shopping experience — all in one place.</p>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-brand">
    <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" class="footer-logo-img">
    FOOVIA
  </div>
  <p>© 2026 Foovia. All rights reserved.</p>
  <ul class="footer-links">
    <li><a href="#">Privacy</a></li>
    <li><a href="#">Terms</a></li>
    <li><a href="#">Support</a></li>
    <li><a href="#">Contact</a></li>
  </ul>
</footer>

<script>
  (function() {
    const root = document.documentElement;
    const toggle = document.querySelector('.theme-toggle');

    const setTheme = (theme) => {
      const isDark = theme === 'dark';
      root.setAttribute('data-theme', theme);
      root.style.colorScheme = theme;
      toggle.setAttribute('aria-pressed', String(isDark));
      toggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
    };

    const stored = localStorage.getItem('theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initialTheme = stored || (prefersDark ? 'dark' : 'light');
    setTheme(initialTheme);

    toggle.addEventListener('click', () => {
      const currentTheme = root.getAttribute('data-theme') || 'light';
      const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
      localStorage.setItem('theme', nextTheme);
      setTheme(nextTheme);
    });
  })();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

