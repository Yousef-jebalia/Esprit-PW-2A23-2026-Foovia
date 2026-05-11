<?php
require_once __DIR__ . '/google-config.php';
if (session_status() === PHP_SESSION_NONE) {
session_start();
}
include_once(__DIR__ . '/../../Model/config.php');
include_once(__DIR__ . '/../../Controller/Controller_user.php');
//zedha chichi famechi mayekhdem el
if (!isset($client) || !is_object($client)) {
        $_SESSION['error_message'] = 'Google sign-in is not configured correctly.';
        header('Location: foovia-signin.php');
        exit;
}

if (isset($_GET['code'])) {
  $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
  if(!isset($token['error'])) {
    $client->setAccessToken($token['access_token']);
    // Fetch user profile using the access token directly
    $access_token = $token['access_token'];
    $opts = [
        "http" => [
            "header" => "Authorization: Bearer " . $access_token
        ]
    ];
    $context = stream_context_create($opts);
    $user_info_json = file_get_contents('https://www.googleapis.com/oauth2/v2/userinfo', false, $context);
    $google_account_info = json_decode($user_info_json);

    $email =  $google_account_info->email;
    $name =  $google_account_info->name;
    $google_id = $google_account_info->id;

    try {
        $db = config::getConnexion();
        $controller = new Controller_user();
        $sql = "SELECT id_user, name_user, email_user FROM user WHERE LOWER(email_user) = :email";
        $query = $db->prepare($sql);
        $query->execute(['email' => strtolower($email)]);
        $user = $query->fetch();

        if (!$user) {
            // Create user if they don't exist
            $random_password = bin2hex(random_bytes(8));
            $insert = "INSERT INTO user (name_user, email_user, password_user, role_user, subscription_user, account_state_user, duration_user) VALUES (:name, :email, :password, :role, :subscription, :account_state, :duration)";
            $stmt = $db->prepare($insert);
            $stmt->execute([
                'name' => $name,
                'email' => strtolower($email),
                'password' => $random_password,
                'role' => 'user',
                'subscription' => 'normal',
                'account_state' => 'active',
                'duration' => '00:00:00'
            ]);
            $user_id = $db->lastInsertId();

            // Generate a reset token to force them to set a password
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $updateSql = "UPDATE user SET reset_token = :token, reset_token_expires_at = :expires_at WHERE id_user = :id_user";
            $updateQuery = $db->prepare($updateSql);
            $updateQuery->execute(['token' => $token, 'expires_at' => $expires_at, 'id_user' => $user_id]);

            // Redirect to reset password page for their first login
            header("Location: reset-password.php?token=" . $token . "&first_login=1");
            exit;
        } else {
            $controller->increment_user_login_count((int) $user['id_user']);
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_name'] = $user['name_user'];
            $_SESSION['user_email'] = $user['email_user'];
        }
        header("Location: foovia.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'An error occurred during Google sign in.';
        header("Location: foovia-signin.php");
        exit;
    }
  } else {
      $_SESSION['error_message'] = 'Failed to authenticate with Google.';
      header("Location: foovia-signin.php");
      exit;
  }
} else {
    header("Location: foovia-signin.php");
    exit;
}
