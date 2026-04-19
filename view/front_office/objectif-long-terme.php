<?php
require_once '../../controller/ObjectifLongTerme_Controller.php';

$controller = new ObjectifLongTerme_Controller();

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
            <a href="../back_office/form-elements-component.php" class="goal-chip goal-chip-add">Add Goal</a>
            <a href="tracking.html#long-term-goals" class="goal-chip goal-chip-track">Back to Tracking</a>
          </div>
        </div>

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
                    $statusLabel = str_replace(['en_attente', 'en_cours', 'termine'], ['pending', 'in progress', 'completed'], $statusRaw);
                    $statusClass = 'goal-status-pending';
                    if ($statusLabel === 'in progress') {
                      $statusClass = 'goal-status-progress';
                    } elseif ($statusLabel === 'completed') {
                      $statusClass = 'goal-status-completed';
                    }
                  ?>
                  <tr>
                    <td><?php echo htmlspecialchars((string) $objectif['id_obj']); ?></td>
                    <td><?php echo htmlspecialchars($objectif['type_obj']); ?></td>
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
                        <a href="../back_office/edit-objectif-long-terme.php?id_obj=<?php echo urlencode((string) $objectif['id_obj']); ?>" class="goal-action goal-edit">Edit</a>
                        <button
                          type="button"
                          class="goal-action goal-delete"
                          data-bs-toggle="modal"
                          data-bs-target="#deleteConfirmModal"
                          data-id="<?php echo htmlspecialchars((string) $objectif['id_obj']); ?>"
                        >
                          Delete
                        </button>
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

        <p class="goal-footnote">Tip: open this page directly for a focused management view, or use it inside the tracking page in Long Term Goals.</p>
      </div>
    </section>
  </main>

  <div class="modal fade goal-modal" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm deletion</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Do you really want to delete this goal?
        </div>
        <div class="modal-footer">
          <button type="button" class="goal-modal-btn goal-modal-cancel" data-bs-dismiss="modal">Cancel</button>
          <form id="deleteObjectifForm" method="post" action="" class="d-inline">
            <input type="hidden" name="delete_id_obj" id="delete_id_obj" value="">
            <button type="submit" class="goal-modal-btn goal-modal-delete">Delete</button>
          </form>
        </div>
      </div>
    </div>
  </div>

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

      const deleteConfirmModal = document.getElementById('deleteConfirmModal');
      if (deleteConfirmModal) {
        deleteConfirmModal.addEventListener('show.bs.modal', function (event) {
          const triggerButton = event.relatedTarget;
          const objectifId = triggerButton.getAttribute('data-id');
          const hiddenInput = document.getElementById('delete_id_obj');
          if (hiddenInput) {
            hiddenInput.value = objectifId;
          }
        });
      }
    })();
  </script>
</body>
</html>