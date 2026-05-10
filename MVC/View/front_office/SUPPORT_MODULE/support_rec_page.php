
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../../../Controller/SUPPORT_MODULE/Reclamtion_Controller.php';
include_once __DIR__ . '/../../../Controller/Controller_user.php';

$user_subscription = 'free';
if (isset($_SESSION['user_id'])) {
    $userController = new Controller_user();
    $userData = $userController->get_user($_SESSION['user_id']);
    $user_subscription = $userData['subscription_user'] ?? 'free';
}

$is_logged_in = isset($_SESSION['user_id']);
$logged_in_user_id = (int) ($_SESSION['user_id'] ?? 0);
$logged_in_user_name = trim((string) ($_SESSION['user_name'] ?? ''));

$chatbot_subscription_raw = strtolower(trim((string) ($_SESSION['subscription_user'] ?? $_SESSION['sunscription_user'] ?? 'normal')));
$chatbot_subscription_plan = ($chatbot_subscription_raw === 'premium' || $chatbot_subscription_raw === 'elite') ? 'premium' : 'normal';

$error = "";
$success = "";

$controller = new Controller_reclamation();
$reclamations = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (! $is_logged_in) {
        $error = "Please sign in to manage your claims.";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete' && !empty($_POST['id_recl'])) {
        $deleteReclamation = new Reclamations(
            $_POST['id_recl'],
            $logged_in_user_id,
            '',
            '',
            '',
            '',
            ''
        );
        if ($controller->suppression_reclamation_for_user($deleteReclamation, $logged_in_user_id)) {
            $success = "Claim deleted successfully.";
        } else {
            $error = "Claim not found or you do not own this claim.";
        }
    } elseif (
        isset($_POST["id_recl"]) && isset($_POST["id_user"]) && isset($_POST["description"]) && 
        isset($_POST["etat_rec"]) && isset($_POST["type"]) && isset($_POST["date_overture"]) 
    ) {
        if (
            !empty($_POST["id_recl"]) && !empty($_POST["description"]) && 
            !empty($_POST["etat_rec"]) && !empty($_POST["type"]) && !empty($_POST["date_overture"]) )
         {
            try {
                $reclamation = new Reclamations(
                    $_POST['id_recl'],
                    $logged_in_user_id,
                    $_POST['description'],
                    $_POST['etat_rec'],
                    $_POST['type'],
                    $_POST['date_overture'],
                    ''
                );
                $controller->add_reclamation($reclamation);
                $success = "Claim added successfully.";
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        } else {
            $error = "All fields are required.";
        }
    } else {
        $error = "Missing form data.";
    }
}

$reclamations = $is_logged_in ? $controller->get_reclamations_by_user($logged_in_user_id) : [];

// Sort claims by opening date (newest first). Rows without a parseable date go last.
if (!empty($reclamations)) {
    usort($reclamations, static function (array $a, array $b): int {
        $tsA = isset($a['dateouvert_reclam']) && $a['dateouvert_reclam'] !== '' && $a['dateouvert_reclam'] !== null
            ? strtotime((string) $a['dateouvert_reclam']) : false;
        $tsB = isset($b['dateouvert_reclam']) && $b['dateouvert_reclam'] !== '' && $b['dateouvert_reclam'] !== null
            ? strtotime((string) $b['dateouvert_reclam']) : false;
        if ($tsA === false && $tsB === false) {
            return 0;
        }
        if ($tsA === false) {
            return 1;
        }
        if ($tsB === false) {
            return -1;
        }
        return $tsB <=> $tsA;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Foovia</title>
    <link rel="icon" type="image/png" sizes="32x32" href="images/logo_web.png"/>
    <link rel="shortcut icon" href="images/logo_web.png"/>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <script>
      (function () {
        try {
          var t = localStorage.getItem('theme');
          if (t === 'dark' || t === 'light') {
            document.documentElement.setAttribute('data-theme', t);
            document.documentElement.style.colorScheme = t;
          }
        } catch (e) {}
      })();
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/vendor.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Open+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <style>
      .support-rec-body {
        background-color: var(--page-bg);
        color: var(--page-text);
      }
      :root[data-theme="dark"] .support-rec-body {
        background-color: var(--page-bg);
        color: var(--page-text);
      }
      :root[data-theme="dark"] header.support-page-header {
        background-color: var(--nav-bg) !important;
      }
      :root[data-theme="dark"] .theme-toggle .icon-sun,
      :root[data-theme="dark"] .theme-toggle .icon-moon {
        color: #e0e0e0;
      }
      .support-hero-anim > *,
      #support-claims-section .support-claims-title,
      #support-claims-section #reclamation-search,
      #reclamation-table thead th,
      #reclamation-table tbody td {
        will-change: transform, opacity;
      }
      .support-team-section { margin-bottom: 2.5rem; }
      .support-team-card {
        max-width: 280px;
        margin-left: auto;
        margin-right: auto;
        outline: none;
      }
      .support-team-card:focus-visible {
        outline: 2px solid var(--bs-primary, #0d6efd);
        outline-offset: 4px;
        border-radius: 1rem;
      }
      .support-team-card__surface {
        background: var(--surface);
        border: 1px solid var(--surface-border);
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        transition: box-shadow 0.25s ease;
      }
      .support-team-card:hover .support-team-card__surface,
      .support-team-card:focus-within .support-team-card__surface {
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
      }
      .support-team-card__figure {
        margin: 0;
        padding: 1.35rem 1.25rem 0.75rem;
        text-align: center;
      }
      .support-team-card__photo {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(27, 79, 43, 0.18);
        display: inline-block;
        vertical-align: middle;
      }
      .support-team-card__drawer {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        padding: 0 1.25rem;
        text-align: center;
        transition: max-height 0.45s ease, opacity 0.3s ease, padding 0.3s ease;
      }
      .support-team-card:hover .support-team-card__drawer,
      .support-team-card:focus-within .support-team-card__drawer {
        max-height: 240px;
        opacity: 1;
        padding: 0 1.25rem 1.35rem;
      }
      :root[data-theme="dark"] .support-team-card__surface {
        background: var(--surface);
        border-color: var(--surface-border);
      }
      :root[data-theme="dark"] .support-team-card__drawer .text-muted {
        color: rgba(255, 255, 255, 0.55) !important;
      }
      #support-claims-section #reclamation-search {
        background-color: var(--panel-bg);
        color: var(--panel-text);
        border-color: var(--surface-border);
      }
      :root[data-theme="dark"] #support-claims-section #reclamation-search {
        background-color: var(--surface);
        color: var(--panel-text);
        border-color: rgba(255, 255, 255, 0.12);
      }
      :root[data-theme="dark"] #reclamation-table,
      :root[data-theme="dark"] #reclamation-table td,
      :root[data-theme="dark"] #reclamation-table th {
        color: var(--page-text);
      }
      :root[data-theme="dark"] #reclamation-table thead th {
        background-color: var(--surface-2);
        border-color: rgba(255, 255, 255, 0.12);
      }
      :root[data-theme="dark"] #reclamation-table tbody td {
        background-color: var(--surface);
        border-color: rgba(255, 255, 255, 0.08);
      }
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
    </style>
  </head>
  <body class="support-rec-body">
    

    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
      <defs>
        <symbol xmlns="http://www.w3.org/2000/svg" id="facebook" viewBox="0 0 24 24"><path fill="currentColor" d="M15.12 5.32H17V2.14A26.11 26.11 0 0 0 14.26 2c-2.72 0-4.58 1.66-4.58 4.7v2.62H6.61v3.56h3.07V22h3.68v-9.12h3.06l.46-3.56h-3.52V7.05c0-1.05.28-1.73 1.76-1.73Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="twitter" viewBox="0 0 24 24"><path fill="currentColor" d="M22.991 3.95a1 1 0 0 0-1.51-.86a7.48 7.48 0 0 1-1.874.794a5.152 5.152 0 0 0-3.374-1.242a5.232 5.232 0 0 0-5.223 5.063a11.032 11.032 0 0 1-6.814-3.924a1.012 1.012 0 0 0-.857-.365a.999.999 0 0 0-.785.5a5.276 5.276 0 0 0-.242 4.769l-.002.001a1.041 1.041 0 0 0-.496.89a3.042 3.042 0 0 0 .027.439a5.185 5.185 0 0 0 1.568 3.312a.998.998 0 0 0-.066.77a5.204 5.204 0 0 0 2.362 2.922a7.465 7.465 0 0 1-3.59.448A1 1 0 0 0 1.45 19.3a12.942 12.942 0 0 0 7.01 2.061a12.788 12.788 0 0 0 12.465-9.363a12.822 12.822 0 0 0 .535-3.646l-.001-.2a5.77 5.77 0 0 0 1.532-4.202Zm-3.306 3.212a.995.995 0 0 0-.234.702c.01.165.009.331.009.488a10.824 10.824 0 0 1-.454 3.08a10.685 10.685 0 0 1-10.546 7.93a10.938 10.938 0 0 1-2.55-.301a9.48 9.48 0 0 0 2.942-1.564a1 1 0 0 0-.602-1.786a3.208 3.208 0 0 1-2.214-.935q.224-.042.445-.105a1 1 0 0 0-.08-1.943a3.198 3.198 0 0 1-2.25-1.726a5.3 5.3 0 0 0 .545.046a1.02 1.02 0 0 0 .984-.696a1 1 0 0 0-.4-1.137a3.196 3.196 0 0 1-1.425-2.673c0-.066.002-.133.006-.198a13.014 13.014 0 0 0 8.21 3.48a1.02 1.02 0 0 0 .817-.36a1 1 0 0 0 .206-.867a3.157 3.157 0 0 1-.087-.729a3.23 3.23 0 0 1 3.226-3.226a3.184 3.184 0 0 1 2.345 1.02a.993.993 0 0 0 .921.298a9.27 9.27 0 0 0 1.212-.322a6.681 6.681 0 0 1-1.026 1.524Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="youtube" viewBox="0 0 24 24"><path fill="currentColor" d="M23 9.71a8.5 8.5 0 0 0-.91-4.13a2.92 2.92 0 0 0-1.72-1A78.36 78.36 0 0 0 12 4.27a78.45 78.45 0 0 0-8.34.3a2.87 2.87 0 0 0-1.46.74c-.9.83-1 2.25-1.1 3.45a48.29 48.29 0 0 0 0 6.48a9.55 9.55 0 0 0 .3 2a3.14 3.14 0 0 0 .71 1.36a2.86 2.86 0 0 0 1.49.78a45.18 45.18 0 0 0 6.5.33c3.5.05 6.57 0 10.2-.28a2.88 2.88 0 0 0 1.53-.78a2.49 2.49 0 0 0 .61-1a10.58 10.58 0 0 0 .52-3.4c.04-.56.04-3.94.04-4.54ZM9.74 14.85V8.66l5.92 3.11c-1.66.92-3.85 1.96-5.92 3.08Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="instagram" viewBox="0 0 24 24"><path fill="currentColor" d="M17.34 5.46a1.2 1.2 0 1 0 1.2 1.2a1.2 1.2 0 0 0-1.2-1.2Zm4.6 2.42a7.59 7.59 0 0 0-.46-2.43a4.94 4.94 0 0 0-1.16-1.77a4.7 4.7 0 0 0-1.77-1.15a7.3 7.3 0 0 0-2.43-.47C15.06 2 14.72 2 12 2s-3.06 0-4.12.06a7.3 7.3 0 0 0-2.43.47a4.78 4.78 0 0 0-1.77 1.15a4.7 4.7 0 0 0-1.15 1.77a7.3 7.3 0 0 0-.47 2.43C2 8.94 2 9.28 2 12s0 3.06.06 4.12a7.3 7.3 0 0 0 .47 2.43a4.7 4.7 0 0 0 1.15 1.77a4.78 4.78 0 0 0 1.77 1.15a7.3 7.3 0 0 0 2.43.47C8.94 22 9.28 22 12 22s3.06 0 4.12-.06a7.3 7.3 0 0 0 2.43-.47a4.7 4.7 0 0 0 1.77-1.15a4.85 4.85 0 0 0 1.16-1.77a7.59 7.59 0 0 0 .46-2.43c0-1.06.06-1.4.06-4.12s0-3.06-.06-4.12ZM20.14 16a5.61 5.61 0 0 1-.34 1.86a3.06 3.06 0 0 1-.75 1.15a3.19 3.19 0 0 1-1.15.75a5.61 5.61 0 0 1-1.86.34c-1 .05-1.37.06-4 .06s-3 0-4-.06a5.73 5.73 0 0 1-1.94-.3a3.27 3.27 0 0 1-1.1-.75a3 3 0 0 1-.74-1.15a5.54 5.54 0 0 1-.4-1.9c0-1-.06-1.37-.06-4s0-3 .06-4a5.54 5.54 0 0 1 .35-1.9A3 3 0 0 1 5 5a3.14 3.14 0 0 1 1.1-.8A5.73 5.73 0 0 1 8 3.86c1 0 1.37-.06 4-.06s3 0 4 .06a5.61 5.61 0 0 1 1.86.34a3.06 3.06 0 0 1 1.19.8a3.06 3.06 0 0 1 .75 1.1a5.61 5.61 0 0 1 .34 1.9c.05 1 .06 1.37.06 4s-.01 3-.06 4ZM12 6.87A5.13 5.13 0 1 0 17.14 12A5.12 5.12 0 0 0 12 6.87Zm0 8.46A3.33 3.33 0 1 1 15.33 12A3.33 3.33 0 0 1 12 15.33Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="amazon" viewBox="0 0 24 24"><path fill="currentColor" d="M1.04 17.52q.1-.16.32-.02a21.308 21.308 0 0 0 10.88 2.9a21.524 21.524 0 0 0 7.74-1.46q.1-.04.29-.12t.27-.12a.356.356 0 0 1 .47.12q.17.24-.11.44q-.36.26-.92.6a14.99 14.99 0 0 1-3.84 1.58A16.175 16.175 0 0 1 12 22a16.017 16.017 0 0 1-5.9-1.09a16.246 16.246 0 0 1-4.98-3.07a.273.273 0 0 1-.12-.2a.215.215 0 0 1 .04-.12Zm6.02-5.7a4.036 4.036 0 0 1 .68-2.36A4.197 4.197 0 0 1 9.6 7.98a10.063 10.063 0 0 1 2.66-.66q.54-.06 1.76-.16v-.34a3.562 3.562 0 0 0-.28-1.72a1.5 1.5 0 0 0-1.32-.6h-.16a2.189 2.189 0 0 0-1.14.42a1.64 1.64 0 0 0-.62 1a.508.508 0 0 1-.4.46L7.8 6.1q-.34-.08-.34-.36a.587.587 0 0 1 .02-.14a3.834 3.834 0 0 1 1.67-2.64A6.268 6.268 0 0 1 12.26 2h.5a5.054 5.054 0 0 1 3.56 1.18a3.81 3.81 0 0 1 .37.43a3.875 3.875 0 0 1 .27.41a2.098 2.098 0 0 1 .18.52q.08.34.12.47a2.856 2.856 0 0 1 .06.56q.02.43.02.51v4.84a2.868 2.868 0 0 0 .15.95a2.475 2.475 0 0 0 .29.62q.14.19.46.61a.599.599 0 0 1 .12.32a.346.346 0 0 1-.16.28q-1.66 1.44-1.8 1.56a.557.557 0 0 1-.58.04q-.28-.24-.49-.46t-.3-.32a4.466 4.466 0 0 1-.29-.39q-.2-.29-.28-.39a4.91 4.91 0 0 1-2.2 1.52a6.038 6.038 0 0 1-1.68.2a3.505 3.505 0 0 1-2.53-.95a3.553 3.553 0 0 1-.99-2.69Zm3.44-.4a1.895 1.895 0 0 0 .39 1.25a1.294 1.294 0 0 0 1.05.47a1.022 1.022 0 0 0 .17-.02a1.022 1.022 0 0 1 .15-.02a2.033 2.033 0 0 0 1.3-1.08a3.13 3.13 0 0 0 .33-.83a3.8 3.8 0 0 0 .12-.73q.01-.28.01-.92v-.5a7.287 7.287 0 0 0-1.76.16a2.144 2.144 0 0 0-1.76 2.22Zm8.4 6.44a.626.626 0 0 1 .12-.16a3.14 3.14 0 0 1 .96-.46a6.52 6.52 0 0 1 1.48-.22a1.195 1.195 0 0 1 .38.02q.9.08 1.08.3a.655.655 0 0 1 .08.36v.14a4.56 4.56 0 0 1-.38 1.65a3.84 3.84 0 0 1-1.06 1.53a.302.302 0 0 1-.18.08a.177.177 0 0 1-.08-.02q-.12-.06-.06-.22a7.632 7.632 0 0 0 .74-2.42a.513.513 0 0 0-.08-.32q-.2-.24-1.12-.24q-.34 0-.8.04q-.5.06-.92.12a.232.232 0 0 1-.16-.04a.065.065 0 0 1-.02-.08a.153.153 0 0 1 .02-.06Z"/></symbol>

        <symbol xmlns="http://www.w3.org/2000/svg" id="menu" viewBox="0 0 24 24"><path fill="currentColor" d="M2 6a1 1 0 0 1 1-1h18a1 1 0 1 1 0 2H3a1 1 0 0 1-1-1m0 6.032a1 1 0 0 1 1-1h18a1 1 0 1 1 0 2H3a1 1 0 0 1-1-1m1 5.033a1 1 0 1 0 0 2h18a1 1 0 0 0 0-2z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="link" viewBox="0 0 24 24"><path fill="currentColor" d="M12 19a1 1 0 1 0-1-1a1 1 0 0 0 1 1Zm5 0a1 1 0 1 0-1-1a1 1 0 0 0 1 1Zm0-4a1 1 0 1 0-1-1a1 1 0 0 0 1 1Zm-5 0a1 1 0 1 0-1-1a1 1 0 0 0 1 1Zm7-12h-1V2a1 1 0 0 0-2 0v1H8V2a1 1 0 0 0-2 0v1H5a3 3 0 0 0-3 3v14a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V6a3 3 0 0 0-3-3Zm1 17a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-9h16Zm0-11H4V6a1 1 0 0 1 1-1h1v1a1 1 0 0 0 2 0V5h8v1a1 1 0 0 0 2 0V5h1a1 1 0 0 1 1 1ZM7 15a1 1 0 1 0-1-1a1 1 0 0 0 1 1Zm0 4a1 1 0 1 0-1-1a1 1 0 0 0 1 1Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="arrow-right" viewBox="0 0 24 24"><path fill="currentColor" d="M17.92 11.62a1 1 0 0 0-.21-.33l-5-5a1 1 0 0 0-1.42 1.42l3.3 3.29H7a1 1 0 0 0 0 2h7.59l-3.3 3.29a1 1 0 0 0 0 1.42a1 1 0 0 0 1.42 0l5-5a1 1 0 0 0 .21-.33a1 1 0 0 0 0-.76Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="category" viewBox="0 0 24 24"><path fill="currentColor" d="M19 5.5h-6.28l-.32-1a3 3 0 0 0-2.84-2H5a3 3 0 0 0-3 3v13a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-10a3 3 0 0 0-3-3Zm1 13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-13a1 1 0 0 1 1-1h4.56a1 1 0 0 1 .95.68l.54 1.64a1 1 0 0 0 .95.68h7a1 1 0 0 1 1 1Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="calendar" viewBox="0 0 24 24"><path fill="currentColor" d="M19 4h-2V3a1 1 0 0 0-2 0v1H9V3a1 1 0 0 0-2 0v1H5a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3Zm1 15a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-7h16Zm0-9H4V7a1 1 0 0 1 1-1h2v1a1 1 0 0 0 2 0V6h6v1a1 1 0 0 0 2 0V6h2a1 1 0 0 1 1 1Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="heart" viewBox="0 0 24 24"><path fill="currentColor" d="M20.16 4.61A6.27 6.27 0 0 0 12 4a6.27 6.27 0 0 0-8.16 9.48l7.45 7.45a1 1 0 0 0 1.42 0l7.45-7.45a6.27 6.27 0 0 0 0-8.87Zm-1.41 7.46L12 18.81l-6.75-6.74a4.28 4.28 0 0 1 3-7.3a4.25 4.25 0 0 1 3 1.25a1 1 0 0 0 1.42 0a4.27 4.27 0 0 1 6 6.05Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="plus" viewBox="0 0 24 24"><path fill="currentColor" d="M19 11h-6V5a1 1 0 0 0-2 0v6H5a1 1 0 0 0 0 2h6v6a1 1 0 0 0 2 0v-6h6a1 1 0 0 0 0-2Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="minus" viewBox="0 0 24 24"><path fill="currentColor" d="M19 11H5a1 1 0 0 0 0 2h14a1 1 0 0 0 0-2Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="cart" viewBox="0 0 24 24"><path fill="currentColor" d="M8.5 19a1.5 1.5 0 1 0 1.5 1.5A1.5 1.5 0 0 0 8.5 19ZM19 16H7a1 1 0 0 1 0-2h8.491a3.013 3.013 0 0 0 2.885-2.176l1.585-5.55A1 1 0 0 0 19 5H6.74a3.007 3.007 0 0 0-2.82-2H3a1 1 0 0 0 0 2h.921a1.005 1.005 0 0 1 .962.725l.155.545v.005l1.641 5.742A3 3 0 0 0 7 18h12a1 1 0 0 0 0-2Zm-1.326-9l-1.22 4.274a1.005 1.005 0 0 1-.963.726H8.754l-.255-.892L7.326 7ZM16.5 19a1.5 1.5 0 1 0 1.5 1.5a1.5 1.5 0 0 0-1.5-1.5Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="check" viewBox="0 0 24 24"><path fill="currentColor" d="M18.71 7.21a1 1 0 0 0-1.42 0l-7.45 7.46l-3.13-3.14A1 1 0 1 0 5.29 13l3.84 3.84a1 1 0 0 0 1.42 0l8.16-8.16a1 1 0 0 0 0-1.47Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="trash" viewBox="0 0 24 24"><path fill="currentColor" d="M10 18a1 1 0 0 0 1-1v-6a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1ZM20 6h-4V5a3 3 0 0 0-3-3h-2a3 3 0 0 0-3 3v1H4a1 1 0 0 0 0 2h1v11a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V8h1a1 1 0 0 0 0-2ZM10 5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v1h-4Zm7 14a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V8h10Zm-3-1a1 1 0 0 0 1-1v-6a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="search" viewBox="0 0 24 24"><path fill="currentColor" d="M21.71 20.29L18 16.61A9 9 0 1 0 16.61 18l3.68 3.68a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.39ZM11 18a7 7 0 1 1 7-7a7 7 0 0 1-7 7Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="close" viewBox="0 0 15 15"><path fill="currentColor" d="M7.953 3.788a.5.5 0 0 0-.906 0L6.08 5.85l-2.154.33a.5.5 0 0 0-.283.843l1.574 1.613l-.373 2.284a.5.5 0 0 0 .736.518l1.92-1.063l1.921 1.063a.5.5 0 0 0 .736-.519l-.373-2.283l1.574-1.613a.5.5 0 0 0-.283-.844L8.921 5.85l-.968-2.062Z"/></symbol>
        
        <symbol xmlns="http://www.w3.org/2000/svg" id="package" viewBox="0 0 48 48"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="m24 13.264l7.288 4.21L24 21.681l-7.288-4.209Z"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M16.712 17.473v8.418L24 30.101l7.288-4.21v-8.418M24 30.1v-8.418"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M40.905 21.405a16.905 16.905 0 1 0-23.389 15.611L24 43.5l6.484-6.484a16.906 16.906 0 0 0 10.42-15.611"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="secure" viewBox="0 0 48 48"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M14.134 36V20.11h19.732M19.279 36h14.587V25.45"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="m19.246 26.606l4.135 4.135l5.373-5.372m-8.934-9.282a4.087 4.087 0 1 1 8.174 0m0 0v4.023m-8.172-4.108v4.108"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M30.288 44.566a21.516 21.516 0 1 1 9.69-6.18"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="quality" viewBox="0 0 48 48"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="m30.59 13.45l4.77 2.94L24 34.68l-10.33-7l3.11-4.6l5.52 3.71l8.26-13.38Z"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M24 4.5s-11.26 2-15.25 2v20a11.16 11.16 0 0 0 .8 4.1a15 15 0 0 0 2 3.61a22 22 0 0 0 2.81 3.07a34.47 34.47 0 0 0 3 2.48a34 34 0 0 0 2.89 1.86c1 .59 1.71 1 2.13 1.19l1 .49a1.44 1.44 0 0 0 1.24 0l1-.49c.42-.2 1.13-.6 2.13-1.19a34 34 0 0 0 2.89-1.86a34.47 34.47 0 0 0 3-2.48a22 22 0 0 0 2.81-3.07a15 15 0 0 0 2-3.61a11.16 11.16 0 0 0 .8-4.1v-20c-3.99.03-15.25-2-15.25-2"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="savings" viewBox="0 0 48 48"><circle cx="24" cy="24" r="21.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M12.5 23.684a3.298 3.298 0 0 1 5.63-2.332l3.212 3.212h0l8.53-8.53a3.298 3.298 0 0 1 5.628 2.333h0c0 .875-.348 1.714-.966 2.333L22.983 32.25a2.321 2.321 0 0 1-3.283 0l-6.234-6.233a3.298 3.298 0 0 1-.966-2.333"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="offers" viewBox="0 0 48 48"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="m41.556 39.297l-22.022 3.11a1.097 1.097 0 0 1-1.245-.97l-2.352-22.311a1.097 1.097 0 0 1 1.08-1.213l24.238-.229a1.097 1.097 0 0 1 1.108 1.09l.137 19.429c.004.55-.4 1.017-.944 1.094M26.1 25.258v2.579m8.494-2.731v2.175"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M34.343 32.346c-1.437.828-1.926 1.198-2.774 1.988c-1.19-.457-2.284-1.228-3.797-1.456m-15.953 8.721l-3.49-1.6a1.12 1.12 0 0 1-.643-.863L5.511 23.593c-.056-.4.108-.8.43-1.046l3.15-2.406a1.257 1.257 0 0 1 2.014.874l1.966 19.69a.887.887 0 0 1-1.252.894m11.989-28.112c.214-.456.964-1.716 2.76-3.618c3.108-3.323 4.26-4.288 4.26-4.288s1.42.75 3.27 3.109c1.876 2.358 1.93 3.832 1.93 3.832s.67-.08-4.797 1.688c-3.055.991-4.368 1.152-4.931 1.152"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M26.97 17.828v-.054c0-.884-.241-1.715-.67-2.412c-.563-.91-1.447-1.608-2.492-1.876a3.58 3.58 0 0 0-1.072-.16c-.429 0-.858.053-1.233.214c-1.152.348-2.063 1.18-2.573 2.278a4.747 4.747 0 0 0-.428 1.956v.134"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M18.93 15.818c-.562-.107-1.5-.349-3.135-.884c-2.304-.75-3.43-1.528-3.43-1.528s-.456-1.393 1.045-3.296s2.653-2.52 2.653-2.52s.911.778 3.43 3.485c1.26 1.313 1.796 2.09 2.01 2.465h.027"/></symbol>
        
        <symbol xmlns="http://www.w3.org/2000/svg" id="delivery" viewBox="0 0 32 32"><path fill="currentColor" d="m29.92 16.61l-3-7A1 1 0 0 0 26 9h-3V7a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v17a1 1 0 0 0 1 1h2.14a4 4 0 0 0 7.72 0h6.28a4 4 0 0 0 7.72 0H29a1 1 0 0 0 1-1v-7a1 1 0 0 0-.08-.39M23 11h2.34l2.14 5H23ZM9 26a2 2 0 1 1 2-2a2 2 0 0 1-2 2m10.14-3h-6.28a4 4 0 0 0-7.72 0H4V8h17v12.56A4 4 0 0 0 19.14 23M23 26a2 2 0 1 1 2-2a2 2 0 0 1-2 2m5-3h-1.14A4 4 0 0 0 23 20v-2h5Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="organic" viewBox="0 0 24 24"><path fill="currentColor" d="M0 2.84c1.402 2.71 1.445 5.241 2.977 10.4c1.855 5.341 8.703 5.701 9.21 5.711c.46.726 1.513 1.704 3.926 2.21l.268-1.272c-2.082-.436-2.844-1.239-3.106-1.68l-.005.006c.087-.484 1.523-5.377-1.323-9.352C7.182 3.583 0 2.84 0 2.84m24 .84c-3.898.611-4.293-.92-11.473 3.093a11.879 11.879 0 0 1 2.625 10.05c3.723-1.486 5.166-3.976 5.606-6.466c0 0 1.27-4.716 3.242-6.677M12.527 6.773l-.002-.002v.004zM2.643 5.22s5.422 1.426 8.543 11.543c-2.945-.889-4.203-3.796-4.63-5.168h.006a15.863 15.863 0 0 0-3.92-6.375z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="fresh" viewBox="0 0 24 24"><g fill="none"><path d="M24 0v24H0V0zM12.594 23.258l-.012.002l-.071.035l-.02.004l-.014-.004l-.071-.036c-.01-.003-.019 0-.024.006l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427c-.002-.01-.009-.017-.016-.018m.264-.113l-.014.002l-.184.093l-.01.01l-.003.011l.018.43l.005.012l.008.008l.201.092c.012.004.023 0 .029-.008l.004-.014l-.034-.614c-.003-.012-.01-.02-.02-.022m-.715.002a.023.023 0 0 0-.027.006l-.006.014l-.034.614c0 .012.007.02.017.024l.015-.002l.201-.093l.01-.008l.003-.011l.018-.43l-.003-.012l-.01-.01z"/><path fill="currentColor" d="M20 9a1 1 0 0 1 1 1v1a8 8 0 0 1-8 8H9.414l.793.793a1 1 0 0 1-1.414 1.414l-2.496-2.496a.997.997 0 0 1-.287-.567L6 17.991a.996.996 0 0 1 .237-.638l.056-.06l2.5-2.5a1 1 0 0 1 1.414 1.414L9.414 17H13a6 6 0 0 0 6-6v-1a1 1 0 0 1 1-1m-4.793-6.207l2.5 2.5a1 1 0 0 1 0 1.414l-2.5 2.5a1 1 0 1 1-1.414-1.414L14.586 7H11a6 6 0 0 0-6 6v1a1 1 0 1 1-2 0v-1a8 8 0 0 1 8-8h3.586l-.793-.793a1 1 0 0 1 1.414-1.414"/></g></symbol>

        <symbol xmlns="http://www.w3.org/2000/svg" id="star-full" viewBox="0 0 24 24"><path fill="currentColor" d="m3.1 11.3l3.6 3.3l-1 4.6c-.1.6.1 1.2.6 1.5c.2.2.5.3.8.3c.2 0 .4 0 .6-.1c0 0 .1 0 .1-.1l4.1-2.3l4.1 2.3s.1 0 .1.1c.5.2 1.1.2 1.5-.1c.5-.3.7-.9.6-1.5l-1-4.6c.4-.3 1-.9 1.6-1.5l1.9-1.7l.1-.1c.4-.4.5-1 .3-1.5s-.6-.9-1.2-1h-.1l-4.7-.5l-1.9-4.3s0-.1-.1-.1c-.1-.7-.6-1-1.1-1c-.5 0-1 .3-1.3.8c0 0 0 .1-.1.1L8.7 8.2L4 8.7h-.1c-.5.1-1 .5-1.2 1c-.1.6 0 1.2.4 1.6"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="star-half" viewBox="0 0 24 24"><path fill="currentColor" d="m3.1 11.3l3.6 3.3l-1 4.6c-.1.6.1 1.2.6 1.5c.2.2.5.3.8.3c.2 0 .4 0 .6-.1c0 0 .1 0 .1-.1l4.1-2.3l4.1 2.3s.1 0 .1.1c.5.2 1.1.2 1.5-.1c.5-.3.7-.9.6-1.5l-1-4.6c.4-.3 1-.9 1.6-1.5l1.9-1.7l.1-.1c.4-.4.5-1 .3-1.5s-.6-.9-1.2-1h-.1l-4.7-.5l-1.9-4.3s0-.1-.1-.1c-.1-.7-.6-1-1.1-1c-.5 0-1 .3-1.3.8c0 0 0 .1-.1.1L8.7 8.2L4 8.7h-.1c-.5.1-1 .5-1.2 1c-.1.6 0 1.2.4 1.6m8.9 5V5.8l1.7 3.8c.1.3.5.5.8.6l4.2.5l-3.1 2.8c-.3.2-.4.6-.3 1c0 .2.5 2.2.8 4.1l-3.6-2.1c-.2-.2-.3-.2-.5-.2"/></symbol>

        <symbol xmlns="http://www.w3.org/2000/svg" id="user" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="9" r="3"/><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M17.97 20c-.16-2.892-1.045-5-5.97-5s-5.81 2.108-5.97 5"/></g></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="wishlist" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16.09v-4.992c0-4.29 0-6.433-1.318-7.766C18.364 2 16.242 2 12 2C7.757 2 5.636 2 4.318 3.332C3 4.665 3 6.81 3 11.098v4.993c0 3.096 0 4.645.734 5.321c.35.323.792.526 1.263.58c.987.113 2.14-.907 4.445-2.946c1.02-.901 1.529-1.352 2.118-1.47c.29-.06.59-.06.88 0c.59.118 1.099.569 2.118 1.47c2.305 2.039 3.458 3.059 4.445 2.945c.47-.053.913-.256 1.263-.579c.734-.676.734-2.224.734-5.321Z"/><path stroke-linecap="round" d="M15 6H9"/></g></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="shopping-bag" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3.864 16.455c-.858-3.432-1.287-5.147-.386-6.301C4.378 9 6.148 9 9.685 9h4.63c3.538 0 5.306 0 6.207 1.154c.901 1.153.472 2.87-.386 6.301c-.546 2.183-.818 3.274-1.632 3.91c-.814.635-1.939.635-4.189.635h-4.63c-2.25 0-3.375 0-4.189-.635c-.814-.636-1.087-1.727-1.632-3.91Z"/><path d="m19.5 9.5l-.71-2.605c-.274-1.005-.411-1.507-.692-1.886A2.5 2.5 0 0 0 17 4.172C16.56 4 16.04 4 15 4M4.5 9.5l.71-2.605c.274-1.005.411-1.507.692-1.886A2.5 2.5 0 0 1 7 4.172C7.44 4 7.96 4 9 4"/><path d="M9 4a1 1 0 0 1 1-1h4a1 1 0 1 1 0 2h-4a1 1 0 0 1-1-1Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 13v4m8-4v4m-4-4v4"/></g></symbol>

        <symbol xmlns="http://www.w3.org/2000/svg" id="fruits" viewBox="0 0 48 48"><g fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"><path d="M18.88 7.566a1 1 0 0 1 1 1v6.6a1 1 0 1 1-2 0v-6.6a1 1 0 0 1 1-1"/><path d="M11.78 13.905c1.13-.27 2.283-.065 3.48.553c.975.505 1.667.736 2.206.847c.538.112.966.114 1.483.114v2h-.02c-.516 0-1.12 0-1.868-.155c-.757-.157-1.622-.462-2.72-1.03c-.878-.453-1.54-.517-2.096-.384c-.584.14-1.201.53-1.912 1.264c-1.632 1.688-2.139 3.426-2.316 4.762c-.1 1.644.197 4.89 1.668 8.063c.5 1.08 1.21 2.57 2.076 3.737c.432.582.866 1.03 1.283 1.306c.405.267.741.34 1.046.288c3.123-.538 3.71-.551 4.319-.551h1.037v2H18.38c-.422 0-.92 0-3.95.522c-.94.162-1.787-.127-2.488-.59c-.689-.455-1.284-1.106-1.787-1.783c-1.005-1.353-1.791-3.024-2.284-4.088c-1.638-3.532-1.972-7.137-1.848-9.064l.003-.032l.004-.032c.212-1.644.844-3.839 2.866-5.928c.845-.874 1.783-1.556 2.885-1.82"/><path d="M14.64 11.41c1.496 1.431 2.307 3.166 2.307 4.51a1 1 0 1 0 2 0c0-2.05-1.168-4.275-2.925-5.956C14.244 8.265 11.743 7 8.896 7a1 1 0 0 0 0 2c2.244 0 4.268.999 5.743 2.41"/><path d="M8.574 7.009a1 1 0 0 1 1.116.868c.492 3.93 3.945 6 6.734 7.115a1 1 0 0 1-.743 1.857c-2.869-1.147-7.335-3.604-7.975-8.724a1 1 0 0 1 .868-1.116m17.188 6.894c-1.152-.264-2.334-.066-3.57.548c-1.02.506-1.747.74-2.317.853c-.57.113-1.022.115-1.56.115a1 1 0 0 0 0 2h.019c.537 0 1.16 0 1.93-.153c.781-.155 1.676-.458 2.816-1.024c.924-.458 1.632-.528 2.236-.39c.626.144 1.277.542 2.017 1.277c1.716 1.703 2.235 3.452 2.414 4.784a1 1 0 0 0 1.982-.266c-.222-1.653-.884-3.85-2.987-5.938c-.881-.874-1.85-1.548-2.98-1.806m.945 20.377a1 1 0 0 0-1.414.027c-.757.786-1.393 1.05-1.931.962c-3.252-.538-3.86-.55-4.485-.55a1 1 0 0 0 0 2h.028c.447 0 .967 0 4.13.523c1.522.252 2.785-.599 3.699-1.548a1 1 0 0 0-.027-1.415"/><path d="M32.65 16.103c-1.003 1.81-1.263 3.709-.864 4.992a1 1 0 1 1-1.91.594c-.609-1.959-.153-4.43 1.025-6.556c1.193-2.152 3.206-4.101 5.925-4.947a1 1 0 1 1 .594 1.91c-2.143.666-3.78 2.222-4.77 4.007"/><path d="M34.719 17.379c-1.168 1.71-2.748 2.793-4.073 3.013a1 1 0 1 0 .326 1.973c2.023-.335 4.027-1.851 5.398-3.858c1.388-2.032 2.227-4.706 1.762-7.515a1 1 0 1 0-1.974.326c.367 2.214-.288 4.375-1.44 6.06"/><path d="M31.78 23a2.5 2.5 0 1 0 0 5a2.5 2.5 0 0 0 0-5m-4.5 2.5a4.5 4.5 0 1 1 9 0a4.5 4.5 0 0 1-9 0"/><path d="M37.845 18.09a4.5 4.5 0 0 1 2.716 5.755a1 1 0 1 1-1.883-.675a2.5 2.5 0 1 0-4.706-1.69a1 1 0 0 1-1.882-.675a4.5 4.5 0 0 1 5.755-2.715"/><path d="M36.253 23.176a4.501 4.501 0 0 1 3.822 8.014a1 1 0 1 1-1.144-1.64a2.5 2.5 0 1 0-3.008-3.99a1 1 0 1 1-1.262-1.552a4.501 4.501 0 0 1 1.592-.832M27.78 29a2.5 2.5 0 1 0 0 5a2.5 2.5 0 0 0 0-5m-4.5 2.5a4.5 4.5 0 1 1 9 0a4.5 4.5 0 0 1-9 0"/><path d="M35.78 29a2.5 2.5 0 1 0 0 5a2.5 2.5 0 0 0 0-5m-4.5 2.5a4.5 4.5 0 1 1 9 0a4.5 4.5 0 0 1-9 0"/><path d="M31.78 35a2.5 2.5 0 1 0 0 5a2.5 2.5 0 0 0 0-5m-4.5 2.5a4.5 4.5 0 1 1 9 0a4.5 4.5 0 0 1-9 0"/><path d="M37.834 33.966a1 1 0 0 1 1.278-.606a4.5 4.5 0 1 1-4.675 7.44a1 1 0 1 1 1.405-1.423a2.5 2.5 0 1 0 2.598-4.133a1 1 0 0 1-.606-1.278"/></g></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="dairy" viewBox="0 0 48 48"><g fill="none"><path d="M0 0h48v48H0z"/><path fill="currentColor" fill-rule="evenodd" d="M10 5a1 1 0 0 1 1-1h18.571a1 1 0 0 1 .559.17l7.428 5A1 1 0 0 1 38 10v33a1 1 0 0 1-1 1H18.429a1 1 0 0 1-.559-.17l-7.428-5A1 1 0 0 1 10 38zm2 1.878v2.494a2 2 0 0 0 .168.802l1.985 4.539a1 1 0 0 0 1.67.258l.682-.781A2 2 0 0 0 17 12.873v-2.63zM19 11v31h17V11zm14.723-2h-14.99l-4.456-3h14.99zM36 23a8 8 0 1 0-16 0a8 8 0 0 0 16 0M17 40.833V16.61a2.964 2.964 0 0 1-2 .702v22.175zm-4-2.692V16.5h.012a2.997 2.997 0 0 1-.691-.986L12 14.781v22.687zM28 17a6 6 0 1 0 0 12a6 6 0 0 0 0-12m-4 5a1 1 0 0 1 1-1h6v2h-6a1 1 0 0 1-1-1m2 3a1 1 0 0 1 1-1h2v2h-2a1 1 0 0 1-1-1" clip-rule="evenodd"/></g></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="meat" viewBox="0 0 48 48"><g fill="currentColor"><path d="M14 14a1 1 0 1 0 0-2a1 1 0 0 0 0 2"/><path fill-rule="evenodd" d="M15.086 6c1.26-1.26 3.414-.368 3.414 1.414V9h1.586c1.782 0 2.674 2.154 1.414 3.414l-1.793 1.793a1.138 1.138 0 0 1-.037.036l3.456 5.847a4 4 0 0 0 4.08 1.914l12.58-2.027c1.63-.263 2.74 1.609 1.728 2.914c-.97 1.251-1.459 2.85-1.812 4.6C38.384 34.02 32.854 39.052 26 39.88V42h2.5v2H19v-2h5v-2c-5.414 0-10.21-2.607-13.107-6.608c-2.324-3.21-1.946-7.335-1.006-10.767l.495-1.805a6.996 6.996 0 0 0 .181-2.822L10.5 18H7.914C6.132 18 5.24 15.846 6.5 14.586zm5 5l-1.466 1.466l-.73-1.233a4.55 4.55 0 0 0-.307-.455c.275.142.586.222.917.222zM16.5 9c0 .334.082.65.227.926a4.548 4.548 0 0 0-1.894-.845L16.5 7.414zm-8.586 7l1.595-1.594c.04.208.096.416.168.624l.334.97zm3.654-1.622a2.548 2.548 0 0 1 4.601-2.127l5.236 8.857a6 6 0 0 0 6.119 2.87l12.148-1.957c-1.082 1.557-1.589 3.383-1.93 5.075a13.09 13.09 0 0 1-1.419 3.815a.999.999 0 0 0-.247.222C34.183 33.513 31.378 35 28.264 35C22.654 35 18 30.136 18 24a1 1 0 0 0-2 0c0 7.12 5.432 13 12.264 13c.4 0 .794-.02 1.184-.06A14.402 14.402 0 0 1 24 38c-4.763 0-8.96-2.291-11.487-5.78c-1.766-2.439-1.6-5.773-.697-9.066l.495-1.806a8.998 8.998 0 0 0-.171-5.311z" clip-rule="evenodd"/></g></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="seafood" viewBox="0 0 24 24"><path fill="currentColor" d="M13.497 4.564c1.649-.906 3.859-1.137 6.669-.694c.119.685.221 1.711.147 2.86c-.102 1.572-.53 3.278-1.602 4.656l-.165.212l-.036.263v.002l-.002.014l-.013.074a9.298 9.298 0 0 1-1.294 3.217l-.84-1.688l-.96.72C13.65 15.513 10.903 16 9 16H8v1c0 .77-.004 1.293-.106 1.804a3.722 3.722 0 0 1-.147.53l-4.011-4.012a8.2 8.2 0 0 1 .978-.209A10.285 10.285 0 0 1 5.985 15H7v-1c0-2.697.864-3.993 1.83-5.442L9.202 8L7.428 5.339a9.688 9.688 0 0 1 1.765-.411c.609-.088 1.228-.13 1.773-.123c.202.002.385.012.548.026c.09.768.373 1.643.861 2.475c.725 1.236 1.938 2.442 3.774 3.13l.936.351l.703-1.872l-.937-.351c-1.364-.512-2.234-1.39-2.75-2.27c-.385-.655-.557-1.28-.604-1.73m6.947 7.845c1.285-1.759 1.752-3.81 1.865-5.55c.117-1.806-.14-3.371-.344-4.121l-.164-.605l-.616-.116c-3.425-.643-6.471-.492-8.855.91a7.649 7.649 0 0 0-1.338-.122c-.66-.009-1.383.042-2.083.143c-.698.1-1.397.252-2.004.455c-.575.193-1.193.471-1.612.89l-.58.58L6.8 8.003c-.813 1.256-1.6 2.711-1.767 5.054c-.19.02-.4.045-.622.08c-.857.131-2.032.409-2.965 1.03l-1.015.678l7.725 7.725l.676-1.015c.563-.845.87-1.59 1.024-2.359c.083-.413.118-.823.133-1.231c1.704-.117 3.837-.545 5.612-1.523l1.12 2.25l.983-.983c1.188-1.189 1.88-2.582 2.273-3.653a11.298 11.298 0 0 0 .467-1.646M17.5 4.58l1.417 1.417L17.5 7.414l-1.417-1.417z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="bakery" viewBox="0 0 24 24"><path fill="currentColor" d="M19.87 17.412q.828.436 1.365-.206q.536-.643.138-1.414l-1.911-3.584l-1.666 4.123zm-5.308-.912h2.084l2.439-6.027q.228-.565.077-.914q-.15-.35-.57-.52l-2-.8q-.455-.19-.88-.047q-.424.145-.474.639zm-7.208 0h2.084l-.676-7.746q-.05-.39-.455-.548q-.405-.158-.9.032l-2 .8q-.45.19-.527.598q-.078.406.112.914zm-3.223.912l2.073-1.081l-1.627-4.123l-1.95 3.661q-.437.79.148 1.366q.585.575 1.356.177m6.307-.912h3.124l.788-8.912q.05-.455-.228-.772q-.278-.316-.772-.316h-2.7q-.373 0-.709.288t-.291.724zM3.53 18.538q-.87 0-1.45-.595q-.579-.595-.579-1.44q0-.28.068-.548q.069-.268.194-.524L4.077 11q-.33-.865-.08-1.715q.25-.85 1.045-1.173l2-.8q.427-.183.873-.185q.447-.002.797.265q.08-.782.628-1.337q.548-.555 1.34-.555h2.67q.78 0 1.338.526t.677 1.29q.293-.281.739-.225q.446.057.854.22l2 .8q.807.324 1.092 1.164q.285.84-.088 1.675l2.315 4.43q.111.218.186.437t.075.456q0 .933-.647 1.6q-.647.665-1.57.665q-.227 0-.431-.052q-.205-.053-.41-.16l-1.626-.826H6.134l-1.515.788q-.248.137-.524.194q-.276.056-.566.056m8.49-6.519"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="canned" viewBox="0 0 32 32"><g fill="none"><g fill="currentColor" clip-path="url(#fluentEmojiHighContrastCannedFood0)"><path d="M8 5.04h16v5.03h-2.239a7.977 7.977 0 0 0-5.771-2.46a7.977 7.977 0 0 0-5.771 2.46H8zm13.846 16.02H24v5.98H8v-5.98h2.134a7.978 7.978 0 0 0 5.856 2.55a7.978 7.978 0 0 0 5.856-2.55m-8.196-8.265a2.232 2.232 0 0 1-1.7-2.165h2.29c.71 0 1.344.333 1.752.852a2.232 2.232 0 0 1 1.768-.862h2.28a2.233 2.233 0 0 1-1.723 2.172a3.952 3.952 0 0 1-.757 7.828h-3.14c-2.18 0-3.95-1.77-3.95-3.95a3.954 3.954 0 0 1 3.18-3.875"/><path d="M3 3.52A3.52 3.52 0 0 1 6.52 0h18.3a3.52 3.52 0 0 1 2.17 6.292v19.5a3.532 3.532 0 0 1 1.35 2.778a3.52 3.52 0 0 1-3.52 3.52H6.52A3.52 3.52 0 0 1 3 28.57a3.54 3.54 0 0 1 2-3.185V6.696A3.52 3.52 0 0 1 3 3.52M24.82 2H6.52a1.52 1.52 0 1 0 0 3.04H7v22h-.48c-.84 0-1.52.69-1.52 1.53c0 .84.68 1.52 1.52 1.52h18.3c.84 0 1.52-.68 1.52-1.52c0-.78-.585-1.43-1.34-1.52V5.03A1.52 1.52 0 0 0 24.82 2"/></g><defs><clipPath id="fluentEmojiHighContrastCannedFood0"><path fill="#fff" d="M0 0h32v32H0z"/></clipPath></defs></g></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="frozen" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 10c0-3.771 0-5.657 1.172-6.828C6.343 2 8.229 2 12 2c3.771 0 5.657 0 6.828 1.172C20 4.343 20 6.229 20 10v3c0 3.771 0 5.657-1.172 6.828C17.657 21 15.771 21 12 21c-3.771 0-5.657 0-6.828-1.172C4 18.657 4 16.771 4 13z"/><path stroke-linejoin="round" d="M17 21v1h-1v-1m-8 0v1H7v-1"/><path d="M20 11.5H4"/><path stroke-linecap="round" d="M17 7v2m0 5v2"/></g></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="pasta" viewBox="0 0 32 32"><path fill="currentColor" d="m11.414 15l-8-8L2 8.414L8.586 15H2v1a14 14 0 0 0 28 0v-1ZM16 28A12.017 12.017 0 0 1 4.042 17h23.917A12.017 12.017 0 0 1 16 28"/><path fill="currentColor" d="M22 8a5.005 5.005 0 0 0-1.57.255A8.024 8.024 0 0 0 14 5a7.936 7.936 0 0 0-4.906 1.68L4.414 2L3 3.414l6.05 6.05l.707-.707A5.96 5.96 0 0 1 14 7a6.02 6.02 0 0 1 4.688 2.264a5.06 5.06 0 0 0-.59.61A2.99 2.99 0 0 1 15.754 11H12v2h3.754a4.98 4.98 0 0 0 3.904-1.874A3 3 0 0 1 25 13h2a5.006 5.006 0 0 0-5-5"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="breakfast" viewBox="0 0 2048 2048"><path fill="currentColor" d="M1408 592q-26 0-45-19t-19-45q0-51 19-98t56-83l79-80q38-38 38-91q0-26 19-45t45-19q26 0 45 19t19 45q0 51-19 98t-56 83l-79 80q-38 38-38 91q0 26-19 45t-45 19m-384 0q-26 0-45-19t-19-45q0-51 19-98t56-83l79-80q38-38 38-91q0-26 19-45t45-19q26 0 45 19t19 45q0 51-19 98t-56 83l-79 80q-38 38-38 91q0 26-19 45t-45 19m832 176q40 0 75 15t61 41t41 61t15 75v384q0 40-15 75t-41 61t-61 41t-75 15h-57q-2 7-3 13t-4 12v39q0 66-25 124t-69 102t-102 69t-124 25h-384q-78 0-144-35t-110-93H334q-66 0-124-25t-102-68t-69-102t-25-125v-64h256q0-79 30-149t83-122t122-83t149-30q30 0 58 5t56 14V640h1024v128zM654 1152q-53 0-99 20t-82 55t-55 81t-20 100h370v-228q-26-13-54-20t-60-8m-320 512h441q-7-29-7-64v-64H153q10 28 28 51t41 41t52 26t60 10m463 67v1l1 2v-1zm867-131V768H896v832q0 40 15 75t41 61t61 41t75 15h384q40 0 75-15t61-41t41-61t15-75m256-256V960q0-26-19-45t-45-19h-64v512h64q26 0 45-19t19-45"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="snacks" viewBox="0 0 48 48"><g fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="4"><path d="M6 14h36V8h-4l-2-4H12l-2 4H6z"/><path stroke-linecap="round" d="m36 44l2-30H10l2 30z"/></g></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="beverages" viewBox="0 0 24 24"><path fill="currentColor" d="M4 2h3.323l1.2 3H3v2h2.118l.827 14.059a1 1 0 0 0 .998.941h10.114a1 1 0 0 0 .998-.941L18.882 7H21V5H10.677l-2-5H4zm3.3 8.025L7.12 7h9.758l-.292 4.967c-2.307-.114-3.164-.475-4.216-.896c-1.092-.436-2.4-.936-5.072-1.046m.117 2.008c2.304.114 3.172.48 4.223.9c1.06.424 2.316.905 4.83 1.031L16.113 20H7.886z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="spices" viewBox="0 0 24 24"><path fill="currentColor" d="M14.178 9.766a9.981 9.981 0 0 0 4.827-2.622V4.003h-14v3.141a9.98 9.98 0 0 0 4.827 2.622a2.5 2.5 0 0 1 4.346 0m.208 2a2.501 2.501 0 0 1-4.762 0a11.941 11.941 0 0 1-4.62-2.015v10.252h14V9.75a11.942 11.942 0 0 1-4.618 2.016M4.005 2.004h16a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1h-16a1 1 0 0 1-1-1v-18a1 1 0 0 1 1-1"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="baby" viewBox="0 0 512 512"><path fill="currentColor" d="M425.39 200.035A184.3 184.3 0 0 0 290.812 91.289l26.756-42.809l-27.136-16.96l-35.305 56.488A184.046 184.046 0 0 0 86.61 200.035a71.978 71.978 0 0 0 0 143.93a184.071 184.071 0 0 0 338.78 0a71.978 71.978 0 0 0 0-143.93m27.152 99.975a39.77 39.77 0 0 1-27.76 11.961l-20.725.394l-8.113 19.074a152.066 152.066 0 0 1-279.887 0l-8.114-19.074l-20.725-.394a39.978 39.978 0 0 1 0-79.942l20.725-.394l8.114-19.074a152.067 152.067 0 0 1 279.887 0l8.113 19.074l20.725.394a39.974 39.974 0 0 1 27.76 67.981"/><path fill="currentColor" d="M168 232h40v40h-40zm136 0h40v40h-40zm-48 152a80 80 0 0 0 80-80H176a80 80 0 0 0 80 80"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="health" viewBox="0 0 24 24"><path fill="currentColor" d="M10.5 13H8v-3h2.5V7.5h3V10H16v3h-2.5v2.5h-3zM12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91c4.59-1.15 8-5.86 8-10.91V5zm6 9.09c0 4-2.55 7.7-6 8.83c-3.45-1.13-6-4.82-6-8.83v-4.7l6-2.25l6 2.25z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="household" viewBox="0 0 14 14"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12.36 6H1.64a1 1 0 0 0-1 1.13l.73 5.5a1 1 0 0 0 1 .87h9.24a1 1 0 0 0 1-.87l.73-5.5A1.001 1.001 0 0 0 12.36 6M4.5 8.5V11M7 8.5V11m2.5-2.5V11"/><path d="M9.48 1.54A2.79 2.79 0 0 1 11.78 4L12 6M2 6l.22-2a2.79 2.79 0 0 1 2.3-2.44"/><path d="M9.5 1.75A1.25 1.25 0 0 1 8.25 3h-2.5a1.25 1.25 0 0 1 0-2.5h2.5A1.25 1.25 0 0 1 9.5 1.75"/></g></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="personal" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M22.012 14.74a3.504 3.504 0 0 1-7.008 0c0-2.628 3.5-7.009 3.5-7.009s3.508 4.381 3.508 7.009M9.998 9.233H3.99a2.002 2.002 0 0 0-2.002 2.002v10.013c0 1.106.896 2.002 2.002 2.002h6.008A2.002 2.002 0 0 0 12 21.248V11.235a2.002 2.002 0 0 0-2.002-2.002M4.766 6.23h4.456a.776.776 0 0 1 .778.775v2.228H3.99V7.005a.776.776 0 0 1 .776-.775M14 2.752l-.447-.895A2 2 0 0 0 11.764.75H2.989m4.005 13.489v4.005m-2.002-2.002h4.004M6.994.75v5.48"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="pet" viewBox="0 0 14 14"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M1.5 9.5c.552 0 1-.672 1-1.5s-.448-1.5-1-1.5s-1 .672-1 1.5s.448 1.5 1 1.5m3-4.5c.552 0 1-.672 1-1.5S5.052 2 4.5 2s-1 .672-1 1.5s.448 1.5 1 1.5m5 0c.552 0 1-.672 1-1.5S10.052 2 9.5 2s-1 .672-1 1.5s.448 1.5 1 1.5m3 4.5c.552 0 1-.672 1-1.5s-.448-1.5-1-1.5s-1 .672-1 1.5s.448 1.5 1 1.5M10 10c0 1.38-1.62 2-3 2s-3-.62-3-2s1-3.5 3-3.5s3 2.12 3 3.5"/></symbol>
      </defs>
    </svg>
    <nav>
  <a href="../foovia.php" class="nav-logo">
    <img src="assets/Plan de travail 1 no bg (3) (1).png" alt="FOOVIA Logo" style="height: 50px; width: auto;">
    FOOVIA
  </a>
  <ul class="nav-links">
    <li><a href="../foovia.php">Dashboard</a></li>
    <li><a href="../marketplace-gateway.php">Marketplace</a></li>
    <li><a href="support_rec_page.php">Support & Community</a></li>
  </ul>
  <div class="nav-actions">
    <a href="../foovia-backoffice.php" class="nav-btn nav-backoffice">Backoffice</a>
    <button class="theme-toggle" type="button" aria-label="Switch to dark mode" aria-pressed="false">
      <svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true">
        <circle cx="12" cy="12" r="4"></circle>
        <path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"></path>
      </svg>
      <svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M21 14.5A8.5 8.5 0 1 1 9.5 3a7 7 0 1 0 11.5 11.5z"></path>
      </svg>
    </button>
    <?php if ($is_logged_in && $logged_in_user_name !== ''): ?>
      <div class="dropdown">
        <a href="#" class="nav-btn dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          Welcome, <?php echo htmlspecialchars($logged_in_user_name); ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="../foovia.php">Dashboard</a></li>
          <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
        </ul>
      </div>
    <?php else: ?>
      <a href="../foovia-signin.php?redirect=support" class="nav-btn nav-signin">Sign In</a>
      <a href="../foovia-signup.php" class="nav-btn nav-signup">Sign Up</a>
    <?php endif; ?>
    <?php if ($is_logged_in && ($user_subscription === 'premium' || $user_subscription === 'elite')): ?>
      <div class="premium-badge-nav" title="Premium Member" onclick="window.location.href='../foovia-premium.php'">
        <img src="../assets/crown-svgrepo-com%20(1).svg" class="premium-icon-nav" alt="Premium">
      </div>
    <?php endif; ?>
  </div>
</nav>

    <div class="preloader-wrapper">
      <div class="preloader">
      </div>
    </div>
    

    <div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasCart">
      <div class="offcanvas-header justify-content-center">
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <div class="order-md-last">
          <h4 class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-primary">Your cart</span>
            <span class="badge bg-primary rounded-pill">3</span>
          </h4>
          <ul class="list-group mb-3">
            <li class="list-group-item d-flex justify-content-between lh-sm">
              <div>
                <h6 class="my-0">Growers cider</h6>
                <small class="text-body-secondary">Brief description</small>
              </div>
              <span class="text-body-secondary">$12</span>
            </li>
            <li class="list-group-item d-flex justify-content-between lh-sm">
              <div>
                <h6 class="my-0">Fresh grapes</h6>
                <small class="text-body-secondary">Brief description</small>
              </div>
              <span class="text-body-secondary">$8</span>
            </li>
            <li class="list-group-item d-flex justify-content-between lh-sm">
              <div>
                <h6 class="my-0">Heinz tomato ketchup</h6>
                <small class="text-body-secondary">Brief description</small>
              </div>
              <span class="text-body-secondary">$5</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span>Total (USD)</span>
              <strong>$20</strong>
            </li>
          </ul>
  
          <button class="w-100 btn btn-primary btn-lg" type="submit">Continue to checkout</button>
        </div>
      </div>
    </div>
    
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar">

      <div class="offcanvas-header justify-content-between">
        <h4 class="fw-normal text-uppercase fs-6">Menu</h4>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body">
    
        <ul class="navbar-nav justify-content-end menu-list list-unstyled d-flex gap-md-3 mb-0">
          <li class="nav-item border-dashed active">
            <a href="index.html" class="nav-link d-flex align-items-center gap-3 text-dark p-2">
              <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#fruits"></use></svg>
              <span>Fruits and vegetables</span>
            </a>
          </li>
          <li class="nav-item border-dashed">
            <a href="index.html" class="nav-link d-flex align-items-center gap-3 text-dark p-2">
              <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#dairy"></use></svg>
              <span>Dairy and Eggs</span>
            </a>
          </li>
          <li class="nav-item border-dashed">
            <a href="index.html" class="nav-link d-flex align-items-center gap-3 text-dark p-2">
              <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#meat"></use></svg>
              <span>Meat and Poultry</span>
            </a>
          </li>
          <li class="nav-item border-dashed">
            <a href="index.html" class="nav-link d-flex align-items-center gap-3 text-dark p-2">
              <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#seafood"></use></svg>
              <span>Seafood</span>
            </a>
          </li>
          <li class="nav-item border-dashed">
            <a href="index.html" class="nav-link d-flex align-items-center gap-3 text-dark p-2">
              <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#bakery"></use></svg>
              <span>Bakery and Bread</span>
            </a>
          </li>
          <li class="nav-item border-dashed">
            <a href="index.html" class="nav-link d-flex align-items-center gap-3 text-dark p-2">
              <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#canned"></use></svg>
              <span>Canned Goods</span>
            </a>
          </li>
          <li class="nav-item border-dashed">
            <a href="index.html" class="nav-link d-flex align-items-center gap-3 text-dark p-2">
              <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#frozen"></use></svg>
              <span>Frozen Foods</span>
            </a>
          </li>
          <li class="nav-item border-dashed">
            <a href="index.html" class="nav-link d-flex align-items-center gap-3 text-dark p-2">
              <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#pasta"></use></svg>
              <span>Pasta and Rice</span>
            </a>
          </li>
          <li class="nav-item border-dashed">
            <a href="index.html" class="nav-link d-flex align-items-center gap-3 text-dark p-2">
              <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#breakfast"></use></svg>
              <span>Breakfast Foods</span>
            </a>
          </li>
          <li class="nav-item border-dashed">
            <a href="index.html" class="nav-link d-flex align-items-center gap-3 text-dark p-2">
              <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#snacks"></use></svg>
              <span>Snacks and Chips</span>
            </a>
          </li>
          <li class="nav-item border-dashed">
            <button class="btn btn-toggle dropdown-toggle position-relative w-100 d-flex justify-content-between align-items-center text-dark p-2" data-bs-toggle="collapse" data-bs-target="#beverages-collapse" aria-expanded="false">
              <div class="d-flex gap-3">
                <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#beverages"></use></svg>
                <span>Beverages</span>
              </div>
            </button>
            <div class="collapse" id="beverages-collapse">
              <ul class="btn-toggle-nav list-unstyled fw-normal ps-5 pb-1">
                <li class="border-bottom py-2"><a href="index.html" class="dropdown-item">Water</a></li>
                <li class="border-bottom py-2"><a href="index.html" class="dropdown-item">Juice</a></li>
                <li class="border-bottom py-2"><a href="index.html" class="dropdown-item">Soda</a></li>
                <li class="border-bottom py-2"><a href="index.html" class="dropdown-item">Tea</a></li>
              </ul>
            </div>
          </li>
          <li class="nav-item border-dashed">
            <a href="index.html" class="nav-link d-flex align-items-center gap-3 text-dark p-2">
              <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#spices"></use></svg>
              <span>Spices and Seasonings</span>
            </a>
          </li>
          <li class="nav-item border-dashed">
            <a href="index.html" class="nav-link d-flex align-items-center gap-3 text-dark p-2">
              <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#baby"></use></svg>
              <span>Baby Food and Formula</span>
            </a>
          </li>
          <li class="nav-item border-dashed">
            <a href="index.html" class="nav-link d-flex align-items-center gap-3 text-dark p-2">
              <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#health"></use></svg>
              <span>Health and Wellness</span>
            </a>
          </li>
          <li class="nav-item border-dashed">
            <a href="index.html" class="nav-link d-flex align-items-center gap-3 text-dark p-2">
              <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#household"></use></svg>
              <span>Household Supplies</span>
            </a>
          </li>
          <li class="nav-item border-dashed">
            <a href="index.html" class="nav-link d-flex align-items-center gap-3 text-dark p-2">
              <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#personal"></use></svg>
              <span>Personal Care</span>
            </a>
          </li>
          <li class="nav-item border-dashed">
            <a href="index.html" class="nav-link d-flex align-items-center gap-3 text-dark p-2">
              <svg width="24" height="24" viewBox="0 0 24 24"><use xlink:href="#pet"></use></svg>
              <span>Pet Food and Supplies</span>
            </a>
          </li>
        </ul>
      
      </div>

    </div>

    <section class="hero">
  <div class="hero-text">
    
    <h1 class="hero-title">
      Eat smart.<br>
      Train <span class="accent">better.</span><br>
      Waste <span class="accent2">nothing.</span>
    </h1>
    <div class="hero-actions mt-4">
      <button id="refresh-claims-btn" type="button" class="btn-secondary">Refresh</button>
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
    <section id="support-claims-section">
        <div class="container mt-5">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <h2 class="mb-0 support-claims-title">List of Claims</h2>
                <div class="d-flex flex-wrap gap-2">
                    <a href="threads_page.php" class="btn btn-outline-success">Community Threads</a>
                    <?php if ($is_logged_in): ?>
                        <a href="add_rec_page.php" class="btn btn-primary">Create Claim</a>
                    <?php else: ?>
                        <a href="../foovia-signin.php?redirect=support" class="btn btn-primary">Sign in to create a claim</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!$is_logged_in): ?>
                <div class="alert alert-info" role="alert">
                    Sign in to see your claims and create new support requests.
                </div>
            <?php endif; ?>
            <div class="mb-3">
                <input id="reclamation-search" type="text" class="form-control" style="border: 2px solid #ddd; border-radius: 8px;" placeholder="Search in the list of claims...">
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="reclamation-table">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Opening Date</th>
                            <th>Closing Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reclamations)): ?>
                            <?php foreach ($reclamations as $reclamation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reclamation['description_reclam']); ?></td>
                                    <td><?php echo htmlspecialchars($reclamation['etat_reclam']); ?></td>
                                    <td><?php echo htmlspecialchars($reclamation['type_reclam']); ?></td>
                                    <td><?php echo htmlspecialchars($reclamation['dateouvert_reclam']); ?></td>
                                    <td><?php echo htmlspecialchars($reclamation['dateferm_reclam'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No claims found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
    </section>
    <br><br>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4" style="text-align: center;">
                    <h2 class="mb-0" >FAQ</h2>
                </div>

        <section >

        <div style="background: #F8F5EE; border: 2px solid #FEF4EA; border-radius: 8px; padding: 24px; width: auto; display: inline-block; box-sizing: border-box; box-shadow: 0 6px 16px rgba(0,0,0,0.15); border-left: 5px solid #FF4800;">
          <p style="margin: 0px 0px 12px; font-weight: bold; text-align: center; font-size: 18px;">FAQ Section</p>
          <ul style="margin: 0; padding-left: 20px;">
          <li><a href="#pourquoi-creer-faq" style="text-decoration: none;">How to pay for subscription?</a></li>
          <li><a href="#exemples-faq" style="text-decoration: none;">How to contact the support team?</a></li>
          <li><a href="#creer-faq-convertit" style="text-decoration: none;">How can i give you 5 stars rating?</a></li>
          <li><a href="#faq-ecommerce" style="text-decoration: none;">Who created this amazing platform ?</a></li>
          <li><a href="#outils-faq" style="text-decoration: none;">Are there reductions offers?</a></li>
          <li><a href="#outils-faq" style="text-decoration: none;">How to edit my profile information && data ?</a></li>
          </ul>
        </div>
        </section>
    </div>
    <br><br>







    <div class="container mt-4">
        <h2 id="support-team-heading" class="h3 fw-bold text-center mb-2 text-body">How to get in touch</h2>
        <p class="text-center text-muted small mb-4 mb-lg-5">Hover or focus a teammate to see their contact details.</p>

        <section class="support-team-section" aria-labelledby="support-team-heading">
          <div class="row justify-content-center g-4">
            <div class="col-sm-6 col-lg-4">
              <article class="support-team-card" tabindex="0" aria-label="Sara Mezzi, Head of Support, sara.mezzi@foovia.com, +216 55 100 077">
                <div class="support-team-card__surface">
                  <figure class="support-team-card__figure">
                    <img class="support-team-card__photo" src="assets/dsi piccpy.jpg" width="140" height="140" alt="agent 2">
                  </figure>
                  <div class="support-team-card__drawer">
                    <h3 class="h5 mb-1 text-body">Youssef Jebalia</h3>
                    <p class="small text-muted mb-2">Head of Support</p>
                    <p class="small mb-1"><a href="mailto:youssef.jebalia@esprit.tn">youssef.jebalia@esprit.tn</a></p>
                    <p class="small mb-0 text-body">+216 29530130</p>
                  </div>
                </div>
              </article>
            </div>
            <div class="col-sm-6 col-lg-4">
              <article class="support-team-card" tabindex="0" aria-label="Karim Ben Salah, Claims specialist, karim.bensalah@foovia.com, +216 98 203 040">
                <div class="support-team-card__surface">
                  <figure class="support-team-card__figure">
                    <img class="support-team-card__photo" src="assets/coolbob.png" width="140" height="140" alt="agent 1">
                  </figure>
                  <div class="support-team-card__drawer">
                    <h3 class="h5 mb-1 text-body">Youssef Jebalia</h3>
                    <p class="small text-muted mb-2">Also Claims specialist</p>
                     <p class="small mb-1"><a href="mailto:youssef.jebalia@esprit.tn">youssef.jebalia@esprit.tn/a></p>
                    <p class="small mb-0 text-body">+216 77 777 777</p>
                  </div>
                </div>
              </article>
            </div>
            <div class="col-sm-6 col-lg-4">
              <article class="support-team-card" tabindex="0" aria-label="Leïla Oueslati, Customer success, leila.oueslati@foovia.com, +216 22 114 455">
                <div class="support-team-card__surface">
                  <figure class="support-team-card__figure">
                    <img class="support-team-card__photo" src="assets/aziz.png" width="140" height="140" alt="agent 3">
                  </figure>
                  <div class="support-team-card__drawer">
                    <h3 class="h5 mb-1 text-body">Aziz Amri</h3>
                    <p class="small text-muted mb-2">Support Agent</p>
                    <p class="small mb-1"><a href="mailto:MohamedAziz.Amri@esprit.tn">MohamedAziz.Amri@esprit.tn</a></p>
                    <p class="small mb-0 text-body">+216 65328769</p>
                  </div>
                </div>
              </article>
            </div>
          </div>
        </section>



    

    
          
    


    
   


    
    <footer class="py-5">
      <div class="container-lg">
        <div class="row">

          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="footer-menu">
              <img src="assets/Plan de travail 1 no bg (3) (1).png" width="120" height="120" alt="logo">
              FOOVIA
              <div class="social-links mt-3">
                <ul class="d-flex list-unstyled gap-2">
                  <li>
                    <a href="#" class="btn btn-outline-light">
                      <svg width="16" height="16"><use xlink:href="#facebook"></use></svg>
                    </a>
                  </li>
                  <li>
                    <a href="#" class="btn btn-outline-light">
                      <svg width="16" height="16"><use xlink:href="#twitter"></use></svg>
                    </a>
                  </li>
                  <li>
                    <a href="#" class="btn btn-outline-light">
                      <svg width="16" height="16"><use xlink:href="#youtube"></use></svg>
                    </a>
                  </li>
                  <li>
                    <a href="#" class="btn btn-outline-light">
                      <svg width="16" height="16"><use xlink:href="#instagram"></use></svg>
                    </a>
                  </li>
                  <li>
                    <a href="#" class="btn btn-outline-light">
                      <svg width="16" height="16"><use xlink:href="#amazon"></use></svg>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          
          
        </div>
      </div>
    </footer>
    <div id="footer-bottom">
      <div class="container-lg">
        <div class="row">
          <div class="col-md-6 copyright">
            <p>© 2026 FOOVIA .  All rights reserved.</p>
          </div>
        </div>
      </div>
    </div>
    <script src="js/jquery-1.11.0.min.js"></script>
    <script src="js/anime.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <script src="js/plugins.js"></script>
    <script src="js/script.js"></script>
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
    </script>
    <script>
        $(document).ready(function() {
            $('#reclamation-search').on('keyup', function() {
                var query = $(this).val().toLowerCase();
                $('#reclamation-table tbody tr').each(function() {
                    var rowText = $(this).text().toLowerCase();
                    $(this).toggle(rowText.indexOf(query) !== -1);
                });
            });

            $('#refresh-claims-btn').on('click', function() {
                location.reload();
            });
        });
    </script>
    <script>
      (function () {
        if (typeof anime === 'undefined') return;
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        var ioOpts = { threshold: 0.05, rootMargin: '0px 0px 0px 0px' };

        function observeOnce(el, onVisible) {
          if (!el) return;
          var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
              if (!entry.isIntersecting) return;
              obs.disconnect();
              requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                  onVisible(entry.target);
                });
              });
            });
          }, ioOpts);
          obs.observe(el);
        }

        function heroLines(hero) {
          return Array.prototype.slice.call(hero.children || []);
        }

        function prepAnimState(targets, opts) {
          var list = targets && targets.length !== undefined && typeof targets !== 'string'
            ? Array.prototype.slice.call(targets)
            : (targets ? [targets] : []);
          opts = opts || {};
          list.forEach(function (el) {
            if (!el || !el.style) return;
            if (opts.opacity != null) el.style.opacity = String(opts.opacity);
            var parts = [];
            if (opts.translateX != null) parts.push('translateX(' + opts.translateX + 'px)');
            if (opts.translateY != null) parts.push('translateY(' + opts.translateY + 'px)');
            el.style.transform = parts.length ? parts.join(' ') : '';
          });
        }

        function staggerDelay(ms, opts) {
          opts = opts || {};
          if (typeof anime !== 'undefined' && typeof anime.stagger === 'function') {
            return anime.stagger(ms, opts);
          }
          var start = opts.start || 0;
          return function (_el, i) {
            return start + i * (ms || 80);
          };
        }

        observeOnce(document.querySelector('.support-hero-anim'), function (hero) {
          var lines = heroLines(hero);
          if (!lines.length) return;
          prepAnimState(lines, { opacity: 0, translateY: 36 });
          anime({
            targets: lines,
            translateY: [36, 0],
            opacity: [0, 1],
            duration: 720,
            delay: staggerDelay(120, { start: 0 }),
            easing: 'easeOutCubic'
          });
        });

        observeOnce(document.querySelector('#support-claims-section'), function (section) {
          var title = section.querySelector('.support-claims-title');
          var search = section.querySelector('#reclamation-search');
          var ths = section.querySelectorAll('#reclamation-table thead th');
          var cells = section.querySelectorAll('#reclamation-table tbody td');
          var cursor = 0;

          function bump(ms) {
            cursor += ms;
          }

          if (title) {
            prepAnimState(title, { opacity: 0, translateY: 22 });
            anime({
              targets: title,
              translateY: [22, 0],
              opacity: [0, 1],
              duration: 580,
              delay: cursor,
              easing: 'easeOutQuad'
            });
            bump(90);
          }
          if (search) {
            prepAnimState(search, { opacity: 0, translateY: 18 });
            anime({
              targets: search,
              translateY: [18, 0],
              opacity: [0, 1],
              duration: 560,
              delay: cursor,
              easing: 'easeOutQuad'
            });
            bump(100);
          }
          if (ths.length) {
            prepAnimState(ths, { opacity: 0, translateX: -28, translateY: 8 });
            anime({
              targets: ths,
              translateX: [-28, 0],
              translateY: [8, 0],
              opacity: [0, 1],
              duration: 620,
              delay: staggerDelay(85, { start: cursor }),
              easing: 'easeOutCubic'
            });
            bump(85 * ths.length + 140);
          }
          if (cells.length) {
            prepAnimState(cells, { opacity: 0, translateY: 20 });
            anime({
              targets: cells,
              translateY: [20, 0],
              opacity: [0, 1],
              duration: 640,
              delay: staggerDelay(45, { start: cursor }),
              easing: 'easeOutCubic'
            });
          }
        });
      })();
    </script>

    <!-- Chatbot UI -->
    <style>
      /* ========================================
         CHATBOT WIDGET STYLES
         ======================================== */
      
      /* Chat button - floating circular button */
      .chat-toggle-btn {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4BAE52 0%, #2E4A28 100%);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(46, 74, 40, 0.4);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        z-index: 9999;
      }

      .chat-toggle-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 28px rgba(46, 74, 40, 0.5);
      }

      .chat-toggle-btn svg {
        width: 28px;
        height: 28px;
        fill: white;
        transition: transform 0.3s ease;
      }

      .chat-toggle-btn.chat-open svg {
        transform: rotate(90deg);
      }

      /* Chat window container */
      .chat-window {
        position: fixed;
        bottom: 100px;
        right: 24px;
        width: 380px;
        max-width: calc(100vw - 48px);
        height: 520px;
        max-height: calc(100vh - 120px);
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 40px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        z-index: 9998;
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px) scale(0.95);
        transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s ease;
      }

      .chat-window.chat-active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
      }

      /* Chat header */
      .chat-header {
        background: linear-gradient(135deg, #4BAE52 0%, #2E4A28 100%);
        color: white;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
      }

      .chat-header-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .chat-header-icon svg {
        width: 22px;
        height: 22px;
        fill: white;
      }

      .chat-header-info {
        flex: 1;
      }

      .chat-header-title {
        font-family: 'Nunito', sans-serif;
        font-size: 16px;
        font-weight: 700;
        margin: 0;
      }

      .chat-header-status {
        font-size: 12px;
        opacity: 0.85;
        display: flex;
        align-items: center;
        gap: 6px;
      }

      .status-dot {
        width: 8px;
        height: 8px;
        background: #4ade80;
        border-radius: 50%;
        animation: pulse 2s infinite;
      }

      @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
      }

      .chat-close-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease;
      }

      .chat-close-btn:hover {
        background: rgba(255, 255, 255, 0.3);
      }

      .chat-close-btn svg {
        width: 18px;
        height: 18px;
        fill: white;
      }

      /* Messages area */
      .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        background: #f8faf9;
      }

      .chat-messages::-webkit-scrollbar {
        width: 6px;
      }

      .chat-messages::-webkit-scrollbar-track {
        background: transparent;
      }

      .chat-messages::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
      }

      /* Message bubbles */
      .message {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        max-width: 85%;
        animation: messageIn 0.3s ease;
      }

      @keyframes messageIn {
        from {
          opacity: 0;
          transform: translateY(10px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      .message.user-message {
        align-self: flex-end;
        flex-direction: row-reverse;
      }

      .message-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
      }

      .bot-avatar {
        background: linear-gradient(135deg, #4BAE52 0%, #2E4A28 100%);
      }

      .user-avatar {
        background: #F5C842;
      }

      .message-avatar svg {
        width: 18px;
        height: 18px;
        fill: white;
      }

      .message-bubble {
        padding: 12px 16px;
        border-radius: 18px;
        font-size: 14px;
        line-height: 1.5;
        word-wrap: break-word;
      }

      .bot-message .message-bubble {
        background: #e8ebe9;
        color: #1a1a1a;
        border-bottom-left-radius: 4px;
      }

      .bot-message .message-bubble a.chat-claim-link {
        color: #2d8a35;
        font-weight: 600;
        text-decoration: underline;
      }

      .bot-message .message-bubble a.chat-claim-link:hover {
        color: #256f2c;
      }

      .message-claim-nudge .message-bubble {
        border: 1px solid #c5dcc7;
        background: #f0faf1;
      }

      .user-message .message-bubble {
        background: linear-gradient(135deg, #4BAE52 0%, #3d9a45 100%);
        color: white;
        border-bottom-right-radius: 4px;
      }

      .message-time {
        font-size: 10px;
        color: #94a3b8;
        margin-top: 4px;
      }

      .user-message .message-time {
        text-align: right;
      }

      /* Typing indicator */
      .typing-indicator {
        display: none;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: #e8ebe9;
        border-radius: 18px;
        border-bottom-left-radius: 4px;
        width: fit-content;
      }

      .typing-indicator.active {
        display: flex;
      }

      .typing-dots {
        display: flex;
        gap: 4px;
      }

      .typing-dot {
        width: 8px;
        height: 8px;
        background: #64748b;
        border-radius: 50%;
        animation: typingBounce 1.4s infinite ease-in-out;
      }

      .typing-dot:nth-child(1) { animation-delay: 0s; }
      .typing-dot:nth-child(2) { animation-delay: 0.2s; }
      .typing-dot:nth-child(3) { animation-delay: 0.4s; }

      @keyframes typingBounce {
        0%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-6px); }
      }

      .typing-text {
        font-size: 12px;
        color: #64748b;
      }

      /* Input area */
      .chat-input-area {
        padding: 16px;
        background: white;
        border-top: 1px solid #e8ebe9;
        display: flex;
        align-items: center;
        gap: 12px;
      }

      .chat-input {
        flex: 1;
        padding: 12px 16px;
        border: 2px solid #e8ebe9;
        border-radius: 24px;
        font-size: 14px;
        font-family: 'Open Sans', sans-serif;
        outline: none;
        transition: border-color 0.2s ease;
      }

      .chat-input:focus {
        border-color: #4BAE52;
      }

      .chat-input::placeholder {
        color: #94a3b8;
      }

      .send-btn {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4BAE52 0%, #2E4A28 100%);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 2px 8px rgba(46, 74, 40, 0.3);
      }

      .send-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(46, 74, 40, 0.4);
      }
      /* Microphone button (premium only) */
      .mic-btn {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 2px 8px rgba(217, 119, 6, 0.3);
      }

      .mic-btn.active {
        box-shadow: 0 6px 20px rgba(217, 119, 6, 0.45);
        transform: scale(1.08);
      }


      .send-btn:active {
        transform: scale(0.95);
      }

      .send-btn svg {
        width: 20px;
        height: 20px;
        fill: white;
        margin-left: 2px;
      }

      /* Welcome message */
      .welcome-message {
        text-align: center;
        padding: 20px;
      }

      .welcome-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #4BAE52 0%, #2E4A28 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
      }

      .welcome-icon svg {
        width: 32px;
        height: 32px;
        fill: white;
      }

      .welcome-title {
        font-family: 'Nunito', sans-serif;
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 8px;
      }

      .welcome-text {
        font-size: 14px;
        color: #64748b;
        line-height: 1.5;
      }

      /* Mobile responsiveness */
      @media (max-width: 480px) {
        .chat-toggle-btn {
          bottom: 16px;
          right: 16px;
          width: 54px;
          height: 54px;
        }

        .chat-window {
          bottom: 88px;
          right: 16px;
          left: 16px;
          width: auto;
          height: calc(100vh - 120px);
        }

        .message {
          max-width: 90%;
        }
      }
    </style>

    <!-- Chat Button -->
    <button class="chat-toggle-btn" id="chatToggleBtn" aria-label="Open chat">
      <!-- Chat icon (messenger) -->
      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
      </svg>
    </button>

    <!-- Chat Window -->
    <div class="chat-window" id="chatWindow">
      <!-- Header -->
      <div class="chat-header">
        <div class="chat-header-icon">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" >
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
          </svg>
        </div>
        <div class="chat-header-info">
          <h3 class="chat-header-title">Wilson</h3>
          <div class="chat-header-status">
            <span class="status-dot"></span>
            <span>Online</span>
          </div>
        </div>
        <button class="chat-close-btn" id="chatCloseBtn" aria-label="Close chat">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
          </svg>
        </button>
      </div>

      <!-- Messages Area -->
      <div class="chat-messages" id="chatMessages">
        <!-- Welcome message -->
        <div class="welcome-message">
          <div class="welcome-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
            </svg>
          </div>
          <h4 class="welcome-title">Welcome! 👋</h4>
          <p class="welcome-text">How can I help you today? Feel free to ask any questions about our services.</p>
        </div>

        <!-- Typing indicator -->
        <div class="typing-indicator" id="typingIndicator">
          <div class="typing-dots">
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
          </div>
          <span class="typing-text">Bot is typing...</span>
        </div>
      </div>

      <!-- Input Area -->
      <div class="chat-input-area">
        <input type="text" class="chat-input" id="chatInput" placeholder="Type your message..." autocomplete="off">
        <?php if ($user_subscription === 'premium' || $user_subscription === 'elite'): ?>
          <button class="mic-btn" id="micBtn" aria-pressed="false" aria-label="Start voice input" title="Voice input (premium)">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 14a3 3 0 0 0 3-3V6a3 3 0 0 0-6 0v5a3 3 0 0 0 3 3zm5-3a5 5 0 0 1-10 0H5a7 7 0 0 0 14 0h-2zM11 19.93V22h2v-2.07A8.001 8.001 0 0 0 20 12h-2a6 6 0 0 1-12 0H4a8 8 0 0 0 7 7.93z"/>
            </svg>
          </button>
        <?php endif; ?>
        <button class="send-btn" id="sendBtn" aria-label="Send message">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
          </svg>
        </button>
      </div>
    </div>

    <!--
      Hybrid Wilson + claim flow:
      1) successfulBotReplies increments after each successful Wilson reply (chatbot-handler.php).
      2) Right after the configured plan reply limit is reached: two static bot bubbles (link, then yes/no) and wilsonDisabled=true.
      3) awaiting_yesno / awaiting_description: no general Wilson. Claim: chatbot-claim-handler.php (AI classifies type only).
      4) If wilsonDisabled and claimAssistState is null (e.g. after "no"), only static hints — no Wilson API.
    -->
    <!-- Chatbot JavaScript -->
    <script>
      (function() {
        // DOM Elements
        const chatToggleBtn = document.getElementById('chatToggleBtn');
        const chatWindow = document.getElementById('chatWindow');
        const chatCloseBtn = document.getElementById('chatCloseBtn');
        const chatMessages = document.getElementById('chatMessages');
        const chatInput = document.getElementById('chatInput');
        const sendBtn = document.getElementById('sendBtn');
        const typingIndicator = document.getElementById('typingIndicator');
        const micBtn = document.getElementById('micBtn');

        // SpeechRecognition (premium-only mic) setup
        let recognition = null;
        let recognizing = false;
        if (micBtn) {
          const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
          if (SpeechRecognition) {
            try {
              recognition = new SpeechRecognition();
              recognition.lang = navigator.language || 'en-US';
              recognition.interimResults = false;
              recognition.maxAlternatives = 1;

              recognition.onstart = function() {
                recognizing = true;
                micBtn.classList.add('active');
                micBtn.setAttribute('aria-pressed', 'true');
                micBtn.title = 'Listening...';
              };

              recognition.onend = function() {
                recognizing = false;
                micBtn.classList.remove('active');
                micBtn.setAttribute('aria-pressed', 'false');
                micBtn.title = 'Voice input (premium)';
              };

              recognition.onerror = function(evt) {
                console.error('SpeechRecognition error', evt);
                if (evt && evt.error) {
                  alert('Voice recognition error: ' + evt.error);
                }
              };

              recognition.onresult = function(event) {
                const transcript = Array.from(event.results).map(r => r[0].transcript).join(' ');
                if (chatInput.value && chatInput.value.trim()) {
                  chatInput.value = chatInput.value.trim() + ' ' + transcript;
                } else {
                  chatInput.value = transcript;
                }
                chatInput.focus();
              };

              micBtn.addEventListener('click', function() {
                if (recognizing) {
                  try { recognition.stop(); } catch (e) { console.error(e); }
                } else {
                  try { recognition.start(); } catch (e) { console.error(e); }
                }
              });
            } catch (e) {
              console.error('SpeechRecognition setup failed', e);
            }
          } else {
            // Browser doesn't support SpeechRecognition
            micBtn.addEventListener('click', function() {
              alert('Voice recognition is not available in this browser. Try Chrome or Edge.');
            });
          }
        }

        const CHATBOT_ENDPOINT = '../../../Controller/SUPPORT_MODULE/chatbot-handler.php';
        const CHATBOT_CLAIM_ENDPOINT = '../../../Controller/SUPPORT_MODULE/chatbot-claim-handler.php';
        const CLAIM_PAGE_URL = 'add_rec_page.php';
        const CAN_CREATE_CLAIMS = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        const WILSON_REPLIES_NORMAL_PLAN = 2;
        const WILSON_REPLIES_PREMIUM_PLAN = 4;
        const CHATBOT_SUBSCRIPTION_PLAN = <?php echo json_encode(($user_subscription === 'premium' || $user_subscription === 'elite') ? 'premium' : 'normal'); ?>;
        /** After this many successful Wilson replies, show claim UI and disable general Wilson. */
        const WILSON_REPLIES_BEFORE_CLAIM_FLOW = CHATBOT_SUBSCRIPTION_PLAN === 'premium'
          ? WILSON_REPLIES_PREMIUM_PLAN
          : WILSON_REPLIES_NORMAL_PLAN;
        /** Successful Wilson replies in this page load (assignment: no cross-tab persistence). */
        let successfulBotReplies = 0;
        /** True once the claim link + yes/no UI has been shown. */
        let claimLinkNudgeShown = false;
        /** After escalation, general Wilson chat is turned off for this page load. */
        let wilsonDisabled = false;
        /** null | 'awaiting_yesno' | 'awaiting_description' */
        let claimAssistState = null;
        let chatbotSending = false;

        async function fetchBotReply(userMessage) {
          const res = await fetch(CHATBOT_ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: userMessage }),
            credentials: 'same-origin'
          });
          const raw = await res.text();
          let data;
          try {
            data = JSON.parse(raw);
          } catch (e) {
            throw new Error('Unexpected response from the server. Please try again.');
          }
          if (data.debug) {
            console.error('[Chatbot debug]', data.debug);
          }
          if (data.error) {
            throw new Error(data.error);
          }
          if (typeof data.reply !== 'string' || !data.reply.trim()) {
            throw new Error('No reply from assistant.');
          }
          return data.reply.trim();
        }

        function parseYesNo(text) {
          const t = String(text).trim().toLowerCase().replace(/\.*$/, '');
          if (t === 'yes' || t === 'y' || t === 'oui') return 'yes';
          if (t === 'no' || t === 'n' || t === 'non') return 'no';
          return 'unknown';
        }

        async function fetchClaimCreation(description) {
          const res = await fetch(CHATBOT_CLAIM_ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ description: description }),
            credentials: 'same-origin'
          });
          const raw = await res.text();
          let data;
          try {
            data = JSON.parse(raw);
          } catch (e) {
            throw new Error('Unexpected response while creating the claim.');
          }
          if (data.debug) {
            console.error('[Claim debug]', data.debug);
          }
          if (data.error) {
            throw new Error(data.error);
          }
          if (!data.success) {
            throw new Error('The claim could not be created.');
          }
          return data;
        }

        // Get current time formatted
        function getCurrentTime() {
          const now = new Date();
          return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        // Create message element
        function createMessageElement(message, isUser) {
          const messageDiv = document.createElement('div');
          messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;

          const avatarSvg = isUser 
            ? '<path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>'
            : '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>';

          messageDiv.innerHTML = `
            <div class="message-avatar ${isUser ? 'user-avatar' : 'bot-avatar'}">
              <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                ${avatarSvg}
              </svg>
            </div>
            <div class="message-content">
              <div class="message-bubble">${escapeHtml(message)}</div>
              <div class="message-time">${getCurrentTime()}</div>
            </div>
          `;

          return messageDiv;
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
          const div = document.createElement('div');
          div.textContent = text;
          return div.innerHTML;
        }

        // Add message to chat
        function addMessage(message, isUser) {
          // Remove welcome message if exists
          const welcomeMsg = chatMessages.querySelector('.welcome-message');
          if (welcomeMsg) {
            welcomeMsg.remove();
          }

          // Hide typing indicator
          typingIndicator.classList.remove('active');

          // Create and add message
          const messageElement = createMessageElement(message, isUser);
          chatMessages.insertBefore(messageElement, typingIndicator);

          // Scroll to bottom
          chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        /**
         * Insert one trusted bot row (static HTML bubble only).
         */
        function insertBotHtmlBubble(extraClass, bubbleInnerHtml) {
          const welcomeMsg = chatMessages.querySelector('.welcome-message');
          if (welcomeMsg) {
            welcomeMsg.remove();
          }
          typingIndicator.classList.remove('active');

          const messageDiv = document.createElement('div');
          messageDiv.className = 'message bot-message message-claim-nudge' + (extraClass ? ' ' + extraClass : '');
          const avatarSvg = '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>';
          messageDiv.innerHTML = `
            <div class="message-avatar bot-avatar">
              <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">${avatarSvg}</svg>
            </div>
            <div class="message-content">
              <div class="message-bubble">${bubbleInnerHtml}</div>
              <div class="message-time">${getCurrentTime()}</div>
            </div>
          `;
          chatMessages.insertBefore(messageDiv, typingIndicator);
          chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        /**
         * Two bot rows: (1) claim link, (2) offer to create claim in chat. Second row deferred one tick for reliable DOM layout.
         */
        function addPostWilsonClaimFlow() {
          var linkPart = '<p style="margin:0">You have had several exchanges with Wilson. For a formal follow-up, please <a href="' + CLAIM_PAGE_URL + '" class="chat-claim-link">create a claim</a> (opens the claim form).</p>';
          var askPart = '<p style="margin:0">Would you like Wilson to <strong>create a claim for you here</strong> in this chat? Please reply with <strong>yes</strong> or <strong>no</strong>.</p>';
          insertBotHtmlBubble('', linkPart);
          window.setTimeout(function () {
            insertBotHtmlBubble('message-claim-offer', askPart);
            chatMessages.scrollTop = chatMessages.scrollHeight;
          }, 0);
        }

        // Show typing indicator
        function showTyping() {
          typingIndicator.classList.add('active');
          chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Hide typing indicator
        function hideTyping() {
          typingIndicator.classList.remove('active');
        }

        async function handleSendMessage() {
          const message = chatInput.value.trim();
          if (!message || chatbotSending) return;

          if (wilsonDisabled && claimAssistState === null) {
            chatbotSending = true;
            sendBtn.disabled = true;
            addMessage(message, true);
            chatInput.value = '';
            addMessage('Wilson is not available for further chat in this session. Please use the claim link above, open the claim form, or contact the Foovia team through the website.', false);
            chatbotSending = false;
            sendBtn.disabled = false;
            return;
          }

          if (claimAssistState === 'awaiting_yesno') {
            chatbotSending = true;
            sendBtn.disabled = true;
            addMessage(message, true);
            chatInput.value = '';
            const yn = parseYesNo(message);
            if (yn === 'yes') {
              addMessage('Please describe your problem in a short message (1 or 2 sentences). We will use it to create your claim.', false);
              claimAssistState = 'awaiting_description';
            } else if (yn === 'no') {
              addMessage('Thank you for letting us know. If the issue persists, please contact the Foovia team through the website. Have a great day!', false);
              claimAssistState = null;
            } else {
              addMessage('Please reply with yes if you want Wilson to create a claim for you here in the chat, or no if you prefer not to.', false);
            }
            chatbotSending = false;
            sendBtn.disabled = false;
            return;
          }

          if (claimAssistState === 'awaiting_description') {
            chatbotSending = true;
            sendBtn.disabled = true;
            addMessage(message, true);
            chatInput.value = '';
            setTimeout(function () {
              showTyping();
            }, 150);
            try {
              const data = await fetchClaimCreation(message);
              hideTyping();
              const typePart = data.type ? ' Type: ' + data.type + '.' : '';
              addMessage((data.message || 'Your claim was created.') + typePart + ' Refreshing the page…', false);
              claimAssistState = null;
              setTimeout(function () {
                window.location.reload();
              }, 900);
            } catch (err) {
              hideTyping();
              addMessage(err.message || 'Could not create the claim. You can still use the claim form on the site.', false);
            } finally {
              chatbotSending = false;
              sendBtn.disabled = false;
            }
            return;
          }

          chatbotSending = true;
          sendBtn.disabled = true;

          addMessage(message, true);
          chatInput.value = '';

          if (wilsonDisabled) {
            hideTyping();
            addMessage('Wilson is not available for general chat anymore. Please use the claim options in the chat above or the claim form.', false);
            chatbotSending = false;
            sendBtn.disabled = false;
            return;
          }

          setTimeout(function () {
            showTyping();
          }, 200);

          try {
            const reply = await fetchBotReply(message);
            hideTyping();
            addMessage(reply, false);
            successfulBotReplies += 1;

            if (successfulBotReplies >= WILSON_REPLIES_BEFORE_CLAIM_FLOW && !claimLinkNudgeShown) {
              if (CAN_CREATE_CLAIMS) {
                claimLinkNudgeShown = true;
                wilsonDisabled = true;
                addPostWilsonClaimFlow();
                claimAssistState = 'awaiting_yesno';
              } else {
                claimLinkNudgeShown = true;
                addMessage('Sign in to create a claim, then come back here to submit it.', false);
              }
            }
          } catch (err) {
            hideTyping();
            addMessage(err.message || 'Something went wrong. Please try again.', false);
          } finally {
            chatbotSending = false;
            sendBtn.disabled = false;
          }
        }

        // Toggle chat window
        function toggleChat() {
          const isActive = chatWindow.classList.contains('chat-active');
          
          if (isActive) {
            chatWindow.classList.remove('chat-active');
            chatToggleBtn.classList.remove('chat-open');
          } else {
            chatWindow.classList.add('chat-active');
            chatToggleBtn.classList.add('chat-open');
            chatInput.focus();
          }
        }

        // Event Listeners
        chatToggleBtn.addEventListener('click', toggleChat);
        chatCloseBtn.addEventListener('click', toggleChat);
        sendBtn.addEventListener('click', handleSendMessage);

        // Send message on Enter key
        chatInput.addEventListener('keypress', function(e) {
          if (e.key === 'Enter') {
            handleSendMessage();
          }
        });

        // Close chat when clicking outside (optional)
        document.addEventListener('click', function(e) {
          if (!chatWindow.contains(e.target) && 
              !chatToggleBtn.contains(e.target) && 
              chatWindow.classList.contains('chat-active')) {
            toggleChat();
          }
        });
      })();
    </script>
  </body>
</html>
