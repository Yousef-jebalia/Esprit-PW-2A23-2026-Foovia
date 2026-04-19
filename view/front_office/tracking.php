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
        header('Location: tracking.php#long-term-goals');
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
  header('Location: tracking.php#long-term-goals');
  exit;
}

$objectifs = $controller->list_objectifs();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>FOOVIA Tracking Module</title>
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

    function bindInlineDeleteConfirm() {
      const panel = document.getElementById('ltg-delete-panel');
      const hiddenInput = document.getElementById('ltg-delete-id');
      const cancelButton = document.getElementById('ltg-delete-cancel');
      const editPanel = document.getElementById('ltg-edit-panel');
      const editId = document.getElementById('ltg-edit-id');
      const editTitle = document.getElementById('ltg-edit-title');
      const editCancelTop = document.getElementById('ltg-edit-cancel');
      const editCancelBottom = document.getElementById('ltg-edit-cancel-bottom');

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
          'ltg-edit-id-display': objectif.id_obj,
          'ltg-edit-user-display': objectif.id_user,
          'ltg-edit-type-display': typeLabels[objectif.type_obj] || objectif.type_obj,
          'ltg-edit-status-display': statusLabels[objectif.status_obj] || objectif.status_obj,
          'ltg-edit-reminder-display': objectif.frequency_rappel_obj,
          'ltg-edit-sport-display': objectif.consistancy_sport_obj,
          'ltg-edit-diet-display': objectif.consistency_alim_obj,
          'ltg-edit-val-init': objectif.val_init_obj,
          'ltg-edit-val-cible': objectif.val_cible_obj,
          'ltg-edit-date-deb': objectif.date_deb_obj,
          'ltg-edit-date-fin': objectif.date_fin_obj,
          'ltg-edit-cal': objectif.obj_cal_obj,
          'ltg-edit-fat': objectif.obj_fat_obj,
          'ltg-edit-prot': objectif.obj_prot_obj,
          'ltg-edit-carb': objectif.obj_carb_obj
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

      document.querySelectorAll('.ltg-delete-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
          if (!panel || !hiddenInput) {
            return;
          }

          hiddenInput.value = trigger.getAttribute('data-id') || '';
          panel.hidden = false;
          panel.classList.add('is-visible');
        });
      });

      if (cancelButton && panel && hiddenInput) {
        cancelButton.addEventListener('click', function () {
          hiddenInput.value = '';
          panel.hidden = true;
          panel.classList.remove('is-visible');
        });
      }

      document.querySelectorAll('.ltg-edit-trigger').forEach(function (trigger) {
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
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', bindInlineDeleteConfirm);
    } else {
      bindInlineDeleteConfirm();
    }
  })();
</script>
<style>
  .ltg-delete-panel {
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

  .ltg-delete-panel.is-visible {
    display: flex;
  }

  .ltg-delete-panel span {
    font-size: 0.86rem;
    line-height: 1.35;
    color: var(--panel-text);
    font-weight: 600;
  }

  .ltg-delete-actions {
    display: flex;
    gap: 0.45rem;
    flex-wrap: wrap;
  }

  .ltg-delete-actions button {
    border: 0;
    border-radius: 999px;
    padding: 0.38rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
  }

  .ltg-delete-yes {
    background: var(--red);
    color: #fff;
  }

  .ltg-delete-no {
    background: rgba(17, 16, 8, 0.08);
    color: var(--panel-text);
  }

  .ltg-edit-panel {
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

  .ltg-edit-panel.is-visible {
    display: block;
  }

  .ltg-edit-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .ltg-edit-head small {
    display: block;
    text-transform: uppercase;
    letter-spacing: 0.13em;
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--green);
    margin-bottom: 0.4rem;
    font-family: 'Syne', sans-serif;
  }

  .ltg-edit-head h3 {
    margin: 0;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    color: var(--panel-text);
  }

  .ltg-edit-close {
    border: 0;
    border-radius: 999px;
    padding: 0.45rem 0.85rem;
    background: rgba(17, 16, 8, 0.08);
    color: var(--panel-text);
    font-weight: 700;
    font-family: 'Syne', sans-serif;
    cursor: pointer;
  }

  .ltg-edit-error {
    margin: 0 0 1rem;
    color: #9d2f14;
    font-weight: 700;
    font-size: 0.92rem;
  }

  .ltg-edit-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
  }

  .ltg-edit-card {
    border-radius: 14px;
    border: 1px solid rgba(17, 16, 8, 0.1);
    padding: 0.95rem;
    background: rgba(255, 255, 255, 0.45);
  }

  .ltg-edit-card h4 {
    margin: 0 0 0.85rem;
    font-size: 0.78rem;
    letter-spacing: 0.13em;
    text-transform: uppercase;
    color: var(--green);
    font-family: 'Syne', sans-serif;
  }

  .ltg-edit-card .form-label {
    font-size: 0.86rem;
    font-weight: 700;
    color: var(--panel-text);
    margin-bottom: 0.38rem;
  }

  .ltg-edit-card .form-control,
  .ltg-edit-card .form-select {
    border-radius: 14px;
    border: 1px solid rgba(17,16,8,.16);
    padding: 0.78rem 0.88rem;
    box-shadow: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    background: var(--surface);
    color: var(--panel-text);
  }

  .ltg-edit-card .form-control:focus,
  .ltg-edit-card .form-select:focus {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(75, 174, 82, 0.16);
  }

  .ltg-edit-card .form-control[readonly] {
    background: rgba(17,16,8,.05);
    color: var(--panel-muted);
  }

  .ltg-edit-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 1rem;
    flex-wrap: wrap;
  }

  .ltg-edit-save,
  .ltg-edit-cancel {
    border: 0;
    border-radius: 999px;
    padding: 0.72rem 1.25rem;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    cursor: pointer;
  }

  .ltg-edit-save {
    background: linear-gradient(135deg, var(--orange) 0%, var(--red) 100%);
    color: #fff;
  }

  .ltg-edit-cancel {
    background: rgba(17, 16, 8, 0.08);
    color: var(--panel-text);
  }

  .weekly-calendar-shell {
    margin-top: 1rem;
    border: 1.5px solid var(--surface-border);
    border-radius: 22px;
    box-shadow: 0 18px 40px rgba(17, 16, 8, 0.08);
    background:
      radial-gradient(120% 120% at 0% 0%, rgba(245, 200, 66, .12) 0%, rgba(245, 200, 66, 0) 58%),
      linear-gradient(160deg, var(--surface) 0%, var(--surface-2) 100%);
    padding: 1.05rem;
  }

  .weekly-calendar-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.95rem;
  }

  .weekly-calendar-head h3 {
    margin: 0;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    color: var(--panel-text);
    font-size: clamp(1.05rem, 2vw, 1.3rem);
  }

  .weekly-cal-controls {
    display: flex;
    gap: 0.45rem;
  }

  .weekly-cal-btn {
    border: 0;
    border-radius: 999px;
    width: 34px;
    height: 34px;
    background: rgba(17, 16, 8, 0.08);
    color: var(--panel-text);
    font-weight: 800;
    font-family: 'Syne', sans-serif;
    cursor: pointer;
  }

  .weekly-cal-btn:hover {
    background: rgba(17, 16, 8, 0.14);
  }

  .weekly-cal-weekdays,
  .weekly-cal-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 0.45rem;
  }

  .weekly-cal-weekdays span {
    text-align: center;
    font-size: 0.72rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--panel-muted);
    font-weight: 700;
    padding: 0.35rem 0;
  }

  .weekly-cal-day {
    border: 1px solid rgba(17,16,8,.11);
    border-radius: 12px;
    min-height: 76px;
    background: rgba(255, 255, 255, 0.5);
    padding: 0.45rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    color: var(--panel-text);
  }

  .weekly-cal-day strong {
    font-size: 0.86rem;
    font-weight: 700;
  }

  .weekly-cal-day small {
    font-size: 0.72rem;
    color: var(--panel-muted);
  }

  .weekly-cal-day.is-other-month {
    opacity: 0.45;
  }

  .weekly-cal-day.is-today {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(75, 174, 82, 0.16);
    background: rgba(75, 174, 82, 0.09);
  }

  @media (max-width: 800px) {
    .weekly-cal-day {
      min-height: 62px;
    }
  }
</style>
</head>
<body>

<nav>
  <a href="index.html" class="nav-logo">
    <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" style="height: 50px; width: auto;">
    FOOVIA
  </a>
  <ul class="nav-links">
    <li><a href="#long-term-goals">Long Term Goals</a></li>
    <li><a href="#weekly-tracking">Weekly Tracking</a></li>
    <li><a href="#progress">Progress</a></li>
    <li><a href="#history">History</a></li>
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

<section class="hero hero-tracking">
  <div class="hero-text">
    <h1 class="hero-title">
      Track smarter.<br>
      Improve <span class="accent">daily.</span><br>
      Stay <span class="accent2">consistent.</span>
    </h1>

    <div class="hero-actions">
      <a href="#weekly-tracking" class="btn-primary">Open tracker</a>
      <a href="index.html" class="btn-secondary">Back to welcome</a>
    </div>
  </div>
</section>

<div class="features-strip">
  <div class="marquee-track">
    <span>Meal Logging</span><span class="sep">*</span>
    <span>Macro Tracking</span><span class="sep">*</span>
    <span>Hydration Goals</span><span class="sep">*</span>
    <span>Workout Consistency</span><span class="sep">*</span>
    <span>Daily Reminders</span><span class="sep">*</span>
    <span>Weekly Reports</span><span class="sep">*</span>
    <span>Meal Logging</span><span class="sep">*</span>
    <span>Macro Tracking</span><span class="sep">*</span>
    <span>Hydration Goals</span><span class="sep">*</span>
    <span>Workout Consistency</span><span class="sep">*</span>
    <span>Daily Reminders</span><span class="sep">*</span>
    <span>Weekly Reports</span><span class="sep">*</span>
  </div>
</div>

<section class="section" id="long-term-goals">
  <p class="section-label">Long Term Goals</p>
  <h2 class="section-title features-title">Manage your long-term goals in one place.</h2>

  <div class="ltg-shell">
    <div class="ltg-head">
      <div>
        <h3 class="ltg-headline">Long term goals list</h3>
      </div>
      <div class="ltg-actions">
        <button
          type="button"
          class="btn-primary ltg-open-survey"
          data-survey-url="../back_office/form-elements-component.php"
          data-can-add="<?php echo $user_has_goal ? '0' : '1'; ?>"
          aria-controls="ltg-survey-panel"
          aria-expanded="false"
        >
          Add Goal
        </button>
        <small id="ltg-add-warning" style="display:none;margin-top:8px;color:var(--panel-muted);font-size:.82rem;">Delete your existing goal before adding a new one.</small>
      </div>
    </div>

    <div class="ltg-table-wrap">
      <table class="ltg-table" aria-label="Long term goals table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Target</th>
            <th>Initial</th>
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
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($objectifs)): ?>
            <?php foreach ($objectifs as $objectif): ?>
              <?php
                $status = goal_status_label((string) $objectif['status_obj']);
                $statusClass = 'ltg-status-pending';
                if ($status === 'in progress') {
                  $statusClass = 'ltg-status-progress';
                } elseif ($status === 'completed') {
                  $statusClass = 'ltg-status-completed';
                }
              ?>
              <tr>
                <td><?php echo htmlspecialchars((string) $objectif['id_obj']); ?></td>
                <td><?php echo htmlspecialchars(goal_type_label((string) $objectif['type_obj'])); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['val_cible_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['val_init_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['date_deb_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['date_fin_obj']); ?></td>
                <td><span class="ltg-status <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($status); ?></span></td>
                <td><?php echo htmlspecialchars((string) $objectif['frequency_rappel_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['consistancy_sport_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['consistency_alim_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['obj_cal_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['obj_fat_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['obj_prot_obj']); ?></td>
                <td><?php echo htmlspecialchars((string) $objectif['obj_carb_obj']); ?></td>
                <td>
                  <div class="ltg-row-actions">
                    <button type="button" class="ltg-action ltg-edit ltg-edit-trigger" data-objectif="<?php echo htmlspecialchars(json_encode($objectif, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8'); ?>">Edit</button>
                    <button type="button" class="ltg-action ltg-delete ltg-delete-trigger" data-id="<?php echo htmlspecialchars((string) $objectif['id_obj']); ?>">Delete</button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="15" class="ltg-empty">No long-term goals found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

        <div class="ltg-delete-panel" id="ltg-delete-panel" hidden>
          <span>Are you sure you want to delete this goal?</span>
          <form method="post" action="" class="ltg-delete-actions" id="ltg-delete-form">
            <input type="hidden" name="delete_id_obj" id="ltg-delete-id" value="">
            <button type="submit" class="ltg-delete-yes">Yes</button>
            <button type="button" class="ltg-delete-no" id="ltg-delete-cancel">No</button>
          </form>
        </div>

        <div class="ltg-edit-panel <?php echo $edit_panel_visible ? 'is-visible' : ''; ?>" id="ltg-edit-panel" <?php echo $edit_panel_visible ? '' : 'hidden'; ?>>
          <div class="ltg-edit-head">
            <div>
              <small>Edit long-term goal</small>
              <h3 id="ltg-edit-title">Goal details</h3>
            </div>
            <button type="button" class="ltg-edit-close" id="ltg-edit-cancel">Close</button>
          </div>

          <?php if (!empty($edit_error_message)): ?>
            <p class="ltg-edit-error"><?php echo htmlspecialchars($edit_error_message); ?></p>
          <?php endif; ?>

          <form method="post" action="" id="ltg-edit-form">
            <input type="hidden" name="update_id_obj" id="ltg-edit-id" value="<?php echo htmlspecialchars((string) ($edit_objectif['id_obj'] ?? '')); ?>">

            <div class="ltg-edit-grid">
              <div class="ltg-edit-card">
                <h4>Locked information</h4>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label" for="ltg-edit-id-display">Goal ID</label>
                    <input type="text" class="form-control" id="ltg-edit-id-display" value="<?php echo htmlspecialchars((string) ($edit_objectif['id_obj'] ?? '')); ?>" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="ltg-edit-user-display">User ID</label>
                    <input type="text" class="form-control" id="ltg-edit-user-display" value="<?php echo htmlspecialchars((string) ($edit_objectif['id_user'] ?? '')); ?>" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="ltg-edit-type-display">Goal type</label>
                    <input type="text" class="form-control" id="ltg-edit-type-display" value="<?php echo htmlspecialchars(goal_type_label((string) ($edit_objectif['type_obj'] ?? ''))); ?>" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="ltg-edit-status-display">Status</label>
                    <input type="text" class="form-control" id="ltg-edit-status-display" value="<?php echo htmlspecialchars(goal_status_label((string) ($edit_objectif['status_obj'] ?? ''))); ?>" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="ltg-edit-reminder-display">Reminder frequency</label>
                    <input type="text" class="form-control" id="ltg-edit-reminder-display" value="<?php echo htmlspecialchars((string) ($edit_objectif['frequency_rappel_obj'] ?? '')); ?>" readonly>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label" for="ltg-edit-sport-display">Sport consistency</label>
                    <input type="text" class="form-control" id="ltg-edit-sport-display" value="<?php echo htmlspecialchars((string) ($edit_objectif['consistancy_sport_obj'] ?? '')); ?>" readonly>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label" for="ltg-edit-diet-display">Diet consistency</label>
                    <input type="text" class="form-control" id="ltg-edit-diet-display" value="<?php echo htmlspecialchars((string) ($edit_objectif['consistency_alim_obj'] ?? '')); ?>" readonly>
                  </div>
                </div>
              </div>

              <div class="ltg-edit-card">
                <h4>Editable fields</h4>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label" for="ltg-edit-val-init">Initial value (kg)</label>
                    <input type="number" class="form-control" id="ltg-edit-val-init" name="val_init_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) ($edit_objectif['val_init_obj'] ?? '')); ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="ltg-edit-val-cible">Target value (kg)</label>
                    <input type="number" class="form-control" id="ltg-edit-val-cible" name="val_cible_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) ($edit_objectif['val_cible_obj'] ?? '')); ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="ltg-edit-date-deb">Start date</label>
                    <input type="date" class="form-control" id="ltg-edit-date-deb" name="date_deb_obj" required value="<?php echo htmlspecialchars((string) ($edit_objectif['date_deb_obj'] ?? '')); ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="ltg-edit-date-fin">End date</label>
                    <input type="date" class="form-control" id="ltg-edit-date-fin" name="date_fin_obj" required value="<?php echo htmlspecialchars((string) ($edit_objectif['date_fin_obj'] ?? '')); ?>">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label" for="ltg-edit-cal">Calories</label>
                    <input type="number" class="form-control" id="ltg-edit-cal" name="obj_cal_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) ($edit_objectif['obj_cal_obj'] ?? '')); ?>">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label" for="ltg-edit-fat">Fat</label>
                    <input type="number" class="form-control" id="ltg-edit-fat" name="obj_fat_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) ($edit_objectif['obj_fat_obj'] ?? '')); ?>">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label" for="ltg-edit-prot">Protein</label>
                    <input type="number" class="form-control" id="ltg-edit-prot" name="obj_prot_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) ($edit_objectif['obj_prot_obj'] ?? '')); ?>">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label" for="ltg-edit-carb">Carbs</label>
                    <input type="number" class="form-control" id="ltg-edit-carb" name="obj_carb_obj" step="0.01" min="0.01" required value="<?php echo htmlspecialchars((string) ($edit_objectif['obj_carb_obj'] ?? '')); ?>">
                  </div>
                </div>
              </div>
            </div>

            <div class="ltg-edit-actions">
              <button type="button" class="ltg-edit-cancel" id="ltg-edit-cancel-bottom">Cancel</button>
              <button type="submit" class="ltg-edit-save">Save changes</button>
            </div>
          </form>
        </div>

  </div>
</section>

<section class="section ltg-survey-section" id="ltg-survey-panel" hidden>
  <div class="ltg-survey-shell">
    <div class="ltg-survey-head">
      <h3 class="ltg-headline">Long term goal survey</h3>
      <button type="button" class="ltg-close-survey" aria-label="Close survey">Close</button>
    </div>
    <iframe
      class="ltg-survey-frame"
      title="Long term goal survey"
      data-src="../back_office/form-elements-component.php"
    ></iframe>
  </div>
</section>

<section class="section" id="weekly-tracking">
  <p class="section-label">Weekly tracking</p>
  <h2 class="section-title features-title">Everything you need to stay on track this week.</h2>

  <div class="weekly-calendar-shell" aria-label="Weekly tracking calendar">
    <div class="weekly-calendar-head">
      <h3 id="weekly-cal-title">Month Year</h3>
      <div class="weekly-cal-controls">
        <button type="button" class="weekly-cal-btn" id="weekly-cal-prev" aria-label="Previous month">&lt;</button>
        <button type="button" class="weekly-cal-btn" id="weekly-cal-next" aria-label="Next month">&gt;</button>
      </div>
    </div>
    <div class="weekly-cal-weekdays" aria-hidden="true">
      <span>Sun</span>
      <span>Mon</span>
      <span>Tue</span>
      <span>Wed</span>
      <span>Thu</span>
      <span>Fri</span>
      <span>Sat</span>
    </div>
    <div class="weekly-cal-grid" id="weekly-cal-grid"></div>
  </div>
</section>

<section class="how" id="progress">
  <p class="section-label">Progress</p>
  <h2 class="section-title how-title">A simple flow to measure and improve your progress.</h2>
  <div class="steps">
    <div class="step">
      <div class="step-dot"></div>
      <div class="step-num">01</div>
      <h3>Log your meals</h3>
      <p>Capture breakfast, lunch, dinner, and snacks in seconds from text or photo.</p>
    </div>
    <div class="step">
      <div class="step-dot" style="background:var(--yellow)"></div>
      <div class="step-num">02</div>
      <h3>Track habits</h3>
      <p>Mark workouts, hydration, and supplements to keep your habit chain complete.</p>
    </div>
    <div class="step">
      <div class="step-dot" style="background:var(--orange)"></div>
      <div class="step-num">03</div>
      <h3>Review trends</h3>
      <p>See daily and weekly trend cards that highlight wins and missed targets.</p>
    </div>
    <div class="step">
      <div class="step-dot" style="background:var(--peach)"></div>
      <div class="step-num">04</div>
      <h3>Adapt your plan</h3>
      <p>Use the recommended adjustments to keep progress steady and sustainable.</p>
    </div>
  </div>
</section>

<div class="stats">
  <div class="stat">
    <div class="stat-num">24h</div>
    <div class="stat-label">Daily tracking cycle</div>
  </div>
  <div class="stat">
    <div class="stat-num">3x</div>
    <div class="stat-label">Faster meal logging</div>
  </div>
  <div class="stat">
    <div class="stat-num">7d</div>
    <div class="stat-label">Weekly progress reports</div>
  </div>
  <div class="stat">
    <div class="stat-num">1</div>
    <div class="stat-label">Single health dashboard</div>
  </div>
</div>

<section class="cta-section" id="history">
  <p class="section-label">History</p>
  <h2 class="cta-title">Your previous weeks,<br><em>clearly organized.</em></h2>
  <p>Review your completed logs, compare weekly snapshots, and learn from your history.</p>
  <a href="#weekly-tracking" class="btn-primary">Review Weekly Tracking</a>
</section>

<footer>
  <div class="footer-brand">
    <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" style="height: 36px; width: auto;">
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

    const openSurveyButton = document.querySelector('.ltg-open-survey');
    const surveyPanel = document.getElementById('ltg-survey-panel');
    const closeSurveyButton = document.querySelector('.ltg-close-survey');
    const surveyFrame = document.querySelector('.ltg-survey-frame');

    if (openSurveyButton && surveyPanel && surveyFrame) {
      openSurveyButton.addEventListener('click', () => {
        const canAdd = openSurveyButton.getAttribute('data-can-add') === '1';
        const addWarning = document.getElementById('ltg-add-warning');

        if (!canAdd) {
          if (addWarning) {
            addWarning.style.display = 'block';
          }
          return;
        }

        if (addWarning) {
          addWarning.style.display = 'none';
        }

        if (!surveyFrame.src) {
          surveyFrame.src = surveyFrame.getAttribute('data-src') || '';
        }
        surveyPanel.hidden = false;
        openSurveyButton.setAttribute('aria-expanded', 'true');
        surveyPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    }

    if (closeSurveyButton && surveyPanel && openSurveyButton) {
      closeSurveyButton.addEventListener('click', () => {
        surveyPanel.hidden = true;
        openSurveyButton.setAttribute('aria-expanded', 'false');
      });
    }

    const calTitle = document.getElementById('weekly-cal-title');
    const calGrid = document.getElementById('weekly-cal-grid');
    const calPrev = document.getElementById('weekly-cal-prev');
    const calNext = document.getElementById('weekly-cal-next');

    if (calTitle && calGrid && calPrev && calNext) {
      const monthLabels = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
      ];
      const baseDate = new Date();
      let displayedMonth = baseDate.getMonth();
      let displayedYear = baseDate.getFullYear();

      const renderCalendar = () => {
        const firstDay = new Date(displayedYear, displayedMonth, 1);
        const lastDay = new Date(displayedYear, displayedMonth + 1, 0);
        const prevMonthLastDay = new Date(displayedYear, displayedMonth, 0);

        const totalDays = lastDay.getDate();
        const firstWeekDayIndex = firstDay.getDay();
        const trailingDays = 42 - (firstWeekDayIndex + totalDays);

        calTitle.textContent = monthLabels[displayedMonth] + ' ' + displayedYear;
        calGrid.innerHTML = '';

        for (let i = firstWeekDayIndex; i > 0; i -= 1) {
          const dayNumber = prevMonthLastDay.getDate() - i + 1;
          const cell = document.createElement('div');
          cell.className = 'weekly-cal-day is-other-month';
          cell.innerHTML = '<strong>' + dayNumber + '</strong><small>Previous month</small>';
          calGrid.appendChild(cell);
        }

        for (let day = 1; day <= totalDays; day += 1) {
          const cellDate = new Date(displayedYear, displayedMonth, day);
          const isToday =
            cellDate.getDate() === baseDate.getDate() &&
            cellDate.getMonth() === baseDate.getMonth() &&
            cellDate.getFullYear() === baseDate.getFullYear();

          const cell = document.createElement('div');
          cell.className = 'weekly-cal-day' + (isToday ? ' is-today' : '');
          cell.innerHTML = '<strong>' + day + '</strong><small>Track goals</small>';
          calGrid.appendChild(cell);
        }

        for (let day = 1; day <= trailingDays; day += 1) {
          const cell = document.createElement('div');
          cell.className = 'weekly-cal-day is-other-month';
          cell.innerHTML = '<strong>' + day + '</strong><small>Next month</small>';
          calGrid.appendChild(cell);
        }
      };

      calPrev.addEventListener('click', () => {
        displayedMonth -= 1;
        if (displayedMonth < 0) {
          displayedMonth = 11;
          displayedYear -= 1;
        }
        renderCalendar();
      });

      calNext.addEventListener('click', () => {
        displayedMonth += 1;
        if (displayedMonth > 11) {
          displayedMonth = 0;
          displayedYear += 1;
        }
        renderCalendar();
      });

      renderCalendar();
    }
  })();
</script>

</body>
</html>
