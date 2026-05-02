<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA — Custom Workouts</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
  /* ── PAGE LAYOUT ── */
  .cw-page {
    min-height: 100vh;
    padding-top: 90px;
    display: flex;
    align-items: flex-start;
    gap: 0;
  }

  /* ── LEFT PANEL ── */
  .cw-left {
    flex: 1;
    padding: 80px 64px 80px 64px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: calc(100vh - 90px);
  }

  .cw-eyebrow {
    display: inline-block;
    background: var(--yellow);
    color: var(--dark);
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: .78rem;
    letter-spacing: .14em;
    text-transform: uppercase;
    padding: 6px 16px;
    border-radius: 100px;
    margin-bottom: 28px;
  }

  .cw-title {
    font-family: 'Boldonse', sans-serif;
    font-size: clamp(2.4rem, 4.5vw, 4rem);
    line-height: 1.15;
    letter-spacing: -.02em;
    margin-bottom: 20px;
  }
  .cw-title .accent  { color: var(--green); }
  .cw-title .accent2 { color: var(--orange); }

  .cw-desc {
    font-family: 'DM Sans', sans-serif;
    font-size: 1.1rem;
    font-weight: 400;
    line-height: 1.75;
    color: var(--page-muted);
    max-width: 460px;
  }

  /* ── DIVIDER ── */
  .cw-divider {
    width: 1.5px;
    background: var(--surface-border);
    align-self: stretch;
    margin: 60px 0;
    flex-shrink: 0;
  }

  /* ── RIGHT PANEL ── */
  .cw-right {
    width: 420px;
    flex-shrink: 0;
    padding: 80px 48px 80px 48px;
    display: flex;
    flex-direction: column;
    gap: 24px;
    justify-content: center;
    min-height: calc(100vh - 90px);
  }

  .cw-right-label {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: .72rem;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--page-muted);
    margin-bottom: 4px;
  }

  /* ── CHOICE CARDS ── */
  .cw-choice-card {
    border: 2px solid var(--surface-border);
    border-radius: 20px;
    padding: 28px 26px;
    background: var(--surface);
    cursor: pointer;
    transition: border-color .22s, box-shadow .22s, transform .18s, background-color .3s;
    display: flex;
    align-items: flex-start;
    gap: 18px;
    text-align: left;
    width: 100%;
    font: inherit;
    color: var(--page-text);
  }
  .cw-choice-card:hover {
    border-color: var(--green);
    box-shadow: 0 8px 32px rgba(75,174,82,.15);
    transform: translateY(-2px);
  }
  .cw-choice-card.ai-card:hover {
    border-color: var(--orange);
    box-shadow: 0 8px 32px rgba(217,79,0,.15);
  }

  .cw-card-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    flex-shrink: 0;
  }
  .manual-card .cw-card-icon { background: rgba(75,174,82,.12); }
  .ai-card     .cw-card-icon { background: rgba(217,79,0,.12); }

  .cw-card-body { flex: 1; }
  .cw-card-title {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.05rem;
    margin-bottom: 6px;
  }
  .cw-card-sub {
    font-family: 'DM Sans', sans-serif;
    font-size: .88rem;
    font-weight: 400;
    line-height: 1.6;
    color: var(--page-muted);
  }

  .cw-card-arrow {
    margin-top: 4px;
    flex-shrink: 0;
    opacity: .35;
    transition: opacity .2s, transform .2s;
  }
  .cw-choice-card:hover .cw-card-arrow { opacity: 1; transform: translateX(4px); }

  /* ── AI FORM OVERLAY ── */
  .ai-form-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 200;
    background: rgba(17,16,8,.55);
    backdrop-filter: blur(6px);
    align-items: center;
    justify-content: center;
    padding: 24px;
  }
  .ai-form-overlay.open { display: flex; }

  .ai-form-panel {
    background: var(--surface);
    border-radius: 28px;
    padding: 44px 44px 40px;
    width: 100%;
    max-width: 560px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 32px 80px rgba(0,0,0,.28);
    animation: formSlideIn .32s cubic-bezier(.22,1,.36,1) both;
    color: var(--panel-text);
    transition: background-color .3s, color .3s;
  }

  @keyframes formSlideIn {
    from { opacity: 0; transform: translateY(28px) scale(.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
  }

  .ai-form-close {
    position: absolute;
    top: 18px; right: 20px;
    background: none;
    border: none;
    cursor: pointer;
    color: var(--panel-muted);
    padding: 6px;
    border-radius: 50%;
    transition: background .2s, color .2s;
    display: flex; align-items: center; justify-content: center;
  }
  .ai-form-close:hover { background: var(--surface-border); color: var(--panel-text); }
  .ai-form-close svg { width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; }

  .ai-form-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(217,79,0,.1);
    color: var(--orange);
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: .72rem;
    letter-spacing: .12em;
    text-transform: uppercase;
    padding: 5px 14px;
    border-radius: 100px;
    margin-bottom: 20px;
  }
  .ai-form-eyebrow .dot {
    width: 7px; height: 7px;
    background: var(--orange);
    border-radius: 50%;
    animation: blink 1.4s ease-in-out infinite;
  }
  @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.25} }

  .ai-form-title {
    font-family: 'Boldonse', sans-serif;
    font-size: 1.7rem;
    line-height: 1.2;
    margin-bottom: 8px;
  }

  .ai-form-subtitle {
    font-family: 'DM Sans', sans-serif;
    font-size: .95rem;
    font-weight: 400;
    line-height: 1.6;
    color: var(--panel-muted);
    margin-bottom: 32px;
  }

  /* ── FORM FIELDS ── */
  .form-group {
    margin-bottom: 22px;
  }
  .form-label {
    display: block;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: .78rem;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 8px;
    color: var(--panel-text);
  }
  .form-input,
  .form-select {
    width: 100%;
    padding: 13px 16px;
    border-radius: 12px;
    border: 1.5px solid var(--surface-border);
    background: var(--surface-2);
    color: var(--panel-text);
    font-family: 'DM Sans', sans-serif;
    font-size: .95rem;
    font-weight: 400;
    transition: border-color .2s, box-shadow .2s, background-color .3s, color .3s;
    outline: none;
    appearance: none;
  }
  .form-input:focus,
  .form-select:focus {
    border-color: var(--orange);
    box-shadow: 0 0 0 3px rgba(217,79,0,.12);
  }
  .form-input::placeholder { color: var(--panel-muted); }

  .form-select-wrap {
    position: relative;
  }
  .form-select-wrap::after {
    content: '';
    position: absolute;
    right: 16px; top: 50%;
    transform: translateY(-50%);
    width: 0; height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 6px solid var(--panel-muted);
    pointer-events: none;
  }

  /* muscle chip multi-select */
  .muscle-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 2px;
  }
  .muscle-chip {
    padding: 7px 14px;
    border-radius: 100px;
    border: 1.5px solid var(--surface-border);
    background: var(--surface-2);
    color: var(--panel-text);
    font-family: 'DM Sans', sans-serif;
    font-size: .82rem;
    font-weight: 500;
    cursor: pointer;
    transition: border-color .18s, background .18s, color .18s;
    user-select: none;
  }
  .muscle-chip:hover { border-color: var(--orange2); }
  .muscle-chip.selected {
    background: var(--orange);
    border-color: var(--orange);
    color: #fff;
  }

  .form-hint {
    font-family: 'DM Sans', sans-serif;
    font-size: .78rem;
    color: var(--panel-muted);
    margin-top: 8px;
  }

  /* ── SUBMIT BTN ── */
  .ai-form-submit {
    width: 100%;
    margin-top: 8px;
    padding: 16px 24px;
    border-radius: 100px;
    border: none;
    background: var(--orange);
    color: #fff;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    transition: background .2s, transform .15s, box-shadow .2s;
    box-shadow: 0 8px 28px rgba(217,79,0,.28);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
  }
  .ai-form-submit:hover {
    background: var(--red);
    transform: translateY(-2px);
    box-shadow: 0 12px 36px rgba(192,56,26,.35);
  }
  .ai-form-submit svg {
    width: 18px; height: 18px;
    stroke: #fff; fill: none;
    stroke-width: 2; stroke-linecap: round;
  }

  /* ── WORKOUTS GRID ── */
  .workouts-section {
    padding: 80px 64px;
    width: 100%;
  }
  .workouts-header {
    margin-bottom: 40px;
  }
  .workouts-title {
    font-family: 'Boldonse', sans-serif;
    font-size: clamp(1.8rem, 3.5vw, 3rem);
    line-height: 1.2;
    margin-bottom: 12px;
  }
  .workouts-subtitle {
    font-family: 'DM Sans', sans-serif;
    font-size: 1rem;
    color: var(--page-muted);
    font-weight: 400;
  }

  .workouts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
  }

  .workout-card {
    border: 1.5px solid var(--surface-border);
    border-radius: 20px;
    padding: 28px;
    background: var(--surface);
    transition: border-color .22s, box-shadow .22s, transform .18s;
    cursor: pointer;
  }
  .workout-card:hover {
    border-color: var(--orange);
    box-shadow: 0 12px 40px rgba(217,79,0,.18);
    transform: translateY(-4px);
  }

  .workout-badge {
    display: inline-block;
    background: rgba(217,79,0,.1);
    color: var(--orange);
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: .68rem;
    letter-spacing: .1em;
    text-transform: uppercase;
    padding: 5px 12px;
    border-radius: 100px;
    margin-bottom: 16px;
  }

  .workout-name {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.2rem;
    margin-bottom: 12px;
    color: var(--page-text);
  }

  .workout-meta {
    display: flex;
    gap: 20px;
    font-family: 'DM Sans', sans-serif;
    font-size: .88rem;
    color: var(--page-muted);
  }
  .meta-item { display: flex; align-items: center; gap: 6px; }

  .workouts-empty {
    text-align: center;
    padding: 60px 40px;
    color: var(--page-muted);
  }
  .workouts-empty-icon {
    font-size: 3.5rem;
    margin-bottom: 16px;
    opacity: .5;
  }
  .workouts-empty-title {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 1.2rem;
    margin-bottom: 8px;
    color: var(--page-text);
  }

  .copy-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(9, 16, 25, 0.72);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 1300;
  }

  .copy-modal {
    width: min(860px, 96vw);
    max-height: 88vh;
    background: var(--panel-bg);
    border: 1px solid var(--surface-border);
    border-radius: 18px;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  .copy-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 16px 18px;
    border-bottom: 1px solid var(--surface-border);
  }

  .copy-modal-title {
    font-family: 'Syne', sans-serif;
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--page-text);
  }

  .copy-modal-close {
    width: 34px;
    height: 34px;
    border: 1px solid var(--surface-border);
    border-radius: 999px;
    background: transparent;
    color: var(--page-text);
    cursor: pointer;
    font-size: 1rem;
  }

  .copy-modal-body {
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .copy-modal-message {
    width: 100%;
    min-height: 360px;
    resize: vertical;
    border: 1px solid var(--surface-border);
    border-radius: 12px;
    padding: 14px;
    font-family: Consolas, Monaco, monospace;
    font-size: 0.92rem;
    line-height: 1.5;
    color: var(--page-text);
    background: var(--surface-2);
    outline: none;
    white-space: pre-wrap;
  }

  .copy-modal-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .copy-modal-btn {
    border: none;
    border-radius: 999px;
    padding: 10px 16px;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    cursor: pointer;
  }

  .copy-modal-btn.primary {
    background: var(--green);
    color: #fff;
  }

  .copy-modal-btn.secondary {
    background: transparent;
    color: var(--page-text);
    border: 1px solid var(--surface-border);
  }

  /* ── RESPONSIVE ── */
  @media (max-width: 900px) {
    .cw-page { flex-direction: column; padding-top: 80px; }
    .cw-left  { padding: 56px 28px 32px; min-height: unset; }
    .cw-divider { width: auto; height: 1.5px; margin: 0 28px; align-self: auto; }
    .cw-right { width: 100%; padding: 32px 28px 64px; }
    .ai-form-panel { padding: 32px 24px 28px; }
    .workouts-section { padding: 56px 28px; }
    .workouts-grid { grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); }
  }
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <a href="#" class="nav-logo">
    <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" style="height: 50px; width: auto;">
    FOOVIA
  </a>
  <ul class="nav-links">
    <li><a href="Exercice.php">Exercice</a></li>
    <li><a href="Workout.php">Workouts</a></li>
    <li><a href="custome_workout.php">Custom Workouts</a></li>
  </ul>
  <div class="nav-actions">
    <a href="backoffice.html" class="nav-btn nav-backoffice">Backoffice</a>
    <button class="theme-toggle" type="button" aria-label="Switch to dark mode" aria-pressed="false">
      <svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true">
        <circle cx="12" cy="12" r="4"></circle>
        <path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"></path>
      </svg>
      <svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M21 14.5A8.5 8.5 0 1 1 9.5 3a7 7 0 1 0 11.5 11.5z"></path>
      </svg>
    </button>
    <a href="signin.html" class="nav-btn nav-signin">Sign In</a>
    <a href="signup.html" class="nav-btn nav-signup">Sign Up</a>
  </div>
</nav>

<!-- MAIN PAGE -->
<div class="cw-page">

  <!-- LEFT: intro copy -->
  <div class="cw-left">
    <span class="cw-eyebrow">Custom Workouts</span>
    <h1 class="cw-title">
      Build Your<br>
      <span class="accent">Perfect</span><br>
      <span class="accent2">Routine.</span>
    </h1>
    <p class="cw-desc">
      Design a workout that fits your goals — every rep, every set, every muscle.
      Go fully manual or let our AI build a smart plan tailored to the muscles you want to train.
    </p>
  </div>

  <!-- DIVIDER -->
  <div class="cw-divider"></div>

  <!-- RIGHT: action buttons -->
  <div class="cw-right">
    <p class="cw-right-label">Choose how to start</p>

    <!-- Manual button -->
    <button class="cw-choice-card manual-card" onclick="handleManual()">
      <div class="cw-card-icon">✏️</div>
      <div class="cw-card-body">
        <div class="cw-card-title">Build it Yourself</div>
        <div class="cw-card-sub">Pick your exercises, set your reps and rest times — full control over every detail.</div>
      </div>
      <svg class="cw-card-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M5 12h14M12 5l7 7-7 7"/>
      </svg>
    </button>

    <!-- AI button -->
    <button class="cw-choice-card ai-card" onclick="AI_workout_form()">
      <div class="cw-card-icon">🤖</div>
      <div class="cw-card-body">
        <div class="cw-card-title">Generate with AI</div>
        <div class="cw-card-sub">Tell us your workout name and target muscles — our AI crafts the perfect plan for you.</div>
      </div>
      <svg class="cw-card-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M5 12h14M12 5l7 7-7 7"/>
      </svg>
    </button>
  </div>

</div>

<!-- AI WORKOUT FORM OVERLAY -->
<div class="ai-form-overlay" id="aiFormOverlay" onclick="closeOnOverlay(event)">
  <div class="ai-form-panel" id="aiFormPanel">

    <!-- close -->
    <button class="ai-form-close" onclick="closeAIForm()" aria-label="Close">
      <svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </button>

    <!-- header -->
    <div class="ai-form-eyebrow">
      <span class="dot"></span> AI Generator
    </div>
    <h2 class="ai-form-title">Design Your Workout</h2>
    <p class="ai-form-subtitle">Name your session and pick the muscles you want to target — we'll handle the rest.</p>

    <!-- Workout name -->
    <div class="form-group">
      <label class="form-label" for="workoutName">Workout Name</label>
      <input
        class="form-input"
        type="text"
        id="workoutName"
        placeholder="e.g. Monday Push Day, Leg Destroyer…"
        maxlength="60"
      />
    </div>

    <!-- Workout image -->
    <div class="form-group">
      <label class="form-label" for="work_picture">Workout Picture</label>
      <input
        class="form-input"
        type="file"
        id="work_picture"
        accept="image/*"
      />
      <p class="form-hint">Add an image for this workout so it appears in your workout list.</p>
    </div>

    <!-- Muscle groups (chip select) -->
    <div class="form-group">
      <label class="form-label">Target Muscles</label>
      <div class="muscle-chips" id="muscleChips">
        <span class="muscle-chip" data-value="calves"      onclick="toggleChip(this)">Calves</span>
        <span class="muscle-chip" data-value="hamstrings"  onclick="toggleChip(this)">Hamstrings</span>
        <span class="muscle-chip" data-value="quadriceps"  onclick="toggleChip(this)">Quadriceps</span>
        <span class="muscle-chip" data-value="adductors"   onclick="toggleChip(this)">Adductors</span>
        <span class="muscle-chip" data-value="glutes"      onclick="toggleChip(this)">Glutes</span>
        <span class="muscle-chip" data-value="abs"         onclick="toggleChip(this)">Abs</span>
        <span class="muscle-chip" data-value="obliques"    onclick="toggleChip(this)">Obliques</span>
        <span class="muscle-chip" data-value="lower_back"  onclick="toggleChip(this)">Lower Back</span>
        <span class="muscle-chip" data-value="lats"        onclick="toggleChip(this)">Lats</span>
        <span class="muscle-chip" data-value="traps"       onclick="toggleChip(this)">Traps</span>
        <span class="muscle-chip" data-value="chest"       onclick="toggleChip(this)">Chest</span>
        <span class="muscle-chip" data-value="delts"       onclick="toggleChip(this)">Delts</span>
        <span class="muscle-chip" data-value="biceps"      onclick="toggleChip(this)">Biceps</span>
        <span class="muscle-chip" data-value="triceps"     onclick="toggleChip(this)">Triceps</span>
        <span class="muscle-chip" data-value="forearms"    onclick="toggleChip(this)">Forearms</span>
        <span class="muscle-chip" data-value="neck"        onclick="toggleChip(this)">Neck</span>
      </div>
      <p class="form-hint">Select one or more muscle groups to focus on.</p>
    </div>

    <!-- Submit -->
    <button class="ai-form-submit" onclick="submitAIWorkout()">
      <svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      Generate My Workout
    </button>

  </div>
</div>

<!-- WORKOUTS LIST SECTION -->
<section class="workouts-section" id="workoutsList">
  <div class="workouts-header">
    <h2 class="workouts-title">Your AI-Generated Workouts</h2>
    <p class="workouts-subtitle">View all your custom workouts created with AI</p>
  </div>
  <div class="workouts-grid" id="workoutsGrid">
    <div class="workouts-empty">
      <div class="workouts-empty-icon">📭</div>
      <div class="workouts-empty-title">No workouts yet</div>
      <p>Create your first AI-generated workout to get started.</p>
    </div>
  </div>
</section>

<!-- COPYABLE ERROR MODAL -->
<div class="copy-modal-overlay" id="copyModalOverlay" onclick="closeCopyModal(event)">
  <div class="copy-modal" id="copyModal">
    <div class="copy-modal-header">
      <div class="copy-modal-title" id="copyModalTitle">Message</div>
      <button class="copy-modal-close" type="button" onclick="closeCopyModalNow()">×</button>
    </div>
    <div class="copy-modal-body">
      <textarea class="copy-modal-message" id="copyModalMessage" readonly></textarea>
      <div class="copy-modal-actions">
        <button class="copy-modal-btn primary" type="button" onclick="copyModalText()">Copy Message</button>
        <button class="copy-modal-btn secondary" type="button" onclick="closeCopyModalNow()">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  // Get user ID (you'll need to set this from your auth system)
  const userId = localStorage.getItem('userId') || '1'; // fallback to 1 for testing

  function loadAIWorkouts() {
    fetch(`../../../MVC/controle/get_ai_workouts.php?user_id=${userId}`)
      .then(res => res.json())
      .then(workouts => {
        const grid = document.getElementById('workoutsGrid');
        
        if (!workouts || workouts.length === 0) {
          grid.innerHTML = `
            <div class="workouts-empty">
              <div class="workouts-empty-icon">📭</div>
              <div class="workouts-empty-title">No workouts yet</div>
              <p>Create your first AI-generated workout to get started.</p>
            </div>
          `;
          return;
        }

        grid.innerHTML = workouts.map(w => `
          <div class="workout-card" onclick="viewWorkout(${w.id_work})">
            <div class="workout-badge">🤖 AI Generated</div>
            <div class="workout-name">${w.name_work}</div>
            <div class="workout-meta">
              <div class="meta-item">
                <span>🔥</span>
                <span>${w.cal_work} cal</span>
              </div>
              <div class="meta-item">
                <span>⏱️</span>
                <span>${w.duree_work} min</span>
              </div>
              <div class="meta-item">
                <span>💪</span>
                <span>${w.exercises_count} exercises</span>
              </div>
            </div>
          </div>
        `).join('');
      })
      .catch(err => console.error('Error loading workouts:', err));
  }

  function viewWorkout(workoutId) {
    alert(`View workout ${workoutId} details (feature coming soon)`);
  }

  function showCopyModal(title, message) {
    document.getElementById('copyModalTitle').textContent = title || 'Message';
    const textArea = document.getElementById('copyModalMessage');
    textArea.value = message || '';
    document.getElementById('copyModalOverlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    setTimeout(() => textArea.focus(), 0);
  }

  function closeCopyModalNow() {
    document.getElementById('copyModalOverlay').style.display = 'none';
    document.body.style.overflow = '';
  }

  function closeCopyModal(event) {
    if (event.target === document.getElementById('copyModalOverlay')) {
      closeCopyModalNow();
    }
  }

  async function copyModalText() {
    const text = document.getElementById('copyModalMessage').value;
    try {
      await navigator.clipboard.writeText(text);
      const button = document.querySelector('.copy-modal-btn.primary');
      const previousText = button.textContent;
      button.textContent = 'Copied';
      setTimeout(() => { button.textContent = previousText; }, 1200);
    } catch (err) {
      const textArea = document.getElementById('copyModalMessage');
      textArea.focus();
      textArea.select();
      document.execCommand('copy');
    }
  }

  // Load workouts on page load
  document.addEventListener('DOMContentLoaded', loadAIWorkouts);
  /* ── THEME TOGGLE ── */
  (function() {
    const root   = document.documentElement;
    const toggle = document.querySelector('.theme-toggle');
    const setTheme = (theme) => {
      const isDark = theme === 'dark';
      root.setAttribute('data-theme', theme);
      root.style.colorScheme = theme;
      toggle.setAttribute('aria-pressed', String(isDark));
      toggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
    };
    const stored      = localStorage.getItem('theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    setTheme(stored || (prefersDark ? 'dark' : 'light'));
    toggle.addEventListener('click', () => {
      const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      localStorage.setItem('theme', next);
      setTheme(next);
    });
  })();

  /* ── MANUAL BUTTON ── */
  function handleManual() {
    alert('This feature will be provided in the next version.');
  }

  /* ── AI FORM OPEN / CLOSE ── */
  function AI_workout_form() {
    document.getElementById('aiFormOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeAIForm() {
    document.getElementById('aiFormOverlay').classList.remove('open');
    document.body.style.overflow = '';
  }

  function closeOnOverlay(e) {
    if (e.target === document.getElementById('aiFormOverlay')) closeAIForm();
  }

  /* ── MUSCLE CHIP TOGGLE ── */
  function toggleChip(el) {
    el.classList.toggle('selected');
  }

  /* ── SUBMIT ── */
  function submitAIWorkout() {
    const name    = document.getElementById('workoutName').value.trim();
    const selectedMuscles = [...document.querySelectorAll('.muscle-chip.selected')].map(c => c.dataset.value);

    if (!name) {
      document.getElementById('workoutName').focus();
      return;
    }
    if (selectedMuscles.length === 0) {
      alert('Please select at least one muscle group.');
      return;
    }

    // Disable button during submission
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle></svg> Generating...';

    const formData = new FormData();
    formData.append('workoutName', name);
    formData.append('targetMuscles', JSON.stringify(selectedMuscles));
    formData.append('userId', userId);
    formData.append('aiService', 'gemini');

    const workoutPicture = document.getElementById('work_picture').files[0];
    if (workoutPicture) {
      formData.append('work_picture', workoutPicture);
    }

    // Call backend to generate and save workout
    fetch('../../../MVC/controle/submit_ai_workout.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.text())
    .then(text => {
      btn.disabled = false;
      btn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg> Generate My Workout';
      
      console.log('Response:', text);
      
      try {
        const data = JSON.parse(text);
        
        if (data.error) {
          showCopyModal('Workout Error', data.error);
          return;
        }

        alert(`✅ Workout "${name}" created successfully!`);
        closeAIForm();
        document.getElementById('workoutName').value = '';
        document.getElementById('work_picture').value = '';
        document.querySelectorAll('.muscle-chip').forEach(c => c.classList.remove('selected'));
        loadAIWorkouts();
      } catch (e) {
        showCopyModal('Invalid AI Response', text);
        console.error('Parse error:', e, 'Response:', text);
      }
    })
    .catch(err => {
      btn.disabled = false;
      btn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg> Generate My Workout';
      showCopyModal('Network Error', err.message);
      console.error('Network Error:', err);
    });
  }

  /* ── ESC to close ── */
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAIForm();
  });
</script>

</body>
</html>