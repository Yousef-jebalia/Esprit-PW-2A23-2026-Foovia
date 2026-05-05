<?php
require_once __DIR__ . '/facebook-config.php';
include_once(__DIR__ . '/../../../model/config.php');

if (isset($_GET['code'])) {
    // 1. Exchange code for access token
    $token_url = "https://graph.facebook.com/v19.0/oauth/access_token?client_id=" . $fb_app_id . "&redirect_uri=" . urlencode($fb_redirect_uri) . "&client_secret=" . $fb_app_secret . "&code=" . $_GET['code'];
    
    // We use stream context to ignore errors if Facebook rejects the code
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true
        ]
    ]);
    
    $response = file_get_contents($token_url, false, $context);
    $params = json_decode($response, true);
    
    if (isset($params['access_token'])) {
        $access_token = $params['access_token'];
        
        // 2. Get user info
        $graph_url = "https://graph.facebook.com/me?fields=id,name,email&access_token=" . $access_token;
        $user_info = json_decode(file_get_contents($graph_url));
        
        $email = $user_info->email ?? '';
        $name = $user_info->name ?? 'Facebook User';
        $fb_id = $user_info->id ?? '';
        
        // If the Facebook account was created with a phone number instead of an email,
        // or the user declined to share it, we generate a secure fallback email.
        if (empty($email) && !empty($fb_id)) {
            $email = $fb_id . '@facebook.foovia.local';
        }

        if (empty($email)) {
            $_SESSION['error_message'] = 'Facebook authentication failed. No ID or Email received.';
            header("Location: foovia-signin.php");
            exit;
        }

        try {
            $db = config::getConnexion();
            $sql = "SELECT id_user, name_user, email_user FROM user WHERE LOWER(email_user) = :email";
            $query = $db->prepare($sql);
            $query->execute(['email' => strtolower($email)]);
            $user = $query->fetch();
            
            if (!$user) {
                // First login, create user and redirect to password set
                $random_password = bin2hex(random_bytes(8));
                $insert = "INSERT INTO user (name_user, email_user, password_user) VALUES (:name, :email, :password)";
                $stmt = $db->prepare($insert);
                $stmt->execute(['name' => $name, 'email' => strtolower($email), 'password' => $random_password]);
                $user_id = $db->lastInsertId();
                
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $updateSql = "UPDATE user SET reset_token = :token, reset_token_expires_at = :expires_at WHERE id_user = :id_user";
                $updateQuery = $db->prepare($updateSql);
                $updateQuery->execute(['token' => $token, 'expires_at' => $expires_at, 'id_user' => $user_id]);

                header("Location: reset-password.php?token=" . $token . "&first_login=1");
                exit;
            } else {
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['user_name'] = $user['name_user'];
                $_SESSION['user_email'] = $user['email_user'];
                header("Location: ../foovia.php");
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'An error occurred during Facebook sign in.';
            header("Location: foovia-signin.php");
            exit;
        }
    } else {
        $_SESSION['error_message'] = 'Failed to authenticate with Facebook. Invalid app credentials?';
        header("Location: foovia-signin.php");
        exit;
    }
} else {
    header("Location: foovia-signin.php");
    exit;
}
