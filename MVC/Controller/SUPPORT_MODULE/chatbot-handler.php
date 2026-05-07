<?php
// Keep output JSON-safe (warnings would break fetch().json() in the browser)
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

// 1. Load the secure API key (Model layer)
$configPath = __DIR__ . '/../../Model/SUPPORT_MODULE/chat-bot-config.php';
if (!is_readable($configPath)) {
    echo json_encode([
        'error' => 'Chatbot configuration file is missing or not readable.',
        'debug' => ['config_path' => $configPath],
    ]);
    exit;
}
require_once $configPath;

if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === '') {
    echo json_encode(['error' => 'GEMINI_API_KEY is not set in chat-bot-config.php.']);
    exit;
}

$geminiModel = defined('GEMINI_MODEL') && GEMINI_MODEL !== '' ? GEMINI_MODEL : 'gemini-2.5-flash';
$chatbotDebug = defined('CHATBOT_DEBUG') && CHATBOT_DEBUG;

// 2. Read the JSON payload from the JavaScript fetch request
$inputData = json_decode(file_get_contents('php://input'), true);
$userMessage = $inputData['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['error' => 'Please provide a message.']);
    exit;
}

/**
 * Best-effort parse of Google Generative Language API error JSON.
 */
function chatbot_gemini_error_message(string $responseBody): string
{
    $decoded = json_decode($responseBody, true);
    if (!is_array($decoded)) {
        return '';
    }
    $msg = $decoded['error']['message'] ?? '';
    return is_string($msg) ? trim($msg) : '';
}

/**
 * Build JSON error payload; includes "debug" when CHATBOT_DEBUG is true.
 */
function chatbot_json_error(
    string $userFacing,
    array $debugExtra,
    bool $withDebug
): void {
    $out = ['error' => $userFacing];
    if ($withDebug) {
        $out['debug'] = $debugExtra;
    }
    echo json_encode($out);
    exit;
}

// 3. Define the System Context (This is your chatbot's personality and rulebook)
$systemContext = "You are a friendly and professional support assistant for a website named foovia .Your name is Wilson. 
foovia is a website that helps users to find healthy recipes and products and encourage healthy life style as well as providing a platform for users to share their own recipes and products.
Your goal is to help users with their queries and issues. 
Do not write long essays; keep your answers direct, clear, and structured in 1 to 2 short sentences. 
If user apologizes tell them you forgive them
If a user asks a question unrelated to the website, politely decline to answer and guide them back to website topics.";

// 4. Prepare the API URL (API key is sent via header, not query string)
$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($geminiModel) . ':generateContent';

// 5. Format the data payload exactly how Google's API expects it
$payload = [
    "system_instruction" => [
        "parts" => [
            ["text" => $systemContext]
        ]
    ],
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => $userMessage]
            ]
        ]
    ]
];

// 6. Initialize native PHP cURL
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

// Execute request and get HTTP status code
$response = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
$curlErrNo = (int) curl_errno($ch);
curl_close($ch);

if ($response === false || $curlErr !== '') {
    chatbot_json_error(
        'Could not reach the AI service. Please try again later.',
        [
            'stage' => 'curl',
            'curl_errno' => $curlErrNo,
            'curl_error' => $curlErr !== '' ? $curlErr : '(empty)',
            'request_url' => $url,
            'model' => $geminiModel,
        ],
        $chatbotDebug
    );
}

// 7. Handle the response based on the HTTP code
if ($httpCode === 200) {
    $responseData = json_decode($response, true);
    if (!is_array($responseData)) {
        chatbot_json_error(
            'Assistant returned data that could not be read. Please try again.',
            [
                'stage' => 'invalid_json_on_200',
                'model' => $geminiModel,
                'raw_preview' => substr((string) $response, 0, 800),
            ],
            $chatbotDebug
        );
    }
    $parts = $responseData['candidates'][0]['content']['parts'] ?? null;
    $botReply = is_array($parts) && isset($parts[0]['text']) ? trim((string) $parts[0]['text']) : '';
    if ($botReply === '') {
        $finish = $responseData['candidates'][0]['finishReason'] ?? null;
        $block = $responseData['promptFeedback']['blockReason'] ?? null;
        $userErr = 'The assistant could not produce a reply. Please try again.';
        if ($chatbotDebug) {
            chatbot_json_error(
                $userErr,
                [
                    'stage' => 'empty_reply',
                    'model' => $geminiModel,
                    'finishReason' => $finish,
                    'blockReason' => $block,
                    'response_preview' => substr((string) $response, 0, 1200),
                ],
                true
            );
        }
        echo json_encode(['error' => $userErr]);
        exit;
    }
    echo json_encode(['reply' => $botReply]);

} elseif ($httpCode === 429) {
    // Limit Hit: Handle the rate limit gracefully
    echo json_encode(['error' => 'The system is receiving too many requests right now. Please wait a few seconds and try again.']);

} else {
    $providerMsg = chatbot_gemini_error_message((string) $response);
    // Typical cause (2025+): retired model returns HTTP 404 "models/gemini-1.5-flash is not found"
    $hint = $httpCode === 404
        ? ' Check GEMINI_MODEL in MVC/Model/chat-bot-config.php (old 1.5 model names often return 404).'
        : '';
    $userFacing = $providerMsg !== ''
        ? 'Assistant request failed (HTTP ' . $httpCode . '): ' . $providerMsg
        : 'Assistant request failed (HTTP ' . $httpCode . '). Connection to the AI provider did not succeed.' . $hint;

    chatbot_json_error(
        $userFacing,
        [
            'stage' => 'gemini_http_error',
            'http_code' => $httpCode,
            'model' => $geminiModel,
            'request_url' => $url,
            'provider_message' => $providerMsg !== '' ? $providerMsg : null,
            'response_body_preview' => substr((string) $response, 0, 1500),
        ],
        $chatbotDebug
    );
}
?>