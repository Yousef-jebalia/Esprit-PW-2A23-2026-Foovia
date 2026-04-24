<?php
session_start();

include_once(__DIR__ . '/../../controller/Controller_user.php');

$controller = new Controller_user();
$users = [];
$searchTerm = trim($_GET['q'] ?? '');
$perPage = 15;
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$totalUsers = 0;
$totalPages = 1;
$successMessage = '';
$errorMessage = '';
$editUser = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'delete') {
    $deleteId = (int) ($_POST['id_user'] ?? 0);

    if ($deleteId > 0) {
      try {
        $controller->delete_user($deleteId);
        $successMessage = 'User deleted successfully.';
      } catch (Exception $e) {
        $errorMessage = 'Unable to delete this user.';
      }
    }
  }

  if ($action === 'edit_save') {
    $editId = (int) ($_POST['id_user'] ?? 0);

    if ($editId > 0) {
      try {
        $currentUser = $controller->get_user($editId);

        if ($currentUser) {
          $height = (int) ($currentUser['height_user'] ?? 0);
          $weight = (int) ($currentUser['weight_user'] ?? 0);

          $updatedUser = new User(
            $editId,
            trim($_POST['name_user'] ?? $currentUser['name_user']),
            trim($_POST['lastname_user'] ?? $currentUser['lastname_user']),
            trim($_POST['email_user'] ?? $currentUser['email_user']),
            $currentUser['password_user'] ?? '',
            trim($_POST['phone_user'] ?? $currentUser['phone_user']),
            $currentUser['gender_user'] ?? '',
            $currentUser['birthday_user'] ?? '',
            $height,
            $weight,
            (int) ($currentUser['bmi_user'] ?? 0),
            $currentUser['activitylvl_user'] ?? '',
            $currentUser['illness_user'] ?? '',
            $currentUser['allergie_user'] ?? '',
            $currentUser['medicament_user'] ?? '',
            $currentUser['inscriptiondate_user'] ?? date('Y-m-d H:i:s'),
            trim($_POST['role_user'] ?? $currentUser['role_user']),
            trim($_POST['subscription_user'] ?? ($currentUser['subscription_user'] ?? 'normal')),
            trim($_POST['account_state_user'] ?? ($currentUser['account_state_user'] ?? 'active')),
            trim($_POST['duration_user'] ?? ($currentUser['duration_user'] ?? '00:00:00'))
          );

          $controller->update_user($updatedUser, $editId);
          $successMessage = 'User updated successfully.';
        } else {
          $errorMessage = 'User not found for update.';
        }
      } catch (Exception $e) {
        $errorMessage = 'Unable to update this user.';
      }
    }
  }
}

$editId = (int) ($_GET['edit_id'] ?? 0);
if ($editId > 0) {
  try {
    $editUser = $controller->get_user($editId);
    if (!$editUser) {
      $errorMessage = 'Selected user was not found.';
    }
  } catch (Exception $e) {
    $errorMessage = 'Unable to load the selected user.';
  }
}

$usersResult = null;

try {
  if ($searchTerm !== '') {
    $usersResult = $controller->search_users($searchTerm);
  } else {
    $usersResult = $controller->listusers();
  }
} catch (Exception $e) {
  $errorMessage = 'Unable to load users list.';
}

try {
    if ($usersResult) {
        $users = $usersResult->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $errorMessage = 'Unable to load users list.';
}

$totalUsers = count($users);
$totalPages = max(1, (int) ceil($totalUsers / $perPage));
if ($currentPage > $totalPages) {
  $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $perPage;
$users = array_slice($users, $offset, $perPage);

$columns = [];
if (!empty($users)) {
    $columns = array_keys($users[0]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Foovia Backoffice - Users</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Boldonse&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --yellow: #f5c842;
      --green: #4bae52;
      --orange: #d94f00;
      --forest: #2e4a28;
      --dark: #111008;
      --off-white: #fdf8ee;
      --line: rgba(17, 16, 8, 0.12);
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'DM Sans', sans-serif;
      color: var(--dark);
      background:
        radial-gradient(circle at 10% 0%, rgba(245, 200, 66, 0.22), transparent 35%),
        radial-gradient(circle at 100% 10%, rgba(75, 174, 82, 0.16), transparent 38%),
        var(--off-white);
      min-height: 100vh;
      padding: 30px;
    }

    .page-shell {
      max-width: 1400px;
      margin: 0 auto;
      background: rgba(255, 255, 255, 0.85);
      border: 1px solid var(--line);
      border-radius: 20px;
      box-shadow: 0 16px 40px rgba(17, 16, 8, 0.12);
      overflow: hidden;
    }

    .hero {
      padding: 28px 30px;
      background: linear-gradient(120deg, #1c1a10 0%, #2f2a19 60%, #2e4a28 100%);
      color: #fff;
    }

    .hero h1 {
      margin: 0;
      font-family: 'Boldonse', sans-serif;
      font-size: clamp(1.5rem, 2vw, 2.1rem);
      letter-spacing: 0.02em;
    }

    .hero p {
      margin: 8px 0 0;
      color: rgba(255, 255, 255, 0.78);
    }

    .content {
      padding: 24px;
    }

    .notice {
      margin-bottom: 16px;
      padding: 12px 14px;
      border-radius: 10px;
      border: 1px solid #f2c1b2;
      background: #fff0eb;
      color: #842b16;
      font-weight: 500;
    }

    .notice.success {
      border-color: #b8e0b0;
      background: #edf9e9;
      color: #2f6f2c;
    }

    .controls {
      display: grid;
      gap: 14px;
      margin-bottom: 16px;
    }

    .search-form {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center;
    }

    .search-form input {
      flex: 1;
      min-width: 240px;
      border: 1px solid var(--line);
      border-radius: 10px;
      padding: 10px 12px;
      font: inherit;
      background: #fff;
    }

    .search-form button,
    .btn-ghost,
    .btn-save,
    .btn-delete,
    .btn-edit {
      border: 1px solid transparent;
      border-radius: 999px;
      padding: 8px 14px;
      font-weight: 700;
      font-size: 0.83rem;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }

    .search-form button,
    .btn-save,
    .btn-edit {
      background: var(--green);
      color: #fff;
    }

    .btn-delete {
      background: var(--orange);
      color: #fff;
    }

    .btn-ghost {
      background: #fff;
      color: var(--forest);
      border-color: var(--line);
    }

    .edit-box {
      border: 1px solid var(--line);
      border-radius: 14px;
      background: #fffef8;
      padding: 16px;
    }

    .edit-box h2 {
      margin: 0 0 12px;
      font-size: 1.05rem;
      color: var(--forest);
      font-family: 'Boldonse', sans-serif;
      letter-spacing: 0.03em;
    }

    .edit-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
      gap: 10px;
    }

    .edit-grid label {
      display: block;
      font-size: 0.8rem;
      color: #4f4b40;
      margin-bottom: 4px;
    }

    .edit-grid input,
    .edit-grid select {
      width: 100%;
      border: 1px solid var(--line);
      border-radius: 9px;
      padding: 9px 10px;
      font: inherit;
      background: #fff;
    }

    .edit-actions {
      margin-top: 12px;
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .empty {
      padding: 30px;
      border: 1px dashed var(--line);
      border-radius: 12px;
      text-align: center;
      color: #5f5a4f;
      background: #fff;
    }

    .table-wrap {
      width: 100%;
      overflow: auto;
      border: 1px solid var(--line);
      border-radius: 14px;
      background: #fff;
    }

    table {
      width: 100%;
      min-width: 1000px;
      border-collapse: collapse;
      font-size: 0.9rem;
    }

    thead th {
      position: sticky;
      top: 0;
      z-index: 1;
      background: linear-gradient(180deg, #fff8d7 0%, #fff 100%);
      color: var(--forest);
      text-align: left;
      padding: 12px 14px;
      border-bottom: 1px solid var(--line);
      white-space: nowrap;
      font-family: 'Boldonse', sans-serif;
      font-size: 0.78rem;
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }

    tbody td {
      padding: 11px 14px;
      border-bottom: 1px solid rgba(17, 16, 8, 0.07);
      white-space: nowrap;
      color: #2a2922;
    }

    tbody tr:nth-child(even) {
      background: #fffcf3;
    }

    tbody tr:hover {
      background: #f4fbe9;
    }

    .action-cell {
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .inline {
      margin: 0;
    }

    .top-actions {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 14px;
    }

    .back-link {
      text-decoration: none;
      color: #fff;
      background: var(--orange);
      border: 1px solid transparent;
      border-radius: 999px;
      padding: 8px 14px;
      font-weight: 700;
      font-size: 0.85rem;
      transition: background 0.2s ease;
    }

    .back-link:hover {
      background: #b63f00;
    }

    .table-meta {
      margin: 12px 2px;
      color: #5d594f;
      font-size: 0.86rem;
      font-weight: 500;
    }

    .pagination {
      margin-top: 14px;
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      align-items: center;
      justify-content: center;
    }

    .page-link {
      min-width: 36px;
      height: 36px;
      border-radius: 999px;
      padding: 0 12px;
      border: 1px solid var(--line);
      background: #fff;
      color: var(--forest);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 0.82rem;
    }

    .page-link.active {
      background: var(--green);
      color: #fff;
      border-color: transparent;
    }

    .page-link.disabled {
      opacity: 0.45;
      pointer-events: none;
    }

    @media (max-width: 900px) {
      .content {
        padding: 16px;
      }
      body {
        padding: 14px;
      }
    }
  </style>
</head>
<body>
  <div class="page-shell">
    <div class="hero">
      <h1>Foovia Users Table</h1>
      <p>Live list of all users from your database.</p>
    </div>

    <div class="content">
      <div class="top-actions">
        <a href="backoffice_work.php" class="back-link">Back to Backoffice</a>
      </div>

      <div class="controls">
        <form method="GET" class="search-form">
          <input
            type="text"
            name="q"
            placeholder="Search by id, name, lastname, email, phone or role"
            value="<?php echo htmlspecialchars($searchTerm); ?>"
          >
          <button type="submit">Search users</button>
          <a href="tabs.php" class="btn-ghost">Reset</a>
        </form>

        <?php if (!empty($editUser)): ?>
          <div class="edit-box">
            <h2>Edit user #<?php echo htmlspecialchars((string) ($editUser['id_user'] ?? '')); ?></h2>
            <form method="POST">
              <input type="hidden" name="action" value="edit_save">
              <input type="hidden" name="id_user" value="<?php echo htmlspecialchars((string) ($editUser['id_user'] ?? '')); ?>">

              <div class="edit-grid">
                <div>
                  <label for="name_user">Name</label>
                  <input id="name_user" name="name_user" type="text" value="<?php echo htmlspecialchars((string) ($editUser['name_user'] ?? '')); ?>" required>
                </div>
                <div>
                  <label for="lastname_user">Lastname</label>
                  <input id="lastname_user" name="lastname_user" type="text" value="<?php echo htmlspecialchars((string) ($editUser['lastname_user'] ?? '')); ?>" required>
                </div>
                <div>
                  <label for="email_user">Email</label>
                  <input id="email_user" name="email_user" type="email" value="<?php echo htmlspecialchars((string) ($editUser['email_user'] ?? '')); ?>" required>
                </div>
                <div>
                  <label for="phone_user">Phone</label>
                  <input id="phone_user" name="phone_user" type="text" value="<?php echo htmlspecialchars((string) ($editUser['phone_user'] ?? '')); ?>">
                </div>
                <div>
                  <label for="role_user">Role</label>
                  <select id="role_user" name="role_user">
                    <?php $roleVal = (string) ($editUser['role_user'] ?? 'user'); ?>
                    <option value="user" <?php echo $roleVal === 'user' ? 'selected' : ''; ?>>user</option>
                    <option value="admin" <?php echo $roleVal === 'admin' ? 'selected' : ''; ?>>admin</option>
                  </select>
                </div>
                <div>
                  <label for="subscription_user">Subscription</label>
                  <input id="subscription_user" name="subscription_user" type="text" value="<?php echo htmlspecialchars((string) ($editUser['subscription_user'] ?? 'normal')); ?>">
                </div>
                <div>
                  <label for="account_state_user">Account state</label>
                  <input id="account_state_user" name="account_state_user" type="text" value="<?php echo htmlspecialchars((string) ($editUser['account_state_user'] ?? 'active')); ?>">
                </div>
                <div>
                  <label for="duration_user">Duration (HH:MM:SS)</label>
                  <input id="duration_user" name="duration_user" type="text" pattern="^([0-1]?\\d|2[0-3]):[0-5]\\d:[0-5]\\d$" value="<?php echo htmlspecialchars((string) ($editUser['duration_user'] ?? '00:00:00')); ?>">
                </div>
              </div>

              <div class="edit-actions">
                <button class="btn-save" type="submit">Save changes</button>
                <a class="btn-ghost" href="tabs.php<?php echo $searchTerm !== '' ? '?q=' . urlencode($searchTerm) : ''; ?>">Cancel</a>
              </div>
            </form>
          </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($successMessage)): ?>
        <div class="notice success"><?php echo htmlspecialchars($successMessage); ?></div>
      <?php endif; ?>

      <?php if (!empty($errorMessage)): ?>
        <div class="notice"><?php echo htmlspecialchars($errorMessage); ?></div>
      <?php endif; ?>

      <?php if (empty($users)): ?>
        <div class="empty">No users found in the database.</div>
      <?php else: ?>
        <div class="table-meta">
          Showing <?php echo htmlspecialchars((string) ($offset + 1)); ?>
          to <?php echo htmlspecialchars((string) min($offset + $perPage, $totalUsers)); ?>
          of <?php echo htmlspecialchars((string) $totalUsers); ?> users
          (page <?php echo htmlspecialchars((string) $currentPage); ?> / <?php echo htmlspecialchars((string) $totalPages); ?>).
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <?php foreach ($columns as $column): ?>
                  <th><?php echo htmlspecialchars($column); ?></th>
                <?php endforeach; ?>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $user): ?>
                <tr>
                  <?php foreach ($columns as $column): ?>
                    <td><?php echo htmlspecialchars((string) ($user[$column] ?? '')); ?></td>
                  <?php endforeach; ?>
                  <td>
                    <div class="action-cell">
                      <a class="btn-edit" href="tabs.php?edit_id=<?php echo urlencode((string) ($user['id_user'] ?? '')); ?><?php echo $searchTerm !== '' ? '&q=' . urlencode($searchTerm) : ''; ?>">Edit</a>

                      <form method="POST" class="inline" onsubmit="return confirm('Delete this user?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_user" value="<?php echo htmlspecialchars((string) ($user['id_user'] ?? '')); ?>">
                        <button class="btn-delete" type="submit">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <?php if ($totalPages > 1): ?>
          <?php
            $baseParams = [];
            if ($searchTerm !== '') {
              $baseParams['q'] = $searchTerm;
            }
          ?>
          <div class="pagination">
            <?php
              $prevParams = $baseParams;
              $prevParams['page'] = $currentPage - 1;
              $prevHref = 'tabs.php?' . http_build_query($prevParams);
            ?>
            <a class="page-link <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>" href="<?php echo htmlspecialchars($prevHref); ?>">Prev</a>

            <?php
              $startPage = max(1, $currentPage - 2);
              $endPage = min($totalPages, $currentPage + 2);
            ?>

            <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
              <?php
                $pageParams = $baseParams;
                $pageParams['page'] = $p;
                $pageHref = 'tabs.php?' . http_build_query($pageParams);
              ?>
              <a class="page-link <?php echo $p === $currentPage ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($pageHref); ?>"><?php echo htmlspecialchars((string) $p); ?></a>
            <?php endfor; ?>

            <?php
              $nextParams = $baseParams;
              $nextParams['page'] = $currentPage + 1;
              $nextHref = 'tabs.php?' . http_build_query($nextParams);
            ?>
            <a class="page-link <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>" href="<?php echo htmlspecialchars($nextHref); ?>">Next</a>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
