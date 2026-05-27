<?php
session_start();
header('Content-Type: application/json');
include_once(__DIR__ . '/../../Model/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $code = trim($data['code'] ?? '');

    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Code is required.']);
        exit;
    }

    if (!isset($_SESSION['whatsapp_login_code']) || !isset($_SESSION['whatsapp_login_phone'])) {
        echo json_encode(['success' => false, 'message' => 'Session expired. Please request a new code.']);
        exit;
    }

    if (time() > $_SESSION['whatsapp_login_expires']) {
        echo json_encode(['success' => false, 'message' => 'Code has expired. Please request a new one.']);
        unset($_SESSION['whatsapp_login_code']);
        exit;
    }

    if ($code !== $_SESSION['whatsapp_login_code']) {
        echo json_encode(['success' => false, 'message' => 'Invalid code. Please try again.']);
        exit;
    }

    // Code is valid!
    $phone = $_SESSION['whatsapp_login_phone'];

    // Clear the code
    unset($_SESSION['whatsapp_login_code']);
    unset($_SESSION['whatsapp_login_expires']);

    // We will use a dummy email based on phone number for the user table since email_user is likely required and unique
    $dummy_email = preg_replace('/[^0-9]/', '', $phone) . '@whatsapp.foovia.local';

    try {
        $db = config::getConnexion();
        $sql = "SELECT id_user, name_user, email_user FROM user WHERE LOWER(email_user) = :email";
        $query = $db->prepare($sql);
        $query->execute(['email' => $dummy_email]);
        $user = $query->fetch();

        if (!$user) {
            // Create user
            $random_password = bin2hex(random_bytes(8));
            $name = "WhatsApp User";

            $insert = "INSERT INTO user (name_user, email_user, password_user) VALUES (:name, :email, :password)";
            $stmt = $db->prepare($insert);
            $stmt->execute(['name' => $name, 'email' => $dummy_email, 'password' => $random_password]);
            $user_id = $db->lastInsertId();

            // Generate reset token so they set a password
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $updateSql = "UPDATE user SET reset_token = :token, reset_token_expires_at = :expires_at WHERE id_user = :id_user";
            $updateQuery = $db->prepare($updateSql);
            $updateQuery->execute(['token' => $token, 'expires_at' => $expires_at, 'id_user' => $user_id]);

            echo json_encode(['success' => true, 'redirect' => "reset-password.php?token=" . $token . "&first_login=1"]);
            exit;
        } else {
            // Login existing user
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_name'] = $user['name_user'];
            $_SESSION['user_email'] = $user['email_user'];

            echo json_encode(['success' => true, 'redirect' => "foovia.php"]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}
echo json_encode(['success' => false, 'message' => 'Invalid request']);
