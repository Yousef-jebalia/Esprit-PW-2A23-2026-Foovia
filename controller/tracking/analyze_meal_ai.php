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

  $key = trim((string) @file_get_contents($filePath));
  return $key;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  send_json_response(['success' => false, 'error' => 'Method not allowed.'], 405);
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody ?: '', true);
if (!is_array($payload)) {
  send_json_response(['success' => false, 'error' => 'Invalid request body.'], 400);
}

$imageDataUrl = (string) ($payload['image_data_url'] ?? '');
if ($imageDataUrl === '' || !preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64,(.+)$/', $imageDataUrl, $matches)) {
  send_json_response(['success' => false, 'error' => 'Missing or invalid image data.'], 400);
}

$mediaType = $matches[1];
$imageBase64 = $matches[2];
$imageBinary = base64_decode($imageBase64, true);
if ($imageBinary === false) {
  send_json_response(['success' => false, 'error' => 'Unable to decode image data.'], 400);
}

$apiKeyPath = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'api tracking.txt';
$apiKey = read_api_key_from_file($apiKeyPath);
if ($apiKey === '') {
  send_json_response(['success' => false, 'error' => 'AI API key is missing.'], 500);
}

$requestBody = [
  'contents' => [[
    'role' => 'user',
    'parts' => [
      [
        'text' => "Analyse this food photo and estimate the meal name and its nutritional content. Respond ONLY with a JSON object, no markdown, no extra text:\n{\n  \"meal_name\": \"string\",\n  \"kcal\": number,\n  \"prot\": number,\n  \"carb\": number,\n  \"fat\": number\n}\nUse realistic values and keep the meal name concise.",
      ],
      [
        'inline_data' => [
          'mime_type' => $mediaType,
          'data' => $imageBase64,
        ],
      ],
    ],
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

$mealName = trim((string) ($parsed['meal_name'] ?? 'Meal'));
if ($mealName === '') {
  $mealName = 'Meal';
}

send_json_response([
  'success' => true,
  'meal_name' => $mealName,
  'kcal' => isset($parsed['kcal']) ? (float) $parsed['kcal'] : '',
  'prot' => isset($parsed['prot']) ? (float) $parsed['prot'] : '',
  'carb' => isset($parsed['carb']) ? (float) $parsed['carb'] : '',
  'fat' => isset($parsed['fat']) ? (float) $parsed['fat'] : '',
]);
