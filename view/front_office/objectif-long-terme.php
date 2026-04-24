<?php
session_start();
require_once '../../controller/ObjectifLongTerme_Controller.php';

$controller = new ObjectifLongTerme_Controller();

function goal_type_label(string $type): string {
  $labels = [
    'prise_de_poids' => 'weight gain',
    'perte_de_poids' => 'weight loss',
    'maintien_de_poids' => 'weight maintenance'
  ];

  return $labels[$type] ?? $type;
}

function goal_status_label(string $status): string {
  $labels = [
    'en_attente' => 'pending',
    'en_cours' => 'in progress',
    'termine' => 'completed'
  ];

  return $labels[$status] ?? $status;
}

$edit_error_message = '';
$edit_objectif = null;
$edit_panel_visible = false;
$current_user_id = (int) ($_SESSION['user_id'] ?? 1);
$user_has_goal = $controller->user_has_goal($current_user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id_obj'])) {
  $update_id_obj = (int) $_POST['update_id_obj'];
  $existing_objectif = $controller->get_objectif_by_id($update_id_obj);

  if (!$existing_objectif) {
    $edit_error_message = 'The selected goal could not be found.';
    $edit_panel_visible = true;
  } else {
    $data = [
      'val_cible_obj' => (float) ($_POST['val_cible_obj'] ?? 0),
      'val_init_obj' => (float) ($_POST['val_init_obj'] ?? 0),
      'date_deb_obj' => $_POST['date_deb_obj'] ?? '',
      'date_fin_obj' => $_POST['date_fin_obj'] ?? '',
      'obj_cal_obj' => (float) ($_POST['obj_cal_obj'] ?? 0),
      'obj_fat_obj' => (float) ($_POST['obj_fat_obj'] ?? 0),
      'obj_prot_obj' => (float) ($_POST['obj_prot_obj'] ?? 0),
      'obj_carb_obj' => (float) ($_POST['obj_carb_obj'] ?? 0)
    ];

    if ($data['val_cible_obj'] <= 0 || $data['val_init_obj'] <= 0 || $data['obj_cal_obj'] <= 0 || $data['obj_fat_obj'] <= 0 || $data['obj_prot_obj'] <= 0 || $data['obj_carb_obj'] <= 0) {
      $edit_error_message = 'All numeric values must be strictly positive.';
      $edit_panel_visible = true;
    } elseif (empty($data['date_deb_obj']) || empty($data['date_fin_obj'])) {
      $edit_error_message = 'Start and end dates are required.';
      $edit_panel_visible = true;
    } elseif ($data['date_deb_obj'] > $data['date_fin_obj']) {
      $edit_error_message = 'The start date cannot be later than the end date.';
      $edit_panel_visible = true;
    } else {
      $updated = $controller->update_objectif_fields($update_id_obj, $data);
      if ($updated) {
        header('Location: objectif-long-terme.php#long-term-goals');
        exit;
      }

      $edit_error_message = 'The update failed.';
      $edit_panel_visible = true;
    }

    $edit_objectif = array_merge($existing_objectif, $data);
    $edit_objectif['id_obj'] = $existing_objectif['id_obj'];
    $edit_objectif['id_user'] = $existing_objectif['id_user'];
    $edit_objectif['type_obj'] = $existing_objectif['type_obj'];
    $edit_objectif['status_obj'] = $existing_objectif['status_obj'];
    $edit_objectif['frequency_rappel_obj'] = $existing_objectif['frequency_rappel_obj'];
    $edit_objectif['consistancy_sport_obj'] = $existing_objectif['consistancy_sport_obj'];
    $edit_objectif['consistency_alim_obj'] = $existing_objectif['consistency_alim_obj'];
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id_obj'])) {
  $controller->delete_objectif((int) $_POST['delete_id_obj']);
  header('Location: objectif-long-terme.php');
  exit;
}

$objectifs = $controller->list_objectifs();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FOOVIA Long Term Goals</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
  <link id="foovia-style" rel="stylesheet" href="./style.css?v=20260419">

  <script>
    (function () {
      const styleLink = document.getElementById('foovia-style');
      const candidates = [
        './style.css?v=20260419',
        'style.css?v=20260419',
        '/foovia/Esprit-PW-2A23-2526-Foovia-/view/front_office/style.css?v=20260419'
      ];
      let idx = 0;

      styleLink.addEventListener('error', function () {
        idx += 1;
        if (idx < candidates.length) {
          styleLink.href = candidates[idx];
        }
      });
    })();
  </script>

  <style>
    .goal-page {
      min-height: 100vh;
      background: var(--page-bg);
      color: var(--page-text);
    }

    .goal-main {
      padding-top: 0;
    }

    .goal-hero {
      padding: 76px 64px 36px;
      background:
        linear-gradient(135deg, rgba(17,16,8,.78) 0%, rgba(17,16,8,.48) 45%, rgba(17,16,8,.14) 100%),
        url('assets/macro-tracking_welcomePage.jpg') center/cover no-repeat;
      color: #fff;
    }

    .goal-kicker {
      font-family: 'Syne', sans-serif;
      font-size: .75rem;
      font-weight: 700;
      letter-spacing: .13em;
      text-transform: uppercase;
      color: var(--yellow);
      margin-bottom: 12px;
    }

    .goal-title {
      font-family: 'Boldonse', sans-serif;
      font-size: clamp(2.1rem, 4.6vw, 3.6rem);
      line-height: 1.2;
      margin-bottom: 14px;
      text-shadow: 0 8px 28px rgba(0,0,0,.28);
    }

    .goal-subtitle {
      font-family: 'DM Sans', sans-serif;
      font-size: 1.06rem;
      line-height: 1.65;
      color: rgba(255,255,255,.85);
      max-width: 680px;
      margin-bottom: 0;
    }

    .goal-section {
      padding: 54px 64px 80px;
    }

    .goal-shell {
      border-radius: 22px;
      border: 1.5px solid var(--surface-border);
      background:
        radial-gradient(120% 120% at 0% 0%, rgba(245, 200, 66, .1) 0%, rgba(245, 200, 66, 0) 58%),
        linear-gradient(160deg, var(--surface) 0%, var(--surface-2) 100%);
      box-shadow: 0 18px 40px rgba(17,16,8,.08);
      overflow: hidden;
    }

    .goal-head {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 24px;
      padding: 24px 26px 16px;
    }

    .goal-head h2 {
      font-family: 'Boldonse', sans-serif;
      font-size: clamp(1.3rem, 2.2vw, 1.8rem);
      line-height: 1.2;
      margin-bottom: 8px;
      color: var(--panel-text);
    }

    .goal-head p {
      font-family: 'DM Sans', sans-serif;
      font-size: .98rem;
      color: var(--panel-muted);
      margin: 0;
    }

    .goal-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      justify-content: flex-end;
    }

    .goal-chip {
      border-radius: 100px;
      padding: 11px 18px;
      font-family: 'Syne', sans-serif;
      font-size: .82rem;
      font-weight: 700;
      text-decoration: none;
      letter-spacing: .02em;
      transition: transform .15s ease, background-color .2s ease, color .2s ease;
      border: 1.5px solid transparent;
      display: inline-flex;
      align-items: center;
    }

    .goal-chip:hover {
      transform: translateY(-1px);
    }

    .goal-chip-add {
      background: var(--green);
      color: #fff;
    }

    .goal-chip-add:hover {
      background: var(--forest);
      color: #fff;
    }

    .goal-chip-track {
      background: transparent;
      color: var(--page-text);
      border-color: var(--surface-border);
    }

    .goal-chip-track:hover {
      background: var(--page-text);
      color: var(--page-bg);
    }

    .goal-table-wrap {
      overflow-x: auto;
      border-top: 1px solid var(--surface-border);
      border-bottom: 1px solid var(--surface-border);
      background: var(--surface);
    }

    .goal-table {
      width: 100%;
      min-width: 1240px;
      border-collapse: collapse;
      font-family: 'DM Sans', sans-serif;
    }

    .goal-table thead th {
      padding: 14px 12px;
      font-size: .76rem;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: var(--panel-muted);
      border-bottom: 1px solid var(--surface-border);
      background: rgba(245, 200, 66, .08);
      white-space: nowrap;
      font-weight: 700;
    }

    .goal-table tbody td {
      padding: 14px 12px;
      border-bottom: 1px solid var(--surface-border);
      color: var(--panel-text);
      font-size: .95rem;
      white-space: nowrap;
      vertical-align: middle;
    }

    .goal-table tbody tr:hover {
      background: rgba(75,174,82,.08);
    }

    .goal-status {
      display: inline-flex;
      align-items: center;
      padding: 4px 10px;
      border-radius: 100px;
      font-size: .78rem;
      font-weight: 700;
      letter-spacing: .02em;
      text-transform: capitalize;
    }

    .goal-status-pending {
      background: rgba(245,200,66,.2);
      color: #7f5f00;
    }

    .goal-status-progress {
      background: rgba(75,174,82,.2);
      color: #1f6f2d;
    }

    .goal-status-completed {
      background: rgba(217,79,0,.17);
      color: #9e3f00;
    }

    .goal-row-actions {
      display: flex;
      gap: 8px;
      justify-content: flex-end;
    }

    .goal-action {
      border: none;
      border-radius: 100px;
      padding: 8px 12px;
      font-family: 'Syne', sans-serif;
      font-size: .74rem;
      font-weight: 700;
      letter-spacing: .04em;
      text-transform: uppercase;
      text-decoration: none;
      cursor: pointer;
      transition: transform .15s ease, opacity .2s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .goal-action:hover {
      transform: translateY(-1px);
      opacity: .92;
    }

    .goal-edit {
      background: #2f6df6;
      color: #fff;
    }

    .goal-delete {
      background: #c0381a;
      color: #fff;
    }

    .goal-empty {
      text-align: center;
      padding: 30px 16px;
      color: var(--panel-muted);
      font-family: 'DM Sans', sans-serif;
    }

    .goal-footnote {
      font-family: 'DM Sans', sans-serif;
      font-size: .92rem;
      color: var(--panel-muted);
      padding: 14px 26px 18px;
      margin: 0;
    }

    .goal-modal .modal-content {
      border-radius: 18px;
      border: 1px solid var(--surface-border);
      background: var(--surface);
      color: var(--panel-text);
    }

    .goal-modal .modal-title {
      font-family: 'Syne', sans-serif;
      font-weight: 700;
    }

    .goal-modal .modal-body {
      font-family: 'DM Sans', sans-serif;
      color: var(--panel-muted);
    }

    .goal-modal-btn {
      border-radius: 999px;
      border: none;
      padding: 9px 16px;
      font-family: 'Syne', sans-serif;
      font-size: .82rem;
      font-weight: 700;
      text-decoration: none;
    }

    .goal-modal-cancel {
      background: transparent;
      color: var(--panel-text);
      border: 1.5px solid var(--surface-border);
    }

    .goal-modal-delete {
      background: #c0381a;
      color: #fff;
    }

    .goal-delete-panel {
      display: none;
      margin: 0 26px 22px;
      padding: 0.95rem 1.05rem;
      border-radius: 16px;
      background: rgba(245, 200, 66, 0.12);
      border: 1px solid rgba(17, 16, 8, 0.12);
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
    }

    .goal-delete-panel.is-visible {
      display: flex;
    }

    .goal-delete-panel span {
      font-size: 0.86rem;
      line-height: 1.35;
      color: var(--panel-text);
      font-weight: 600;
    }

    .goal-delete-actions {
      display: flex;
      gap: 0.45rem;
      flex-wrap: wrap;
    }

    .goal-delete-actions button {
      border: 0;
      border-radius: 999px;
      padding: 0.38rem 0.75rem;
      font-size: 0.8rem;
      font-weight: 700;
      cursor: pointer;
    }

    .goal-delete-yes {
      background: #c0381a;
      color: #fff;
    }

    .goal-delete-no {
      background: rgba(17, 16, 8, 0.08);
      color: var(--panel-text);
    }

    .goal-delete-wrap {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 0.5rem;
    }

    .goal-delete-confirm {
      display: none;
      flex-direction: column;
      gap: 0.45rem;
      padding: 0.7rem 0.8rem;
      border-radius: 14px;
      background: rgba(245, 200, 66, 0.12);
      border: 1px solid rgba(17, 16, 8, 0.12);
      min-width: 190px;
    }

    .goal-delete-confirm.is-visible {
      display: flex;
    }

    .goal-delete-confirm span {
      font-size: 0.86rem;
      line-height: 1.35;
      color: var(--panel-text);
      font-weight: 600;
    }

    .goal-delete-actions {
      display: flex;
      gap: 0.45rem;
    }

    .goal-delete-actions button {
      border: 0;
      border-radius: 999px;
      padding: 0.38rem 0.75rem;
      font-size: 0.8rem;
      font-weight: 700;
      cursor: pointer;
    }

    .goal-delete-yes {
      background: #c0381a;
      color: #fff;
    }

    .goal-delete-no {
      background: rgba(17, 16, 8, 0.08);
      color: var(--panel-text);
    }

    .goal-edit-trigger {
      border: 0;
    }

    .goal-edit-panel {
      display: none;
      margin: 0 26px 22px;
      padding: 1.15rem 1.15rem 1.25rem;
      border-radius: 18px;
      border: 1px solid rgba(17, 16, 8, 0.12);
      background:
        radial-gradient(120% 120% at 0% 0%, rgba(245, 200, 66, 0.14) 0%, rgba(245, 200, 66, 0) 58%),
        linear-gradient(160deg, var(--surface) 0%, var(--surface-2) 100%);
      box-shadow: 0 16px 34px rgba(17, 16, 8, 0.08);
    }

    .goal-edit-panel.is-visible {
      display: block;
    }

    .goal-edit-head {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .goal-edit-head small {
      display: block;
      text-transform: uppercase;
      letter-spacing: 0.13em;
      font-size: 0.72rem;
      font-weight: 700;
      color: var(--green);
      margin-bottom: 0.4rem;
      font-family: 'Syne', sans-serif;
    }

    .goal-edit-head h3 {
      margin: 0;
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      color: var(--panel-text);
    }

    .goal-edit-close {
      border: 0;
      border-radius: 999px;
      padding: 0.45rem 0.85rem;
      background: rgba(17, 16, 8, 0.08);
      color: var(--panel-text);
      font-weight: 700;
      font-family: 'Syne', sans-serif;
      cursor: pointer;
    }

    .goal-edit-error {
      margin: 0 0 1rem;
      color: #9d2f14;
      font-weight: 700;
      font-size: 0.92rem;
    }

    .goal-edit-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 1rem;
    }

    .goal-edit-card {
      border-radius: 14px;
      border: 1px solid rgba(17, 16, 8, 0.1);
      padding: 0.95rem;
      background: rgba(255, 255, 255, 0.45);
    }

    .goal-edit-card h4 {
      margin: 0 0 0.85rem;
      font-size: 0.78rem;
      letter-spacing: 0.13em;
      text-transform: uppercase;
      color: var(--green);
      font-family: 'Syne', sans-serif;
    }

    .goal-edit-card .form-label {
      font-size: 0.86rem;
      font-weight: 700;
      color: var(--panel-text);
      margin-bottom: 0.38rem;
    }

    .goal-edit-card .form-control,
    .goal-edit-card .form-select {
      border-radius: 14px;
      border: 1px solid rgba(17,16,8,.16);
      padding: 0.78rem 0.88rem;
      box-shadow: none;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
      background: var(--surface);
      color: var(--panel-text);
    }

    .goal-edit-card .form-control:focus,
    .goal-edit-card .form-select:focus {
      border-color: var(--green);
      box-shadow: 0 0 0 3px rgba(75, 174, 82, 0.16);
    }

    .goal-edit-card .form-control[readonly] {
      background: rgba(17,16,8,.05);
      color: var(--panel-muted);
    }

    .goal-edit-actions {
      display: flex;
      justify-content: flex-end;
      gap: 0.75rem;
      margin-top: 1rem;
      flex-wrap: wrap;
    }

    .goal-edit-save,
    .goal-edit-cancel {
      border: 0;
      border-radius: 999px;
      padding: 0.72rem 1.25rem;
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      cursor: pointer;
    }

    .goal-edit-save {
      background: #c0381a;
      color: #fff;
    }

    .goal-edit-cancel {
      background: rgba(17, 16, 8, 0.08);
      color: var(--panel-text);
    }

    @media (max-width: 900px) {
      .goal-main {
        padding-top: 82px;
      }

      .goal-hero {
        padding: 56px 28px 28px;
      }

      .goal-section {
        padding: 38px 28px 56px;
      }

      .goal-head {
        flex-direction: column;
        gap: 16px;
      }

      .goal-actions {
        width: 100%;
        justify-content: flex-start;
      }
    }

    @media (max-width: 600px) {
      .goal-title {
        line-height: 1.25;
      }

      .goal-actions {
        flex-direction: column;
      }

      .goal-chip {
        width: 100%;
        justify-content: center;
      }
    }
  </style>
</head>
<body class="goal-page">
  <main class="goal-main">
    <section class="goal-section" id="long-term-goals">
      <div class="goal-shell">
        <div class="goal-head">
          <div>
            <h2>Your goals list</h2>
            <p>View, edit, and remove long-term goals from one centralized table.</p>
          </div>
          <div class="goal-actions">
            <button
              type="button"
              class="goal-chip goal-chip-add"
              id="goal-add-trigger"
              data-can-add="<?php echo $user_has_goal ? '0' : '1'; ?>"
              data-add-url="../front_office/add-objectif-long-terme.php"
              style="border:0;"
            >
              Add Goal
            </button>
            <a href="tracking.php#long-term-goals" class="goal-chip goal-chip-track">Back to Tracking</a>
          </div>
        </div>
        <p id="goal-add-warning" style="display:none;margin:0 26px 12px;color:var(--panel-muted);font-size:.9rem;">Delete your existing goal before adding a new one.</p>

        <div class="goal-table-wrap">
          <table class="goal-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Target value</th>
                <th>Initial value</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th>Reminder</th>
                <th>Sport</th>
                <th>Diet</th>
                <th>Calories</th>
                <th>Fat</th>
                <th>Protein</th>
                <th>Carbs</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($objectifs)): ?>
                <?php foreach ($objectifs as $objectif): ?>
                  <?php
                    $statusRaw = (string) $objectif['status_obj'];
                    $statusLabel = goal_status_label($statusRaw);
                    $statusClass = 'goal-status-pending';
                    if ($statusLabel === 'in progress') {
                      $statusClass = 'goal-status-progress';
                    } elseif ($statusLabel === 'completed') {
                      $statusClass = 'goal-status-completed';
                    }
                  ?>
                  <tr>
                    <td><?php echo htmlspecialchars((string) $objectif['id_obj']); ?></td>
                    <td><?php echo htmlspecialchars(goal_type_label((string) $objectif['type_obj'])); ?></td>
                    <td><?php echo htmlspecialchars((string) $objectif['val_cible_obj']); ?></td>
                    <td><?php echo htmlspecialchars((string) $objectif['val_init_obj']); ?></td>
                    <td><?php echo htmlspecialchars($objectif['date_deb_obj']); ?></td>
                    <td><?php echo htmlspecialchars($objectif['date_fin_obj']); ?></td>
                    <td><span class="goal-status <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($statusLabel); ?></span></td>
                    <td><?php echo htmlspecialchars((string) $objectif['frequency_rappel_obj']); ?></td>
                    <td><?php echo htmlspecialchars((string) $objectif['consistancy_sport_obj']); ?></td>
                    <td><?php echo htmlspecialchars((string) $objectif['consistency_alim_obj']); ?></td>
                    <td><?php echo htmlspecialchars((string) $objectif['obj_cal_obj']); ?></td>
                    <td><?php echo htmlspecialchars((string) $objectif['obj_fat_obj']); ?></td>
                    <td><?php echo htmlspecialchars((string) $objectif['obj_prot_obj']); ?></td>
                    <td><?php echo htmlspecialchars((string) $objectif['obj_carb_obj']); ?></td>
                    <td>
                      <div class="goal-row-actions">
                        <button type="button" class="goal-action goal-edit goal-edit-trigger" data-objectif="<?php echo htmlspecialchars(json_encode($objectif, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8'); ?>">Edit</button>
                        <button type="button" class="goal-action goal-delete goal-delete-trigger" data-id="<?php echo htmlspecialchars((string) $objectif['id_obj']); ?>">Delete</button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="15" class="goal-empty">No long-term goals found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="goal-delete-panel" id="goal-delete-panel" hidden>
          <span>Are you sure you want to delete this goal?</span>
          <form method="post" action="" class="goal-delete-actions" id="goal-delete-form">
            <input type="hidden" name="delete_id_obj" id="goal-delete-id" value="">
            <button type="submit" class="goal-delete-yes">Yes</button>
            <button type="button" class="goal-delete-no" id="goal-delete-cancel">No</button>
          </form>
        </div>

        <div class="goal-edit-panel <?php echo $edit_panel_visible ? 'is-visible' : ''; ?>" id="goal-edit-panel" <?php echo $edit_panel_visible ? '' : 'hidden'; ?>>
          <div class="goal-edit-head">
            <div>
              <small>Edit long-term goal</small>
              <h3 id="goal-edit-title">Goal details</h3>
            </div>
            <button type="button" class="goal-edit-close" id="goal-edit-cancel">Close</button>
          </div>

          <?php if (!empty($edit_error_message)): ?>
            <p class="goal-edit-error"><?php echo htmlspecialchars($edit_error_message); ?></p>
          <?php endif; ?>

          <form method="post" action="" id="goal-edit-form">
            <input type="hidden" name="update_id_obj" id="goal-edit-id" value="<?php echo htmlspecialchars((string) ($edit_objectif['id_obj'] ?? '')); ?>">

            <div class="goal-edit-grid">
              <div class="goal-edit-card">
                <h4>Locked information</h4>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label" for="goal-edit-id-display">Goal ID</label>
                    <input type="text" class="form-control" id="goal-edit-id-display" value="<?php echo htmlspecialchars((string) ($edit_objectif['id_obj'] ?? '')); ?>" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="goal-edit-user-display">User ID</label>
                    <input type="text" class="form-control" id="goal-edit-user-display" value="<?php echo htmlspecialchars((string) ($edit_objectif['id_user'] ?? '')); ?>" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="goal-edit-type-display">Goal type</label>
                    <input type="text" class="form-control" id="goal-edit-type-display" value="<?php echo htmlspecialchars(goal_type_label((string) ($edit_objectif['type_obj'] ?? ''))); ?>" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="goal-edit-status-display">Status</label>
                    <input type="text" class="form-control" id="goal-edit-status-display" value="<?php echo htmlspecialchars(goal_status_label((string) ($edit_objectif['status_obj'] ?? ''))); ?>" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="goal-edit-reminder-display">Reminder frequency</label>
                    <input type="text" class="form-control" id="goal-edit-reminder-display" value="<?php echo htmlspecialchars((string) ($edit_objectif['frequency_rappel_obj'] ?? '')); ?>" readonly>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label" for="goal-edit-sport-display">Sport consistency</label>
                    <input type="text" class="form-control" id="goal-edit-sport-display" value="<?php echo htmlspecialchars((string) ($edit_objectif['consistancy_sport_obj'] ?? '')); ?>" readonly>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label" for="goal-edit-diet-display">Diet consistency</label>
                    <input type="text" class="form-control" id="goal-edit-diet-display" value="<?php echo htmlspecialchars((string) ($edit_objectif['consistency_alim_obj'] ?? '')); ?>" readonly>
                  </div>
                </div>
              </div>

              <div class="goal-edit-card">
                <h4>Editable fields</h4>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label" for="goal-edit-val-init">Initial value (kg)</label>
                    <input type="number" class="form-control" id="goal-edit-val-init" name="val_init_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) ($edit_objectif['val_init_obj'] ?? '')); ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="goal-edit-val-cible">Target value (kg)</label>
                    <input type="number" class="form-control" id="goal-edit-val-cible" name="val_cible_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) ($edit_objectif['val_cible_obj'] ?? '')); ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="goal-edit-date-deb">Start date</label>
                    <input type="date" class="form-control" id="goal-edit-date-deb" name="date_deb_obj" required value="<?php echo htmlspecialchars((string) ($edit_objectif['date_deb_obj'] ?? '')); ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="goal-edit-date-fin">End date</label>
                    <input type="date" class="form-control" id="goal-edit-date-fin" name="date_fin_obj" required value="<?php echo htmlspecialchars((string) ($edit_objectif['date_fin_obj'] ?? '')); ?>">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label" for="goal-edit-cal">Calories</label>
                    <input type="number" class="form-control" id="goal-edit-cal" name="obj_cal_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) ($edit_objectif['obj_cal_obj'] ?? '')); ?>">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label" for="goal-edit-fat">Fat</label>
                    <input type="number" class="form-control" id="goal-edit-fat" name="obj_fat_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) ($edit_objectif['obj_fat_obj'] ?? '')); ?>">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label" for="goal-edit-prot">Protein</label>
                    <input type="number" class="form-control" id="goal-edit-prot" name="obj_prot_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) ($edit_objectif['obj_prot_obj'] ?? '')); ?>">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label" for="goal-edit-carb">Carbs</label>
                    <input type="number" class="form-control" id="goal-edit-carb" name="obj_carb_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) ($edit_objectif['obj_carb_obj'] ?? '')); ?>">
                  </div>
                </div>
              </div>
            </div>

            <div class="goal-edit-actions">
              <button type="button" class="goal-edit-cancel" id="goal-edit-cancel-bottom">Cancel</button>
              <button type="submit" class="goal-edit-save">Save changes</button>
            </div>
          </form>
        </div>

        <p class="goal-footnote">Tip: open this page directly for a focused management view, or use it inside the tracking page in Long Term Goals.</p>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
  <script>
    (function () {
      const root = document.documentElement;
      const toggle = document.querySelector('.theme-toggle');

      const setTheme = function (theme) {
        const isDark = theme === 'dark';
        root.setAttribute('data-theme', theme);
        root.style.colorScheme = theme;
        if (toggle) {
          toggle.setAttribute('aria-pressed', String(isDark));
          toggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
        }
      };

      const stored = localStorage.getItem('theme');
      const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      const initialTheme = stored || (prefersDark ? 'dark' : 'light');
      setTheme(initialTheme);

      if (toggle) {
        toggle.addEventListener('click', function () {
          const currentTheme = root.getAttribute('data-theme') || 'light';
          const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
          localStorage.setItem('theme', nextTheme);
          setTheme(nextTheme);
        });
      }

      const deletePanel = document.getElementById('goal-delete-panel');
      const deleteInput = document.getElementById('goal-delete-id');
      const deleteCancel = document.getElementById('goal-delete-cancel');
      const goalAddTrigger = document.getElementById('goal-add-trigger');
      const goalAddWarning = document.getElementById('goal-add-warning');
      const editPanel = document.getElementById('goal-edit-panel');
      const editId = document.getElementById('goal-edit-id');
      const editTitle = document.getElementById('goal-edit-title');
      const editCancelTop = document.getElementById('goal-edit-cancel');
      const editCancelBottom = document.getElementById('goal-edit-cancel-bottom');

      function fillEditPanel(objectif) {
        const typeLabels = {
          prise_de_poids: 'weight gain',
          perte_de_poids: 'weight loss',
          maintien_de_poids: 'weight maintenance'
        };
        const statusLabels = {
          en_attente: 'pending',
          en_cours: 'in progress',
          termine: 'completed'
        };

        const fieldMap = {
          'goal-edit-id-display': objectif.id_obj,
          'goal-edit-user-display': objectif.id_user,
          'goal-edit-type-display': typeLabels[objectif.type_obj] || objectif.type_obj,
          'goal-edit-status-display': statusLabels[objectif.status_obj] || objectif.status_obj,
          'goal-edit-reminder-display': objectif.frequency_rappel_obj,
          'goal-edit-sport-display': objectif.consistancy_sport_obj,
          'goal-edit-diet-display': objectif.consistency_alim_obj,
          'goal-edit-val-init': objectif.val_init_obj,
          'goal-edit-val-cible': objectif.val_cible_obj,
          'goal-edit-date-deb': objectif.date_deb_obj,
          'goal-edit-date-fin': objectif.date_fin_obj,
          'goal-edit-cal': objectif.obj_cal_obj,
          'goal-edit-fat': objectif.obj_fat_obj,
          'goal-edit-prot': objectif.obj_prot_obj,
          'goal-edit-carb': objectif.obj_carb_obj
        };

        Object.keys(fieldMap).forEach(function (fieldId) {
          const field = document.getElementById(fieldId);
          if (field) {
            field.value = fieldMap[fieldId] ?? '';
          }
        });

        if (editId) {
          editId.value = objectif.id_obj || '';
        }

        if (editTitle) {
          editTitle.textContent = 'Goal #' + (objectif.id_obj || '');
        }
      }

      document.querySelectorAll('.goal-delete-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
          if (!deletePanel || !deleteInput) {
            return;
          }

          deleteInput.value = trigger.getAttribute('data-id') || '';
          deletePanel.hidden = false;
          deletePanel.classList.add('is-visible');
        });
      });

      if (deleteCancel && deletePanel && deleteInput) {
        deleteCancel.addEventListener('click', function () {
          deleteInput.value = '';
          deletePanel.hidden = true;
          deletePanel.classList.remove('is-visible');
        });
      }

      if (goalAddTrigger) {
        goalAddTrigger.addEventListener('click', function () {
          const canAdd = goalAddTrigger.getAttribute('data-can-add') === '1';
          const addUrl = goalAddTrigger.getAttribute('data-add-url') || '';

          if (!canAdd) {
            if (goalAddWarning) {
              goalAddWarning.style.display = 'block';
            }
            return;
          }

          if (goalAddWarning) {
            goalAddWarning.style.display = 'none';
          }

          if (addUrl) {
            window.location.href = addUrl;
          }
        });
      }

      document.querySelectorAll('.goal-edit-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
          if (!editPanel) {
            return;
          }

          let objectif = null;
          try {
            objectif = JSON.parse(trigger.getAttribute('data-objectif') || '{}');
          } catch (error) {
            objectif = null;
          }

          if (!objectif) {
            return;
          }

          fillEditPanel(objectif);
          editPanel.hidden = false;
          editPanel.classList.add('is-visible');
          editPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
      });

      function closeEditPanel() {
        if (!editPanel) {
          return;
        }

        editPanel.hidden = true;
        editPanel.classList.remove('is-visible');
      }

      if (editCancelTop) {
        editCancelTop.addEventListener('click', closeEditPanel);
      }

      if (editCancelBottom) {
        editCancelBottom.addEventListener('click', closeEditPanel);
      }
    })();
  </script>
</body>
</html>