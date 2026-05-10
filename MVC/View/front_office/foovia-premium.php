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
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FOOVIA — Go Premium</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,300&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
  <link rel="stylesheet" href="foovia.css">
  <style>
    :root {
      --yellow: #F5C842;
      --green: #4BAE52;
      --orange: #D94F00;
      --yellow-mid: #F0A830;
      --forest: #2E4A28;
      --peach: #F2A98A;
      --red: #C0381A;
      --off-white: #FDF8EE;
      --dark: #111008;
      --gold: #E8B84B;
      --gold-light: #FFF0B3;
      --gold-dark: #A07820;

      /* Nav specific variables from foovia.css */
      --page-text: var(--dark);
      --page-bg: var(--off-white);
      --nav-bg: rgba(253,248,238,.85);
      --nav-border: rgba(75,174,82,.18);
    }

    :root[data-theme="dark"] {
      --page-bg: #0f0f0b;
      --page-text: #fdf8ee;
      --nav-bg: rgba(15,15,11,.85);
      --nav-border: rgba(245,200,66,.18);
      --dark: #0f0f0b;
    }

    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--dark);
      color: #fff;
      overflow-x: hidden;
    }

    /* ── NAV (from foovia.css) ── */
    nav {
      position: fixed; top: 0; left: 0; width: 100%;
      z-index: 200;
      display: flex; align-items: center; justify-content: space-between;
      padding: 18px 48px;
      background: var(--nav-bg);
      backdrop-filter: blur(12px);
      border-bottom: 1.5px solid var(--nav-border);
      transition: background-color .3s ease, border-color .3s ease;
    }
    .nav-logo {
      display: flex; align-items: center; gap: 10px;
      font-family: 'Syne', sans-serif;
      font-weight: 800; font-size: 1.5rem;
      letter-spacing: .04em;
      color: var(--page-text);
      text-decoration: none;
    }
    .nav-logo-img {
      height: 50px;
      width: auto;
    }
    .nav-links {
      display: flex; gap: 36px; list-style: none;
      margin: 0;
      padding: 0;
    }
    .nav-links a {
      font-family: 'DM Sans', sans-serif;
      font-size: .9rem; font-weight: 500;
      color: var(--page-text); text-decoration: none;
      transition: color .2s;
    }
    .nav-links a:hover { color: var(--green); }
    .nav-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .nav-btn {
      border-radius: 100px;
      padding: 9px 18px;
      font-family: 'Syne', sans-serif;
      font-weight: 700;
      font-size: .84rem;
      letter-spacing: .01em;
      text-decoration: none;
      transition: background-color .2s ease, color .2s ease, border-color .2s ease, transform .15s ease;
    }
    .nav-signin {
      background: transparent;
      color: var(--page-text);
      border: 1.5px solid var(--nav-border);
    }
    .nav-signin:hover {
      background: var(--page-text);
      color: var(--page-bg);
      transform: translateY(-1px);
    }
    .nav-backoffice {
      background: #2f6df6;
      color: #fff;
      border: 1.5px solid transparent;
    }
    .nav-backoffice:hover {
      background: #1f56cd;
      transform: translateY(-1px);
    }
    .nav-signup {
      background: var(--green);
      color: #fff;
      border: 1.5px solid transparent;
    }
    .nav-signup:hover {
      background: var(--forest);
      transform: translateY(-1px);
    }
    .theme-toggle {
      width: 40px; height: 40px;
      border-radius: 50%;
      border: 1.5px solid var(--nav-border);
      background: transparent;
      color: var(--page-text);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background-color .2s ease, color .2s ease, transform .15s ease, border-color .2s ease;
    }
    .theme-toggle:hover {
      background: var(--page-text);
      color: var(--page-bg);
      transform: scale(1.05);
    }
    .theme-toggle svg {
      width: 18px; height: 18px;
      stroke: currentColor;
      fill: none;
      stroke-width: 1.7;
      stroke-linecap: round;
      stroke-linejoin: round;
      display: block;
    }
    .theme-toggle .icon-moon { display: none; }
    :root[data-theme="dark"] .theme-toggle .icon-moon { display: block; }
    :root[data-theme="dark"] .theme-toggle .icon-sun { display: none; }
    
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

    /* Custom adjustment for Premium page - make sure it doesn't break body */
    body {
      transition: background-color .3s ease, color .3s ease;
    }
    :root[data-theme="dark"] body {
      background: var(--dark);
      color: #fff;
    }

    /* ── HERO ── */
    .hero {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 120px 32px 80px;
      position: relative;
      overflow: hidden;
    }

    /* animated gradient mesh background */
    .hero-bg {
      position: absolute;
      inset: 0;
      background: var(--dark);
      z-index: 0;
    }

    .hero-bg::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(ellipse 70% 60% at 20% 20%, rgba(232, 184, 75, .14) 0%, transparent 60%),
        radial-gradient(ellipse 50% 50% at 80% 70%, rgba(75, 174, 82, .1) 0%, transparent 55%),
        radial-gradient(ellipse 60% 40% at 50% 90%, rgba(217, 79, 0, .08) 0%, transparent 50%);
      animation: meshDrift 12s ease-in-out infinite alternate;
    }

    @keyframes meshDrift {
      from {
        transform: scale(1) rotate(0deg);
      }

      to {
        transform: scale(1.06) rotate(2deg);
      }
    }

    /* floating particles */
    .particles {
      position: absolute;
      inset: 0;
      pointer-events: none;
      z-index: 1;
    }

    .particle {
      position: absolute;
      border-radius: 50%;
      background: var(--gold);
      opacity: 0;
      animation: floatUp linear infinite;
    }

    @keyframes floatUp {
      0% {
        opacity: 0;
        transform: translateY(0) scale(0);
      }

      10% {
        opacity: .6;
      }

      90% {
        opacity: .2;
      }

      100% {
        opacity: 0;
        transform: translateY(-90vh) scale(1.5);
      }
    }

    .hero-content {
      position: relative;
      z-index: 2;
    }

    /* crown badge */
    .crown-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(135deg, rgba(232, 184, 75, .2), rgba(232, 184, 75, .08));
      border: 1px solid rgba(232, 184, 75, .35);
      border-radius: 100px;
      padding: 8px 20px;
      font-family: 'Boldonse', system-ui;
      font-size: .68rem;
      letter-spacing: .2em;
      text-transform: uppercase;
      color: var(--gold);
      margin-bottom: 28px;
      animation: fadeUp .7s .1s both;
    }

    .hero-title {
      font-family: 'Boldonse', system-ui;
      font-size: clamp(2.8rem, 7vw, 6rem);
      line-height: 1.0;
      margin-bottom: 22px;
      animation: fadeUp .7s .25s both;
    }

    .hero-title .line-1 {
      display: block;
      color: #fff;
    }

    .hero-title .line-2 {
      display: block;
      background: linear-gradient(135deg, var(--gold) 0%, #fff 40%, var(--gold) 80%);
      background-size: 200% 100%;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: fadeUp .7s .25s both, shimmer 3s ease-in-out infinite;
    }

    @keyframes shimmer {

      0%,
      100% {
        background-position: 0% 50%;
      }

      50% {
        background-position: 100% 50%;
      }
    }

    .hero-sub {
      font-size: 1.05rem;
      color: rgba(255, 255, 255, .55);
      line-height: 1.7;
      max-width: 520px;
      margin: 0 auto 48px;
      animation: fadeUp .7s .4s both;
    }

    /* scroll cue */
    .scroll-cue {
      animation: fadeUp .7s .8s both;
    }

    .scroll-cue a {
      color: rgba(255, 255, 255, .35);
      font-size: .82rem;
      text-decoration: none;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
      transition: color .2s;
    }

    .scroll-cue a:hover {
      color: var(--gold);
    }

    .scroll-line {
      width: 1px;
      height: 40px;
      background: linear-gradient(to bottom, rgba(255, 255, 255, .3), transparent);
      margin: 0 auto;
      animation: scrollBounce 2s ease-in-out infinite;
    }

    @keyframes scrollBounce {

      0%,
      100% {
        transform: translateY(0)
      }

      50% {
        transform: translateY(8px)
      }
    }

    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(28px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* ── PRICING ── */
    .pricing-section {
      padding: 100px 32px;
      background: var(--off-white);
      position: relative;
      overflow: hidden;
    }

    .pricing-section::before {
      content: '';
      position: absolute;
      top: -120px;
      left: 50%;
      transform: translateX(-50%);
      width: 800px;
      height: 240px;
      background: radial-gradient(ellipse, rgba(232, 184, 75, .12) 0%, transparent 70%);
      pointer-events: none;
    }

    .section-label {
      font-family: 'Boldonse', system-ui;
      font-size: .68rem;
      letter-spacing: .18em;
      text-transform: uppercase;
      color: var(--green);
      text-align: center;
      margin-bottom: 12px;
    }

    .section-title {
      font-family: 'Boldonse', system-ui;
      font-size: clamp(2rem, 4vw, 3rem);
      color: var(--dark);
      text-align: center;
      line-height: 1.05;
      margin-bottom: 10px;
    }

    .section-title span {
      color: var(--orange);
    }

    .section-sub {
      text-align: center;
      color: #666;
      font-size: .95rem;
      margin-bottom: 14px;
    }

    /* billing toggle */
    .billing-toggle {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 14px;
      margin-bottom: 52px;
    }

    .billing-lbl {
      font-size: .88rem;
      font-weight: 500;
      color: #888;
    }

    .billing-lbl.active {
      color: var(--dark);
      font-weight: 700;
    }

    .toggle-track {
      width: 48px;
      height: 26px;
      border-radius: 100px;
      background: var(--dark);
      cursor: pointer;
      position: relative;
      transition: background .2s;
    }

    .toggle-thumb {
      position: absolute;
      top: 3px;
      left: 3px;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: var(--yellow);
      transition: transform .25s cubic-bezier(.34, 1.56, .64, 1);
    }

    .toggle-track.annual .toggle-thumb {
      transform: translateX(22px);
    }

    .save-badge {
      background: var(--green);
      color: #fff;
      font-family: 'Boldonse', system-ui;
      font-size: .65rem;
      padding: 3px 9px;
      border-radius: 100px;
      letter-spacing: .06em;
    }

    /* pricing grid */
    .pricing-grid {
      display: grid;
      grid-template-columns: 1fr 1.12fr;
      gap: 20px;
      max-width: 720px;
      margin: 0 auto;
    }

    .plan-card {
      border-radius: 26px;
      padding: 36px 30px;
      position: relative;
      overflow: hidden;
      border: 2px solid rgba(0, 0, 0, .08);
      background: #fff;
      transition: transform .25s, box-shadow .25s;
    }

    .plan-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 28px 56px rgba(0, 0, 0, .1);
    }

    /* FREE */
    .plan-free {
      background: #fff;
    }

    /* PREMIUM — featured */
    .plan-premium {
      background: var(--dark);
      border-color: var(--gold);
      color: #fff;
      transform: translateY(-12px);
      box-shadow: 0 32px 64px rgba(232, 184, 75, .2);
    }

    .plan-premium:hover {
      transform: translateY(-18px);
      box-shadow: 0 40px 80px rgba(232, 184, 75, .25);
    }

    /* gold shimmer overlay */
    .plan-premium::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(ellipse 80% 50% at 50% -20%, rgba(232, 184, 75, .18) 0%, transparent 60%),
        radial-gradient(ellipse 40% 40% at 90% 90%, rgba(75, 174, 82, .08) 0%, transparent 50%);
      pointer-events: none;
    }

    /* ELITE */
    .plan-elite {
      background: #fff;
    }

    /* most popular badge */
    .popular-badge {
      position: absolute;
      top: 18px;
      right: 18px;
      background: var(--gold);
      color: var(--dark);
      font-family: 'Boldonse', system-ui;
      font-size: .62rem;
      letter-spacing: .1em;
      text-transform: uppercase;
      padding: 5px 12px;
      border-radius: 100px;
    }

    .plan-icon {
      font-size: 2.2rem;
      margin-bottom: 14px;
      display: block;
    }

    .plan-name {
      font-family: 'Boldonse', system-ui;
      font-size: 1.1rem;
      margin-bottom: 4px;
    }

    .plan-name-free {
      color: var(--dark);
    }

    .plan-name-premium {
      color: var(--gold);
    }

    .plan-name-elite {
      color: var(--orange);
    }

    .plan-tagline {
      font-size: .82rem;
      color: #aaa;
      margin-bottom: 24px;
    }

    .plan-premium .plan-tagline {
      color: rgba(255, 255, 255, .5);
    }

    .plan-price {
      display: flex;
      align-items: flex-end;
      gap: 4px;
      margin-bottom: 6px;
    }

    .price-currency {
      font-family: 'Boldonse', system-ui;
      font-size: 1.1rem;
      margin-bottom: 8px;
    }

    .price-amount {
      font-family: 'Boldonse', system-ui;
      font-size: 3.2rem;
      line-height: 1;
    }

    .price-period {
      font-size: .82rem;
      color: #888;
      margin-bottom: 6px;
    }

    .plan-premium .price-period {
      color: rgba(255, 255, 255, .45);
    }

    .plan-premium .price-currency {
      color: var(--gold);
    }

    .plan-premium .price-amount {
      color: #fff;
    }

    .price-annual {
      font-size: .78rem;
      color: #aaa;
      margin-bottom: 24px;
      min-height: 20px;
    }

    .plan-premium .price-annual {
      color: rgba(255, 255, 255, .4);
    }

    .plan-divider {
      height: 1px;
      background: rgba(0, 0, 0, .08);
      margin-bottom: 22px;
    }

    .plan-premium .plan-divider {
      background: rgba(255, 255, 255, .1);
    }

    .plan-features {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 11px;
      margin-bottom: 30px;
    }

    .plan-feature {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      font-size: .85rem;
      color: #555;
      line-height: 1.4;
    }

    .plan-premium .plan-feature {
      color: rgba(255, 255, 255, .75);
    }

    .feat-check {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .65rem;
      font-weight: 700;
      margin-top: 1px;
    }

    .feat-check.yes {
      background: rgba(75, 174, 82, .15);
      color: var(--green);
    }

    .plan-premium .feat-check.yes {
      background: rgba(75, 174, 82, .2);
      color: #6de87a;
    }

    .feat-check.no {
      background: rgba(0, 0, 0, .06);
      color: #ccc;
    }

    .feat-check.gold {
      background: rgba(232, 184, 75, .2);
      color: var(--gold);
    }

    .plan-feature.muted {
      opacity: .45;
    }

    .plan-cta {
      width: 100%;
      padding: 14px;
      border-radius: 14px;
      font-family: 'Boldonse', system-ui;
      font-size: .9rem;
      cursor: pointer;
      border: none;
      transition: background .2s, transform .15s;
    }

    .plan-cta:hover {
      transform: scale(1.02);
    }

    .cta-free {
      background: rgba(0, 0, 0, .07);
      color: var(--dark);
    }

    .cta-free:hover {
      background: rgba(0, 0, 0, .12);
    }

    .cta-premium {
      background: linear-gradient(135deg, var(--gold), var(--yellow-mid));
      color: var(--dark);
      box-shadow: 0 8px 28px rgba(232, 184, 75, .35);
    }

    .cta-premium:hover {
      box-shadow: 0 12px 36px rgba(232, 184, 75, .45);
    }

    .cta-elite {
      background: var(--dark);
      color: var(--yellow);
    }

    .cta-elite:hover {
      background: var(--forest);
    }

    /* ── FEATURES BREAKDOWN ── */
    .features-section {
      padding: 100px 32px;
      background: var(--dark);
    }

    .features-section .section-label {
      color: var(--gold);
    }

    .features-section .section-title {
      color: #fff;
    }

    .features-section .section-title span {
      color: var(--yellow);
    }

    .features-section .section-sub {
      color: rgba(255, 255, 255, .45);
    }

    .feat-table {
      max-width: 620px;
      margin: 52px auto 0;
      border-radius: 22px;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, .08);
    }

    .feat-table-head {
      display: grid;
      grid-template-columns: 1fr repeat(2, 140px);
      background: rgba(255, 255, 255, .04);
      border-bottom: 1px solid rgba(255, 255, 255, .08);
      padding: 18px 28px;
      gap: 8px;
    }

    .ft-head-cell {
      font-family: 'Boldonse', system-ui;
      font-size: .75rem;
      text-align: center;
      color: rgba(255, 255, 255, .4);
      letter-spacing: .08em;
      text-transform: uppercase;
    }

    .ft-head-cell.highlight {
      color: var(--gold);
    }

    .feat-table-row {
      display: grid;
      grid-template-columns: 1fr repeat(2, 140px);
      padding: 14px 28px;
      gap: 8px;
      border-bottom: 1px solid rgba(255, 255, 255, .05);
      align-items: center;
      transition: background .15s;
    }

    .feat-table-row:last-child {
      border-bottom: none;
    }

    .feat-table-row:hover {
      background: rgba(255, 255, 255, .03);
    }

    .ft-feat-name {
      font-size: .88rem;
      color: rgba(255, 255, 255, .75);
    }

    .ft-feat-name strong {
      font-weight: 700;
      color: #fff;
      font-size: .92rem;
      display: block;
      margin-bottom: 2px;
    }

    .ft-feat-name span {
      font-size: .75rem;
      color: rgba(255, 255, 255, .35);
    }

    .ft-cell {
      text-align: center;
      font-size: .82rem;
    }

    .ft-check {
      font-size: 1rem;
    }

    .ft-check.y {
      color: var(--green);
    }

    .ft-check.g {
      color: var(--gold);
    }

    .ft-check.n {
      color: rgba(255, 255, 255, .15);
    }

    .ft-val {
      font-family: 'Boldonse', system-ui;
      font-size: .8rem;
      color: rgba(255, 255, 255, .6);
    }

    .ft-val.gold {
      color: var(--gold);
    }

    .ft-val.green {
      color: var(--green);
    }

    .ft-cat-row {
      background: rgba(255, 255, 255, .03);
      padding: 10px 28px;
    }

    .ft-cat-label {
      font-family: 'Boldonse', system-ui;
      font-size: .68rem;
      letter-spacing: .14em;
      text-transform: uppercase;
      color: rgba(255, 255, 255, .3);
    }

    /* ── TESTIMONIALS ── */
    .testimonials-section {
      padding: 100px 32px;
      background: var(--off-white);
    }

    .testimonials-section .section-title {
      color: var(--dark);
    }

    .testi-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      max-width: 960px;
      margin: 52px auto 0;
    }

    .testi-card {
      background: #fff;
      border-radius: 20px;
      padding: 28px;
      border: 1.5px solid rgba(0, 0, 0, .07);
      transition: transform .2s, box-shadow .2s;
    }

    .testi-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 16px 40px rgba(0, 0, 0, .08);
    }

    .testi-stars {
      color: var(--gold);
      font-size: 1rem;
      margin-bottom: 14px;
      letter-spacing: 2px;
    }

    .testi-text {
      font-size: .88rem;
      line-height: 1.7;
      color: #555;
      margin-bottom: 18px;
      font-style: italic;
    }

    .testi-author {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .testi-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      font-size: 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .testi-name {
      font-family: 'Boldonse', system-ui;
      font-size: .82rem;
    }

    .testi-role {
      font-size: .72rem;
      color: #aaa;
    }

    /* ── FAQ ── */
    .faq-section {
      padding: 100px 32px 120px;
      background: var(--dark);
    }

    .faq-section .section-label {
      color: var(--gold);
    }

    .faq-section .section-title {
      color: #fff;
    }

    .faq-list {
      max-width: 640px;
      margin: 48px auto 0;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .faq-item {
      border: 1px solid rgba(255, 255, 255, .08);
      border-radius: 16px;
      overflow: hidden;
    }

    .faq-q {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 22px;
      cursor: pointer;
      font-family: 'Boldonse', system-ui;
      font-size: .9rem;
      color: #fff;
      transition: background .15s;
      user-select: none;
    }

    .faq-q:hover {
      background: rgba(255, 255, 255, .04);
    }

    .faq-chevron {
      font-size: .8rem;
      color: var(--gold);
      transition: transform .3s;
    }

    .faq-item.open .faq-chevron {
      transform: rotate(180deg);
    }

    .faq-a {
      max-height: 0;
      overflow: hidden;
      transition: max-height .35s ease, padding .35s ease;
      font-size: .87rem;
      color: rgba(255, 255, 255, .55);
      line-height: 1.7;
      padding: 0 22px;
    }

    .faq-item.open .faq-a {
      max-height: 200px;
      padding: 0 22px 18px;
    }

    /* ── FINAL CTA ── */
    .final-cta {
      padding: 100px 32px;
      background: var(--dark);
      border-top: 1px solid rgba(255, 255, 255, .06);
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .final-cta::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse 80% 60% at 50% 100%, rgba(232, 184, 75, .12) 0%, transparent 60%);
      pointer-events: none;
    }

    .final-cta-title {
      font-family: 'Boldonse', system-ui;
      font-size: clamp(2rem, 5vw, 3.8rem);
      line-height: 1.05;
      margin-bottom: 18px;
      position: relative;
      z-index: 2;
    }

    .final-cta-title span {
      background: linear-gradient(135deg, var(--gold), var(--yellow-mid));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .final-cta-sub {
      font-size: .95rem;
      color: rgba(255, 255, 255, .5);
      margin-bottom: 40px;
      max-width: 440px;
      margin-left: auto;
      margin-right: auto;
      position: relative;
      z-index: 2;
    }

    .final-cta-btns {
      display: flex;
      gap: 14px;
      justify-content: center;
      flex-wrap: wrap;
      position: relative;
      z-index: 2;
    }

    .btn-gold {
      background: linear-gradient(135deg, var(--gold), var(--yellow-mid));
      color: var(--dark);
      border: none;
      padding: 17px 44px;
      border-radius: 100px;
      font-family: 'Boldonse', system-ui;
      font-size: 1rem;
      cursor: pointer;
      transition: transform .15s, box-shadow .2s;
      box-shadow: 0 8px 32px rgba(232, 184, 75, .3);
      text-decoration: none;
      display: inline-block;
    }

    .btn-gold:hover {
      transform: scale(1.04);
      box-shadow: 0 14px 44px rgba(232, 184, 75, .4);
    }

    .btn-ghost {
      background: transparent;
      color: rgba(255, 255, 255, .6);
      border: 1.5px solid rgba(255, 255, 255, .15);
      padding: 15px 32px;
      border-radius: 100px;
      font-family: 'Boldonse', system-ui;
      font-size: 1rem;
      cursor: pointer;
      transition: border-color .2s, color .2s;
      text-decoration: none;
      display: inline-block;
    }

    .btn-ghost:hover {
      border-color: rgba(255, 255, 255, .4);
      color: #fff;
    }

    .guarantee {
      font-size: .78rem;
      color: rgba(255, 255, 255, .3);
      margin-top: 18px;
      position: relative;
      z-index: 2;
    }

    /* ── FOOTER ── */
    footer {
      background: var(--dark);
      border-top: 1px solid rgba(255, 255, 255, .06);
      padding: 28px 52px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 16px;
    }

    .footer-logo {
      font-family: 'Boldonse', system-ui;
      color: var(--yellow);
      font-size: 1.1rem;
    }

    footer p {
      font-size: .78rem;
      color: rgba(255, 255, 255, .3);
    }

    .footer-links {
      display: flex;
      gap: 22px;
    }

    .footer-links a {
      font-size: .78rem;
      color: rgba(255, 255, 255, .3);
      text-decoration: none;
      transition: color .2s;
    }

    .footer-links a:hover {
      color: var(--gold);
    }

    /* ── RESPONSIVE ── */
    @media (max-width:860px) {
      nav {
        padding: 14px 20px;
      }

      .pricing-grid {
        grid-template-columns: 1fr;
        max-width: 400px;
      }

      .plan-premium {
        transform: none;
      }

      .plan-premium:hover {
        transform: translateY(-6px);
      }

      .testi-grid {
        grid-template-columns: 1fr;
        max-width: 440px;
        margin-left: auto;
        margin-right: auto;
      }

      .feat-table-head,
      .feat-table-row {
        grid-template-columns: 1fr repeat(2, 90px);
        padding: 12px 16px;
      }

      .ft-head-cell,
      .ft-cell {
        font-size: .72rem;
      }

      footer {
        padding: 24px 20px;
        flex-direction: column;
        text-align: center;
      }
    }
  </style>
</head>

<body>

  <!-- NAV -->
  <nav>
    <a href="foovia.php" class="nav-logo">
      <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" class="nav-logo-img">
      FOOVIA
    </a>
    <ul class="nav-links">
      <li><a href="foovia.php#features">Features</a></li>
      <li><a href="foovia.php#how">How it works</a></li>
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
      <?php if ($is_logged_in && ($user_subscription === 'premium' || $user_subscription === 'elite')): ?>
        <div class="premium-badge-nav" title="Premium Member" onclick="window.location.reload()">
          <img src="assets/crown-svgrepo-com%20(1).svg" class="premium-icon-nav" alt="Premium">
        </div>
      <?php endif; ?>
    </div>
  </nav>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-bg"></div>
    <div class="particles" id="particles"></div>

    <div class="hero-content">
      <div class="crown-badge">👑 Premium membership</div>
      <h1 class="hero-title">
        <span class="line-1">Unlock your</span>
        <span class="line-2">full potential.</span>
      </h1>
      <p class="hero-sub">Get unlimited recipes, AI-powered meal plans, advanced macro tracking, and a zero-waste
        marketplace — all personalised around you.</p>

      <div class="scroll-cue">
        <a href="#pricing">
          <span>See plans</span>
          <div class="scroll-line"></div>
        </a>
      </div>
    </div>
  </section>

  <!-- PRICING -->
  <section class="pricing-section" id="pricing">
    <p class="section-label">Choose your plan</p>
    <h2 class="section-title">Simple, <span>honest</span> pricing</h2>
    <p class="section-sub">No hidden fees. Cancel anytime.</p>

    <div class="billing-toggle">
      <span class="billing-lbl active" id="lbl-monthly">Monthly</span>
      <div class="toggle-track" id="billing-toggle" onclick="toggleBilling()">
        <div class="toggle-thumb"></div>
      </div>
      <span class="billing-lbl" id="lbl-annual">Annual</span>
      <span class="save-badge">Save 33%</span>
    </div>

    <div class="pricing-grid">

      <!-- FREE -->
      <div class="plan-card plan-free">
        <span class="plan-icon">🌱</span>
        <div class="plan-name plan-name-free">Free</div>
        <div class="plan-tagline">Get started, no credit card needed</div>
        <div class="plan-price">
          <span class="price-currency">DT</span>
          <span class="price-amount">0</span>
          <span class="price-period">/ month</span>
        </div>
        <div class="price-annual">Always free</div>
        <div class="plan-divider"></div>
        <ul class="plan-features">
          <li class="plan-feature">
            <div class="feat-check yes">✓</div>5 recipes per day
          </li>
          <li class="plan-feature">
            <div class="feat-check yes">✓</div>Basic macro tracker
          </li>
          <li class="plan-feature">
            <div class="feat-check yes">✓</div>Water intake tracking
          </li>
          <li class="plan-feature">
            <div class="feat-check yes">✓</div>7-day meal planner
          </li>
          <li class="plan-feature muted">
            <div class="feat-check no">✕</div>AI recipe suggestions
          </li>
          <li class="plan-feature muted">
            <div class="feat-check no">✕</div>Ingredient photo scan
          </li>
          <li class="plan-feature muted">
            <div class="feat-check no">✕</div>Marketplace access
          </li>
          <li class="plan-feature muted">
            <div class="feat-check no">✕</div>Community rewards
          </li>
        </ul>
        <button class="plan-cta cta-free" onclick="choosePlan('free')">
          <?php echo ($user_subscription === 'free' || $user_subscription === 'normal') ? 'Current plan' : 'Select Free'; ?>
        </button>
      </div>

      <!-- PREMIUM -->
      <div class="plan-card plan-premium">
        <div class="popular-badge">👑 Most popular</div>
        <span class="plan-icon">⚡</span>
        <div class="plan-name plan-name-premium">Premium</div>
        <div class="plan-tagline">Everything you need to reach your goals</div>
        <div class="plan-price">
          <span class="price-currency" style="color:var(--gold)">DT</span>
          <span class="price-amount" id="premium-price">19</span>
          <span class="price-period">/ month</span>
        </div>
        <div class="price-annual" id="premium-annual">Billed monthly</div>
        <div class="plan-divider"></div>
        <ul class="plan-features">
          <li class="plan-feature">
            <div class="feat-check yes">✓</div>Custom meal plan builder
          </li>
          <li class="plan-feature">
            <div class="feat-check yes">✓</div>Ingredient photo recognition
          </li>
          <li class="plan-feature">
            <div class="feat-check yes">✓</div>Full progress reports
          </li>
          <li class="plan-feature">
            <div class="feat-check yes">✓</div>Macros from meal image
          </li>
          <li class="plan-feature">
            <div class="feat-check yes">✓</div>Custom workout plans
          </li>
          <li class="plan-feature">
            <div class="feat-check yes">✓</div>AI workout suggestions
          </li>
          <li class="plan-feature">
            <div class="feat-check gold">★</div>Marketplace delivery system
          </li>
        </ul>
        <button class="plan-cta cta-premium" onclick="choosePlan('premium')">
          <?php echo ($user_subscription === 'premium') ? 'Current plan' : 'Start Premium →'; ?>
        </button>
      </div>


    </div>
  </section>

  <!-- FEATURE TABLE -->
  <section class="features-section">
    <p class="section-label">Compare</p>
    <h2 class="section-title">Everything <span>side by side</span></h2>
    <p class="section-sub" style="color:rgba(255,255,255,.4)">See exactly what's included in each plan</p>

    <div class="feat-table">
      <div class="feat-table-head">
        <div></div>
        <div class="ft-head-cell">Free</div>
        <div class="ft-head-cell highlight">Premium ⚡</div>
      </div>

      <div class="ft-cat-row">
        <div class="ft-cat-label">🍽️ Recipes</div>
      </div>
      <div class="feat-table-row">
        <div class="ft-feat-name"><strong>Custom meal plan builder</strong><span>Unlimited weeks</span></div>
        <div class="ft-cell"><span class="ft-check n">✕</span></div>
        <div class="ft-cell"><span class="ft-check y">✓</span></div>
      </div>
      <div class="feat-table-row">
        <div class="ft-feat-name"><strong>Ingredient photo recognition</strong><span>Scan to find recipes</span></div>
        <div class="ft-cell"><span class="ft-check n">✕</span></div>
        <div class="ft-cell"><span class="ft-check y">✓</span></div>
      </div>
      <div class="feat-table-row">
        <div class="ft-feat-name"><strong>Browse recipes</strong></div>
        <div class="ft-cell"><span class="ft-val">5 / day</span></div>
        <div class="ft-cell"><span class="ft-val gold">Unlimited</span></div>
      </div>

      <div class="ft-cat-row">
        <div class="ft-cat-label">📊 Tracking</div>
      </div>
      <div class="feat-table-row">
        <div class="ft-feat-name"><strong>Full progress reports</strong><span>Weekly & monthly insights</span></div>
        <div class="ft-cell"><span class="ft-check n">✕</span></div>
        <div class="ft-cell"><span class="ft-check y">✓</span></div>
      </div>
      <div class="feat-table-row">
        <div class="ft-feat-name"><strong>Macros from meal image</strong><span>Photo-based scan</span></div>
        <div class="ft-cell"><span class="ft-check n">✕</span></div>
        <div class="ft-cell"><span class="ft-check y">✓</span></div>
      </div>
      <div class="feat-table-row">
        <div class="ft-feat-name"><strong>Basic macro & water tracking</strong></div>
        <div class="ft-cell"><span class="ft-check y">✓</span></div>
        <div class="ft-cell"><span class="ft-check y">✓</span></div>
      </div>

      <div class="ft-cat-row">
        <div class="ft-cat-label">🏋️ Sport</div>
      </div>
      <div class="feat-table-row">
        <div class="ft-feat-name"><strong>Custom workout plans</strong><span>Body-mapped builder</span></div>
        <div class="ft-cell"><span class="ft-check n">✕</span></div>
        <div class="ft-cell"><span class="ft-check y">✓</span></div>
      </div>
      <div class="feat-table-row">
        <div class="ft-feat-name"><strong>AI workout suggestions</strong><span>Adapts to your progress</span></div>
        <div class="ft-cell"><span class="ft-check n">✕</span></div>
        <div class="ft-cell"><span class="ft-check y">✓</span></div>
      </div>

      <div class="ft-cat-row">
        <div class="ft-cat-label">🛒 Marketplace</div>
      </div>
      <div class="feat-table-row">
        <div class="ft-feat-name"><strong>Browse marketplace</strong></div>
        <div class="ft-cell"><span class="ft-check y">✓</span></div>
        <div class="ft-cell"><span class="ft-check y">✓</span></div>
      </div>
      <div class="feat-table-row">
        <div class="ft-feat-name"><strong>Delivery system</strong><span>Order fresh to your door</span></div>
        <div class="ft-cell"><span class="ft-check n">✕</span></div>
        <div class="ft-cell"><span class="ft-check g">★</span></div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS -->
  <section class="testimonials-section">
    <p class="section-label">Social proof</p>
    <h2 class="section-title" style="color:var(--dark)">Real people. <span style="color:var(--green)">Real
        results.</span></h2>
    <div class="testi-grid">
      <div class="testi-card">
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">"The ingredient photo scan alone is worth the upgrade. I snap my lunch and get full macros
          in seconds. I've lost 8kg in 3 months without feeling deprived."</p>
        <div class="testi-author">
          <div class="testi-avatar" style="background:#d4edda">🏃</div>
          <div>
            <div class="testi-name">Amina B.</div>
            <div class="testi-role">Premium · Tunis</div>
          </div>
        </div>
      </div>
      <div class="testi-card" style="border-color:rgba(232,184,75,.3);">
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">"The AI meal plans are scarily good. It remembered I hate cilantro and started avoiding it
          in suggestions. The marketplace is where I do all my grocery shopping now."</p>
        <div class="testi-author">
          <div class="testi-avatar" style="background:#fdf3dc">🧑‍🍳</div>
          <div>
            <div class="testi-name">Karim T.</div>
            <div class="testi-role">Premium · Sfax</div>
          </div>
        </div>
      </div>
      <div class="testi-card">
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">"The workout planner combined with macro tracking is a game changer. I finally have
          everything in one app — no more switching between 4 different tools."</p>
        <div class="testi-author">
          <div class="testi-avatar" style="background:#fce4ec">💪</div>
          <div>
            <div class="testi-name">Youssef A.</div>
            <div class="testi-role">Premium · Sousse</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="faq-section">
    <p class="section-label">Questions</p>
    <h2 class="section-title">Got questions?</h2>
    <div class="faq-list" id="faq-list">
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">Can I cancel anytime? <span class="faq-chevron">▾</span></div>
        <div class="faq-a">Yes, absolutely. You can cancel your subscription at any time from your account settings.
          You'll keep access to Premium features until the end of your billing cycle, and then revert to the Free plan —
          no questions asked.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">What payment methods do you accept? <span
            class="faq-chevron">▾</span></div>
        <div class="faq-a">We accept all major credit and debit cards (Visa, Mastercard), local Tunisian bank transfers,
          and D17 mobile payment. All transactions are secured and encrypted.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">Is there a free trial for Premium? <span
            class="faq-chevron">▾</span></div>
        <div class="faq-a">Yes! New users get a 7-day free trial of Premium with no credit card required. You'll get
          full access to all Premium features, and you can upgrade or cancel before the trial ends.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">How does the AI meal plan work? <span class="faq-chevron">▾</span>
        </div>
        <div class="faq-a">Our AI analyses your health profile, dietary goals, allergies, food preferences, and even
          your ingredient inventory to suggest personalised meals. The more you use Foovia, the smarter your suggestions
          get.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">Can I switch between plans? <span class="faq-chevron">▾</span>
        </div>
        <div class="faq-a">Yes. You can upgrade or downgrade between Free and Premium at any time. If you upgrade
          mid-cycle, you'll be charged a prorated amount. Downgrading takes effect at the next billing date.</div>
      </div>
    </div>
  </section>

  <!-- FINAL CTA -->
  <section class="final-cta">
    <h2 class="final-cta-title">Ready to eat<br><span>smarter?</span></h2>
    <p class="final-cta-sub">Join thousands of Foovia Premium members who have already transformed their health.</p>
    <div class="final-cta-btns">
      <a href="#pricing" class="btn-gold">👑 Start Premium — 7 days free</a>
      <a href="foovia.html" class="btn-ghost">Explore the app first</a>
    </div>
    <p class="guarantee">🔒 No credit card required · Cancel anytime · 30-day money-back guarantee</p>
  </section>

  <footer>
    <div class="footer-logo">🌿 FOOVIA</div>
    <p>© 2026 Foovia. All rights reserved.</p>
    <div class="footer-links">
      <a href="#">Privacy</a>
      <a href="#">Terms</a>
      <a href="#">Support</a>
      <a href="#">Refund policy</a>
    </div>
  </footer>

  <!-- SUCCESS MODAL -->
  <div id="success-modal"
    style="display:none;position:fixed;inset:0;background:rgba(17,16,8,.8);z-index:600;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(8px);">
    <div
      style="background:var(--off-white);border-radius:32px;padding:60px 40px;max-width:440px;width:100%;text-align:center;animation:modalIn .5s cubic-bezier(.34,1.56,.64,1) both;box-shadow:0 20px 50px rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.2);">
      <div style="font-size:5rem;margin-bottom:24px;filter:drop-shadow(0 10px 10px rgba(0,0,0,.1));" id="success-icon">🎉</div>
      <h2 style="font-family:'Boldonse',system-ui;font-size:2.2rem;margin-bottom:12px;color:var(--dark);background:linear-gradient(135deg,var(--gold-dark),var(--gold));-webkit-background-clip:text;-webkit-text-fill-color:transparent;"
        id="success-title">Congratulations!</h2>
      <p style="font-size:1rem;color:#444;margin-bottom:32px;line-height:1.6;" id="success-body">You've successfully updated your plan. Welcome to the next level of Foovia!</p>
      <button onclick="window.location.reload()"
        style="width:100%;background:linear-gradient(135deg,var(--green),#3d8e43);color:white;border:none;border-radius:16px;padding:18px;font-family:'Boldonse',system-ui;font-size:1.05rem;cursor:pointer;font-weight:bold;box-shadow:0 10px 20px rgba(75,174,82,.3);">Great, let's go! →</button>
    </div>
  </div>

  <!-- PLAN MODAL -->
  <div id="plan-modal"
    style="display:none;position:fixed;inset:0;background:rgba(17,16,8,.7);z-index:500;align-items:center;justify-content:center;padding:20px;">
    <div
      style="background:var(--off-white);border-radius:26px;padding:48px 40px;max-width:400px;width:100%;text-align:center;animation:modalIn .35s cubic-bezier(.34,1.56,.64,1) both;">
      <div style="font-size:3.5rem;margin-bottom:16px;" id="modal-icon">👑</div>
      <h2 style="font-family:'Boldonse',system-ui;font-size:1.7rem;margin-bottom:10px;color:var(--dark);"
        id="modal-title">Upgrade to Premium</h2>
      <p style="font-size:.9rem;color:#666;margin-bottom:28px;line-height:1.65;" id="modal-body">You're about to unlock
        unlimited recipes, AI meal plans, and so much more.</p>
      <button id="confirm-upgrade-btn" onclick="confirmUpgrade()"
        style="width:100%;background:linear-gradient(135deg,var(--gold),var(--yellow-mid));color:var(--dark);border:none;border-radius:14px;padding:15px;font-family:'Boldonse',system-ui;font-size:.95rem;cursor:pointer;margin-bottom:10px;">Confirm
        & upgrade →</button>
      <button onclick="closeModal()"
        style="width:100%;background:none;border:1.5px solid rgba(0,0,0,.1);border-radius:14px;padding:13px;font-family:'Boldonse',system-ui;font-size:.88rem;cursor:pointer;color:#888;">Maybe
        later</button>
    </div>
  </div>

  <script>
    // ── PARTICLES ──
    (function () {
      const wrap = document.getElementById('particles');
      for (let i = 0; i < 28; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        const size = Math.random() * 4 + 2;
        p.style.cssText = `
      width:${size}px; height:${size}px;
      left:${Math.random() * 100}%;
      bottom:${Math.random() * 20}%;
      animation-duration:${6 + Math.random() * 14}s;
      animation-delay:${Math.random() * 10}s;
    `;
        wrap.appendChild(p);
      }
    })();

    // ── BILLING TOGGLE ──
    let isAnnual = false;
    const PRICES = { premium: { monthly: 19, annual: 13 }, elite: { monthly: 39, annual: 26 } };

    function toggleBilling() {
      isAnnual = !isAnnual;
      document.getElementById('billing-toggle').classList.toggle('annual', isAnnual);
      document.getElementById('lbl-monthly').classList.toggle('active', !isAnnual);
      document.getElementById('lbl-annual').classList.toggle('active', isAnnual);
      const mode = isAnnual ? 'annual' : 'monthly';
      document.getElementById('premium-price').textContent = PRICES.premium[mode];
      document.getElementById('elite-price').textContent = PRICES.elite[mode];
      document.getElementById('premium-annual').textContent = isAnnual ? `DT ${PRICES.premium.monthly * 12} billed annually` : 'Billed monthly';
      document.getElementById('elite-annual').textContent = isAnnual ? `DT ${PRICES.elite.monthly * 12} billed annually` : 'Billed monthly';
    }

    // ── PLAN SELECTION ──
    const PLAN_DATA = {
      free: { icon: '🌱', title: 'You\'re on the Free plan', body: 'You\'re already using Foovia for free. Upgrade to Premium to unlock the full experience.' },
      premium: { icon: '👑', title: 'Upgrade to Premium', body: 'You\'re about to unlock unlimited recipes, AI meal plans, ingredient scanning, and full marketplace access.' },
      elite: { icon: '🔥', title: 'Go Elite', body: 'You\'re about to unlock everything in Premium plus a personal dietitian, weekly reports, 1-on-1 coaching, and more.' },
    };
    let selectedPlan = '';
    function choosePlan(plan) {
      selectedPlan = plan;
      const d = PLAN_DATA[plan];
      document.getElementById('modal-icon').textContent = d.icon;
      document.getElementById('modal-title').textContent = d.title;
      document.getElementById('modal-body').textContent = d.body;
      const modal = document.getElementById('plan-modal');
      modal.style.display = 'flex';
      
      // Update button text and behavior based on plan
      const btn = document.getElementById('confirm-upgrade-btn');
      if (plan === 'free') {
          btn.textContent = 'Stay on Free';
      } else {
          btn.textContent = 'Confirm & upgrade →';
      }
    }

    function confirmUpgrade() {
        if (!selectedPlan) return;
        
        const btn = document.getElementById('confirm-upgrade-btn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Processing...';

        fetch('../../Controller/update_subscription.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ subscription: selectedPlan }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal();
                showSuccessModal(selectedPlan);
            } else {
                if (data.message === 'User not logged in') {
                    window.location.href = 'foovia-signin.php';
                } else {
                    alert('Error: ' + data.message);
                }
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = originalText;
            closeModal();
        });
    }

    function closeModal() { document.getElementById('plan-modal').style.display = 'none'; }
    function showSuccessModal(plan) {
        const modal = document.getElementById('success-modal');
        const title = document.getElementById('success-title');
        const body = document.getElementById('success-body');
        const icon = document.getElementById('success-icon');
        
        if (plan === 'free') {
            icon.textContent = '🌱';
            title.textContent = 'Plan Updated';
            body.textContent = 'You are now on the Free plan. You can upgrade back to Premium anytime to regain full access!';
        } else {
            icon.textContent = plan === 'elite' ? '🔥' : '👑';
            title.textContent = 'Congratulations!';
            body.textContent = `You've successfully upgraded to ${plan.charAt(0).toUpperCase() + plan.slice(1)}. Welcome to the next level of Foovia!`;
        }
        
        modal.style.display = 'flex';
    }
    document.getElementById('plan-modal').addEventListener('click', e => { if (e.target === document.getElementById('plan-modal')) closeModal(); });

    // ── FAQ ──
    function toggleFaq(el) {
      const item = el.closest('.faq-item');
      const wasOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
      if (!wasOpen) item.classList.add('open');
    }

    // ── THEME TOGGLE (from foovia.php) ──
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