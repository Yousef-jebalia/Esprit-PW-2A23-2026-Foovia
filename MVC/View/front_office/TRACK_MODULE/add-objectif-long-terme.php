<?php
session_start();
include '../../../Controller/tracking/ObjectifLongTerme_Controller.php';

// Initialisation des variables
$error_message = '';
$success_message = '';
$controller = new ObjectifLongTerme_Controller();
$user_id = $_SESSION['user_id'] ?? 1; // À adapter selon votre système d'authentification
$next_objectif_id = $controller->get_next_objectif_id();

$form_values = [
    'id_obj' => $next_objectif_id,
    'id_user' => $user_id,
    'type_obj' => '',
    'val_init_obj' => '',
    'val_cible_obj' => '',
    'date_deb_obj' => '',
    'date_fin_obj' => '',
    'status_obj' => 'en_attente',
    'frequency_rappel_obj' => '7',
    'consistancy_sport_obj' => '70',
    'consistency_alim_obj' => '70',
    'obj_cal_obj' => '',
    'obj_fat_obj' => '',
    'obj_prot_obj' => '',
    'obj_carb_obj' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_values = array_merge($form_values, [
        'id_obj' => (int) ($_POST['id_obj'] ?? $next_objectif_id),
        'id_user' => (int) ($_POST['id_user'] ?? $user_id),
        'type_obj' => (string) ($_POST['type_obj'] ?? ''),
        'val_init_obj' => (string) ($_POST['val_init_obj'] ?? ''),
        'val_cible_obj' => (string) ($_POST['val_cible_obj'] ?? ''),
        'date_deb_obj' => (string) ($_POST['date_deb_obj'] ?? ''),
        'date_fin_obj' => (string) ($_POST['date_fin_obj'] ?? ''),
        'status_obj' => (string) ($_POST['status_obj'] ?? 'en_attente'),
        'frequency_rappel_obj' => (string) ($_POST['frequency_rappel_obj'] ?? '7'),
        'consistancy_sport_obj' => (string) ($_POST['consistancy_sport_obj'] ?? '70'),
        'consistency_alim_obj' => (string) ($_POST['consistency_alim_obj'] ?? '70'),
        'obj_cal_obj' => (string) ($_POST['obj_cal_obj'] ?? ''),
        'obj_fat_obj' => (string) ($_POST['obj_fat_obj'] ?? ''),
        'obj_prot_obj' => (string) ($_POST['obj_prot_obj'] ?? ''),
        'obj_carb_obj' => (string) ($_POST['obj_carb_obj'] ?? '')
    ]);

    $data = [
        'id_obj' => (int) $form_values['id_obj'],
        'id_user' => (int) $form_values['id_user'],
        'type_obj' => $form_values['type_obj'],
        'val_cible_obj' => (float) $form_values['val_cible_obj'],
        'val_init_obj' => (float) $form_values['val_init_obj'],
        'date_deb_obj' => $form_values['date_deb_obj'],
        'date_fin_obj' => $form_values['date_fin_obj'],
        'status_obj' => $form_values['status_obj'],
        'frequency_rappel_obj' => (int) $form_values['frequency_rappel_obj'],
        'consistancy_sport_obj' => (int) $form_values['consistancy_sport_obj'],
        'consistency_alim_obj' => (int) $form_values['consistency_alim_obj'],
        'obj_cal_obj' => (float) $form_values['obj_cal_obj'],
        'obj_fat_obj' => (float) $form_values['obj_fat_obj'],
        'obj_prot_obj' => (float) $form_values['obj_prot_obj'],
        'obj_carb_obj' => (float) $form_values['obj_carb_obj']
    ];

    $errors = [];

    if (empty($data['type_obj'])) $errors[] = 'Goal type is required.';
    if ($data['val_init_obj'] <= 0) $errors[] = 'Initial value is required.';
    if ($data['val_cible_obj'] <= 0) $errors[] = 'Target value is required.';
    if (empty($data['date_deb_obj'])) $errors[] = 'Start date is required.';
    if (empty($data['date_fin_obj'])) $errors[] = 'End date is required.';

    $positive_fields = ['val_cible_obj', 'val_init_obj', 'obj_cal_obj', 'obj_fat_obj', 'obj_prot_obj', 'obj_carb_obj', 'frequency_rappel_obj'];
    foreach ($positive_fields as $field) {
        if (!empty($data[$field]) && $data[$field] <= 0) {
            $errors[] = 'The field must be strictly positive.';
        }
    }

    if (!empty($data['date_deb_obj']) && !empty($data['date_fin_obj'])) {
        $date_deb = new DateTime($data['date_deb_obj']);
        $date_fin = new DateTime($data['date_fin_obj']);
        $today = new DateTime();
        $today->setTime(0, 0, 0);

        if ($date_deb < $today) $errors[] = 'The start date cannot be earlier than today.';
        if ($date_deb > $date_fin) $errors[] = 'The start date cannot be later than the end date.';

        $diff = $date_deb->diff($date_fin);
        if ($diff->days < 30) $errors[] = 'The minimum goal duration is 30 days.';

        if (empty($errors)) {
            $start_date = new DateTime($data['date_deb_obj']);
            $data['status_obj'] = $start_date < $today ? 'en_cours' : 'en_attente';
        }
    }

    if (empty($errors) && $controller->user_has_goal((int) $data['id_user'])) {
        $errors[] = 'You already have a long-term goal. Delete your existing goal before adding a new one.';
    }

    if (empty($errors)) {
        $objectif = new ObjectifLongTerme(
            $data['id_obj'],
            $data['id_user'],
            $data['type_obj'],
            $data['val_cible_obj'],
            $data['val_init_obj'],
            $data['date_deb_obj'],
            $data['date_fin_obj'],
            $data['status_obj'],
            $data['frequency_rappel_obj'],
            $data['consistancy_sport_obj'],
            $data['consistency_alim_obj'],
            $data['obj_cal_obj'],
            $data['obj_fat_obj'],
            $data['obj_prot_obj'],
            $data['obj_carb_obj']
        );

        ob_start();
        $saved = $controller->add_objectif($objectif, $data);
        ob_end_clean();

        if ($saved) {
            $success_message = 'Goal added successfully.';
            $next_objectif_id = $controller->get_next_objectif_id();
            $form_values = [
                'id_obj' => $next_objectif_id,
                'id_user' => $user_id,
                'type_obj' => '',
                'val_init_obj' => '',
                'val_cible_obj' => '',
                'date_deb_obj' => '',
                'date_fin_obj' => '',
                'status_obj' => 'en_attente',
                'frequency_rappel_obj' => '7',
                'consistancy_sport_obj' => '70',
                'consistency_alim_obj' => '70',
                'obj_cal_obj' => '',
                'obj_fat_obj' => '',
                'obj_prot_obj' => '',
                'obj_carb_obj' => ''
            ];
        } else {
            $error_message = 'The objective could not be saved.';
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/Plan de travail 1 no bg (3) (1).png">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foovia - Long Term Goal Survey</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Boldonse&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">
    <style>
        :root {
            --yellow: #F5C842;
            --green: #4BAE52;
            --orange: #D94F00;
            --yellow-mid: #F0A830;
            --green-light: #A8C45A;
            --peach: #F2A98A;
            --forest: #2E4A28;
            --red: #C0381A;
            --off-white: #FDF8EE;
            --dark: #111008;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            min-height: 100%;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background:
                radial-gradient(circle at 10% 10%, rgba(245, 200, 66, .24) 0%, rgba(245, 200, 66, 0) 48%),
                radial-gradient(circle at 90% 90%, rgba(75, 174, 82, .16) 0%, rgba(75, 174, 82, 0) 45%),
                var(--off-white);
            color: var(--dark);
            padding: 28px 22px 32px;
        }

        .survey-wrap {
            max-width: 980px;
            margin: 0 auto;
        }

        .survey-intro {
            margin-bottom: 1.1rem;
            border-radius: 22px;
            background:
                radial-gradient(120% 120% at 0% 0%, rgba(245, 200, 66, .16) 0%, rgba(245, 200, 66, 0) 58%),
                linear-gradient(160deg, #fff 0%, rgba(255, 255, 255, .92) 100%);
            border: 1.5px solid rgba(17, 16, 8, 0.08);
            box-shadow: 0 18px 36px rgba(17, 16, 8, 0.08);
            padding: 1rem 1.15rem;
        }

        .survey-intro small {
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.13em;
            font-weight: 700;
            font-size: 0.68rem;
            color: var(--green);
            margin-bottom: 0.35rem;
            font-family: 'Boldonse', system-ui;
        }

        .survey-intro h2 {
            margin: 0;
            font-family: 'Boldonse', system-ui;
            font-weight: 400;
            font-size: clamp(1.1rem, 2vw, 1.45rem);
            color: var(--dark);
        }

        .lt-goal-banner {
            background: linear-gradient(135deg, var(--forest) 0%, #1a3015 100%);
            border-radius: 22px;
            padding: 28px;
            border: 1.5px solid rgba(75,174,82,.25);
            position: relative;
            overflow: hidden;
            box-shadow: 0 22px 44px rgba(17, 16, 8, 0.1);
        }

        .lt-goal-banner::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(75,174,82,.08);
            pointer-events: none;
        }

        .sec-label {
            font-family: 'Boldonse', system-ui;
            font-size: .65rem;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--green-light);
            margin-bottom: 4px;
        }

        .card-title {
            margin: 0 0 22px;
            font-family: 'Boldonse', system-ui;
            font-size: 1.1rem;
            color: var(--yellow);
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .emoji {
            font-size: 1.1rem;
        }

        .lt-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 100px;
            font-family: 'Boldonse', system-ui;
            font-size: .68rem;
            letter-spacing: .05em;
            background: rgba(245,200,66,.12);
            color: var(--yellow);
            border: 1.5px solid rgba(245,200,66,.2);
            margin-left: 4px;
            vertical-align: middle;
        }

        .lt-status-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            flex-shrink: 0;
        }

        .lt-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 6px 0 18px;
        }

        .lt-divider::before,
        .lt-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,.1);
        }

        .lt-divider span {
            font-family: 'Boldonse', system-ui;
            font-size: .62rem;
            letter-spacing: .12em;
            color: rgba(255,255,255,.22);
            text-transform: uppercase;
            white-space: nowrap;
        }

        .lt-grid,
        .lt-consistency-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .lt-field {
            display: flex;
            flex-direction: column;
        }

        .lt-field.wide {
            grid-column: span 2;
        }

        .lt-field label {
            display: block;
            font-family: 'Boldonse', system-ui;
            font-size: .7rem;
            margin-bottom: 6px;
            color: rgba(253,248,238,.55);
            letter-spacing: .04em;
        }

        .lt-field label.accent-green { color: var(--green-light); }
        .lt-field label.accent-yellow { color: var(--yellow); }
        .lt-field label.accent-orange { color: var(--peach); }

        .lt-input,
        .lt-select,
        .lt-macro-pill input {
            width: 100%;
            border: 1.5px solid rgba(255,255,255,.12);
            border-radius: 12px;
            padding: 10px 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: .93rem;
            background: rgba(255,255,255,.07);
            color: var(--off-white);
            outline: none;
            transition: border-color .2s, background .2s;
        }

        .lt-input::placeholder,
        .lt-macro-pill input::placeholder {
            color: rgba(255,255,255,.25);
        }

        .lt-input:focus,
        .lt-select:focus,
        .lt-macro-pill input:focus {
            border-color: var(--green);
            background: rgba(255,255,255,.11);
        }

        .lt-input[readonly] {
            background: rgba(0,0,0,.2);
            color: rgba(255,255,255,.35);
            cursor: default;
            border-color: transparent;
        }

        .lt-select {
            appearance: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23A8C45A' stroke-width='1.6' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 12px;
            padding-right: 36px;
        }

        .lt-select option {
            background: #1e3318;
            color: var(--off-white);
        }

        .lt-slider-wrap label {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            font-family: 'Boldonse', system-ui;
            font-size: .7rem;
            margin-bottom: 8px;
            color: rgba(253,248,238,.55);
        }

        .lt-slider-wrap label span {
            font-size: .8rem;
            font-family: 'Boldonse', system-ui;
        }

        .lt-slider-wrap label.sport-lbl span { color: #3dd5cf; }
        .lt-slider-wrap label.diet-lbl span { color: var(--yellow); }

        .lt-slider {
            width: 100%;
            -webkit-appearance: none;
            height: 6px;
            border-radius: 100px;
            outline: none;
            cursor: pointer;
            background: linear-gradient(to right, #3dd5cf var(--val, 70%), rgba(255,255,255,.12) var(--val, 70%));
        }

        .lt-slider.diet {
            background: linear-gradient(to right, var(--yellow) var(--val, 70%), rgba(255,255,255,.12) var(--val, 70%));
        }

        .lt-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 2.5px solid var(--off-white);
            box-shadow: 0 2px 8px rgba(0,0,0,.4);
            cursor: pointer;
        }

        .lt-slider.sport::-webkit-slider-thumb { background: #3dd5cf; }
        .lt-slider.diet::-webkit-slider-thumb { background: var(--yellow); }

        .lt-macros-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .lt-macro-pill {
            background: rgba(0,0,0,.25);
            border-radius: 14px;
            padding: 12px 10px;
            text-align: center;
            border: 1.5px solid rgba(255,255,255,.07);
        }

        .lt-macro-pill label {
            font-family: 'Boldonse', system-ui;
            font-size: .62rem;
            display: block;
            margin-bottom: 8px;
            letter-spacing: .06em;
        }

        .lt-macro-pill.m-kcal label { color: var(--orange); }
        .lt-macro-pill.m-prot label { color: var(--green-light); }
        .lt-macro-pill.m-carb label { color: var(--yellow); }
        .lt-macro-pill.m-fat label { color: var(--peach); }

        .form-error {
            margin: 0 0 16px;
            padding: 12px 14px;
            border-radius: 14px;
            background: rgba(192,56,26,.12);
            color: #ffd9d2;
            border: 1px solid rgba(192,56,26,.2);
            font-weight: 700;
        }

        .btn-save-lt-goal {
            width: 100%;
            background: var(--green);
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 15px;
            font-family: 'Boldonse', system-ui;
            font-size: .95rem;
            cursor: pointer;
            transition: background .2s, transform .15s;
            letter-spacing: .02em;
            margin-top: 4px;
        }

        .btn-save-lt-goal:hover {
            background: #3a9440;
            transform: scale(1.01);
        }

        .lt-note {
            margin-top: 12px;
            font-size: 0.78rem;
            color: rgba(253,248,238,.45);
            line-height: 1.5;
        }

        @media (max-width: 760px) {
            body {
                padding: 18px 14px 24px;
            }

            .lt-grid,
            .lt-consistency-row,
            .lt-macros-grid {
                grid-template-columns: 1fr 1fr;
            }

            .lt-field.wide {
                grid-column: span 2;
            }
        }

        @media (max-width: 480px) {
            .lt-grid,
            .lt-consistency-row,
            .lt-macros-grid {
                grid-template-columns: 1fr;
            }

            .lt-field.wide {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <main class="survey-wrap">
        <div class="survey-intro">
            <small>🎯 Long-term objective</small>
            <h2>Set Your Goal</h2>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="form-error" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="form-error" role="status" style="background: rgba(75,174,82,.14); color: #dff8e1; border-color: rgba(75,174,82,.22);">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <section class="lt-goal-banner">
            <p class="sec-label">🎯 Long-term objective</p>
            <h2 class="card-title"><span class="emoji">🏆</span> Set Your Goal <span class="lt-status-badge" id="lt-status-badge">Pending</span></h2>

            <form id="objectifForm" method="POST" action="">
                <div class="lt-grid">
                    <div class="lt-field">
                        <label>Goal ID</label>
                        <input class="lt-input" type="text" id="lt-id-objectif" name="id_obj" value="<?php echo htmlspecialchars((string) $next_objectif_id); ?>" readonly>
                    </div>
                    <div class="lt-field">
                        <label>User ID</label>
                        <input class="lt-input" type="text" id="lt-id-user" name="id_user" value="<?php echo htmlspecialchars((string) $user_id); ?>" readonly>
                    </div>
                </div>

                <div class="lt-divider"><span>Goal parameters</span></div>

                <div class="lt-grid">
                    <div class="lt-field">
                        <label class="accent-green">Goal type</label>
                        <select class="lt-select" id="lt-goal-type" name="type_obj" onchange="updateLtGoalType()" required>
                            <option value="">Select a goal…</option>
                            <option value="prise_de_poids">⬆️ Weight gain</option>
                            <option value="perte_de_poids">⬇️ Weight loss</option>
                            <option value="maintien_de_poids">⚖️ Weight maintenance</option>
                        </select>
                    </div>
                    <div class="lt-field">
                        <label class="accent-green">Reminder frequency</label>
                        <select class="lt-select" id="lt-reminder" name="frequency_rappel_obj" required>
                            <option value="1">Daily</option>
                            <option value="2">Every 2 days</option>
                            <option value="7" selected>Weekly</option>
                            <option value="14">Bi-weekly</option>
                            <option value="30">Monthly</option>
                        </select>
                    </div>
                </div>

                <div class="lt-grid" style="margin-top:14px">
                    <div class="lt-field">
                        <label class="accent-yellow">Initial weight (kg)</label>
                        <input class="lt-input" type="number" id="lt-initial-value" name="val_init_obj" placeholder="e.g. 82.5" min="0.01" step="0.1" required value="<?php echo htmlspecialchars((string) ($form_values['val_init_obj'] ?? '')); ?>">
                    </div>
                    <div class="lt-field">
                        <label class="accent-yellow">Target weight (kg)</label>
                        <input class="lt-input" type="number" id="lt-target-value" name="val_cible_obj" placeholder="e.g. 72.0" min="0.01" step="0.1" required value="<?php echo htmlspecialchars((string) ($form_values['val_cible_obj'] ?? '')); ?>">
                    </div>
                </div>

                <div class="lt-grid" style="margin-top:14px">
                    <div class="lt-field">
                        <label>Start date</label>
                        <input class="lt-input" type="date" id="lt-start-date" name="date_deb_obj" required value="<?php echo htmlspecialchars((string) ($form_values['date_deb_obj'] ?? '')); ?>">
                    </div>
                    <div class="lt-field">
                        <label>End date</label>
                        <input class="lt-input" type="date" id="lt-end-date" name="date_fin_obj" required value="<?php echo htmlspecialchars((string) ($form_values['date_fin_obj'] ?? '')); ?>">
                    </div>
                </div>

                <div class="lt-divider" style="margin-top:18px"><span>Consistency targets</span></div>

                <div class="lt-consistency-row" style="margin-bottom:18px">
                    <div class="lt-slider-wrap">
                        <label class="sport-lbl">🏋️ Sport consistency <span id="lt-sport-val"><?php echo htmlspecialchars((string) (($form_values['consistancy_sport_obj'] ?? 70))); ?>%</span></label>
                        <input class="lt-slider sport" type="range" id="lt-sport" name="consistancy_sport_obj" min="0" max="100" value="<?php echo htmlspecialchars((string) (($form_values['consistancy_sport_obj'] ?? 70))); ?>" oninput="updateLtSlider('lt-sport','lt-sport-val')" style="--val:<?php echo htmlspecialchars((string) (($form_values['consistancy_sport_obj'] ?? 70))); ?>%">
                    </div>
                    <div class="lt-slider-wrap">
                        <label class="diet-lbl">🥗 Diet consistency <span id="lt-diet-val"><?php echo htmlspecialchars((string) (($form_values['consistency_alim_obj'] ?? 70))); ?>%</span></label>
                        <input class="lt-slider diet" type="range" id="lt-diet" name="consistency_alim_obj" min="0" max="100" value="<?php echo htmlspecialchars((string) (($form_values['consistency_alim_obj'] ?? 70))); ?>" oninput="updateLtSlider('lt-diet','lt-diet-val')" style="--val:<?php echo htmlspecialchars((string) (($form_values['consistency_alim_obj'] ?? 70))); ?>%">
                    </div>
                </div>

                <div class="lt-divider"><span>Macronutrient targets</span></div>

                <div class="lt-macros-grid" style="margin-bottom:22px">
                    <div class="lt-macro-pill m-kcal">
                        <label><svg width="14" height="14" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;fill:red;margin-right:6px;"><path d="M9.75838 1.09929C9.85156 1.13153 9.9852 1.17902 10.1535 1.24207C10.49 1.36812 10.9661 1.55678 11.5355 1.81078C12.6715 2.31752 14.193 3.09073 15.7215 4.15505C18.745 6.26052 22 9.65692 22 14.5393C22 16.6738 21.4305 18.7869 20.1046 20.3856C18.7552 22.0126 16.7095 23 14 23C13.9352 23 13.6752 22.9978 13.4169 22.8125C13.0566 22.5541 12.9699 22.1541 13.0085 21.8667C13.0376 21.6502 13.1305 21.5025 13.1576 21.4602C13.1966 21.3993 13.234 21.3556 13.2534 21.3338C13.293 21.2893 13.3281 21.2581 13.3407 21.247C13.3575 21.2322 13.3716 21.2207 13.3801 21.214C13.4065 21.1929 13.4323 21.1745 13.4402 21.1689L13.4413 21.1681L13.5185 21.1136C13.5762 21.0727 13.6587 21.0131 13.7588 20.9348C13.9606 20.7768 14.2297 20.546 14.4969 20.2526C15.0448 19.6509 15.5 18.8819 15.5 18C15.5 16.3681 14.571 14.8515 13.5067 13.669C12.9869 13.0914 12.4644 12.6267 12.0715 12.3065C12.0471 12.2866 12.0233 12.2674 12 12.2487C11.9767 12.2674 11.9529 12.2866 11.9285 12.3065C11.5356 12.6267 11.0131 13.0914 10.4933 13.669C9.42904 14.8515 8.5 16.3681 8.5 18C8.5 18.8887 8.95405 19.6581 9.49825 20.2564C9.76406 20.5486 10.0319 20.7779 10.2327 20.934C10.3323 21.0114 10.4142 21.0699 10.47 21.1087C10.4933 21.125 10.5115 21.1374 10.5281 21.1487L10.5401 21.1569C10.5471 21.1616 10.5635 21.1728 10.5787 21.1837C10.5832 21.187 10.6139 21.2089 10.6476 21.2376C10.6583 21.2467 10.6772 21.2632 10.6995 21.285C10.7154 21.3005 10.7647 21.3492 10.8157 21.4212C10.8424 21.4607 10.901 21.5658 10.9302 21.6326C10.9668 21.7437 10.9991 22.045 10.9733 22.2301C10.89 22.4562 10.6027 22.798 10.4241 22.9056C10.2979 22.9546 10.0834 22.9965 10 23C7.29045 23 5.24478 22.0126 3.89543 20.3856C2.56953 18.7869 2 16.6738 2 14.5393C2 11.9892 2.88357 10.3815 4.05286 9.15507C4.5965 8.58486 5.19715 8.10224 5.73579 7.66945L5.77852 7.63511C6.34602 7.17903 6.84273 6.7759 7.26778 6.31893C8.30821 5.20037 8.54446 4.18717 8.56055 3.49802C8.56885 3.14245 8.51857 2.85417 8.46943 2.66213C8.44495 2.56644 8.42112 2.49608 8.40592 2.45502C8.39834 2.43455 8.39298 2.42158 8.39089 2.41662C8.22725 2.05872 8.28834 1.6367 8.54841 1.34037C8.86981 0.974175 9.32884 0.950674 9.75838 1.09929Z" fill="red"/></svg> Calories</label>
                        <input type="number" id="lt-kcal" name="obj_cal_obj" placeholder="kcal" min="0.01" step="0.01" value="<?php echo htmlspecialchars((string) ($form_values['obj_cal_obj'] ?? '')); ?>">
                    </div>
                    <div class="lt-macro-pill m-prot">
                        <label><svg width="18" height="14" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;margin-right:6px;"><path style="fill:#666666;" d="M431.197,121.41C540.2,192.013,533.727,340.034,442.92,413.628c-1.704,1.375-3.432,2.713-5.199,4.025
    c-56.306,41.744-167.656,46.375-226.814-43.485c-50.854-77.266-103.236-76.534-132.954-78.276
    c-29.73-1.741-98.554-3.483-71.966-85.241C32.589,128.906,168.672,22.793,353.263,83.679
    C384.747,94.065,409.96,107.643,431.197,121.41z"/>
<path style="fill:#F95428;" d="M480.247,260.623c2.625,49.315-18.764,97.343-57.189,128.486c-1.363,1.098-2.751,2.183-4.126,3.205
    c-19.004,14.083-46.766,22.5-74.237,22.5c-0.013,0-0.013,0-0.013,0c-25.869,0-74.212-7.546-107.425-57.997
    c-24.758-37.617-52.444-62.969-84.635-77.506c-27.749-12.518-52.495-13.83-68.862-14.701c-1.363-0.076-6.347-0.353-6.347-0.353
    c-11.244-0.631-37.567-2.12-43.75-11.168c-3.521-5.149-2.65-17.364,2.335-32.671c9.048-27.812,34.21-57.921,67.297-80.547
    c28.506-19.471,76.307-42.69,142.216-42.69c31.838,0,64.773,5.54,97.86,16.455c24.771,8.164,47.22,19.055,70.679,34.248
    C454.252,173.93,477.761,213.97,480.247,260.623z"/>
<path style="fill:#F2F2F2;" d="M361.023,228.924c27.169,0,49.201,22.033,49.201,49.214s-22.033,49.214-49.201,49.214
    c-27.181,0-49.214-22.033-49.214-49.214S333.842,228.924,361.023,228.924z"/></svg> Protein</label>
                        <input type="number" id="lt-prot" name="obj_prot_obj" placeholder="g" min="0.01" step="0.01" value="<?php echo htmlspecialchars((string) ($form_values['obj_prot_obj'] ?? '')); ?>">
                    </div>
                    <div class="lt-macro-pill m-carb">
                        <label><svg width="18" height="14" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;margin-right:6px;"><path fill="#D99E82" d="M36 13.5c0-4.558-4.435-8.267-10-8.479V5H10v.021C4.435 5.233 0 8.942 0 13.5c0 1.861.747 3.576 2 4.976V31a4 4 0 0 0 4 4h24a4 4 0 0 0 4-4V18.476c1.253-1.4 2-3.115 2-4.976z"></path><path fill="#CC927A" d="M19 18.476h15v1.5H19z"></path><path fill="#FFE8B6" d="M21 13.5c0-3.461-3.538-6.291-8-6.489C12.835 7.004 10.668 7 10.5 7C5.806 7 2 9.91 2 13.5c0 1.595.754 3.053 2 4.184V30a3 3 0 0 0 3 3h9a3 3 0 0 0 3-3V17.679c1.244-1.131 2-2.586 2-4.179z"></path></svg> Carbs</label>
                        <input type="number" id="lt-carb" name="obj_carb_obj" placeholder="g" min="0.01" step="0.01" value="<?php echo htmlspecialchars((string) ($form_values['obj_carb_obj'] ?? '')); ?>">
                    </div>
                    <div class="lt-macro-pill m-fat">
                        <label><svg width="18" height="14" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align:middle;margin-right:6px;"><path d="M51.87 55.06s-22.76 8.59-28.21 10.49c-4.19 1.47-14.04 5.32-16.49 6.54s-3.3 2.17-3.57 4.08c-.27 1.91-.11 4.37.85 5.59s6.54 6.95 14.04 12.4s15.26 11.58 22.35 11.72c7.09.14 27.67-10.36 47.7-19.49s29.98-13.76 31.35-14.58s4.67-2.29 4.56-6.78c-.09-3.68-7.99-6.93-7.99-6.93l-27.64-13l-36.95 9.96z" fill="#6ca4ae"></path><path d="M55.96 62.56s-8.72 3.41-11.86 5.04c-3.13 1.64-27.39 10.63-27.39 10.63s-6.13 2.45-6.81 3.27s.55 2.73 1.36 3.82c.82 1.09 11.74 9.26 15.81 11.72c4.35 2.64 8.99 4.91 10.63 5.04c1.64.14 9.81-1.5 9.81-1.5l35.98-14.62c4.03-1.73 16.46-7.68 22.22-10.33c5.76-2.65 11.2-5.53 13.14-6.77c1.89-1.2 4.07-2.59 4.01-5.35c-.05-2.11-1.84-2.99-4.14-4.38c-2.3-1.38-11.28-5.41-11.28-5.41l-51.48 8.84z" fill="#a8cfd7"></path></svg> Fat</label>
                        <input type="number" id="lt-fat" name="obj_fat_obj" placeholder="g" min="0.01" step="0.01" value="<?php echo htmlspecialchars((string) ($form_values['obj_fat_obj'] ?? '')); ?>">
                    </div>
                </div>

                <input type="hidden" id="lt-status" name="status_obj" value="en_attente">

                <button class="btn-save-lt-goal" type="submit">💾 Save long-term goal</button>
            </form>

        </section>
    </main>

    <script>
        function updateLtSlider(sliderId, labelId) {
            const slider = document.getElementById(sliderId);
            const label = document.getElementById(labelId);
            if (!slider || !label) {
                return;
            }

            const value = slider.value;
            label.textContent = value + '%';
            slider.style.setProperty('--val', value + '%');
        }

        function updateLtGoalType() {
            const type = document.getElementById('lt-goal-type');
            const badge = document.getElementById('lt-status-badge');
            if (!type || !badge) {
                return;
            }

            const colors = {
                prise_de_poids: { bg: 'rgba(75,174,82,.13)', color: '#4BAE52', border: 'rgba(75,174,82,.25)', text: 'Weight gain' },
                perte_de_poids: { bg: 'rgba(217,79,0,.15)', color: '#D94F00', border: 'rgba(217,79,0,.25)', text: 'Weight loss' },
                maintien_de_poids: { bg: 'rgba(245,200,66,.13)', color: '#F5C842', border: 'rgba(245,200,66,.25)', text: 'Weight maintenance' }
            };

            const selected = colors[type.value];
            if (!selected) {
                badge.style.background = 'rgba(245,200,66,.12)';
                badge.style.color = '#F5C842';
                badge.style.borderColor = 'rgba(245,200,66,.2)';
                badge.textContent = 'Pending';
                return;
            }

            badge.style.background = selected.bg;
            badge.style.color = selected.color;
            badge.style.borderColor = selected.border;
            badge.textContent = selected.text;
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateLtSlider('lt-sport', 'lt-sport-val');
            updateLtSlider('lt-diet', 'lt-diet-val');
            updateLtGoalType();

            const startDate = document.getElementById('lt-start-date');
            if (startDate && !startDate.value) {
                startDate.value = new Date().toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>
