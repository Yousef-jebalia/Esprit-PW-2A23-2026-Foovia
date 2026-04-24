<?php
session_start();

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role'] ?? '') !== 'admin') {
  header('Location: ../frontoffice/foovia-backoffice.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FOOVIA Backoffice Work</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f4f6f8;
      color: #1f2937;
    }
    .wrap {
      max-width: 900px;
      margin: 60px auto;
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      padding: 28px;
    }
    h1 {
      margin-top: 0;
      font-size: 28px;
    }
    p {
      line-height: 1.6;
    }
    .actions {
      margin-top: 20px;
      display: flex;
      gap: 10px;
    }
    a {
      display: inline-block;
      text-decoration: none;
      padding: 10px 14px;
      border-radius: 8px;
      border: 1px solid #d1d5db;
      color: #111827;
      background: #ffffff;
    }
    a.primary {
      background: #2563eb;
      color: #fff;
      border-color: #2563eb;
    }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Backoffice Work</h1>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>.</p>
    <p>This is your admin workspace page.</p>
    <div class="actions">
      <a class="primary" href="tabs.php">Open dashboard</a>
      <a href="../frontoffice/foovia.php">Go to frontoffice</a>
    </div>
  </div>
</body>
</html>
