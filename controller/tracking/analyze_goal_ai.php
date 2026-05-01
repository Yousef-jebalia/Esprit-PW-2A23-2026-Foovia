<?php
header('Content-Type: application/json; charset=utf-8');

function send_json_response(array $payload, int $statusCode = 200): void {
  http_response_code($statusCode);
  echo json_encode($payload);
  exit;
}

function read_api_key_from_file(string $filePath): string {
  if (!is_file($filePath)) {
    return '';
  }

  return trim((string) @file_get_contents($filePath));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  send_json_response(['success' => false, 'error' => 'Method not allowed.'], 405);
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody ?: '', true);
if (!is_array($payload)) {
  send_json_response(['success' => false, 'error' => 'Invalid request body.'], 400);
}

$goalType = trim((string) ($payload['goal_type'] ?? 'not specified'));
$initialWeight = trim((string) ($payload['initial_weight'] ?? 'not specified'));
$targetWeight = trim((string) ($payload['target_weight'] ?? 'not specified'));
$startDate = trim((string) ($payload['start_date'] ?? 'not specified'));
$endDate = trim((string) ($payload['end_date'] ?? 'not specified'));
$sportConsistency = trim((string) ($payload['sport_consistency'] ?? 'not specified'));
$dietConsistency = trim((string) ($payload['diet_consistency'] ?? 'not specified'));

$apiKeyPath = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'api tracking.txt';
$apiKey = read_api_key_from_file($apiKeyPath);
if ($apiKey === '') {
  send_json_response(['success' => false, 'error' => 'AI API key is missing.'], 500);
}

$requestBody = [
  'contents' => [[
    'role' => 'user',
    'parts' => [[
      'text' => "You are a nutrition expert. Based on the user's long-term objective, suggest personalized DAILY macronutrient targets.\n\nUser profile:\n- Goal type: {$goalType}\n- Initial weight: {$initialWeight} kg\n- Target weight: {$targetWeight} kg\n- Start date: {$startDate}\n- End date: {$endDate}\n- Sport consistency: {$sportConsistency}\n- Diet consistency: {$dietConsistency}\n\nReturn ONLY a JSON object, with no markdown and no extra text, in this exact shape:\n{\n  \"meal_name\": \"Suggested macro targets\",\n  \"kcal\": number,\n  \"prot\": number,\n  \"carb\": number,\n  \"fat\": number,\n  \"rationale\": \"2 sentences max explaining the reasoning behind these targets\"\n}\nKeep the targets realistic, conservative, and aligned with the stated goal.",
    ]],
  ]],
  'generationConfig' => [
    'temperature' => 0.2,
    'maxOutputTokens' => 512,
    'responseMimeType' => 'application/json',
  ],
];

$candidateModels = [
  'gemini-2.5-flash-lite',
  'gemini-2.0-flash-lite',
  'gemini-2.5-flash',
  'gemini-flash-latest',
];

$response = null;
$httpStatus = 0;
$lastErrorMessage = 'AI request failed.';

foreach ($candidateModels as $modelName) {
  $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($modelName) . ':generateContent?key=' . rawurlencode($apiKey));
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
      'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($requestBody),
    CURLOPT_TIMEOUT => 60,
  ]);

  $rawResponse = curl_exec($ch);
  $curlError = curl_error($ch);
  $httpStatus = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
  curl_close($ch);

  if ($rawResponse === false) {
    $lastErrorMessage = 'AI request failed: ' . $curlError;
    continue;
  }

  $decodedResponse = json_decode($rawResponse, true);
  if (!is_array($decodedResponse)) {
    $lastErrorMessage = 'Unexpected AI response.';
    continue;
  }

  if ($httpStatus >= 200 && $httpStatus < 300) {
    $response = $decodedResponse;
    break;
  }

  $errorMessage = 'AI request failed.';
  if (!empty($decodedResponse['error']['message'])) {
    $errorMessage = (string) $decodedResponse['error']['message'];
  }
  if (!empty($decodedResponse['error']['status'])) {
    $errorMessage = $decodedResponse['error']['status'] . ': ' . $errorMessage;
  }

  $lastErrorMessage = $errorMessage;
  if (stripos($errorMessage, 'UNAVAILABLE') === false && stripos($errorMessage, 'high demand') === false) {
    break;
  }
}

if (!is_array($response)) {
  send_json_response(['success' => false, 'error' => $lastErrorMessage], $httpStatus ?: 502);
}

$content = '';
if (!empty($response['candidates'][0]['content']['parts']) && is_array($response['candidates'][0]['content']['parts'])) {
  foreach ($response['candidates'][0]['content']['parts'] as $block) {
    if (is_array($block) && isset($block['text'])) {
      $content .= (string) $block['text'];
    }
  }
}

$content = trim(preg_replace('/^```json\s*|\s*```$/', '', $content) ?? $content);
$parsed = json_decode($content, true);
if (!is_array($parsed)) {
  if (preg_match('/\{.*\}/s', $content, $match)) {
    $parsed = json_decode($match[0], true);
  }
}

if (!is_array($parsed)) {
  send_json_response(['success' => false, 'error' => 'AI returned invalid JSON.'], 502);
}

$mealName = trim((string) ($parsed['meal_name'] ?? 'Suggested macro targets'));
if ($mealName === '') {
  $mealName = 'Suggested macro targets';
}

send_json_response([
  'success' => true,
  'meal_name' => $mealName,
  'kcal' => isset($parsed['kcal']) ? (float) $parsed['kcal'] : '',
  'prot' => isset($parsed['prot']) ? (float) $parsed['prot'] : '',
  'carb' => isset($parsed['carb']) ? (float) $parsed['carb'] : '',
  'fat' => isset($parsed['fat']) ? (float) $parsed['fat'] : '',
  'rationale' => (string) ($parsed['rationale'] ?? ''),
]);
