<?php
session_start();
include_once(__DIR__ . '/../../Controller/Controller_user.php');
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';
$user_subscription = 'free';

if ($is_logged_in) {
    $controller = new Controller_user();
    $user_data = $controller->get_user($_SESSION['user_id']);
    $user_subscription = $user_data['subscription_user'] ?? 'free';
  if (!isset($_SESSION['role_user'])) {
    $_SESSION['role_user'] = $user_data['role_user'] ?? 'user';
  }
}

$nav_pages = [
    ['label' => 'Home', 'href' => 'foovia.php'],
    ['label' => 'Recipes', 'href' => 'menu_module/recipe_page.php'],
    ['label' => 'Tracking', 'href' => 'TRACK_MODULE/tracking.php'],
  ['label' => 'Sport', 'href' => 'SPORT_MOULE/Exercice.php'],
    ['label' => 'Marketplace', 'href' => 'marketplace-gateway.php'],
    ['label' => 'Support', 'href' => 'SUPPORT_MODULE/support_rec_page.php'],
];

$current_page = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'foovia.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="assets/Plan de travail 1 no bg (3) (1).png">
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA — Eat Smart. Live Bold.</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
<link rel="stylesheet" href="foovia.css?v=20260527-1">
<style>
    /* Premium Badge Navigation Component */
    .premium-badge-nav {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, #E8B84B 0%, #F0A830 100%);
      border-radius: 50%;
      color: #fff;
      box-shadow: 0 4px 12px rgba(232, 184, 75, 0.3);
      margin-left: 10px;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      border: 2px solid #fff;
      flex-shrink: 0;
    }
    .premium-badge-nav:hover {
      transform: scale(1.1) rotate(5deg);
      box-shadow: 0 6px 16px rgba(232, 184, 75, 0.4);
    }
    .premium-icon-nav {
      width: 22px;
      height: 22px;
      filter: brightness(0) invert(1);
    }

    /* Floating Premium Button */
    .floating-premium-btn {
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 65px;
      height: 65px;
      background: linear-gradient(135deg, #E8B84B 0%, #F0A830 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 10px 25px rgba(232, 184, 75, 0.5);
      cursor: pointer;
      z-index: 9999;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      border: 3px solid #fff;
      text-decoration: none;
    }

    .floating-premium-btn:hover {
      transform: scale(1.15) rotate(12deg);
      box-shadow: 0 15px 35px rgba(232, 184, 75, 0.6);
    }

    .floating-premium-btn .premium-icon-large {
      width: 32px;
      height: 32px;
      filter: brightness(0) invert(1);
    }

    .floating-premium-btn::after {
      content: "Go Premium";
      position: absolute;
      right: 80px;
      background: rgba(255, 255, 255, 0.95);
      color: #F0A830;
      padding: 8px 16px;
      border-radius: 12px;
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      font-size: 0.85rem;
      white-space: nowrap;
      opacity: 0;
      transform: translateX(20px);
      transition: all 0.3s ease;
      pointer-events: none;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      border: 1px solid #E8B84B;
    }

    .floating-premium-btn:hover::after {
      opacity: 1;
      transform: translateX(0);
    }
</style>

</head>
<body>

<!-- NAV -->
<nav class="site-nav">
  <div class="nav-left" style="display:flex;align-items:center;gap:2px;margin-left:0;">
    <button class="nav-sidebar-toggle" type="button" aria-label="Open page list" aria-controls="navSidebar" aria-expanded="false" style="width:54px;height:54px;border-radius:12px;gap:4px;padding:0;display:inline-flex;flex-direction:column;align-items:center;justify-content:center;background:rgba(255,255,255,.72);border-color:rgba(17,16,8,.18);margin-right:8px;">
      <span style="width:26px;height:4px;border-radius:999px;display:block;background:#111008;"></span>
      <span style="width:26px;height:4px;border-radius:999px;display:block;background:#111008;"></span>
      <span style="width:26px;height:4px;border-radius:999px;display:block;background:#111008;"></span>
    </button>
    <a href="#" class="nav-logo" style="margin-left:0;">
      <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" class="nav-logo-img">
      FOOVIA
    </a>
  </div>
  <ul class="nav-links">
    <li><a href="#features">Features</a></li>
    <li><a href="#how">How it works</a></li>
    <li><a href="marketplace-gateway.php">Marketplace</a></li>
    <li><a href="SUPPORT_MODULE/support_rec_page.php">Support & Community</a></li>
  </ul>
  <div class="nav-actions">
    <?php if ((isset($_SESSION['role_user']) && strtolower(trim($_SESSION['role_user'])) === 'admin') || (isset($userData) && strtolower(trim($userData['role_user'] ?? '')) === 'admin')): ?>
      <a href="foovia-backoffice.php" class="nav-btn nav-backoffice">Backoffice</a>
    <?php endif; ?>
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
    <?php if ($is_logged_in && ($user_subscription === 'premium' || $user_subscription === 'elite')): ?>
      <div class="premium-badge-nav" title="Premium Member" onclick="window.location.href='foovia-premium.php'" style="display:flex;align-items:center;justify-content:center;width:40px;height:40px;background:linear-gradient(135deg,#E8B84B 0%,#F0A830 100%);border-radius:50%;color:#fff;box-shadow:0 4px 12px rgba(232,184,75,0.3);margin-left:10px;cursor:pointer;transition:all 0.3s cubic-bezier(0.175,0.885,0.32,1.275);border:2px solid #fff;flex-shrink:0;">
        <img src="assets/crown-svgrepo-com%20(1).svg" class="premium-icon-nav" alt="Premium" style="width:22px;height:22px;filter:brightness(0) invert(1);">
      </div>
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
         <a href="menu_module/recipe_page.php" class="feat-btn">Explore Recipes</a>
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
         <a href="SUPPORT_MODULE/support_rec_page.php" class="feat-btn">Join Community</a>
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
        <div class="mkt-item-icon">
          <svg width="28" height="28" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path style="fill:#71A517;" d="M358.863,261.909c-3.605,9.007-7.793,20.107-12.178,32.966
    c-16.997,49.815-36.943,126.092-36.943,209.815H202.245c0-83.723-19.946-160-36.931-209.815
    c-4.386-12.872-8.573-23.972-12.178-32.979c10.989-1.437,21.011-5.909,29.213-12.55c11.088,14.42,26.314,25.484,43.893,31.443
    c1.747,11.509,3.481,24.046,5.092,37.488c0.458,3.704,0.892,7.483,1.313,11.323c1.338,11.906,11.36,20.937,23.34,20.937H256
    c11.98,0,22.015-9.031,23.34-20.937c0.421-3.841,0.855-7.619,1.301-11.323c1.611-13.442,3.345-25.979,5.117-37.488
    c17.567-5.959,32.793-17.022,43.881-31.443C337.852,255.988,347.875,260.472,358.863,261.909z"/>
            <g>
              <path style="fill:#648E13;" d="M196.875,300.312c8.621,7.128,18.546,12.737,29.367,16.405c1.672,0.57,3.37,1.09,5.092,1.561
        c-1.611-13.442-3.345-25.979-5.092-37.488c-12.966-4.396-24.644-11.573-34.333-20.82l0,0c-1.698-1.62-3.335-3.304-4.908-5.047
        c-0.028-0.031-0.057-0.061-0.085-0.093c-0.71-0.788-1.401-1.592-2.084-2.405c-0.089-0.107-0.182-0.208-0.271-0.315
        c-0.754-0.907-1.493-1.827-2.213-2.763c-8.201,6.64-18.224,11.113-29.213,12.55c3.605,9.007,7.792,20.107,12.178,32.979
        c16.985,49.815,36.931,126.092,36.931,209.815h29.733C231.978,423.952,213.43,350.147,196.875,300.312z"/>
              <path style="fill:#648E13;" d="M358.863,261.909c-3.605,9.007-7.793,20.107-12.178,32.966c-6.244-2.242-11.992-5.513-17.047-9.601
        c-11.088,14.42-26.314,25.484-43.881,31.443c-1.685,0.57-3.395,1.09-5.117,1.561c1.611-13.442,3.345-25.979,5.117-37.488
        c17.567-5.959,32.793-17.022,43.881-31.443C337.852,255.988,347.875,260.472,358.863,261.909z"/>
            </g>
            <path style="fill:#9AD14B;" d="M358.863,268.104c-10.989-1.437-21.011-5.922-29.225-12.562
    c-11.088,14.42-26.314,25.484-43.881,31.443c-9.341,3.159-19.351,4.869-29.758,4.869c-10.407,0-20.417-1.71-29.758-4.869
    c-17.58-5.959-32.805-17.022-43.893-31.443c-8.201,6.64-18.224,11.113-29.213,12.55c-2.515,0.335-5.092,0.508-7.693,0.508
    c-0.31,0-0.607-0.025-0.917-0.025c-4.894,27.503-28.903,48.39-57.806,48.39c-32.434,0-58.735-26.289-58.735-58.735
    c0-10.345,2.688-20.07,7.396-28.519C14.148,216.505,0,192.966,0,166.108c0-41.341,33.511-74.853,74.852-74.853
    c3.531,0,7.012,0.273,10.419,0.743c5.835-35.531,36.671-62.65,73.849-62.65c15.337,0,29.597,4.633,41.477,12.55
    c9.676-20.441,30.476-34.589,54.597-34.589c24.443,0,45.479,14.544,54.981,35.432c12.116-8.424,26.822-13.392,42.704-13.392
    c37.178,0,68.014,27.119,73.837,62.65c3.407-0.471,6.888-0.743,10.431-0.743c41.341,0,74.853,33.511,74.853,74.853
    c0,26.859-14.148,50.397-35.395,63.604c4.708,8.449,7.396,18.174,7.396,28.519c0,32.446-26.289,58.735-58.722,58.735
    c-28.915,0-52.924-20.887-57.806-48.39c-0.31,0-0.619,0.025-0.929,0.025C363.943,268.599,361.378,268.426,358.863,268.104z"/>
            <path style="fill:#90BC42;" d="M117.185,258.23c0-10.345,2.688-20.07,7.396-28.519c-21.234-13.206-35.382-36.745-35.382-63.604
    c0-41.341,33.511-74.853,74.852-74.853c3.531,0,7.012,0.273,10.419,0.743c67.902-101.401,121.296-60.567,125.322-64.98
    c-11.038-12.105-26.926-19.709-44.597-19.709c-24.121,0-44.921,14.148-54.597,34.589c-11.881-7.916-26.14-12.55-41.477-12.55
    c-37.178,0-68.014,27.119-73.849,62.65c-3.407-0.471-6.888-0.743-10.419-0.743C33.511,91.255,0,124.767,0,166.108
    c0,26.859,14.148,50.397,35.382,63.604c-4.708,8.449-7.396,18.174-7.396,28.519c0,32.446,26.301,58.735,58.735,58.735
    c17.85,0,33.826-7.973,44.593-20.544C122.513,286.152,117.185,272.818,117.185,258.23z"/>
          </svg>
        </div>
        <div class="mkt-item-body">
          <strong>Organic Broccoli</strong>
          <span>Local Farm — 800g</span>
        </div>
        <div class="mkt-item-price">0.90 DT</div>
      </div>
      <div class="mkt-item">
        <div class="mkt-item-icon">
          <svg width="28" height="28" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path style="fill:#F95428;" d="M412.416,82.31c53.13,44.627,86.898,111.567,86.898,186.383C499.314,403.063,390.377,512,256.008,512
    C121.623,512,12.686,403.063,12.686,268.694c0-75.596,34.472-143.133,88.55-187.76c21.136-17.435,45.269-21.304,71.513-20.815
    l166.762,0.382C366.336,60.165,390.943,64.279,412.416,82.31z"/>
            <polygon style="fill:#9AD14B;" points="387.533,134.584 296.995,97.742 256.008,157.05 215.021,97.742 124.482,134.584 
    172.749,60.119 178.01,51.998 158.128,0 256.008,44.352 353.887,0 334.005,51.998 339.511,60.502 "/>
            <path style="fill:#E54728;" d="M138.094,268.694c0-64.394,25.025-122.93,65.864-166.45l-79.475,32.34l48.267-74.465
    c-26.244-0.489-50.377,3.38-71.513,20.815c-54.078,44.627-88.55,112.163-88.55,187.76C12.686,403.063,121.623,512,256.008,512
    c21.682,0,42.695-2.852,62.701-8.173C214.718,476.167,138.094,381.383,138.094,268.694z"/>
            <polygon style="fill:#90BC42;" points="265.184,80.536 256.008,157.05 215.021,97.742 124.482,134.584 172.749,60.119 
    178.01,51.998 158.128,0 256.008,44.352 230.008,66.772 "/>
          </svg>
        </div>
        <div class="mkt-item-body">
          <strong>Sun-dried Tomatoes</strong>
          <span>Surplus — 500g</span>
        </div>
        <div class="mkt-item-price">1.20 DT</div>
      </div>
      <div class="mkt-item">
        <div class="mkt-item-icon">
          <svg width="28" height="28" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M356.9 298.1s-32.2-25.4-62.4-20.5c-30.3 4.9-136.2 51.6-188.3 323.3s724.6 127.3 724.6 127.3 95.4-27.3 80.7-150.8c-14.7-123.5-97.9-275.3-159.7-292s-224.6 56.8-224.6 56.8l-170.3-44.1z" fill="#EAAD6A" />
            <path d="M761.8 750.4s77.3-18 67.2-126.7-54.9-213.1-72.9-248.9c-17.9-35.8-58.3-97.3-58.3-97.3L643 318l118.8 432.4z" fill="#D89660" />
            <path d="M527.2 826.8c168.6 5.4 271.3-91.7 256-225.9S668 208.1 527.2 198.5 272.4 525.3 272 647.5c-0.4 178 255.2 179.3 255.2 179.3z" fill="#FFDEB3" />
            <path d="M783.1 598c-12.8-112-84.4-310.3-189.5-378.6 77.3 88.2 128.5 240.8 139.1 333.8 15.3 134.2-87.4 231.3-256 225.9 0 0-102-0.6-177-45.9 66 89.7 227.4 90.7 227.4 90.7 168.6 5.4 271.3-91.7 256-225.9z" fill="#F4D0A4" />
            <path d="M928 601.4c0-112.7-89.7-338-207.5-338-16.2 0-32.4 4.5-48.6 13-41.5-53.7-90.9-90.1-144.8-90.1-58.2 0-111.2 42.4-154.5 103.2-22.6-17.3-45.8-26.1-69.1-26.1-117.8 0-207.5 225.3-207.5 338 0 101.9 72.8 158.3 204.8 159.1 36.4 41.2 94.3 66.6 171.9 74.4 0.3 0 0.5 0.1 0.8 0.1h0.3c16.9 1.7 34.5 2.7 53.2 2.7 106.4 0 183.5-27.4 227.7-78.7C866.5 749.6 928 693.9 928 601.4zM280.2 731.6c-75.8-4.4-155.8-32.2-155.8-130.2 0-102 82.9-309.7 179.2-309.7 17.4 0 35.3 7.2 53.4 21.3-49.7 78.9-84.3 180.2-96.2 261.3l-0.6 3.9c-0.6 4.5-1.2 8.9-1.7 13.3l-0.6 6c-0.4 3.9-0.7 7.7-0.9 11.5-0.1 1.9-0.3 3.8-0.4 5.7-0.3 5.5-0.5 10.9-0.5 16.1 0 9.1 0.5 17.9 1.4 26.4 0.3 2.4 0.7 4.7 1 7.1 0.8 6.1 1.8 12.1 3.1 18 0.6 2.4 1.2 4.8 1.9 7.2 1.5 5.7 3.2 11.2 5.1 16.6 0.8 2.1 1.5 4.1 2.4 6.1 2.3 5.7 5 11.3 7.8 16.7 0.5 0.9 0.9 1.8 1.4 2.7z m246.9 77.8c-11.1 0-22.5-0.4-33.9-1.1l19.8-55.1 49.3-15.2 35.2 16.5c7.1 3.4 15.5 0.3 18.8-6.8 3.3-7.1 0.3-15.5-6.8-18.8l-9.5-4.5 6-11.4c3.6-6.9 1-15.5-6-19.1-6.9-3.6-15.5-0.9-19.1 6l-6.5 12.5-4.9-2.3c-3.2-1.5-6.8-1.8-10.2-0.7l-52.9 16.3-26.2-27.8 9.5-32.3c2.2-7.5-2.1-15.4-9.6-17.6-7.6-2.2-15.4 2.1-17.6 9.6l-11.8 40.1c-1.4 4.8-0.2 10 3.3 13.7l31.8 33.8-21.8 60.6c-56.8-7.3-111.8-26.3-145.6-67.9-0.1-0.1-0.1-0.2-0.2-0.3l-0.9-1.2c-3.3-4.1-6.3-8.5-9.1-13.1-0.9-1.4-1.8-2.9-2.6-4.4-2.9-5.1-5.7-10.5-8.1-16.2-0.3-0.7-0.5-1.5-0.8-2.2-2.1-5.3-4-11-5.6-16.8-0.4-1.5-0.9-3-1.2-4.5-1.6-6.4-2.8-13.1-3.7-20.2-0.2-1.4-0.3-2.9-0.5-4.4-0.8-7.5-1.4-15.3-1.4-23.6 0-4.6 0.1-9.4 0.4-14.5 0-0.7 0.1-1.5 0.2-2.3 0.3-4.5 0.6-9 1.1-13.7 0.1-0.6 0.2-1.3 0.3-1.9 0.7-5.1 1.6-10.4 2.5-15.7 0.2-0.9 0.3-1.8 0.5-2.6 0.9-4.8 1.9-9.8 2.9-14.7 0.3-1.6 0.6-3.1 1-4.6 0.8-3.5 1.6-7.2 2.5-10.8 12.4-52.5 32.9-109.9 59.1-161.3 0.3-0.5 0.5-1 0.8-1.6 2.4-4.8 4.9-9.5 7.5-14.1 7.1-12.9 14.6-25.5 22.6-37.6 0-0.1 0-0.1 0.1-0.2 39.9-60.5 88.3-103.6 139.4-103.6 46.8 0 91.2 36 129 88.5 0.1 0.1 0.1 0.2 0.1 0.3 8.7 12 16.9 24.8 24.7 38 1.1 1.9 2.2 3.9 3.3 5.9 1.9 3.4 3.9 6.8 5.7 10.3 35.4 65.2 61.5 142.1 73.1 207.1 0.7 3.7 1.3 7.5 1.9 11.1 0.3 2.2 0.6 4.3 0.9 6.4 0.6 3.9 1.1 7.8 1.5 11.6 0.2 1.9 0.4 3.7 0.6 5.6 0.4 4 0.7 7.9 1 11.8 0.1 1.5 0.2 3.1 0.3 4.6 0.3 5.2 0.5 10.3 0.5 15.1 0 8.3-0.6 16-1.4 23.6-0.2 1.5-0.3 2.9-0.5 4.4-0.9 7-2.1 13.6-3.7 19.9-0.4 1.7-0.9 3.4-1.4 5.1-1.6 5.7-3.4 11.2-5.4 16.5-0.4 1.1-0.9 2.2-1.3 3.3-2.2 5.1-4.7 10-7.3 14.7-1 1.7-1.9 3.4-2.9 5-42.9 68.6-136.6 85.8-218.8 85.8zM776 728.1c0.3-0.6 0.6-1.3 0.9-2 2.7-5.3 5.2-10.9 7.4-16.5 0.7-1.8 1.4-3.5 2-5.3 1.9-5.5 3.5-11.1 5-16.9 0.5-2.1 1.2-4.1 1.6-6.3 1.3-5.9 2.2-11.9 3-18.1 0.3-2.2 0.7-4.2 0.9-6.4 0.8-8.3 1.3-16.9 1.3-25.8 0-5.2-0.2-10.6-0.5-16.2 0-0.4-0.1-0.8-0.1-1.3-4.9-89.5-46.1-220.5-109.1-313.8 10.8-5.2 21.5-7.8 31.9-7.8 96.2 0 179.2 207.7 179.2 309.7 0.2 71.4-41.4 113.9-123.5 126.7zM280.2 731.6c-75.8-4.4-155.8-32.2-155.8-130.2 0-102 82.9-309.7 179.2-309.7 17.4 0 35.3 7.2 53.4 21.3-49.7 78.9-84.3 180.2-96.2 261.3l-0.6 3.9c-0.6 4.5-1.2 8.9-1.7 13.3l-0.6 6c-0.4 3.9-0.7 7.7-0.9 11.5-0.1 1.9-0.3 3.8-0.4 5.7-0.3 5.5-0.5 10.9-0.5 16.1 0 9.1 0.5 17.9 1.4 26.4 0.3 2.4 0.7 4.7 1 7.1 0.8 6.1 1.8 12.1 3.1 18 0.6 2.4 1.2 4.8 1.9 7.2 1.5 5.7 3.2 11.2 5.1 16.6 0.8 2.1 1.5 4.1 2.4 6.1 2.3 5.7 5 11.3 7.8 16.7 0.5 0.9 0.9 1.8 1.4 2.7z m246.9 77.8c-11.1 0-22.5-0.4-33.9-1.1l19.8-55.1 49.3-15.2 35.2 16.5c7.1 3.4 15.5 0.3 18.8-6.8 3.3-7.1 0.3-15.5-6.8-18.8l-9.5-4.5 6-11.4c3.6-6.9 1-15.5-6-19.1-6.9-3.6-15.5-0.9-19.1 6l-6.5 12.5-4.9-2.3c-3.2-1.5-6.8-1.8-10.2-0.7l-52.9 16.3-26.2-27.8 9.5-32.3c2.2-7.5-2.1-15.4-9.6-17.6-7.6-2.2-15.4 2.1-17.6 9.6l-11.8 40.1c-1.4 4.8-0.2 10 3.3 13.7l31.8 33.8-21.8 60.6c-56.8-7.3-111.8-26.3-145.6-67.9-0.1-0.1-0.1-0.2-0.2-0.3l-0.9-1.2c-3.3-4.1-6.3-8.5-9.1-13.1-0.9-1.4-1.8-2.9-2.6-4.4-2.9-5.1-5.7-10.5-8.1-16.2-0.3-0.7-0.5-1.5-0.8-2.2-2.1-5.3-4-11-5.6-16.8-0.4-1.5-0.9-3-1.2-4.5-1.6-6.4-2.8-13.1-3.7-20.2-0.2-1.4-0.3-2.9-0.5-4.4-0.8-7.5-1.4-15.3-1.4-23.6 0-4.6 0.1-9.4 0.4-14.5 0-0.7 0.1-1.5 0.2-2.3 0.3-4.5 0.6-9 1.1-13.7 0.1-0.6 0.2-1.3 0.3-1.9 0.7-5.1 1.6-10.4 2.5-15.7 0.2-0.9 0.3-1.8 0.5-2.6 0.9-4.8 1.9-9.8 2.9-14.7 0.3-1.6 0.6-3.1 1-4.6 0.8-3.5 1.6-7.2 2.5-10.8 12.4-52.5 32.9-109.9 59.1-161.3 0.3-0.5 0.5-1 0.8-1.6 2.4-4.8 4.9-9.5 7.5-14.1 7.1-12.9 14.6-25.5 22.6-37.6 0-0.1 0-0.1 0.1-0.2 39.9-60.5 88.3-103.6 139.4-103.6 46.8 0 91.2 36 129 88.5 0.1 0.1 0.1 0.2 0.1 0.3 8.7 12 16.9 24.8 24.7 38 1.1 1.9 2.2 3.9 3.3 5.9 1.9 3.4 3.9 6.8 5.7 10.3 35.4 65.2 61.5 142.1 73.1 207.1 0.7 3.7 1.3 7.5 1.9 11.1 0.3 2.2 0.6 4.3 0.9 6.4 0.6 3.9 1.1 7.8 1.5 11.6 0.2 1.9 0.4 3.7 0.6 5.6 0.4 4 0.7 7.9 1 11.8 0.1 1.5 0.2 3.1 0.3 4.6 0.3 5.2 0.5 10.3 0.5 15.1 0 8.3-0.6 16-1.4 23.6-0.2 1.5-0.3 2.9-0.5 4.4-0.9 7-2.1 13.6-3.7 19.9-0.4 1.7-0.9 3.4-1.4 5.1-1.6 5.7-3.4 11.2-5.4 16.5-0.4 1.1-0.9 2.2-1.3 3.3-2.2 5.1-4.7 10-7.3 14.7-1 1.7-1.9 3.4-2.9 5-42.9 68.6-136.6 85.8-218.8 85.8zM776 728.1c0.3-0.6 0.6-1.3 0.9-2 2.7-5.3 5.2-10.9 7.4-16.5 0.7-1.8 1.4-3.5 2-5.3 1.9-5.5 3.5-11.1 5-16.9 0.5-2.1 1.2-4.1 1.6-6.3 1.3-5.9 2.2-11.9 3-18.1 0.3-2.2 0.7-4.2 0.9-6.4 0.8-8.3 1.3-16.9 1.3-25.8 0-5.2-0.2-10.6-0.5-16.2 0-0.4-0.1-0.8-0.1-1.3-4.9-89.5-46.1-220.5-109.1-313.8 10.8-5.2 21.5-7.8 31.9-7.8 96.2 0 179.2 207.7 179.2 309.7 0.2 71.4-41.4 113.9-123.5 126.7z" fill="#004364" />
          </svg>
        </div>
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
  // Unified sidebar initialization
</script>
<script src="js/sidebar.js"></script>
    <?php if ($user_subscription !== 'premium' && $user_subscription !== 'elite'): ?>
      <a href="foovia-premium.php" class="floating-premium-btn" title="Upgrade to Premium">
        <img src="assets/crown-svgrepo-com%20(1).svg" class="premium-icon-large" alt="Premium">
      </a>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
