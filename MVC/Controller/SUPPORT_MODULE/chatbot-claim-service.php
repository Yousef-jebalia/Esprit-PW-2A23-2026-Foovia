<?php

const CHATBOT_CLAIM_TYPES = ['Authentication', 'Subscription', 'Other', 'Bugs', 'Delivery', 'Payement'];

function chatbot_claim_load_config(): array
{
    $configPath = dirname(__DIR__, 3) . '/support_api';
    if (!is_readable($configPath)) {
        return ['error' => 'Chatbot configuration is missing.'];
    }

    require_once $configPath;

    if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === '') {
        return ['error' => 'GEMINI_API_KEY is not set.'];
    }

    return [
        'gemini_model' => defined('GEMINI_MODEL') && GEMINI_MODEL !== '' ? GEMINI_MODEL : 'gemini-2.5-flash',
        'debug' => defined('CHATBOT_DEBUG') && CHATBOT_DEBUG,
    ];
}

function chatbot_claim_parse_payload(?string $rawBody): array
{
    $inputData = json_decode($rawBody ?? '', true);
    if (!is_array($inputData)) {
        return ['error' => 'Invalid request payload.'];
    }

    $subject = isset($inputData['subject']) ? trim((string) $inputData['subject']) : '';
    $description = isset($inputData['description']) ? trim((string) $inputData['description']) : '';

    if ($subject === '') {
        return ['error' => 'Please provide a subject for your claim.'];
    }
    if (strlen($subject) < 3) {
        return ['error' => 'Subject is too short. Please add a bit more detail.'];
    }
    if (strlen($subject) > 200) {
        return ['error' => 'Subject is too long. Please shorten it.'];
    }

    if ($description === '') {
        return ['error' => 'Please provide a short description of the problem.'];
    }
    if (strlen($description) < 5) {
        return ['error' => 'Description is too short. Please add a bit more detail.'];
    }
    if (strlen($description) > 4000) {
        return ['error' => 'Description is too long. Please shorten it.'];
    }

    return [
        'subject' => $subject,
        'description' => $description,
    ];
}

function chatbot_claim_classify_description(string $description, string $geminiModel): string
{
    $system = 'You are a strict classifier for Foovia support claims. '
        . 'Given the user problem description, output ONLY a single JSON object with one key "type". '
        . 'The value must be exactly one of these strings: Authentication, Subscription, Other, Bugs, Delivery, Payment. '
        . 'Rules: Authentication = login, password, account access, email verification, session issues. '
        . 'Subscription = billing, payment, plan, renewal, premium access tied to payment. '
        . 'Bugs = software defects, crashes, unexpected behavior. '
        . 'Delivery = shipping, tracking, delivery issues. '
        . 'Payment = payment processing, refunds, billing disputes. '
        . 'Other = anything that does not clearly fit the first five categories. '
        . 'No markdown, no code fences, no explanation — only the JSON object.';

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($geminiModel) . ':generateContent';
    $payload = [
        'system_instruction' => [
            'parts' => [['text' => $system]],
        ],
        'contents' => [
            [
                'role' => 'user',
                'parts' => [['text' => "Description:\n" . $description]],
            ],
        ],
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-goog-api-key: ' . GEMINI_API_KEY,
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($response === false || $curlErr !== '') {
        return '';
    }
    if ($httpCode !== 200) {
        return '';
    }

    $responseData = json_decode($response, true);
    if (!is_array($responseData)) {
        return '';
    }

    $parts = $responseData['candidates'][0]['content']['parts'] ?? null;
    $text = '';
    if (is_array($parts) && isset($parts[0]['text'])) {
        $text = trim((string) $parts[0]['text']);
    }
    if ($text === '') {
        return '';
    }

    if (preg_match('/\{[\s\S]*"type"[\s\S]*\}/', $text, $m)) {
        $text = $m[0];
    }

    $decoded = json_decode($text, true);
    if (!is_array($decoded) || !isset($decoded['type'])) {
        return '';
    }

    $raw = trim((string) $decoded['type']);
    foreach (CHATBOT_CLAIM_TYPES as $allowed) {
        if (strcasecmp($raw, $allowed) === 0) {
            return $allowed;
        }
    }

    $lower = strtolower($raw);
    if (strpos($lower, 'auth') !== false) {
        return 'Authentication';
    }
    if (strpos($lower, 'subscri') !== false || strpos($lower, 'billing') !== false || strpos($lower, 'payment') !== false) {
        return 'Subscription';
    }
    if ($raw !== '') {
        return 'Other';
    }

    return '';
}

function chatbot_claim_normalize_type(string $fromGemini): string
{
    if ($fromGemini !== '' && in_array($fromGemini, CHATBOT_CLAIM_TYPES, true)) {
        return $fromGemini;
    }

    return 'Other';
}

function chatbot_claim_process_request(
    Controller_reclamation $controller,
    int $userId,
    bool $isLoggedIn,
    ?string $rawBody
): array {
    $config = chatbot_claim_load_config();
    if (isset($config['error'])) {
        return ['error' => $config['error']];
    }

    $chatbotDebug = (bool) ($config['debug'] ?? false);
    $payload = chatbot_claim_parse_payload($rawBody);
    if (isset($payload['error'])) {
        return ['error' => $payload['error']];
    }

    if (!$isLoggedIn || $userId <= 0) {
        $out = ['error' => 'Please sign in before creating a claim.'];
        if ($chatbotDebug) {
            $out['debug'] = ['user_id' => $userId > 0 ? $userId : null];
        }
        return $out;
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    @set_time_limit(120);

    $resolvedType = chatbot_claim_classify_description($payload['description'], $config['gemini_model']);
    $resolvedType = chatbot_claim_normalize_type($resolvedType);

    try {
        $reclamation = new Reclamations(
            '',
            $userId,
            $payload['description'],
            'Pending',
            $resolvedType,
            '',
            '',
            $payload['subject']
        );
        $controller->add_reclamation($reclamation);
    } catch (Exception $e) {
        $out = ['error' => 'Could not save the claim. Please try the claim form on the site, or try again later.'];
        if ($chatbotDebug) {
            $out['debug'] = ['exception' => $e->getMessage()];
        }
        return $out;
    }

    $out = [
        'success' => true,
        'type' => $resolvedType,
        'message' => 'Your claim was created successfully. A human agent will resolve it as soon as possible. Thank you for your patience.',
    ];
    if ($chatbotDebug) {
        $out['debug'] = ['classified_type' => $resolvedType];
    }

    return $out;
}

function chatbot_claim_process_session_request(?string $rawBody): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/Reclamtion_Controller.php';

    $userId = (int) ($_SESSION['user_id'] ?? 0);
    $isLoggedIn = $userId > 0 && trim((string) ($_SESSION['user_name'] ?? '')) !== '';
    $controller = new Controller_reclamation();

    return chatbot_claim_process_request($controller, $userId, $isLoggedIn, $rawBody);
}

function chatbot_claim_send_json_response(array $payload): void
{
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($payload);
}
