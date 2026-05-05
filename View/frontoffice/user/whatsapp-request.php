<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $phone = trim($data['phone'] ?? '');

    if (empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Phone number is required.']);
        exit;
    }

    // Generate a random 4-digit code
    $code = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    
    // Store in session for verification
    $_SESSION['whatsapp_login_phone'] = $phone;
    $_SESSION['whatsapp_login_code'] = $code;
    $_SESSION['whatsapp_login_expires'] = time() + 300; // 5 minutes expiry
    
    // Force write the session to disk immediately to prevent locking or loss
    session_write_close();

    // --- REAL WHATSAPP SENDING VIA TWILIO ---
    $env = parse_ini_file(__DIR__ . '/../../.env');
    $sid = $env['TWILIO_ACCOUNT_SID'] ?? '';
    $token = $env['TWILIO_AUTH_TOKEN'] ?? '';
    $twilio_number = $env['TWILIO_WHATSAPP_NUMBER'] ?? ''; // Format: "whatsapp:+14155238886"
    
    if (empty($sid) || empty($token)) {
        echo json_encode(['success' => false, 'message' => 'Twilio API keys not configured in .env']);
        exit;
    }

    $to_number = "whatsapp:" . $phone;
    $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
    
    $post_data = [
        'From' => $twilio_number,
        'To' => $to_number,
        'Body' => 'Your FOOVIA secure login code is: ' . $code
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 201) {
        echo json_encode([
            'success' => true, 
            'message' => 'Code sent to WhatsApp.'
        ]);
    } else {
        $error_data = json_decode($response, true);
        echo json_encode([
            'success' => false, 
            'message' => 'Twilio Error: ' . ($error_data['message'] ?? 'Unknown error')
        ]);
    }
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid request']);
