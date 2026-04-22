<?php
session_start();
require_once '../../controller/ObjectifLongTerme_Controller.php';
require_once '../../controller/ObjectifHebdomadaire_Controller.php';

$controller = new ObjectifLongTerme_Controller();
$hebdo_controller = new ObjectifHebdomadaire_Controller();

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

$goal_action_error = '';
$long_term_error_message = '';
$current_user_id = (int) ($_SESSION['user_id'] ?? 1);
$system_date = date('Y-m-d');
$next_objectif_id = $controller->get_next_objectif_id();
$user_has_goal = $controller->user_has_goal($current_user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id_obj'])) {
  $delete_id_obj = (int) $_POST['delete_id_obj'];
  $goal_to_delete = $delete_id_obj > 0 ? $controller->get_objectif_by_id($delete_id_obj) : null;

  if (!empty($goal_to_delete) && (int) ($goal_to_delete['id_user'] ?? 0) === $current_user_id) {
    if ($controller->delete_objectif($delete_id_obj)) {
      header('Location: tracking.php#long-term-goals');
      exit;
    }

    $goal_action_error = 'The goal could not be deleted.';
  } else {
    $goal_action_error = 'You can only delete your own long-term goal.';
  }
}

$objectifs = $controller->list_objectifs();
$current_user_goal = null;
foreach ($objectifs as $objectif) {
  if ((int) ($objectif['id_user'] ?? 0) === $current_user_id) {
    $current_user_goal = $objectif;
    break;
  }
}

$long_term_form = [
  'id_obj' => !empty($current_user_goal['id_obj']) ? (int) $current_user_goal['id_obj'] : (int) $next_objectif_id,
  'id_user' => $current_user_id,
  'type_obj' => (string) ($current_user_goal['type_obj'] ?? ''),
  'val_init_obj' => (string) ($current_user_goal['val_init_obj'] ?? ''),
  'val_cible_obj' => (string) ($current_user_goal['val_cible_obj'] ?? ''),
  'date_deb_obj' => (string) ($current_user_goal['date_deb_obj'] ?? ''),
  'date_fin_obj' => (string) ($current_user_goal['date_fin_obj'] ?? ''),
  'status_obj' => (string) ($current_user_goal['status_obj'] ?? 'en_attente'),
  'frequency_rappel_obj' => (string) ($current_user_goal['frequency_rappel_obj'] ?? ''),
  'consistancy_sport_obj' => (string) ($current_user_goal['consistancy_sport_obj'] ?? '70'),
  'consistency_alim_obj' => (string) ($current_user_goal['consistency_alim_obj'] ?? '70'),
  'obj_cal_obj' => (string) ($current_user_goal['obj_cal_obj'] ?? ''),
  'obj_fat_obj' => (string) ($current_user_goal['obj_fat_obj'] ?? ''),
  'obj_prot_obj' => (string) ($current_user_goal['obj_prot_obj'] ?? ''),
  'obj_carb_obj' => (string) ($current_user_goal['obj_carb_obj'] ?? '')
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['long_term_save_goal']) && empty($current_user_goal)) {
  $long_term_form = array_merge($long_term_form, [
    'type_obj' => (string) ($_POST['type_obj'] ?? ''),
    'val_init_obj' => (string) ($_POST['val_init_obj'] ?? ''),
    'val_cible_obj' => (string) ($_POST['val_cible_obj'] ?? ''),
    'date_deb_obj' => (string) ($_POST['date_deb_obj'] ?? ''),
    'date_fin_obj' => (string) ($_POST['date_fin_obj'] ?? ''),
    'frequency_rappel_obj' => (string) ($_POST['frequency_rappel_obj'] ?? ''),
    'consistancy_sport_obj' => (string) ($_POST['consistancy_sport_obj'] ?? '70'),
    'consistency_alim_obj' => (string) ($_POST['consistency_alim_obj'] ?? '70'),
    'obj_cal_obj' => (string) ($_POST['obj_cal_obj'] ?? ''),
    'obj_fat_obj' => (string) ($_POST['obj_fat_obj'] ?? ''),
    'obj_prot_obj' => (string) ($_POST['obj_prot_obj'] ?? ''),
    'obj_carb_obj' => (string) ($_POST['obj_carb_obj'] ?? ''),
  ]);

  $data = [
    'id_obj' => (int) $long_term_form['id_obj'],
    'id_user' => (int) $long_term_form['id_user'],
    'type_obj' => $long_term_form['type_obj'],
    'val_cible_obj' => (float) $long_term_form['val_cible_obj'],
    'val_init_obj' => (float) $long_term_form['val_init_obj'],
    'date_deb_obj' => $long_term_form['date_deb_obj'],
    'date_fin_obj' => $long_term_form['date_fin_obj'],
    'status_obj' => 'en_attente',
    'frequency_rappel_obj' => (int) $long_term_form['frequency_rappel_obj'],
    'consistancy_sport_obj' => (int) $long_term_form['consistancy_sport_obj'],
    'consistency_alim_obj' => (int) $long_term_form['consistency_alim_obj'],
    'obj_cal_obj' => (float) $long_term_form['obj_cal_obj'],
    'obj_fat_obj' => (float) $long_term_form['obj_fat_obj'],
    'obj_prot_obj' => (float) $long_term_form['obj_prot_obj'],
    'obj_carb_obj' => (float) $long_term_form['obj_carb_obj'],
  ];

  $errors = [];
  $required_fields = [
    'type_obj' => 'Goal type',
    'val_init_obj' => 'Initial weight',
    'val_cible_obj' => 'Target weight',
    'date_deb_obj' => 'Start date',
    'date_fin_obj' => 'End date',
    'frequency_rappel_obj' => 'Reminder frequency',
    'obj_cal_obj' => 'Calories',
    'obj_fat_obj' => 'Fat',
    'obj_prot_obj' => 'Protein',
    'obj_carb_obj' => 'Carbs',
  ];
  $missing_fields = [];
  foreach ($required_fields as $field_key => $field_label) {
    if (trim((string) ($long_term_form[$field_key] ?? '')) === '') {
      $missing_fields[] = $field_label;
    }
  }

  if (!empty($missing_fields)) {
    $errors[] = 'Please complete all required fields: ' . implode(', ', $missing_fields) . '.';
  }

  if (empty($missing_fields) && ($data['val_init_obj'] <= 0 || $data['val_cible_obj'] <= 0 || $data['obj_cal_obj'] <= 0 || $data['obj_fat_obj'] <= 0 || $data['obj_prot_obj'] <= 0 || $data['obj_carb_obj'] <= 0 || $data['frequency_rappel_obj'] <= 0)) {
    $errors[] = 'All numeric values must be strictly positive.';
  }

  if (empty($data['date_deb_obj']) || empty($data['date_fin_obj'])) {
    $errors[] = 'Start and end dates are required.';
  } elseif ($data['date_deb_obj'] < $system_date) {
    $errors[] = 'The start date cannot be before today.';
  } elseif ($data['date_deb_obj'] > $data['date_fin_obj']) {
    $errors[] = 'The start date cannot be later than the end date.';
  } else {
    $start_timestamp = strtotime($data['date_deb_obj']);
    $end_timestamp = strtotime($data['date_fin_obj']);
    if ($start_timestamp === false || $end_timestamp === false || (($end_timestamp - $start_timestamp) < 30 * 24 * 60 * 60)) {
      $errors[] = 'The goal period must be at least 30 days.';
    }
  }

  if (empty($missing_fields) && $data['type_obj'] === 'prise_de_poids' && $data['val_cible_obj'] <= $data['val_init_obj']) {
    $errors[] = 'For weight gain, target value must be greater than initial value.';
  }
  if (empty($missing_fields) && $data['type_obj'] === 'perte_de_poids' && $data['val_cible_obj'] >= $data['val_init_obj']) {
    $errors[] = 'For weight loss, target value must be lower than initial value.';
  }
  if (empty($missing_fields) && $data['type_obj'] === 'maintien_de_poids' && abs($data['val_cible_obj'] - $data['val_init_obj']) > 0.000001) {
    $errors[] = 'For weight maintenance, target value must be equal to initial value.';
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
      header('Location: tracking.php#long-term-goals');
      exit;
    }

    $long_term_error_message = 'The goal could not be saved.';
  } else {
    $long_term_error_message = implode(' ', $errors);
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['long_term_update_goal']) && !empty($current_user_goal)) {
  $long_term_form = array_merge($long_term_form, [
    'val_init_obj' => (string) ($_POST['val_init_obj'] ?? $long_term_form['val_init_obj']),
    'val_cible_obj' => (string) ($_POST['val_cible_obj'] ?? $long_term_form['val_cible_obj']),
    'date_deb_obj' => (string) ($_POST['date_deb_obj'] ?? $long_term_form['date_deb_obj']),
    'date_fin_obj' => (string) ($_POST['date_fin_obj'] ?? $long_term_form['date_fin_obj']),
    'obj_cal_obj' => (string) ($_POST['obj_cal_obj'] ?? $long_term_form['obj_cal_obj']),
    'obj_fat_obj' => (string) ($_POST['obj_fat_obj'] ?? $long_term_form['obj_fat_obj']),
    'obj_prot_obj' => (string) ($_POST['obj_prot_obj'] ?? $long_term_form['obj_prot_obj']),
    'obj_carb_obj' => (string) ($_POST['obj_carb_obj'] ?? $long_term_form['obj_carb_obj']),
  ]);

  $update_data = [
    'type_obj' => (string) $long_term_form['type_obj'],
    'val_cible_obj' => (float) $long_term_form['val_cible_obj'],
    'val_init_obj' => (float) $long_term_form['val_init_obj'],
    'date_deb_obj' => $long_term_form['date_deb_obj'],
    'date_fin_obj' => $long_term_form['date_fin_obj'],
    'obj_cal_obj' => (float) $long_term_form['obj_cal_obj'],
    'obj_fat_obj' => (float) $long_term_form['obj_fat_obj'],
    'obj_prot_obj' => (float) $long_term_form['obj_prot_obj'],
    'obj_carb_obj' => (float) $long_term_form['obj_carb_obj'],
  ];

  if ($update_data['val_cible_obj'] <= 0 || $update_data['val_init_obj'] <= 0 || $update_data['obj_cal_obj'] <= 0 || $update_data['obj_fat_obj'] <= 0 || $update_data['obj_prot_obj'] <= 0 || $update_data['obj_carb_obj'] <= 0) {
    $long_term_error_message = 'All numeric values must be strictly positive.';
  } elseif (empty($update_data['date_deb_obj']) || empty($update_data['date_fin_obj'])) {
    $long_term_error_message = 'Start and end dates are required.';
  } elseif ($update_data['date_deb_obj'] < $system_date) {
    $long_term_error_message = 'The start date cannot be before today.';
  } elseif ($update_data['date_deb_obj'] > $update_data['date_fin_obj']) {
    $long_term_error_message = 'The start date cannot be later than the end date.';
  } elseif ((strtotime($update_data['date_fin_obj']) - strtotime($update_data['date_deb_obj'])) < 30 * 24 * 60 * 60) {
    $long_term_error_message = 'The goal period must be at least 30 days.';
  } elseif ($update_data['type_obj'] === 'prise_de_poids' && $update_data['val_cible_obj'] <= $update_data['val_init_obj']) {
    $long_term_error_message = 'For weight gain, target value must be greater than initial value.';
  } elseif ($update_data['type_obj'] === 'perte_de_poids' && $update_data['val_cible_obj'] >= $update_data['val_init_obj']) {
    $long_term_error_message = 'For weight loss, target value must be lower than initial value.';
  } elseif ($update_data['type_obj'] === 'maintien_de_poids' && abs($update_data['val_cible_obj'] - $update_data['val_init_obj']) > 0.000001) {
    $long_term_error_message = 'For weight maintenance, target value must be equal to initial value.';
  } else {
    $updated = $controller->update_objectif_fields((int) $current_user_goal['id_obj'], $update_data);
    if ($updated) {
      header('Location: tracking.php#long-term-goals');
      exit;
    }

    $long_term_error_message = 'The goal could not be updated.';
  }
}

$user_has_goal = !empty($current_user_goal);

$goal_start_date = null;
$goal_end_date = null;
$long_term_edit_mode = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['long_term_update_goal']) && !empty($long_term_error_message);
if (!empty($current_user_goal) && !empty($current_user_goal['date_deb_obj'])) {
  $goal_start_date = $current_user_goal['date_deb_obj'];
}
if (!empty($current_user_goal) && !empty($current_user_goal['date_fin_obj'])) {
  $goal_end_date = $current_user_goal['date_fin_obj'];
}

$today_date = date('Y-m-d');
$weekly_today_objectif = $hebdo_controller->get_objectif_by_user_and_date($current_user_id, $today_date);
$weekly_error_message = '';
$weekly_form_objectif = $weekly_today_objectif ?: [
  'id_suiv' => '',
  'id_obj' => !empty($current_user_goal['id_obj']) ? (int) $current_user_goal['id_obj'] : 0,
  'date_suiv' => $today_date,
  'val_cal_suiv' => '',
  'val_fat_suiv' => '',
  'val_prot_suiv' => '',
  'val_carb_suiv' => '',
  'note_suiv' => '',
  'status_obj_quot_suiv' => '',
  'nb_verre_eau_suiv' => '',
  'nb_h_sommeil_suiv' => '',
  'nb_pas_suiv' => '',
  'id_user' => $current_user_id,
];
$weekly_has_record = !empty($weekly_today_objectif);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['weekly_save_objective'])) {
  if (empty($current_user_goal)) {
    $weekly_error_message = 'You need a long-term goal before saving daily tracking.';
  } else {
    $postedDate = $_POST['survey_date'] ?? '';
    if ($postedDate !== $today_date) {
      $weekly_error_message = 'You can only save tracking for today.';
    } else {
      $postedId = (int) ($_POST['weekly_objectif_id'] ?? 0);
      $weekly_form_objectif = [
        'id_suiv' => $postedId,
        'id_obj' => (int) $current_user_goal['id_obj'],
        'date_suiv' => $today_date,
        'val_cal_suiv' => (float) ($_POST['val_cal_suiv'] ?? 0),
        'val_fat_suiv' => (float) ($_POST['val_fat_suiv'] ?? 0),
        'val_prot_suiv' => (float) ($_POST['val_prot_suiv'] ?? 0),
        'val_carb_suiv' => (float) ($_POST['val_carb_suiv'] ?? 0),
        'note_suiv' => trim((string) ($_POST['note_suiv'] ?? '')),
        'status_obj_quot_suiv' => trim((string) ($_POST['status_obj_quot_suiv'] ?? '')),
        'nb_verre_eau_suiv' => (int) ($_POST['nb_verre_eau_suiv'] ?? 0),
        'nb_h_sommeil_suiv' => trim((string) ($_POST['nb_h_sommeil_suiv'] ?? '')),
        'nb_pas_suiv' => (int) ($_POST['nb_pas_suiv'] ?? 0),
        'id_user' => $current_user_id,
      ];

      $saved = $hebdo_controller->save_objectif_hebdo($weekly_form_objectif, $weekly_has_record ? (int) $weekly_today_objectif['id_suiv'] : null);
      if ($saved) {
        header('Location: tracking.php#weekly-tracking');
        exit;
      }

      $weekly_error_message = 'The daily tracking could not be saved.';
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['weekly_delete_objective_id'])) {
  $deleteWeeklyId = (int) $_POST['weekly_delete_objective_id'];
  if ($deleteWeeklyId > 0) {
    $deleted = $hebdo_controller->delete_objectif_hebdo($deleteWeeklyId, $current_user_id);
    if ($deleted) {
      header('Location: tracking.php#weekly-tracking');
      exit;
    }
  }
}
?>

<!---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->

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

<!---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->

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

  .lt-goal-banner .ltg-delete-panel {
    margin: 0.75rem 0 0;
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

  .weekly-swipe-layout {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    gap: 1.1rem;
    margin-top: 0.65rem;
  }

  .weekly-swipe-layout .weekly-calendar-shell {
    transition: transform .35s ease;
  }

  .weekly-swipe-layout.is-survey-open .weekly-calendar-shell {
    transform: translateX(-18px);
  }

  .weekly-calendar-shell {
    margin: 1rem auto 0;
    background: #fff;
    border-radius: 22px;
    border: 1.5px solid rgba(0, 0, 0, .07);
    padding: 0.9rem;
    max-width: 560px;
    position: sticky;
    top: 88px;
  }

  .weekly-calendar-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.7rem;
  }

  .weekly-calendar-head h3 {
    margin: 0;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    color: var(--panel-text);
    font-size: 0.9rem;
  }

  .weekly-calendar-note {
    font-size: 0.7rem;
    color: var(--panel-muted);
    font-weight: 600;
    margin-bottom: 0.55rem;
    padding: 0.4rem 0.65rem;
    background: rgba(75, 174, 82, 0.08);
    border-radius: 8px;
    border-left: 3px solid var(--green);
  }

  .weekly-calendar-action-wrap {
    display: none;
    margin-top: 1rem;
    justify-content: flex-end;
    gap: 0.5rem;
  }

  .weekly-calendar-action-wrap.is-visible {
    display: flex;
  }

  .weekly-calendar-action-btn {
    border: 0;
    border-radius: 999px;
    padding: 0.72rem 1.2rem;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 10px 20px rgba(17, 16, 8, 0.12);
  }

  .weekly-calendar-edit-btn {
    background: var(--green);
    color: #fff;
    border: 1.5px solid transparent;
  }

  .weekly-calendar-edit-btn:hover {
    background: var(--forest);
    transform: translateY(-1px);
  }

  .weekly-calendar-add-btn {
    background: var(--green);
    color: #fff;
    border: 1.5px solid transparent;
  }

  .weekly-calendar-add-btn:hover {
    background: var(--forest);
    transform: translateY(-1px);
  }

  .weekly-calendar-delete-btn {
    background: rgba(17, 16, 8, 0.08);
    color: var(--panel-text);
  }

  .weekly-delete-panel {
    display: none;
    margin-top: 0.85rem;
    padding: 0.9rem 1rem;
    border-radius: 12px;
    background: rgba(245, 200, 66, 0.12);
    border: 1px solid rgba(17, 16, 8, 0.12);
    align-items: center;
    justify-content: space-between;
    gap: 0.8rem;
    flex-wrap: wrap;
  }

  .weekly-delete-panel.is-visible {
    display: flex;
  }

  .weekly-delete-panel span {
    font-size: 0.86rem;
    line-height: 1.35;
    color: var(--panel-text);
    font-weight: 700;
  }

  .weekly-delete-actions {
    display: flex;
    gap: 0.45rem;
    flex-wrap: wrap;
  }

  .weekly-delete-actions button {
    border: 0;
    border-radius: 999px;
    padding: 0.38rem 0.8rem;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
  }

  .weekly-delete-yes {
    background: var(--red);
    color: #fff;
  }

  .weekly-delete-no {
    background: rgba(17, 16, 8, 0.08);
    color: var(--panel-text);
  }

  .weekly-calendar-error {
    margin-top: 0.65rem;
    padding: 0.75rem 0.85rem;
    border-radius: 12px;
    background: rgba(192, 56, 26, 0.12);
    border: 1px solid rgba(192, 56, 26, 0.18);
    color: #9d2f14;
    font-size: 0.82rem;
    font-weight: 700;
    display: none;
  }

  .weekly-calendar-error.is-visible {
    display: block;
  }

  .weekly-survey-error {
    margin: 0 0 1rem;
    color: #9d2f14;
    font-weight: 700;
    font-size: 0.92rem;
  }

  .weekly-cal-controls {
    display: flex;
    gap: 0.45rem;
  }

  .weekly-cal-btn {
    border: 1.5px solid rgba(0, 0, 0, .1);
    border-radius: 10px;
    width: 34px;
    height: 30px;
    background: transparent;
    color: var(--panel-text);
    font-weight: 700;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: background .15s;
  }

  .weekly-cal-btn:hover {
    background: rgba(0, 0, 0, .05);
  }

  .weekly-cal-weekdays,
  .weekly-cal-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 3px;
  }

  .weekly-cal-weekdays span {
    text-align: center;
    font-family: 'Syne', sans-serif;
    font-size: 0.58rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #000;
    font-weight: 800;
    padding: 0.12rem 0 0.32rem;
  }

  .weekly-cal-day {
    aspect-ratio: 1;
    border-radius: 8px;
    border: 1.5px solid transparent;
    background: transparent;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--panel-text);
    position: relative;
    transition: background .15s, border-color .15s;
  }

  .weekly-cal-day strong {
    font-size: 0.74rem;
    font-weight: 500;
  }

  .weekly-cal-day.is-other-month {
    opacity: 0.35;
  }

  .weekly-cal-day.is-today {
    background: var(--yellow);
    color: var(--dark);
    border-color: transparent;
  }

  .weekly-cal-day.is-today strong {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
  }

  .weekly-cal-day.is-disabled {
    opacity: 0.45;
    cursor: not-allowed;
  }

  .weekly-cal-day.is-goal-range {
    border-color: var(--green);
    background: rgba(75, 174, 82, .08);
  }

  .weekly-cal-day.is-goal-range::after {
    content: '';
    position: absolute;
    bottom: 5px;
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--orange);
  }

  .weekly-cal-day.is-today.is-goal-range::after {
    background: var(--dark);
  }

  .weekly-cal-day.is-today,
  .weekly-cal-day.is-today.is-goal-range {
    background: var(--yellow);
    color: var(--dark);
    border-color: transparent;
  }

  .weekly-cal-day:hover {
    background: rgba(0, 0, 0, .05);
  }

  .weekly-cal-day.is-disabled:hover {
    background: transparent;
  }

  [data-theme='dark'] .weekly-cal-day {
    color: #f3f1e8;
  }

  [data-theme='dark'] .weekly-cal-day strong {
    color: #f3f1e8;
  }

  [data-theme='dark'] .weekly-calendar-shell {
    background: #1c1a14;
    border-color: rgba(255, 255, 255, 0.14);
    box-shadow: 0 16px 30px rgba(0, 0, 0, 0.35);
  }

  [data-theme='dark'] .weekly-calendar-head h3 {
    color: #f3f1e8;
  }

  [data-theme='dark'] .weekly-calendar-note {
    color: #f3f1e8;
    background: rgba(245, 200, 66, 0.16);
    border-left-color: #f5c842;
  }

  [data-theme='dark'] .weekly-cal-weekdays span {
    color: #f3f1e8;
  }

  [data-theme='dark'] .weekly-cal-btn {
    border-color: rgba(255, 255, 255, 0.22);
    color: #f3f1e8;
    background: rgba(255, 255, 255, 0.03);
  }

  [data-theme='dark'] .weekly-cal-btn:hover {
    background: rgba(255, 255, 255, 0.1);
  }

  [data-theme='dark'] .weekly-cal-day {
    background: rgba(255, 255, 255, 0.03);
    border-color: rgba(255, 255, 255, 0.08);
  }

  [data-theme='dark'] .weekly-cal-day:hover {
    background: rgba(255, 255, 255, 0.12);
  }

  [data-theme='dark'] .weekly-cal-day.is-goal-range {
    background: rgba(75, 174, 82, 0.22);
    border-color: rgba(168, 196, 90, 0.75);
  }

  [data-theme='dark'] .weekly-cal-day.is-today,
  [data-theme='dark'] .weekly-cal-day.is-today.is-goal-range {
    background: var(--yellow);
    border-color: transparent;
    color: #111008;
  }

  [data-theme='dark'] .weekly-cal-day.is-today,
  [data-theme='dark'] .weekly-cal-day.is-today strong {
    color: #111008;
  }

  [data-theme='dark'] .weekly-cal-day.is-other-month,
  [data-theme='dark'] .weekly-cal-day.is-disabled {
    color: rgba(243, 241, 232, 0.62);
  }

  [data-theme='dark'] .weekly-water-glass {
    border-color: rgba(243, 241, 232, 0.28);
    background: rgba(243, 241, 232, 0.06);
    color: rgba(243, 241, 232, 0.55);
  }

  [data-theme='dark'] .weekly-water-summary {
    color: rgba(243, 241, 232, 0.86);
  }

  @media (max-width: 800px) {
    .weekly-cal-day {
      aspect-ratio: 1;
    }
  }

  .weekly-survey-shell {
    margin-top: 0;
    border: 1.5px solid var(--surface-border);
    border-radius: 22px;
    box-shadow: 0 18px 40px rgba(17, 16, 8, 0.08);
    background:
      radial-gradient(120% 120% at 0% 0%, rgba(75, 174, 82, .12) 0%, rgba(75, 174, 82, 0) 58%),
      linear-gradient(160deg, var(--surface) 0%, var(--surface-2) 100%);
    padding: 1.25rem;
    width: min(620px, calc(100vw - 3rem));
    transform: translateX(24px);
    opacity: 0;
    transition: transform .35s ease, opacity .35s ease;
  }

  .weekly-survey-shell.is-visible {
    transform: translateX(0);
    opacity: 1;
  }

  @media (max-width: 1100px) {
    .weekly-swipe-layout {
      flex-direction: column;
      align-items: center;
    }

    .weekly-swipe-layout.is-survey-open .weekly-calendar-shell {
      transform: none;
    }

    .weekly-survey-shell {
      width: min(760px, 100%);
    }
  }

  .weekly-survey-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.25rem;
  }

  .weekly-survey-head h3 {
    margin: 0;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    color: var(--panel-text);
    font-size: clamp(1.05rem, 2vw, 1.3rem);
  }

  .weekly-survey-close {
    border: 0;
    border-radius: 999px;
    padding: 0.45rem 0.85rem;
    background: rgba(17, 16, 8, 0.08);
    color: var(--panel-text);
    font-weight: 700;
    font-family: 'Syne', sans-serif;
    cursor: pointer;
  }

  .weekly-macro-overview {
    border: 1px solid rgba(17, 16, 8, 0.08);
    border-radius: 16px;
    padding: 0.95rem;
    background: rgba(255, 255, 255, 0.5);
    margin-bottom: 1rem;
  }

  .weekly-macros-top {
    display: grid;
    grid-template-columns: repeat(4, minmax(110px, 1fr));
    gap: 0.6rem;
    margin-bottom: 0.9rem;
  }

  .weekly-macro-bubble {
    background: var(--dark);
    border-radius: 14px;
    padding: 0.85rem 0.65rem;
    text-align: center;
    position: relative;
    overflow: hidden;
  }

  .weekly-macro-bubble::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
  }

  .weekly-macro-bubble.kcal::after { background: var(--orange); }
  .weekly-macro-bubble.prot::after { background: var(--green); }
  .weekly-macro-bubble.carb::after { background: var(--yellow); }
  .weekly-macro-bubble.fat::after { background: var(--peach); }

  .weekly-macro-val {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.15rem;
    line-height: 1;
    margin-bottom: 0.2rem;
  }

  .weekly-macro-bubble.kcal .weekly-macro-val { color: var(--orange); }
  .weekly-macro-bubble.prot .weekly-macro-val { color: var(--green); }
  .weekly-macro-bubble.carb .weekly-macro-val { color: var(--yellow); }
  .weekly-macro-bubble.fat .weekly-macro-val { color: var(--peach); }

  .weekly-macro-lbl {
    font-size: 0.64rem;
    color: rgba(255, 255, 255, 0.58);
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .weekly-macro-rem {
    font-size: 0.62rem;
    color: rgba(255, 255, 255, 0.4);
    margin-top: 0.2rem;
  }

  .weekly-macro-bars {
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
  }

  .weekly-macro-row-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 0.22rem;
    gap: 0.8rem;
  }

  .weekly-macro-name {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 0.73rem;
  }

  .weekly-macro-nums {
    font-size: 0.68rem;
    color: #777;
  }

  .weekly-macro-nums strong {
    color: var(--panel-text);
  }

  .weekly-macro-track {
    height: 8px;
    background: rgba(0, 0, 0, 0.08);
    border-radius: 999px;
    overflow: hidden;
  }

  .weekly-macro-fill {
    height: 100%;
    border-radius: 999px;
    transition: width .35s ease;
  }

  .weekly-macro-fill.kcal { background: var(--orange); }
  .weekly-macro-fill.prot { background: var(--green); }
  .weekly-macro-fill.carb { background: var(--yellow); }
  .weekly-macro-fill.fat { background: var(--peach); }
  .weekly-macro-fill.over { background: #c0381a !important; }

  @media (max-width: 760px) {
    .weekly-macros-top {
      grid-template-columns: repeat(2, minmax(120px, 1fr));
    }
  }

  .weekly-survey-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .weekly-survey-field {
    border-radius: 14px;
    border: 1px solid rgba(17, 16, 8, 0.1);
    padding: 0.95rem;
    background: rgba(255, 255, 255, 0.45);
  }

  .weekly-survey-field.weekly-macro-field {
    border: 0;
    padding: 0;
    background: transparent;
  }

  .weekly-survey-field.weekly-water-field {
    grid-column: 1 / -1;
    width: 100%;
  }

  .weekly-survey-field label {
    font-size: 0.78rem;
    letter-spacing: 0.13em;
    text-transform: uppercase;
    color: var(--green);
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    display: block;
    margin-bottom: 0.65rem;
  }

  .weekly-survey-field.weekly-macro-field label {
    font-family: 'Syne', sans-serif;
    letter-spacing: 0;
    text-transform: none;
    font-size: 0.74rem;
    margin-bottom: 0.42rem;
  }

  .weekly-survey-field.weekly-macro-field.kcal label { color: var(--orange); }
  .weekly-survey-field.weekly-macro-field.prot label { color: var(--green); }
  .weekly-survey-field.weekly-macro-field.carb label { color: var(--yellow-mid); }
  .weekly-survey-field.weekly-macro-field.fat label { color: #c08060; }

  .weekly-survey-field input,
  .weekly-survey-field textarea {
    width: 100%;
    border-radius: 12px;
    border: 1px solid rgba(17, 16, 8, 0.16);
    padding: 0.72rem;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.92rem;
    background: var(--surface);
    color: var(--panel-text);
    box-shadow: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
  }

  .weekly-survey-field.weekly-macro-field input {
    border: 1.5px solid rgba(0, 0, 0, .1);
    border-radius: 12px;
    padding: 10px 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.95rem;
    background: #fdf8ee;
    color: var(--dark);
  }

  .weekly-survey-field input:focus,
  .weekly-survey-field textarea:focus {
    outline: none;
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(75, 174, 82, 0.16);
  }

  .weekly-water-glasses {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
  }

  .weekly-water-glass {
    width: 34px;
    height: 44px;
    border-radius: 8px 8px 12px 12px;
    border: 2px solid rgba(17, 16, 8, 0.15);
    background: rgba(17, 16, 8, 0.05);
    cursor: pointer;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding-bottom: 4px;
    font-size: 0.62rem;
    color: #a6a6a6;
    position: relative;
    overflow: hidden;
  }

  .weekly-water-glass.is-filled {
    background: #d0eeff;
    border-color: #5ab5f5;
    color: #1a80c4;
  }

  .weekly-water-glass.is-filled::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 70%;
    background: linear-gradient(to top, #5ab5f5, #90d0ff);
    border-radius: 0 0 10px 10px;
  }

  .weekly-water-glass span {
    position: relative;
    z-index: 1;
    line-height: 1;
  }

  .weekly-water-summary {
    margin-top: 0.55rem;
    font-size: 0.78rem;
    color: var(--panel-muted);
    font-weight: 700;
  }

  .weekly-tracker-overview {
    grid-column: 1 / -1;
    border: 1px solid rgba(17, 16, 8, 0.08);
    border-radius: 16px;
    padding: 0.95rem;
    background: rgba(255, 255, 255, 0.5);
    /*width: min(540px, 100%);
    /*margin: 0.25rem auto 0;*/
  }

  .weekly-tracker-sec-label {
    font-family: 'Syne', sans-serif;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #7c6fcd;
    /*margin-bottom: 4px;*/
  }
  .weeklytracker-field label {
    display: block;
    font-family: 'Boldonse', system-ui;
    font-size: 0.72rem;
    
  }

  .weekly-tracker-field.sleep label { color: #7c6fcd; }
  .weekly-tracker-field.tracker-steps label { color: #0ea5a0; }

  .weekly-tracker-field input {
    width: 100%;
    border: 1.5px solid rgba(0,0,0,.1);
    border-radius: 12px;
    padding: 10px 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: .95rem;
    background: var(--off-white);
    color: var(--dark);
    outline: none;
    transition: border-color .2s;
  }

  .weekly-tracker-field.sleep input:focus { border-color: #7c6fcd; }
  .weekly-tracker-field.tracker-steps input:focus { border-color: #0ea5a0; }

  .weekly-tracker-summary {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-top: 18px;
  }

  .weekly-tracker-bubble {
    background: var(--dark);
    border-radius: 18px;
    padding: 18px 16px 14px;
    position: relative;
    overflow: hidden;
  }

  .weekly-tracker-bubble::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 4px;
    border-radius: 0 0 18px 18px;
  }

  .weekly-tracker-bubble.sleep::after {
    background: #7c6fcd !important;
    opacity: 1;
  }
  .weekly-tracker-bubble.tracker-steps::after { background: #0ea5a0; }
  .weekly-tracker-bubble.tracker-steps { margin: 0; }

  .weekly-tracker-bubble-val {
    font-family: 'Boldonse', system-ui;
    font-size: 1.7rem; line-height: 1;
    margin-bottom: 2px;
  }

  .weekly-tracker-bubble.sleep .weekly-tracker-bubble-val { color: #a99ef5; }
  .weekly-tracker-bubble.tracker-steps .weekly-tracker-bubble-val { color: #3dd5cf; }

  .weekly-tracker-bubble-lbl {font-size: .72rem; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: .08em;}

  .weekly-tracker-bubble-sub {font-size: .68rem; color: rgba(255,255,255,.35); margin-top: 4px;}

  .weekly-tracker-bars {
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .weekly-tracker-bar-row .weekly-tracker-bar-head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 7px;
    gap: 0.8rem;
  }

  .weekly-tracker-bar-row .weekly-tracker-bar-name {
    font-family: 'Boldonse', system-ui;
    font-size: .85rem;
  }

  .weekly-tracker-bar-row .weekly-tracker-bar-nums {
    font-size: .78rem;
    color: #777;
  }

  .weekly-tracker-bar-row .weekly-tracker-bar-nums strong {
    color: var(--dark);
    font-weight: 600;
  }

  .weekly-tracker-bar-track {
    height: 12px;
    background: rgba(0,0,0,.07);
    border-radius: 100px;
    overflow: hidden;
  }

  .weekly-tracker-bar-fill {
    height: 100%;
    border-radius: 100px;
    transition: width .5s ease;
  }

  .weekly-tracker-bar-fill.sleep { background: #7c6fcd; }
  .weekly-tracker-bar-fill.tracker-steps { background: #0ea5a0; }
  .weekly-tracker-bar-fill.over { background: var(--red) !important; }

  .weekly-tracker-bar-sub {
    display: flex;
    justify-content: space-between;
    margin-top: 5px;
    font-size: .72rem;
  }

  .weekly-tracker-bar-sub .consumed { color: #555; }
  .weekly-tracker-bar-sub .remaining.ok { font-weight: 600; }
  .weekly-tracker-bar-sub .remaining.ok.sleep-ok { color: #7c6fcd; }
  .weekly-tracker-bar-sub .remaining.ok.steps-ok { color: #0ea5a0; }
  .weekly-tracker-bar-sub .remaining.warn { color: var(--orange); font-weight: 600; }
  .weekly-tracker-bar-sub .remaining.over { color: var(--red); font-weight: 600; }

  [data-theme='dark'] .weekly-tracker-overview,
  [data-theme='dark'] .weekly-macro-overview {
    background: rgba(17, 16, 8, 0.56);
    border-color: rgba(255, 255, 255, 0.14);
  }

  [data-theme='dark'] .weekly-tracker-field input {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(255, 255, 255, 0.14);
    color: #f3f1e8;
  }

  [data-theme='dark'] .weekly-tracker-bar-row .weekly-tracker-bar-nums,
  [data-theme='dark'] .weekly-tracker-bar-sub .consumed {
    color: rgba(243, 241, 232, 0.78);
  }

  .weekly-daily-log {
    grid-column: 1 / -1;
    margin-top: 0.2rem;
  }

  .weekly-daily-log-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
  }

  .weekly-daily-log-field {
    display: flex;
    flex-direction: column;
  }

  .weekly-daily-log-field.wide {
    grid-column: span 2;
  }

  .weekly-daily-log-field label {
    display: block;
    font-family: 'Boldonse', system-ui;
    font-size: .72rem;
    margin-bottom: 6px;
  }

  .weekly-daily-log-field .label-status { color: var(--orange); }
  .weekly-daily-log-field .label-notes { color: #555; }

  .weekly-daily-log-textarea {
    width: 100%;
    border: 1.5px solid rgba(0,0,0,.1);
    border-radius: 12px;
    padding: 10px 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: .92rem;
    background: var(--off-white);
    color: var(--dark);
    outline: none;
    resize: vertical;
    min-height: 100px;
    line-height: 1.55;
    transition: border-color .2s;
  }

  .weekly-daily-log-textarea:focus {
    border-color: var(--green);
  }

  .weekly-daily-log-select {
    width: 100%;
    border: 1.5px solid rgba(0,0,0,.1);
    border-radius: 12px;
    padding: 10px 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: .92rem;
    background: var(--off-white);
    color: var(--dark);
    outline: none;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23888' stroke-width='1.6' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    background-size: 12px;
    padding-right: 36px;
    transition: border-color .2s;
  }

  .weekly-daily-log-select:focus {
    border-color: var(--orange);
  }

  .weekly-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 10px;
    padding: 6px 14px;
    border-radius: 100px;
    font-family: 'Boldonse', system-ui;
    font-size: .72rem;
    letter-spacing: .04em;
    transition: background .25s, color .25s;
  }

  .weekly-status-badge.on-track { background: rgba(75,174,82,.12); color: var(--forest); }
  .weekly-status-badge.great-day { background: rgba(245,200,66,.22); color: #7a5c00; }
  .weekly-status-badge.needs-work { background: rgba(217,79,0,.12); color: var(--orange); }
  .weekly-status-badge.off-track { background: rgba(192,56,26,.12); color: var(--red); }
  .weekly-status-badge.rest-day { background: rgba(90,181,245,.12); color: #1a80c4; }
  .weekly-status-badge.hidden { display: none; }

  .weekly-status-badge::before {
    content: '';
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: currentColor;
    flex-shrink: 0;
  }

  .weekly-daily-log-char-count {
    text-align: right;
    font-size: .68rem;
    color: #bbb;
    margin-top: 5px;
    font-weight: 500;
  }

  .weekly-daily-log-char-count.warn {
    color: var(--orange);
  }

  .weekly-survey-textarea {
    grid-column: 1 / -1;
  }

  .weekly-survey-textarea textarea {
    min-height: 100px;
    resize: vertical;
  }

  .weekly-survey-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    flex-wrap: wrap;
  }

  .weekly-survey-actions button {
    border: 0;
    border-radius: 999px;
    padding: 0.72rem 1.25rem;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    cursor: pointer;
  }

  .weekly-survey-save {
    background: var(--green);
    color: #fff;
    border: 1.5px solid transparent;
  }

  .weekly-survey-save:hover {
    background: var(--forest);
    transform: translateY(-1px);
  }

  .weekly-survey-cancel {
    background: rgba(17, 16, 8, 0.08);
    color: var(--panel-text);
  }

  .lt-goal-banner {
    background:
      radial-gradient(120% 120% at 0% 0%, rgba(75, 174, 82, .12) 0%, rgba(75, 174, 82, 0) 58%),
      linear-gradient(160deg, var(--surface) 0%, var(--surface-2) 100%);
    border-radius: 22px;
    padding: 28px;
    border: 1.5px solid var(--surface-border);
    position: relative;
    overflow: hidden;
    box-shadow: 0 18px 40px rgba(17, 16, 8, 0.08);
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
  }

  .lt-goal-banner .sec-label {
    color: var(--green);
  }

  .lt-goal-banner .card-title {
    color: var(--panel-text);
    margin-bottom: 18px;
  }

  .lt-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: 100px;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: .68rem;
    letter-spacing: .05em;
    background: rgba(245,200,66,.12);
    color: var(--yellow);
    border: 1.5px solid rgba(245,200,66,.2);
    margin-left: 8px;
    vertical-align: middle;
    text-transform: uppercase;
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
    margin: 8px 0 18px;
  }

  .lt-divider::before,
  .lt-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(17, 16, 8, 0.12);
  }

  .lt-divider span {
    font-family: 'Syne', sans-serif;
    font-size: .62rem;
    letter-spacing: .12em;
    color: var(--panel-muted);
    text-transform: uppercase;
    white-space: nowrap;
    font-weight: 700;
  }

  .lt-grid,
  .lt-consistency-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 14px;
  }

  .lt-field {
    display: flex;
    flex-direction: column;
  }

  .lt-field-warning {
    margin-top: 0.42rem;
    font-size: 0.82rem;
    line-height: 1.35;
    color: #9d2f14;
    font-weight: 700;
    display: none;
  }

  .lt-field-warning.is-visible {
    display: block;
  }

  .lt-macro-pill .lt-field-warning {
    text-align: left;
    font-size: 0.74rem;
    margin-top: 0.45rem;
  }

  .lt-field label {
    display: block;
    font-family: 'Syne', sans-serif;
    font-size: .68rem;
    margin-bottom: 6px;
    color: var(--panel-muted);
    letter-spacing: .06em;
    text-transform: uppercase;
    font-weight: 700;
  }

  .lt-field label.accent-green { color: var(--green-light); }
  .lt-field label.accent-yellow { color: var(--yellow); }
  .lt-field label.accent-orange { color: var(--peach); }

  .lt-input,
  .lt-select {
    width: 100%;
    border: 1.5px solid rgba(17, 16, 8, .16);
    border-radius: 12px;
    padding: 10px 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: .93rem;
    background: var(--surface);
    color: var(--panel-text);
    outline: none;
    transition: border-color .2s, background .2s;
  }

  .lt-input::placeholder { color: rgba(17, 16, 8, .35); }
  .lt-input:focus,
  .lt-select:focus {
    border-color: var(--green);
    background: var(--surface);
  }

  .lt-input.is-invalid,
  .lt-select.is-invalid {
    border-color: #c0381a !important;
    box-shadow: 0 0 0 2px rgba(192, 56, 26, 0.16);
  }

  .lt-input.is-valid,
  .lt-select.is-valid {
    border-color: var(--green) !important;
    box-shadow: 0 0 0 2px rgba(75, 174, 82, 0.16);
  }

  .lt-input[readonly] {
    background: rgba(17,16,8,.05);
    color: var(--panel-muted);
    border-color: rgba(17, 16, 8, .12);
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
    background: #ffffff;
    color: var(--panel-text);
  }

  .lt-consistency-row {
    margin-bottom: 18px;
  }

  .lt-slider-wrap label {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
  }

  .lt-slider-wrap label span {
    font-size: .8rem;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
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
  }

  .lt-slider.sport {
    background: linear-gradient(to right, #3dd5cf var(--val, 70%), rgba(17,16,8,.14) var(--val, 70%));
  }

  .lt-slider.diet {
    background: linear-gradient(to right, var(--yellow) var(--val, 70%), rgba(17,16,8,.14) var(--val, 70%));
  }

  .lt-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 2.5px solid #fff;
    box-shadow: 0 2px 8px rgba(17,16,8,.22);
    cursor: pointer;
  }

  .lt-slider.sport::-webkit-slider-thumb { background: #3dd5cf; }
  .lt-slider.diet::-webkit-slider-thumb { background: var(--yellow); }

  .lt-macros-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 22px;
  }

  .lt-macro-pill {
    background: rgba(255,255,255,.45);
    border-radius: 14px;
    padding: 12px 10px;
    text-align: center;
    border: 1.5px solid rgba(17,16,8,.1);
  }

  .lt-macro-pill label {
    font-size: .62rem;
    display: block;
    margin-bottom: 8px;
    letter-spacing: .06em;
  }

  .lt-macro-pill.m-kcal label { color: var(--orange); }
  .lt-macro-pill.m-prot label { color: var(--green-light); }
  .lt-macro-pill.m-carb label { color: var(--yellow); }
  .lt-macro-pill.m-fat label { color: var(--peach); }

  .lt-macro-pill input {
    width: 100%;
    border: 1.5px solid rgba(17,16,8,.14);
    border-radius: 9px;
    padding: 7px 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: .88rem;
    background: var(--surface);
    color: var(--panel-text);
    outline: none;
    text-align: center;
  }

  .lt-macro-pill input::placeholder { color: rgba(17,16,8,.35); }

  .lt-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.7rem;
    flex-wrap: wrap;
  }

  .lt-action-btn {
    border: 0;
    border-radius: 999px;
    padding: 0.72rem 1.25rem;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(17, 16, 8, 0.15);
  }

  .lt-action-btn.edit {
    background: var(--green);
    color: #fff;
    border: 1.5px solid transparent;
  }

  .lt-action-btn.edit:hover {
    background: var(--forest);
    transform: translateY(-1px);
  }

  .lt-action-btn.save {
    background: var(--green);
    color: #fff;
    border: 1.5px solid transparent;
  }

  .lt-action-btn.save:hover {
    background: var(--forest);
    transform: translateY(-1px);
  }

  .lt-action-btn.delete {
    background: var(--red);
    color: #fff;
  }

  @media (max-width: 980px) {
    .lt-macros-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 700px) {
    .lt-grid,
    .lt-consistency-row,
    .lt-macros-grid {
      grid-template-columns: 1fr;
    }

    .lt-actions {
      flex-direction: column;
    }

    .lt-action-btn {
      width: 100%;
    }
  }

</style>

<!---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<!---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->

</head>
<body<?php echo $goal_start_date ? ' data-goal-start-date="' . htmlspecialchars($goal_start_date) . '"' : ''; ?><?php echo $goal_end_date ? ' data-goal-end-date="' . htmlspecialchars($goal_end_date) . '"' : ''; ?><?php echo $weekly_has_record ? ' data-weekly-has-record="1" data-weekly-id="' . htmlspecialchars((string) $weekly_today_objectif['id_suiv']) . '"' : ' data-weekly-has-record="0"'; ?> data-has-long-term-goal="<?php echo !empty($current_user_goal) ? '1' : '0'; ?>" data-long-term-edit-mode="<?php echo $long_term_edit_mode ? '1' : '0'; ?>">

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

<section class="section" id="long-term-goals">
  <p class="section-label">Long Term Goal</p>
  <h2 class="section-title features-title">Set and manage your long-term goal directly.</h2>

  <?php if (!empty($goal_action_error)): ?>
    <div style="margin: 0 0 1rem; padding: 0.85rem 1rem; border-radius: 14px; background: rgba(192, 56, 26, 0.12); color: var(--red); font-weight: 700; border: 1px solid rgba(192, 56, 26, 0.18);">
      <?php echo htmlspecialchars($goal_action_error); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($long_term_error_message)): ?>
    <div style="margin: 0 0 1rem; padding: 0.85rem 1rem; border-radius: 14px; background: rgba(192, 56, 26, 0.12); color: var(--red); font-weight: 700; border: 1px solid rgba(192, 56, 26, 0.18);">
      <?php echo htmlspecialchars($long_term_error_message); ?>
    </div>
  <?php endif; ?>

  <div class="lt-goal-banner">
    <p class="sec-label">Long-term objective</p>
    <h2 class="card-title"><span class="emoji">Set Your Goal</span>
      <span class="lt-status-badge"><?php echo $user_has_goal ? 'Active' : 'Pending'; ?></span>
    </h2>

    <form method="post" action="">
      <div class="lt-grid">
        <div class="lt-field">
          <label for="lt-id-objectif">Goal ID</label>
          <input class="lt-input" type="text" id="lt-id-objectif" name="id_obj" value="<?php echo htmlspecialchars((string) $long_term_form['id_obj']); ?>" readonly>
        </div>
        <div class="lt-field">
          <label for="lt-id-user">User ID</label>
          <input class="lt-input" type="text" id="lt-id-user" name="id_user" value="<?php echo htmlspecialchars((string) $long_term_form['id_user']); ?>" readonly>
        </div>
      </div>

      <div class="lt-divider"><span>Goal parameters</span></div>

      <div class="lt-grid">
        <div class="lt-field">
          <label class="accent-green" for="lt-goal-type">Goal type</label>
          <select class="lt-select" id="lt-goal-type" name="type_obj" required <?php echo $user_has_goal ? 'disabled' : ''; ?>>
            <option value="">Select a goal</option>
            <option value="prise_de_poids" <?php echo $long_term_form['type_obj'] === 'prise_de_poids' ? 'selected' : ''; ?>>Weight gain</option>
            <option value="perte_de_poids" <?php echo $long_term_form['type_obj'] === 'perte_de_poids' ? 'selected' : ''; ?>>Weight loss</option>
            <option value="maintien_de_poids" <?php echo $long_term_form['type_obj'] === 'maintien_de_poids' ? 'selected' : ''; ?>>Weight maintenance</option>
          </select>
          <?php if ($user_has_goal): ?>
            <input type="hidden" name="type_obj" value="<?php echo htmlspecialchars((string) $long_term_form['type_obj']); ?>">
          <?php endif; ?>
        </div>
        <div class="lt-field">
          <label class="accent-green" for="lt-reminder">Reminder frequency (days)</label>
          <input class="lt-input" type="number" id="lt-reminder" name="frequency_rappel_obj" min="1" required value="<?php echo htmlspecialchars((string) $long_term_form['frequency_rappel_obj']); ?>" <?php echo $user_has_goal ? 'readonly' : ''; ?>>
          <p class="lt-field-warning" id="lt-reminder-warning" aria-live="polite">Reminder frequency must be a positive value.</p>
        </div>
      </div>

      <div class="lt-grid">
        <div class="lt-field">
          <label class="accent-yellow" for="val_init_obj">Initial weight (kg)</label>
          <input class="lt-input" type="number" id="val_init_obj" name="val_init_obj" step="0.01" min="0.01" required data-lt-editable="1" <?php echo $user_has_goal ? 'disabled' : ''; ?> value="<?php echo htmlspecialchars((string) $long_term_form['val_init_obj']); ?>">
          <p class="lt-field-warning" id="val-init-warning" aria-live="polite">Initial weight must be a positive value.</p>
        </div>
        <div class="lt-field">
          <label class="accent-yellow" for="val_cible_obj">Target weight (kg)</label>
          <input class="lt-input" type="number" id="val_cible_obj" name="val_cible_obj" step="0.01" min="0.01" required data-lt-editable="1" <?php echo $user_has_goal ? 'disabled' : ''; ?> value="<?php echo htmlspecialchars((string) $long_term_form['val_cible_obj']); ?>">
          <p class="lt-field-warning" id="val-cible-warning" aria-live="polite">Target weight must be a positive value.</p>
          <p class="lt-field-warning" id="val-cible-goal-warning" aria-live="polite">Target weight is not valid for the selected goal type.</p>
        </div>
      </div>

      <div class="lt-grid">
        <div class="lt-field">
          <label for="date_deb_obj">Start date</label>
          <input class="lt-input" type="date" id="date_deb_obj" name="date_deb_obj" min="<?php echo htmlspecialchars($system_date); ?>" required data-lt-editable="1" <?php echo $user_has_goal ? 'disabled' : ''; ?> value="<?php echo htmlspecialchars((string) $long_term_form['date_deb_obj']); ?>">
        </div>
        <div class="lt-field">
          <label for="date_fin_obj">End date</label>
          <input class="lt-input" type="date" id="date_fin_obj" name="date_fin_obj" required data-lt-editable="1" <?php echo $user_has_goal ? 'disabled' : ''; ?> value="<?php echo htmlspecialchars((string) $long_term_form['date_fin_obj']); ?>">
          <p class="lt-field-warning" id="lt-goal-period-warning" aria-live="polite">The goal period must be at least 30 days.</p>
        </div>
      </div>

      <div class="lt-divider"><span>Consistency targets</span></div>

      <div class="lt-consistency-row">
        <div class="lt-slider-wrap lt-field">
          <label class="sport-lbl" for="consistancy_sport_obj">Sport consistency</label>
          <input class="lt-input" type="text" id="consistancy_sport_obj" name="consistancy_sport_obj" value="0" readonly>
        </div>
        <div class="lt-slider-wrap lt-field">
          <label class="diet-lbl" for="consistency_alim_obj">Diet consistency</label>
          <input class="lt-input" type="text" id="consistency_alim_obj" name="consistency_alim_obj" value="0" readonly>
        </div>
      </div>

      <div class="lt-divider"><span>Macronutrient targets</span></div>

      <div class="lt-macros-grid">
        <div class="lt-macro-pill m-kcal">
          <label for="obj_cal_obj">Calories</label>
          <input type="number" id="obj_cal_obj" name="obj_cal_obj" min="0.01" step="0.01" required data-lt-editable="1" <?php echo $user_has_goal ? 'disabled' : ''; ?> value="<?php echo htmlspecialchars((string) $long_term_form['obj_cal_obj']); ?>">
          <p class="lt-field-warning" id="obj-cal-warning" aria-live="polite">Calories must be a positive value.</p>
        </div>
        <div class="lt-macro-pill m-prot">
          <label for="obj_prot_obj">Protein</label>
          <input type="number" id="obj_prot_obj" name="obj_prot_obj" min="0.01" step="0.01" required data-lt-editable="1" <?php echo $user_has_goal ? 'disabled' : ''; ?> value="<?php echo htmlspecialchars((string) $long_term_form['obj_prot_obj']); ?>">
          <p class="lt-field-warning" id="obj-prot-warning" aria-live="polite">Protein must be a positive value.</p>
        </div>
        <div class="lt-macro-pill m-carb">
          <label for="obj_carb_obj">Carbs</label>
          <input type="number" id="obj_carb_obj" name="obj_carb_obj" min="0.01" step="0.01" required data-lt-editable="1" <?php echo $user_has_goal ? 'disabled' : ''; ?> value="<?php echo htmlspecialchars((string) $long_term_form['obj_carb_obj']); ?>">
          <p class="lt-field-warning" id="obj-carb-warning" aria-live="polite">Carbs must be a positive value.</p>
        </div>
        <div class="lt-macro-pill m-fat">
          <label for="obj_fat_obj">Fat</label>
          <input type="number" id="obj_fat_obj" name="obj_fat_obj" min="0.01" step="0.01" required data-lt-editable="1" <?php echo $user_has_goal ? 'disabled' : ''; ?> value="<?php echo htmlspecialchars((string) $long_term_form['obj_fat_obj']); ?>">
          <p class="lt-field-warning" id="obj-fat-warning" aria-live="polite">Fat must be a positive value.</p>
        </div>
      </div>

      <div class="lt-actions">
        <?php if ($user_has_goal): ?>
          <button type="button" id="lt-edit-start-btn" class="lt-action-btn edit">Edit</button>
          <button type="submit" name="long_term_update_goal" id="lt-save-changes-btn" class="lt-action-btn save" hidden>Save</button>
          <button
            type="button"
            class="lt-action-btn delete ltg-delete-trigger"
            data-id="<?php echo htmlspecialchars((string) $long_term_form['id_obj']); ?>"
            formnovalidate
          >Delete</button>
        <?php else: ?>
          <button type="submit" name="long_term_save_goal" class="lt-action-btn save">Save long-term goal</button>
        <?php endif; ?>
      </div>

      <?php if ($user_has_goal): ?>
        <input type="hidden" id="ltg-delete-id" name="delete_id_obj" value="">
        <div class="ltg-delete-panel" id="ltg-delete-panel" hidden>
          <span>Are you sure you want to delete your long-term goal? This also deletes linked daily tracking entries.</span>
          <div class="ltg-delete-actions">
            <button type="submit" class="ltg-delete-yes" formnovalidate>Yes</button>
            <button type="button" class="ltg-delete-no" id="ltg-delete-cancel">No</button>
          </div>
        </div>
      <?php endif; ?>
    </form>
  </div>
</section>

<section class="section" id="weekly-tracking">
  <p class="section-label">Weekly tracking</p>
  <h2 class="section-title features-title">Everything you need to stay on track this week.</h2>

  <div class="weekly-swipe-layout" id="weekly-swipe-layout">
  <div class="weekly-calendar-shell" aria-label="Weekly tracking calendar">
    <div class="weekly-calendar-head">
      <h3 id="weekly-cal-title">Month Year</h3>
      <div class="weekly-cal-controls">
        <button type="button" class="weekly-cal-btn" id="weekly-cal-prev" aria-label="Previous month">&lt;</button>
        <button type="button" class="weekly-cal-btn" id="weekly-cal-next" aria-label="Next month">&gt;</button>
      </div>
    </div>
    <div class="weekly-calendar-note">
      <strong>📅 Today only:</strong> You can complete your daily tracking only for today's date.
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
    <div class="weekly-calendar-action-wrap" id="weekly-calendar-action-wrap">
      <button type="button" class="weekly-calendar-action-btn weekly-calendar-add-btn" id="weekly-calendar-add-btn" <?php echo $weekly_has_record ? 'hidden' : ''; ?>>Add</button>
      <button type="button" class="weekly-calendar-action-btn weekly-calendar-edit-btn" id="weekly-calendar-edit-btn" <?php echo $weekly_has_record ? '' : 'hidden'; ?>>Edit</button>
      <button type="button" class="weekly-calendar-action-btn weekly-calendar-delete-btn" id="weekly-calendar-delete-btn" <?php echo $weekly_has_record ? '' : 'hidden'; ?>>Delete</button>
    </div>
    <div class="weekly-calendar-error" id="weekly-goal-required-msg">You must complete the long-term goal survey first before adding weekly tracking.</div>
    <div class="weekly-delete-panel" id="weekly-delete-panel" hidden>
      <span>Are you sure you want to delete your daily tracking?</span>
      <div class="weekly-delete-actions">
        <button type="button" class="weekly-delete-yes" id="weekly-delete-confirm">Yes</button>
        <button type="button" class="weekly-delete-no" id="weekly-delete-cancel">No</button>
      </div>
    </div>
  </div>

  <div class="weekly-survey-shell" id="weekly-survey-panel" hidden>
    <div class="weekly-survey-head">
      <h3 id="weekly-survey-date">Track for</h3>
      <button type="button" class="weekly-survey-close" id="weekly-survey-close" aria-label="Close survey">Close</button>
    </div>

    <?php if (!empty($weekly_error_message)): ?>
      <p class="weekly-survey-error"><?php echo htmlspecialchars($weekly_error_message); ?></p>
    <?php endif; ?>

    <form method="post" action="" id="weekly-survey-form">
      <input type="hidden" name="survey_date" id="survey-date" value="">
      <input type="hidden" name="weekly_objectif_id" id="weekly-objectif-id" value="<?php echo htmlspecialchars((string) ($weekly_form_objectif['id_suiv'] ?? '')); ?>">
      <input type="hidden" name="weekly_save_objective" value="1">

      <div
        class="weekly-macro-overview"
        id="weekly-macro-overview"
        data-target-cal="<?php echo htmlspecialchars((string) (!empty($current_user_goal['obj_cal_obj']) ? $current_user_goal['obj_cal_obj'] : 2000)); ?>"
        data-target-prot="<?php echo htmlspecialchars((string) (!empty($current_user_goal['obj_prot_obj']) ? $current_user_goal['obj_prot_obj'] : 150)); ?>"
        data-target-carb="<?php echo htmlspecialchars((string) (!empty($current_user_goal['obj_carb_obj']) ? $current_user_goal['obj_carb_obj'] : 200)); ?>"
        data-target-fat="<?php echo htmlspecialchars((string) (!empty($current_user_goal['obj_fat_obj']) ? $current_user_goal['obj_fat_obj'] : 65)); ?>"
      >
        <div class="weekly-macros-top">
          <div class="weekly-macro-bubble kcal">
            <div class="weekly-macro-val" id="weekly-macro-val-cal">0</div>
            <div class="weekly-macro-lbl">Calories</div>
            <div class="weekly-macro-rem" id="weekly-macro-rem-cal">0 remaining</div>
          </div>
          <div class="weekly-macro-bubble prot">
            <div class="weekly-macro-val" id="weekly-macro-val-prot">0g</div>
            <div class="weekly-macro-lbl">Protein</div>
            <div class="weekly-macro-rem" id="weekly-macro-rem-prot">0g remaining</div>
          </div>
          <div class="weekly-macro-bubble carb">
            <div class="weekly-macro-val" id="weekly-macro-val-carb">0g</div>
            <div class="weekly-macro-lbl">Carbs</div>
            <div class="weekly-macro-rem" id="weekly-macro-rem-carb">0g remaining</div>
          </div>
          <div class="weekly-macro-bubble fat">
            <div class="weekly-macro-val" id="weekly-macro-val-fat">0g</div>
            <div class="weekly-macro-lbl">Fat</div>
            <div class="weekly-macro-rem" id="weekly-macro-rem-fat">0g remaining</div>
          </div>
        </div>

        <div class="weekly-macro-bars">
          <div>
            <div class="weekly-macro-row-head">
              <span class="weekly-macro-name">Calories</span>
              <span class="weekly-macro-nums"><strong id="weekly-macro-num-cal">0</strong> / <span id="weekly-macro-target-cal">0</span> kcal</span>
            </div>
            <div class="weekly-macro-track"><div class="weekly-macro-fill kcal" id="weekly-macro-fill-cal" style="width:0%"></div></div>
          </div>
          <div>
            <div class="weekly-macro-row-head">
              <span class="weekly-macro-name">Protein</span>
              <span class="weekly-macro-nums"><strong id="weekly-macro-num-prot">0</strong> / <span id="weekly-macro-target-prot">0</span> g</span>
            </div>
            <div class="weekly-macro-track"><div class="weekly-macro-fill prot" id="weekly-macro-fill-prot" style="width:0%"></div></div>
          </div>
          <div>
            <div class="weekly-macro-row-head">
              <span class="weekly-macro-name">Carbs</span>
              <span class="weekly-macro-nums"><strong id="weekly-macro-num-carb">0</strong> / <span id="weekly-macro-target-carb">0</span> g</span>
            </div>
            <div class="weekly-macro-track"><div class="weekly-macro-fill carb" id="weekly-macro-fill-carb" style="width:0%"></div></div>
          </div>
          <div>
            <div class="weekly-macro-row-head">
              <span class="weekly-macro-name">Fat</span>
              <span class="weekly-macro-nums"><strong id="weekly-macro-num-fat">0</strong> / <span id="weekly-macro-target-fat">0</span> g</span>
            </div>
            <div class="weekly-macro-track"><div class="weekly-macro-fill fat" id="weekly-macro-fill-fat" style="width:0%"></div></div>
          </div>
        </div>
      </div>

      <div class="weekly-survey-grid">
        <div class="weekly-survey-field weekly-macro-field kcal">
          <label for="survey-cal">Calories (kcal)</label>
          <input type="number" id="survey-cal" name="val_cal_suiv" step="0.01" min="0" value="<?php echo htmlspecialchars((string) ($weekly_form_objectif['val_cal_suiv'] ?? '')); ?>">
        </div>
        <div class="weekly-survey-field weekly-macro-field fat">
          <label for="survey-fat">Fat (g)</label>
          <input type="number" id="survey-fat" name="val_fat_suiv" step="0.01" min="0" value="<?php echo htmlspecialchars((string) ($weekly_form_objectif['val_fat_suiv'] ?? '')); ?>">
        </div>
        <div class="weekly-survey-field weekly-macro-field prot">
          <label for="survey-prot">Protein (g)</label>
          <input type="number" id="survey-prot" name="val_prot_suiv" step="0.01" min="0" value="<?php echo htmlspecialchars((string) ($weekly_form_objectif['val_prot_suiv'] ?? '')); ?>">
        </div>
        <div class="weekly-survey-field weekly-macro-field carb">
          <label for="survey-carb">Carbs (g)</label>
          <input type="number" id="survey-carb" name="val_carb_suiv" step="0.01" min="0" value="<?php echo htmlspecialchars((string) ($weekly_form_objectif['val_carb_suiv'] ?? '')); ?>">
        </div>
        <div class="weekly-survey-field weekly-water-field">
          <label for="survey-water">Water (glasses)</label>
          <input type="hidden" id="survey-water" name="nb_verre_eau_suiv" value="<?php echo htmlspecialchars((string) ($weekly_form_objectif['nb_verre_eau_suiv'] ?? '')); ?>">
          <div class="weekly-water-glasses" id="weekly-water-glasses" data-target="8"></div>
          <div class="weekly-water-summary"><span id="weekly-water-count">0</span> / 8 glasses</div>
        </div>
        <div class="weekly-tracker-overview">
          <div class="weekly-tracker-row">
            <div class="weekly-tracker-field sleep">
              <label for="survey-sleep">Hours of sleep</label>
              <input type="number" id="survey-sleep" name="nb_h_sommeil_suiv" placeholder="0" min="0" max="24" step="0.5" value="<?php echo htmlspecialchars((string) ($weekly_form_objectif['nb_h_sommeil_suiv'] ?? '')); ?>">
            </div>
            <div class="weekly-tracker-field tracker-steps">
              <label for="survey-steps">Steps taken</label>
              <input type="number" id="survey-steps" name="nb_pas_suiv" placeholder="0" min="0" value="<?php echo htmlspecialchars((string) ($weekly_form_objectif['nb_pas_suiv'] ?? '')); ?>">
            </div>
          </div>

          <div class="weekly-tracker-summary">
            <div class="weekly-tracker-bubble sleep">
              <div class="weekly-tracker-bubble-val" id="weekly-tracker-sleep-display">0h</div>
              <div class="weekly-tracker-bubble-lbl">Sleep</div>
              <div class="weekly-tracker-bubble-sub" id="weekly-tracker-sleep-goal">Goal: 8h</div>
            </div>
            <div class="weekly-tracker-bubble tracker-steps">
              <div class="weekly-tracker-bubble-val" id="weekly-tracker-steps-display">0</div>
              <div class="weekly-tracker-bubble-lbl">Steps</div>
              <div class="weekly-tracker-bubble-sub" id="weekly-tracker-steps-goal">Goal: 10 000</div>
            </div>
          </div>

          <div class="weekly-tracker-bars">
            <div class="weekly-tracker-bar-row">
              <div class="weekly-tracker-bar-head">
                <span class="weekly-tracker-bar-name">🌙 Sleep</span>
                <span class="weekly-tracker-bar-nums"><strong id="weekly-tracker-sleep-num">0</strong> / <span id="weekly-tracker-sleep-target">8</span> h</span>
              </div>
              <div class="weekly-tracker-bar-track">
                <div class="weekly-tracker-bar-fill sleep" id="weekly-tracker-sleep-fill" style="width:0%"></div>
              </div>
              <div class="weekly-tracker-bar-sub">
                <span class="consumed">Logged: <strong id="weekly-tracker-sleep-consumed">0 h</strong></span>
                <span class="remaining ok sleep-ok" id="weekly-tracker-sleep-remain">8 h remaining</span>
              </div>
            </div>
            <div class="weekly-tracker-bar-row">
              <div class="weekly-tracker-bar-head">
                <span class="weekly-tracker-bar-name">👟 Steps</span>
                <span class="weekly-tracker-bar-nums"><strong id="weekly-tracker-steps-num">0</strong> / <span id="weekly-tracker-steps-target">10 000</span></span>
              </div>
              <div class="weekly-tracker-bar-track">
                <div class="weekly-tracker-bar-fill tracker-steps" id="weekly-tracker-steps-fill" style="width:0%"></div>
              </div>
              <div class="weekly-tracker-bar-sub">
                <span class="consumed">Done: <strong id="weekly-tracker-steps-consumed">0</strong></span>
                <span class="remaining ok steps-ok" id="weekly-tracker-steps-remain">10 000 remaining</span>
              </div>
            </div>
          </div>
        </div>
        <div class="weekly-daily-log">
          <div class="weekly-daily-log-grid">
            <div class="weekly-daily-log-field">
              <label class="label-status" for="survey-status">Daily Status</label>
              <select class="weekly-daily-log-select" id="survey-status" name="status_obj_quot_suiv">
                <option value="">- Pick a status -</option>
                <option value="On track" data-tone="on-track" <?php echo (($weekly_form_objectif['status_obj_quot_suiv'] ?? '') === 'On track') ? 'selected' : ''; ?>>On track</option>
                <option value="Great day" data-tone="great-day" <?php echo (($weekly_form_objectif['status_obj_quot_suiv'] ?? '') === 'Great day') ? 'selected' : ''; ?>>Great day</option>
                <option value="Needs work" data-tone="needs-work" <?php echo (($weekly_form_objectif['status_obj_quot_suiv'] ?? '') === 'Needs work') ? 'selected' : ''; ?>>Needs work</option>
                <option value="Off track" data-tone="off-track" <?php echo (($weekly_form_objectif['status_obj_quot_suiv'] ?? '') === 'Off track') ? 'selected' : ''; ?>>Off track</option>
                <option value="Rest day" data-tone="rest-day" <?php echo (($weekly_form_objectif['status_obj_quot_suiv'] ?? '') === 'Rest day') ? 'selected' : ''; ?>>Rest day</option>
              </select>
              <span class="weekly-status-badge hidden" id="weekly-status-badge"></span>
            </div>

            <div class="weekly-daily-log-field wide" style="margin-top:4px;">
              <label class="label-notes" for="survey-notes">Notes</label>
              <textarea class="weekly-daily-log-textarea" id="survey-notes" name="note_suiv" maxlength="500" placeholder="How did today go? Any observations about your nutrition, energy levels, or mood..." ><?php echo htmlspecialchars((string) ($weekly_form_objectif['note_suiv'] ?? '')); ?></textarea>
              <div class="weekly-daily-log-char-count" id="weekly-notes-char-count">0 / 500</div>
            </div>
          </div>
        </div>
      </div>

      <div class="weekly-survey-actions">
        <button type="button" class="weekly-survey-cancel" id="weekly-survey-cancel">Cancel</button>
        <button type="submit" class="weekly-survey-save"><?php echo $weekly_has_record ? 'Update tracking' : 'Save tracking'; ?></button>
      </div>
    </form>
  </div>
  </div>
  <form method="post" action="" id="weekly-delete-form" hidden>
    <input type="hidden" name="weekly_delete_objective_id" id="weekly-delete-id" value="<?php echo htmlspecialchars((string) ($weekly_today_objectif['id_suiv'] ?? '')); ?>">
  </form>
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
    const weeklyCalendarActionWrap = document.getElementById('weekly-calendar-action-wrap');
    const weeklyCalendarAddBtn = document.getElementById('weekly-calendar-add-btn');
    const weeklyCalendarEditBtn = document.getElementById('weekly-calendar-edit-btn');
    const weeklyCalendarDeleteBtn = document.getElementById('weekly-calendar-delete-btn');
    const weeklyDeletePanel = document.getElementById('weekly-delete-panel');
    const weeklyDeleteConfirmBtn = document.getElementById('weekly-delete-confirm');
    const weeklyDeleteCancelBtn = document.getElementById('weekly-delete-cancel');
    const weeklyHasRecord = document.body.getAttribute('data-weekly-has-record') === '1';
    const weeklyRecordId = document.body.getAttribute('data-weekly-id') || '';
    const hasLongTermGoal = document.body.getAttribute('data-has-long-term-goal') === '1';
    const weeklyGoalRequiredMsg = document.getElementById('weekly-goal-required-msg');
    const longTermSportSlider = document.getElementById('consistancy_sport_obj');
    const longTermDietSlider = document.getElementById('consistency_alim_obj');
    const longTermSportValue = document.getElementById('lt-sport-val');
    const longTermDietValue = document.getElementById('lt-diet-val');
    const longTermTypeSelect = document.getElementById('lt-goal-type');
    const longTermStatusBadge = document.querySelector('.lt-status-badge');
    const hasLongTermGoalFlag = document.body.getAttribute('data-has-long-term-goal') === '1';
    const longTermInitialEditMode = document.body.getAttribute('data-long-term-edit-mode') === '1';
    const longTermEditStartButton = document.getElementById('lt-edit-start-btn');
    const longTermSaveChangesButton = document.getElementById('lt-save-changes-btn');
    const longTermDeleteButton = document.querySelector('.lt-actions .ltg-delete-trigger');
    const longTermDeletePanel = document.getElementById('ltg-delete-panel');
    const longTermEditableFields = Array.from(document.querySelectorAll('[data-lt-editable="1"]'));
    const longTermStartDateInput = document.getElementById('date_deb_obj');
    const longTermEndDateInput = document.getElementById('date_fin_obj');
    const longTermPeriodWarning = document.getElementById('lt-goal-period-warning');
    const longTermInitialWeightInput = document.getElementById('val_init_obj');
    const longTermTargetWeightInput = document.getElementById('val_cible_obj');
    const longTermTargetPositiveWarning = document.getElementById('val-cible-warning');
    const longTermTargetGoalWarning = document.getElementById('val-cible-goal-warning');
    const positiveLongTermFields = [
      { inputId: 'val_init_obj', warningId: 'val-init-warning', message: 'Initial weight must be a positive value.' },
      { inputId: 'lt-reminder', warningId: 'lt-reminder-warning', message: 'Reminder frequency must be a positive value.' },
      { inputId: 'obj_cal_obj', warningId: 'obj-cal-warning', message: 'Calories must be a positive value.' },
      { inputId: 'obj_prot_obj', warningId: 'obj-prot-warning', message: 'Protein must be a positive value.' },
      { inputId: 'obj_fat_obj', warningId: 'obj-fat-warning', message: 'Fat must be a positive value.' },
      { inputId: 'obj_carb_obj', warningId: 'obj-carb-warning', message: 'Carbs must be a positive value.' }
    ];

    const syncLongTermSlider = (slider, valueNode) => {
      if (!slider || !valueNode) {
        return;
      }
      const value = parseInt(slider.value || '0', 10) || 0;
      valueNode.textContent = String(value) + '%';
      slider.style.setProperty('--val', String(value) + '%');
    };

    const updateLongTermGoalBadgeTone = () => {
      if (!longTermTypeSelect || !longTermStatusBadge) {
        return;
      }

      const type = longTermTypeSelect.value;
      const tones = {
        perte_de_poids: {
          bg: 'rgba(217,79,0,.15)',
          color: '#D94F00',
          border: 'rgba(217,79,0,.25)'
        },
        prise_de_poids: {
          bg: 'rgba(75,174,82,.13)',
          color: '#4BAE52',
          border: 'rgba(75,174,82,.25)'
        },
        maintien_de_poids: {
          bg: 'rgba(245,200,66,.13)',
          color: '#F5C842',
          border: 'rgba(245,200,66,.25)'
        }
      };

      const tone = tones[type];
      if (!tone) {
        longTermStatusBadge.style.removeProperty('background');
        longTermStatusBadge.style.removeProperty('color');
        longTermStatusBadge.style.removeProperty('border-color');
        return;
      }

      longTermStatusBadge.style.background = tone.bg;
      longTermStatusBadge.style.color = tone.color;
      longTermStatusBadge.style.borderColor = tone.border;
    };

    const parseIsoDate = (isoDate) => {
      if (!isoDate) {
        return null;
      }
      const parsed = new Date(isoDate + 'T00:00:00');
      if (Number.isNaN(parsed.getTime())) {
        return null;
      }
      return parsed;
    };

    const validateLongTermPeriod = () => {
      if (!longTermStartDateInput || !longTermEndDateInput || !longTermPeriodWarning) {
        return;
      }

      const startDate = parseIsoDate(longTermStartDateInput.value);
      const endDate = parseIsoDate(longTermEndDateInput.value);
      if (!startDate || !endDate) {
        longTermPeriodWarning.classList.remove('is-visible');
        longTermStartDateInput.classList.remove('is-valid');
        longTermStartDateInput.classList.remove('is-invalid');
        longTermEndDateInput.classList.remove('is-valid');
        longTermEndDateInput.classList.remove('is-invalid');
        longTermEndDateInput.setCustomValidity('');
        return;
      }

      const minStartDate = parseIsoDate(longTermStartDateInput.getAttribute('min') || '');
      const diffInMs = endDate.getTime() - startDate.getTime();
      const minDurationMs = 30 * 24 * 60 * 60 * 1000;
      const isUnderMonth = diffInMs < minDurationMs;
      const isBeforeMinDate = !!(minStartDate && startDate < minStartDate);
      const isPeriodInvalid = isUnderMonth || isBeforeMinDate;

      if (isPeriodInvalid) {
        longTermPeriodWarning.classList.add('is-visible');
        longTermEndDateInput.setCustomValidity('The goal period must be at least 30 days.');
        longTermStartDateInput.classList.remove('is-valid');
        longTermEndDateInput.classList.remove('is-valid');
        longTermStartDateInput.classList.add('is-invalid');
        longTermEndDateInput.classList.add('is-invalid');
      } else {
        longTermPeriodWarning.classList.remove('is-visible');
        longTermEndDateInput.setCustomValidity('');
        longTermStartDateInput.classList.remove('is-invalid');
        longTermEndDateInput.classList.remove('is-invalid');
        longTermStartDateInput.classList.add('is-valid');
        longTermEndDateInput.classList.add('is-valid');
      }
    };

    const validatePositiveLongTermField = (input, warning, message) => {
      if (!input || !warning) {
        return;
      }

      const rawValue = (input.value || '').trim();
      if (rawValue === '') {
        warning.classList.remove('is-visible');
        input.setCustomValidity('');
        input.classList.remove('is-valid');
        input.classList.remove('is-invalid');
        return;
      }

      const numericValue = Number(rawValue);
      const isInvalid = Number.isNaN(numericValue) || numericValue <= 0;
      if (isInvalid) {
        warning.classList.add('is-visible');
        input.setCustomValidity(message);
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
      } else {
        warning.classList.remove('is-visible');
        input.setCustomValidity('');
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
      }
    };

    const validateTargetWeightAgainstGoalType = () => {
      if (!longTermTargetWeightInput || !longTermTargetPositiveWarning || !longTermTargetGoalWarning) {
        return;
      }

      const targetRaw = (longTermTargetWeightInput.value || '').trim();
      if (targetRaw === '') {
        longTermTargetPositiveWarning.classList.remove('is-visible');
        longTermTargetGoalWarning.classList.remove('is-visible');
        longTermTargetWeightInput.setCustomValidity('');
        longTermTargetWeightInput.classList.remove('is-valid');
        longTermTargetWeightInput.classList.remove('is-invalid');
        return;
      }

      const target = Number(targetRaw);
      if (Number.isNaN(target) || target <= 0) {
        longTermTargetPositiveWarning.classList.add('is-visible');
        longTermTargetGoalWarning.classList.remove('is-visible');
        longTermTargetWeightInput.setCustomValidity('Target weight must be a positive value.');
        longTermTargetWeightInput.classList.remove('is-valid');
        longTermTargetWeightInput.classList.add('is-invalid');
        return;
      }

      longTermTargetPositiveWarning.classList.remove('is-visible');

      if (!longTermTypeSelect || !longTermInitialWeightInput) {
        longTermTargetGoalWarning.classList.remove('is-visible');
        longTermTargetWeightInput.setCustomValidity('');
        longTermTargetWeightInput.classList.remove('is-invalid');
        longTermTargetWeightInput.classList.add('is-valid');
        return;
      }

      const initialRaw = (longTermInitialWeightInput.value || '').trim();
      const initial = Number(initialRaw);
      const type = longTermTypeSelect.value;
      if (initialRaw === '' || Number.isNaN(initial) || initial <= 0 || !type) {
        longTermTargetGoalWarning.textContent = 'Select a goal type and enter a positive initial weight first.';
        longTermTargetGoalWarning.classList.add('is-visible');
        longTermTargetWeightInput.setCustomValidity('Select a goal type and enter a positive initial weight first.');
        longTermTargetWeightInput.classList.remove('is-valid');
        longTermTargetWeightInput.classList.add('is-invalid');
        if (longTermTypeSelect) {
          if (type) {
            longTermTypeSelect.classList.remove('is-invalid');
            longTermTypeSelect.classList.add('is-valid');
          } else {
            longTermTypeSelect.classList.remove('is-valid');
            longTermTypeSelect.classList.add('is-invalid');
          }
        }
        return;
      }

      if (longTermTypeSelect) {
        longTermTypeSelect.classList.remove('is-invalid');
        longTermTypeSelect.classList.add('is-valid');
      }

      let goalRuleMessage = '';
      if (type === 'perte_de_poids' && !(target < initial)) {
        goalRuleMessage = 'For weight loss, target value must be lower than initial value.';
      } else if (type === 'prise_de_poids' && !(target > initial)) {
        goalRuleMessage = 'For weight gain, target value must be greater than initial value.';
      } else if (type === 'maintien_de_poids' && Math.abs(target - initial) > 0.000001) {
        goalRuleMessage = 'For weight maintenance, target value must be equal to initial value.';
      }

      if (goalRuleMessage) {
        longTermTargetGoalWarning.textContent = goalRuleMessage;
        longTermTargetGoalWarning.classList.add('is-visible');
        longTermTargetWeightInput.setCustomValidity(goalRuleMessage);
        longTermTargetWeightInput.classList.remove('is-valid');
        longTermTargetWeightInput.classList.add('is-invalid');
      } else {
        longTermTargetGoalWarning.classList.remove('is-visible');
        longTermTargetWeightInput.setCustomValidity('');
        longTermTargetWeightInput.classList.remove('is-invalid');
        longTermTargetWeightInput.classList.add('is-valid');
      }
    };

    const bindPositiveLongTermFieldValidation = () => {
      positiveLongTermFields.forEach((fieldConfig) => {
        const input = document.getElementById(fieldConfig.inputId);
        const warning = document.getElementById(fieldConfig.warningId);
        if (!input || !warning) {
          return;
        }

        const runValidation = () => {
          validatePositiveLongTermField(input, warning, fieldConfig.message);
        };

        runValidation();
        input.addEventListener('input', runValidation);
        input.addEventListener('change', runValidation);
        input.addEventListener('blur', runValidation);
      });
    };

    const setLongTermEditMode = (enabled) => {
      if (!hasLongTermGoalFlag) {
        return;
      }

      longTermEditableFields.forEach((field) => {
        if (enabled) {
          field.removeAttribute('disabled');
        } else {
          field.setAttribute('disabled', 'disabled');
          field.classList.remove('is-valid');
          field.classList.remove('is-invalid');
        }
      });

      if (longTermEditStartButton) {
        longTermEditStartButton.hidden = enabled;
      }

      if (longTermSaveChangesButton) {
        longTermSaveChangesButton.hidden = !enabled;
      }

      if (longTermDeleteButton) {
        longTermDeleteButton.hidden = enabled;
      }

      if (longTermDeletePanel) {
        longTermDeletePanel.hidden = true;
        longTermDeletePanel.classList.remove('is-visible');
      }
    };

    if (longTermSportSlider) {
      syncLongTermSlider(longTermSportSlider, longTermSportValue);
      longTermSportSlider.addEventListener('input', () => {
        syncLongTermSlider(longTermSportSlider, longTermSportValue);
      });
    }

    if (longTermDietSlider) {
      syncLongTermSlider(longTermDietSlider, longTermDietValue);
      longTermDietSlider.addEventListener('input', () => {
        syncLongTermSlider(longTermDietSlider, longTermDietValue);
      });
    }

    if (longTermTypeSelect) {
      updateLongTermGoalBadgeTone();
      longTermTypeSelect.addEventListener('change', updateLongTermGoalBadgeTone);
      longTermTypeSelect.addEventListener('change', validateTargetWeightAgainstGoalType);
    }

    if (longTermStartDateInput && longTermEndDateInput) {
      validateLongTermPeriod();
      longTermStartDateInput.addEventListener('input', validateLongTermPeriod);
      longTermEndDateInput.addEventListener('input', validateLongTermPeriod);
      longTermStartDateInput.addEventListener('change', validateLongTermPeriod);
      longTermEndDateInput.addEventListener('change', validateLongTermPeriod);
    }

    bindPositiveLongTermFieldValidation();

    if (longTermInitialWeightInput) {
      longTermInitialWeightInput.addEventListener('input', validateTargetWeightAgainstGoalType);
      longTermInitialWeightInput.addEventListener('change', validateTargetWeightAgainstGoalType);
    }

    if (longTermTargetWeightInput) {
      validateTargetWeightAgainstGoalType();
      longTermTargetWeightInput.addEventListener('input', validateTargetWeightAgainstGoalType);
      longTermTargetWeightInput.addEventListener('change', validateTargetWeightAgainstGoalType);
      longTermTargetWeightInput.addEventListener('blur', validateTargetWeightAgainstGoalType);
    }

    if (hasLongTermGoalFlag) {
      setLongTermEditMode(longTermInitialEditMode);
      if (longTermEditStartButton) {
        longTermEditStartButton.addEventListener('click', () => {
          setLongTermEditMode(true);
        });
      }
    }

    if (calTitle && calGrid && calPrev && calNext) {
      const monthLabels = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
      ];
      const baseDate = new Date();
      let displayedMonth = baseDate.getMonth();
      let displayedYear = baseDate.getFullYear();
      
      const goalStartDateStr = document.body.getAttribute('data-goal-start-date');
      const goalEndDateStr = document.body.getAttribute('data-goal-end-date');
      let goalStartDate = null;
      let goalEndDate = null;
      if (goalStartDateStr) {
        const parts = goalStartDateStr.split('-');
        if (parts.length === 3) {
          goalStartDate = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        }
      }
      if (goalEndDateStr) {
        const parts = goalEndDateStr.split('-');
        if (parts.length === 3) {
          goalEndDate = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        }
      }

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
          cell.innerHTML = '<strong>' + dayNumber + '</strong>';
          calGrid.appendChild(cell);
        }

        for (let day = 1; day <= totalDays; day += 1) {
          const cellDate = new Date(displayedYear, displayedMonth, day);
          const isToday =
            cellDate.getDate() === baseDate.getDate() &&
            cellDate.getMonth() === baseDate.getMonth() &&
            cellDate.getFullYear() === baseDate.getFullYear();
          
          const isBeforeStart = goalStartDate && cellDate < goalStartDate;
          const isInGoalRange = goalStartDate && goalEndDate && cellDate >= goalStartDate && cellDate <= goalEndDate;
          const isClickable = isToday && !isBeforeStart;

          const cell = document.createElement('div');
          let className = 'weekly-cal-day';
          if (isToday) className += ' is-today';
          if (isInGoalRange) className += ' is-goal-range';
          if (!isClickable) className += ' is-disabled';
          cell.className = className;
          cell.innerHTML = '<strong>' + day + '</strong>';
          if (!isClickable) {
            cell.style.pointerEvents = 'none';
          } else {
            cell.style.cursor = 'pointer';
            cell.addEventListener('click', () => {
              if (weeklyCalendarActionWrap) {
                weeklyCalendarActionWrap.classList.add('is-visible');
              }
              if (weeklyDeletePanel) {
                weeklyDeletePanel.hidden = true;
                weeklyDeletePanel.classList.remove('is-visible');
              }
              if (weeklyGoalRequiredMsg) {
                weeklyGoalRequiredMsg.classList.remove('is-visible');
              }

              if (weeklyCalendarAddBtn && weeklyCalendarEditBtn && weeklyCalendarDeleteBtn) {
                weeklyCalendarAddBtn.hidden = weeklyHasRecord;
                weeklyCalendarEditBtn.hidden = !weeklyHasRecord;
                weeklyCalendarDeleteBtn.hidden = !weeklyHasRecord;
                weeklyCalendarAddBtn.dataset.date = String(displayedYear) + '-' + String(displayedMonth + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
                weeklyCalendarAddBtn.dataset.label = new Date(displayedYear, displayedMonth, day).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                weeklyCalendarEditBtn.dataset.date = weeklyCalendarAddBtn.dataset.date;
                weeklyCalendarEditBtn.dataset.label = weeklyCalendarAddBtn.dataset.label;
                weeklyCalendarDeleteBtn.dataset.id = weeklyRecordId;
              }
            });
          }
          calGrid.appendChild(cell);
        }

        for (let day = 1; day <= trailingDays; day += 1) {
          const cell = document.createElement('div');
          cell.className = 'weekly-cal-day is-other-month';
          cell.innerHTML = '<strong>' + day + '</strong>';
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

    const weeklySurveyPanel = document.getElementById('weekly-survey-panel');
    const weeklySurveyClose = document.getElementById('weekly-survey-close');
    const weeklySurveyCancel = document.getElementById('weekly-survey-cancel');
    const weeklySurveyForm = document.getElementById('weekly-survey-form');
    const weeklySurveyObjectifId = document.getElementById('weekly-objectif-id');
    const weeklySurveyDeleteForm = document.getElementById('weekly-delete-form');
    const weeklySurveyDeleteId = document.getElementById('weekly-delete-id');
    const weeklySwipeLayout = document.getElementById('weekly-swipe-layout');
    const weeklyWaterInput = document.getElementById('survey-water');
    const weeklyWaterGlasses = document.getElementById('weekly-water-glasses');
    const weeklyWaterCount = document.getElementById('weekly-water-count');
    const weeklyMacroOverview = document.getElementById('weekly-macro-overview');
    const weeklyMacroInputCal = document.getElementById('survey-cal');
    const weeklyMacroInputProt = document.getElementById('survey-prot');
    const weeklyMacroInputCarb = document.getElementById('survey-carb');
    const weeklyMacroInputFat = document.getElementById('survey-fat');
    const weeklyTrackerSleepInput = document.getElementById('survey-sleep');
    const weeklyTrackerStepsInput = document.getElementById('survey-steps');
    const weeklyStatusInput = document.getElementById('survey-status');
    const weeklyStatusBadge = document.getElementById('weekly-status-badge');
    const weeklyNotesInput = document.getElementById('survey-notes');
    const weeklyNotesCharCount = document.getElementById('weekly-notes-char-count');

    const updateWeeklyStatusBadge = () => {
      if (!weeklyStatusInput || !weeklyStatusBadge) {
        return;
      }

      const selectedOption = weeklyStatusInput.options[weeklyStatusInput.selectedIndex];
      const statusValue = weeklyStatusInput.value;
      const statusTone = selectedOption ? (selectedOption.getAttribute('data-tone') || '') : '';

      if (!statusValue) {
        weeklyStatusBadge.className = 'weekly-status-badge hidden';
        weeklyStatusBadge.textContent = '';
        return;
      }

      weeklyStatusBadge.className = 'weekly-status-badge ' + (statusTone || 'on-track');
      weeklyStatusBadge.textContent = statusValue;
    };

    const updateWeeklyNotesCharCount = () => {
      if (!weeklyNotesInput || !weeklyNotesCharCount) {
        return;
      }

      const length = weeklyNotesInput.value.length;
      weeklyNotesCharCount.textContent = String(length) + ' / 500';
      weeklyNotesCharCount.className = 'weekly-daily-log-char-count' + (length > 400 ? ' warn' : '');
    };

    const weeklyTrackerTargets = {
      sleep: 8,
      steps: 10000
    };

    const renderWeeklyTrackerOverview = () => {
      if (!weeklyTrackerSleepInput || !weeklyTrackerStepsInput) {
        return;
      }

      const sleepValue = Math.max(0, parseFloat(weeklyTrackerSleepInput.value || '0') || 0);
      const stepsValue = Math.max(0, parseInt(weeklyTrackerStepsInput.value || '0', 10) || 0);
      const sleepTarget = weeklyTrackerTargets.sleep;
      const stepsTarget = weeklyTrackerTargets.steps;

      const sleepPct = sleepTarget > 0 ? Math.min((sleepValue / sleepTarget) * 100, 100) : 0;
      const stepsPct = stepsTarget > 0 ? Math.min((stepsValue / stepsTarget) * 100, 100) : 0;
      const sleepRem = Math.round((sleepTarget - sleepValue) * 10) / 10;
      const stepsRem = Math.max(0, stepsTarget - stepsValue);
      const sleepOver = sleepValue > sleepTarget;
      const stepsOver = stepsValue > stepsTarget;

      const sleepDisplay = document.getElementById('weekly-tracker-sleep-display');
      const stepsDisplay = document.getElementById('weekly-tracker-steps-display');
      const sleepGoal = document.getElementById('weekly-tracker-sleep-goal');
      const stepsGoal = document.getElementById('weekly-tracker-steps-goal');
      const sleepTargetEl = document.getElementById('weekly-tracker-sleep-target');
      const stepsTargetEl = document.getElementById('weekly-tracker-steps-target');
      const sleepNum = document.getElementById('weekly-tracker-sleep-num');
      const stepsNum = document.getElementById('weekly-tracker-steps-num');
      const sleepConsumed = document.getElementById('weekly-tracker-sleep-consumed');
      const stepsConsumed = document.getElementById('weekly-tracker-steps-consumed');
      const sleepFill = document.getElementById('weekly-tracker-sleep-fill');
      const stepsFill = document.getElementById('weekly-tracker-steps-fill');
      const sleepRemain = document.getElementById('weekly-tracker-sleep-remain');
      const stepsRemain = document.getElementById('weekly-tracker-steps-remain');

      if (sleepDisplay) sleepDisplay.textContent = String(sleepValue) + 'h';
      if (stepsDisplay) stepsDisplay.textContent = stepsValue.toLocaleString();
      if (sleepGoal) sleepGoal.textContent = sleepOver ? 'Above target!' : 'Goal: 8h';
      if (stepsGoal) stepsGoal.textContent = stepsOver ? 'Above target!' : 'Goal: 10 000';
      if (sleepTargetEl) sleepTargetEl.textContent = String(sleepTarget);
      if (stepsTargetEl) stepsTargetEl.textContent = '10 000';
      if (sleepNum) sleepNum.textContent = String(sleepValue);
      if (stepsNum) stepsNum.textContent = stepsValue.toLocaleString();
      if (sleepConsumed) sleepConsumed.textContent = String(sleepValue) + ' h';
      if (stepsConsumed) stepsConsumed.textContent = stepsValue.toLocaleString();
      if (sleepFill) {
        sleepFill.style.width = String(sleepPct) + '%';
        sleepFill.classList.toggle('over', sleepOver);
      }
      if (stepsFill) {
        stepsFill.style.width = String(stepsPct) + '%';
        stepsFill.classList.toggle('over', stepsOver);
      }
      if (sleepRemain) {
        sleepRemain.textContent = sleepOver ? Math.abs(sleepRem) + ' h over!' : sleepRem + ' h remaining';
        sleepRemain.className = 'remaining ' + (sleepOver ? 'over' : sleepPct > 80 ? 'warn' : 'ok sleep-ok');
      }
      if (stepsRemain) {
        stepsRemain.textContent = stepsOver ? stepsRem.toLocaleString() + ' over!' : stepsRem.toLocaleString() + ' remaining';
        stepsRemain.className = 'remaining ' + (stepsOver ? 'over' : stepsPct > 80 ? 'warn' : 'ok steps-ok');
      }
    };

    const readWeeklyMacroTarget = (key, fallbackValue) => {
      if (!weeklyMacroOverview) {
        return fallbackValue;
      }
      const raw = parseFloat(weeklyMacroOverview.dataset[key] || String(fallbackValue));
      return Number.isFinite(raw) && raw > 0 ? raw : fallbackValue;
    };

    const weeklyMacroTargets = {
      cal: readWeeklyMacroTarget('targetCal', 2000),
      prot: readWeeklyMacroTarget('targetProt', 150),
      carb: readWeeklyMacroTarget('targetCarb', 200),
      fat: readWeeklyMacroTarget('targetFat', 65)
    };

    const weeklyMacroConfig = {
      cal: {
        input: weeklyMacroInputCal,
        valueEl: document.getElementById('weekly-macro-val-cal'),
        remEl: document.getElementById('weekly-macro-rem-cal'),
        numEl: document.getElementById('weekly-macro-num-cal'),
        targetEl: document.getElementById('weekly-macro-target-cal'),
        fillEl: document.getElementById('weekly-macro-fill-cal'),
        unit: ''
      },
      prot: {
        input: weeklyMacroInputProt,
        valueEl: document.getElementById('weekly-macro-val-prot'),
        remEl: document.getElementById('weekly-macro-rem-prot'),
        numEl: document.getElementById('weekly-macro-num-prot'),
        targetEl: document.getElementById('weekly-macro-target-prot'),
        fillEl: document.getElementById('weekly-macro-fill-prot'),
        unit: 'g'
      },
      carb: {
        input: weeklyMacroInputCarb,
        valueEl: document.getElementById('weekly-macro-val-carb'),
        remEl: document.getElementById('weekly-macro-rem-carb'),
        numEl: document.getElementById('weekly-macro-num-carb'),
        targetEl: document.getElementById('weekly-macro-target-carb'),
        fillEl: document.getElementById('weekly-macro-fill-carb'),
        unit: 'g'
      },
      fat: {
        input: weeklyMacroInputFat,
        valueEl: document.getElementById('weekly-macro-val-fat'),
        remEl: document.getElementById('weekly-macro-rem-fat'),
        numEl: document.getElementById('weekly-macro-num-fat'),
        targetEl: document.getElementById('weekly-macro-target-fat'),
        fillEl: document.getElementById('weekly-macro-fill-fat'),
        unit: 'g'
      }
    };

    const renderWeeklyMacroOverview = () => {
      if (!weeklyMacroOverview) {
        return;
      }

      Object.keys(weeklyMacroConfig).forEach((key) => {
        const conf = weeklyMacroConfig[key];
        if (!conf.input || !conf.valueEl || !conf.remEl || !conf.numEl || !conf.targetEl || !conf.fillEl) {
          return;
        }

        const target = weeklyMacroTargets[key];
        const rawValue = parseFloat(conf.input.value || '0');
        const consumed = Number.isFinite(rawValue) && rawValue > 0 ? rawValue : 0;
        const remaining = target - consumed;
        const percent = target > 0 ? Math.min((consumed / target) * 100, 100) : 0;
        const roundedConsumed = Math.round(consumed * 10) / 10;
        const roundedRemaining = Math.round(Math.abs(remaining) * 10) / 10;
        const roundedTarget = Math.round(target * 10) / 10;

        conf.valueEl.textContent = String(roundedConsumed) + conf.unit;
        conf.numEl.textContent = String(roundedConsumed);
        conf.targetEl.textContent = String(roundedTarget);
        conf.fillEl.style.width = String(percent) + '%';
        conf.fillEl.classList.toggle('over', consumed > target);

        if (consumed > target) {
          conf.remEl.textContent = String(roundedRemaining) + conf.unit + ' over';
        } else {
          conf.remEl.textContent = String(roundedRemaining) + conf.unit + ' remaining';
        }
      });
    };

    [weeklyMacroInputCal, weeklyMacroInputProt, weeklyMacroInputCarb, weeklyMacroInputFat].forEach((macroInput) => {
      if (macroInput) {
        macroInput.addEventListener('input', renderWeeklyMacroOverview);
      }
    });

    [weeklyTrackerSleepInput, weeklyTrackerStepsInput].forEach((trackerInput) => {
      if (trackerInput) {
        trackerInput.addEventListener('input', renderWeeklyTrackerOverview);
      }
    });

    if (weeklyStatusInput) {
      weeklyStatusInput.addEventListener('change', updateWeeklyStatusBadge);
    }

    if (weeklyNotesInput) {
      weeklyNotesInput.addEventListener('input', updateWeeklyNotesCharCount);
    }

    const renderWeeklyWaterSelector = () => {
      if (!weeklyWaterInput || !weeklyWaterGlasses || !weeklyWaterCount) {
        return;
      }

      const target = parseInt(weeklyWaterGlasses.getAttribute('data-target') || '8', 10);
      const current = Math.max(0, Math.min(parseInt(weeklyWaterInput.value || '0', 10), target));
      weeklyWaterInput.value = String(current);
      weeklyWaterCount.textContent = String(current);
      weeklyWaterGlasses.innerHTML = '';

      for (let i = 0; i < target; i += 1) {
        const isFilled = i < current;
        const glass = document.createElement('button');
        glass.type = 'button';
        glass.className = 'weekly-water-glass' + (isFilled ? ' is-filled' : '');
        glass.innerHTML = '<span>' + (isFilled ? '💧' : '') + '</span>';
        glass.addEventListener('click', () => {
          const nextValue = current === (i + 1) ? i : (i + 1);
          weeklyWaterInput.value = String(nextValue);
          renderWeeklyWaterSelector();
        });
        weeklyWaterGlasses.appendChild(glass);
      }
    };

    renderWeeklyWaterSelector();
    renderWeeklyMacroOverview();
    renderWeeklyTrackerOverview();
    updateWeeklyStatusBadge();
    updateWeeklyNotesCharCount();

    if (weeklyCalendarAddBtn && weeklySurveyPanel) {
      weeklyCalendarAddBtn.addEventListener('click', () => {
        if (!hasLongTermGoal) {
          if (weeklyGoalRequiredMsg) {
            weeklyGoalRequiredMsg.classList.add('is-visible');
            weeklyGoalRequiredMsg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
          return;
        }

        if (weeklyGoalRequiredMsg) {
          weeklyGoalRequiredMsg.classList.remove('is-visible');
        }

        const surveyDateInput = document.getElementById('survey-date');
        const surveyDateTitle = document.getElementById('weekly-survey-date');
        const dateValue = weeklyCalendarAddBtn.dataset.date || '';
        const dateLabel = weeklyCalendarAddBtn.dataset.label || '';

        if (weeklySurveyForm) {
          weeklySurveyForm.reset();
        }
        if (weeklySurveyObjectifId) {
          weeklySurveyObjectifId.value = '';
        }
        renderWeeklyWaterSelector();
        renderWeeklyMacroOverview();
        renderWeeklyTrackerOverview();
        updateWeeklyStatusBadge();
        updateWeeklyNotesCharCount();
        if (surveyDateInput) {
          surveyDateInput.value = dateValue;
        }
        if (surveyDateTitle) {
          surveyDateTitle.textContent = dateLabel ? 'Track for ' + dateLabel : 'Track for';
        }

        weeklySurveyPanel.hidden = false;
        weeklySurveyPanel.classList.add('is-visible');
        if (weeklySwipeLayout) {
          weeklySwipeLayout.classList.add('is-survey-open');
        }
        weeklySurveyPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (weeklyDeletePanel) {
          weeklyDeletePanel.hidden = true;
          weeklyDeletePanel.classList.remove('is-visible');
        }
      });
    }

    if (weeklyCalendarEditBtn && weeklySurveyPanel) {
      weeklyCalendarEditBtn.addEventListener('click', () => {
        const surveyDateInput = document.getElementById('survey-date');
        const surveyDateTitle = document.getElementById('weekly-survey-date');
        const dateValue = weeklyCalendarEditBtn.dataset.date || '';
        const dateLabel = weeklyCalendarEditBtn.dataset.label || '';

        if (weeklySurveyObjectifId) {
          weeklySurveyObjectifId.value = weeklyRecordId;
        }
        renderWeeklyWaterSelector();
        renderWeeklyMacroOverview();
        renderWeeklyTrackerOverview();
        updateWeeklyStatusBadge();
        updateWeeklyNotesCharCount();
        if (surveyDateInput) {
          surveyDateInput.value = dateValue;
        }
        if (surveyDateTitle) {
          surveyDateTitle.textContent = dateLabel ? 'Track for ' + dateLabel : 'Track for';
        }

        weeklySurveyPanel.hidden = false;
        weeklySurveyPanel.classList.add('is-visible');
        if (weeklySwipeLayout) {
          weeklySwipeLayout.classList.add('is-survey-open');
        }
        weeklySurveyPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (weeklyDeletePanel) {
          weeklyDeletePanel.hidden = true;
          weeklyDeletePanel.classList.remove('is-visible');
        }
      });
    }

    if (weeklyCalendarDeleteBtn && weeklySurveyDeleteForm && weeklySurveyDeleteId) {
      weeklyCalendarDeleteBtn.addEventListener('click', () => {
        weeklySurveyDeleteId.value = weeklyCalendarDeleteBtn.dataset.id || weeklyRecordId;
        if (weeklyDeletePanel) {
          weeklyDeletePanel.hidden = false;
          weeklyDeletePanel.classList.add('is-visible');
        }
      });
    }

    if (weeklyDeleteCancelBtn && weeklyDeletePanel) {
      weeklyDeleteCancelBtn.addEventListener('click', () => {
        weeklyDeletePanel.hidden = true;
        weeklyDeletePanel.classList.remove('is-visible');
      });
    }

    if (weeklyDeleteConfirmBtn && weeklySurveyDeleteForm && weeklySurveyDeleteId) {
      weeklyDeleteConfirmBtn.addEventListener('click', () => {
        if (!weeklySurveyDeleteId.value) {
          weeklySurveyDeleteId.value = weeklyRecordId;
        }
        weeklySurveyDeleteForm.submit();
      });
    }

    if (weeklySurveyClose && weeklySurveyPanel) {
      weeklySurveyClose.addEventListener('click', () => {
        weeklySurveyPanel.hidden = true;
        weeklySurveyPanel.classList.remove('is-visible');
        if (weeklySwipeLayout) {
          weeklySwipeLayout.classList.remove('is-survey-open');
        }
      });
    }

    if (weeklySurveyCancel && weeklySurveyPanel) {
      weeklySurveyCancel.addEventListener('click', () => {
        weeklySurveyPanel.hidden = true;
        weeklySurveyPanel.classList.remove('is-visible');
        if (weeklySwipeLayout) {
          weeklySwipeLayout.classList.remove('is-survey-open');
        }
      });
    }

    if (weeklySurveyForm) {
      weeklySurveyForm.addEventListener('submit', (e) => {
        e.preventDefault();
        weeklySurveyForm.submit();
      });
    }
  })();
</script>

</body>
</html>