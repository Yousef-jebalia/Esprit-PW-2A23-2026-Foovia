<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

const CLAIM_TYPES = ['Authentication', 'Subscription', 'Other','Bugs','Delivery','Payement'];

$configPath = __DIR__ . '/../../Model/SUPPORT_MODULE/chat-bot-config.php';
if (!is_readable($configPath)) {
    echo json_encode(['error' => 'Chatbot configuration is missing.']);
    exit;
}
require_once $configPath;

if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === '') {
    echo json_encode(['error' => 'GEMINI_API_KEY is not set.']);
    exit;
}

$geminiModel = defined('GEMINI_MODEL') && GEMINI_MODEL !== '' ? GEMINI_MODEL : 'gemini-2.5-flash';
$chatbotDebug = defined('CHATBOT_DEBUG') && CHATBOT_DEBUG;

$inputData = json_decode(file_get_contents('php://input'), true);
$description = isset($inputData['description']) ? trim((string) $inputData['description']) : '';

if ($description === '') {
    echo json_encode(['error' => 'Please provide a short description of the problem.']);
    exit;
}
if (strlen($description) < 5) {
    echo json_encode(['error' => 'Description is too short. Please add a bit more detail.']);
    exit;
}
if (strlen($description) > 4000) {
    echo json_encode(['error' => 'Description is too long. Please shorten it.']);
    exit;
}


function claim_classify_description(string $description, string $geminiModel): string
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);

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

    // Strip optional ```json fences
    if (preg_match('/\{[\s\S]*"type"[\s\S]*\}/', $text, $m)) {
        $text = $m[0];
    }

    $decoded = json_decode($text, true);
    if (!is_array($decoded) || !isset($decoded['type'])) {
        return '';
    }
    $raw = trim((string) $decoded['type']);
    foreach (CLAIM_TYPES as $allowed) {
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

function claim_normalize_fallback(string $fromGemini): string
{
    if ($fromGemini !== '' && in_array($fromGemini, CLAIM_TYPES, true)) {
        return $fromGemini;
    }
    return 'Other';
}

$resolvedType = claim_classify_description($description, $geminiModel);
$resolvedType = claim_normalize_fallback($resolvedType);

if (empty($_SESSION['user_id']) || empty($_SESSION['user_name'])) {
    $out = ['error' => 'Please sign in before creating a claim.'];
    if ($chatbotDebug) {
        $out['debug'] = [
            'session_user_id' => $_SESSION['user_id'] ?? null,
            'session_user_name' => $_SESSION['user_name'] ?? null,
            'session_user_email' => $_SESSION['user_email'] ?? null,
            'session_id_user' => $_SESSION['id_user'] ?? null,
            'session_id' => $_SESSION['id'] ?? null,
        ];
    }
    echo json_encode($out);
    exit;
}

require_once __DIR__ . '/Reclamtion_Controller.php';

try {
    $controller = new Controller_reclamation();
    $reclamation = new Reclamations(
        '',
        (int) $_SESSION['user_id'],
        $description,
        'Pending',
        $resolvedType,
        '',
        ''
    );
    $controller->add_reclamation($reclamation);
} catch (Exception $e) {
    $out = ['error' => 'Could not save the claim. Please try the claim form on the site, or try again later.'];
    if ($chatbotDebug) {
        $out['debug'] = ['exception' => $e->getMessage()];
    }
    echo json_encode($out);
    exit;
}

$out = [
    'success' => true,
    'type' => $resolvedType,
    'message' => 'Your claim was created successfully. A human agent will resolve it as soon as possible. Thank you for your patience.',
];
if ($chatbotDebug) {
    $out['debug'] = ['classified_type' => $resolvedType];
}
echo json_encode($out);
