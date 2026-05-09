<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../Model/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

if (!isset($_FILES['image']) || !is_array($_FILES['image'])) {
  http_response_code(400);
  echo json_encode(['error' => 'No image file received']);
  exit;
}

$imageFile = $_FILES['image'];
if (($imageFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
  http_response_code(400);
  echo json_encode(['error' => 'Image upload failed']);
  exit;
}

$tmpPath = $imageFile['tmp_name'] ?? '';
if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid uploaded file']);
  exit;
}

$bytes = @file_get_contents($tmpPath);
if ($bytes === false || $bytes === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Unable to read image']);
  exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($tmpPath) ?: 'image/jpeg';
if (strpos($mimeType, 'image/') !== 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Uploaded file is not an image']);
  exit;
}

$apiKey = trim((string)getenv('GEMINI_API_KEY'));
if ($apiKey === '' && isset($_SERVER['GEMINI_API_KEY'])) {
  $apiKey = trim((string)$_SERVER['GEMINI_API_KEY']);
}
if ($apiKey === '' && isset($_ENV['GEMINI_API_KEY'])) {
  $apiKey = trim((string)$_ENV['GEMINI_API_KEY']);
}
if ($apiKey === '' && isset($_SERVER['REDIRECT_GEMINI_API_KEY'])) {
  $apiKey = trim((string)$_SERVER['REDIRECT_GEMINI_API_KEY']);
}
if ($apiKey === '' && function_exists('apache_getenv')) {
  $apacheEnvLocal = apache_getenv('GEMINI_API_KEY');
  if ($apacheEnvLocal !== false && trim((string)$apacheEnvLocal) !== '') {
    $apiKey = trim((string)$apacheEnvLocal);
  }
}
if ($apiKey === '' && function_exists('apache_getenv')) {
  $apacheEnvTop = apache_getenv('GEMINI_API_KEY', true);
  if ($apacheEnvTop !== false && trim((string)$apacheEnvTop) !== '') {
    $apiKey = trim((string)$apacheEnvTop);
  }
}

// XAMPP fallback: read key from a local file outside the project repo.
if ($apiKey === '') {
  $localKeyPath = 'C:/xampp/htdocs/gemini_api_key.txt';
  if (is_readable($localKeyPath)) {
    $fileKey = trim((string)@file_get_contents($localKeyPath));
    if ($fileKey !== '') {
      $apiKey = $fileKey;
    }
  }
}

if (!$apiKey) {
  http_response_code(500);
  echo json_encode(['error' => 'Gemini API key is missing on server. Set GEMINI_API_KEY or create C:/xampp/htdocs/gemini_api_key.txt']);
  exit;
}

if (!function_exists('foovia_normalize_ingredient_name')) {
  function foovia_normalize_ingredient_name($value) {
    $value = trim((string)$value);
    if ($value === '') {
      return '';
    }
    if (function_exists('mb_strtolower')) {
      $value = mb_strtolower($value, 'UTF-8');
    } else {
      $value = strtolower($value);
    }
    $value = preg_replace('/[^\pL\pN]+/u', ' ', $value);
    $value = preg_replace('/\s+/', ' ', $value);
    return trim($value);
  }
}

$prompt = "You are a food ingredient detector. Analyze the image and identify visible edible ingredients. Return STRICT JSON only in this format: {\"ingredients\":[\"name1\",\"name2\"]}. Use short lowercase ingredient names. No extra text.";

$payload = [
  'contents' => [
    [
      'parts' => [
        [
          'text' => $prompt,
        ],
        [
          'inline_data' => [
            'mime_type' => $mimeType,
            'data' => base64_encode($bytes),
          ],
        ],
      ],
    ],
  ],
  'generationConfig' => [
    'temperature' => 0.2,
    'responseMimeType' => 'application/json',
  ],
];

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . rawurlencode($apiKey);

$ch = curl_init();
curl_setopt_array($ch, [
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => [
    'Content-Type: application/json',
  ],
  CURLOPT_POSTFIELDS => json_encode($payload),
  CURLOPT_TIMEOUT => 35,
]);

$responseBody = curl_exec($ch);
$curlErrNo = curl_errno($ch);
$curlError = curl_error($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlErrNo !== 0) {
  http_response_code(502);
  echo json_encode(['error' => 'Gemini request failed: ' . $curlError]);
  exit;
}

if ($responseBody === false || $responseBody === '') {
  http_response_code(502);
  echo json_encode(['error' => 'Empty response from Gemini']);
  exit;
}

$decoded = json_decode($responseBody, true);
if (!is_array($decoded)) {
  http_response_code(502);
  echo json_encode(['error' => 'Invalid response from Gemini']);
  exit;
}

if ($httpCode < 200 || $httpCode >= 300) {
  $apiError = $decoded['error']['message'] ?? 'Gemini API error';
  http_response_code(502);
  echo json_encode(['error' => $apiError]);
  exit;
}

$text = '';
if (isset($decoded['candidates'][0]['content']['parts']) && is_array($decoded['candidates'][0]['content']['parts'])) {
  foreach ($decoded['candidates'][0]['content']['parts'] as $part) {
    if (isset($part['text']) && is_string($part['text'])) {
      $text .= $part['text'];
    }
  }
}

$text = trim($text);
if ($text === '') {
  http_response_code(502);
  echo json_encode(['error' => 'Gemini returned no ingredient data']);
  exit;
}

$jsonText = $text;
if (preg_match('/```(?:json)?\s*(\{[\s\S]*\})\s*```/i', $text, $matches)) {
  $jsonText = trim($matches[1]);
}

$parsed = json_decode($jsonText, true);
if (!is_array($parsed)) {
  if (preg_match('/\[[\s\S]*\]/', $text, $arrayMatch)) {
    $parsed = ['ingredients' => json_decode($arrayMatch[0], true)];
  }
}

$ingredients = [];
if (is_array($parsed) && isset($parsed['ingredients']) && is_array($parsed['ingredients'])) {
  foreach ($parsed['ingredients'] as $name) {
    $name = strtolower(trim((string)$name));
    if ($name !== '') {
      $ingredients[] = $name;
    }
  }
}

$ingredients = array_values(array_unique($ingredients));

$matchedIngredients = [];
$unmatchedIngredients = [];

if (!empty($ingredients)) {
  try {
    $db = config::getConnexion();
    $stmt = $db->query('SELECT id_ing, name_ing, img_ing FROM ingrediant');
    $rows = $stmt ? $stmt->fetchAll() : [];

    $catalog = [];
    $lookup = [];
    foreach ($rows as $row) {
      $name = isset($row['name_ing']) ? (string)$row['name_ing'] : '';
      $normalized = foovia_normalize_ingredient_name($name);
      if ($normalized === '') {
        continue;
      }
      $catalog[] = [
        'id' => (int)($row['id_ing'] ?? 0),
        'name' => $name,
        'img' => (string)($row['img_ing'] ?? ''),
        'norm' => $normalized
      ];
      if (!isset($lookup[$normalized])) {
        $lookup[$normalized] = count($catalog) - 1;
      }
    }

    $matchedIds = [];
    foreach ($ingredients as $detectedName) {
      $normalizedDetected = foovia_normalize_ingredient_name($detectedName);
      if ($normalizedDetected === '') {
        continue;
      }

      $matchedItem = null;
      if (isset($lookup[$normalizedDetected])) {
        $matchedItem = $catalog[$lookup[$normalizedDetected]];
      } else if (strlen($normalizedDetected) >= 3) {
        $bestIndex = -1;
        $bestScore = 0;
        foreach ($catalog as $index => $item) {
          $candidate = $item['norm'] ?? '';
          if ($candidate === '') {
            continue;
          }
          if (strpos($candidate, $normalizedDetected) !== false || strpos($normalizedDetected, $candidate) !== false) {
            $score = min(strlen($candidate), strlen($normalizedDetected));
            if ($score > $bestScore) {
              $bestScore = $score;
              $bestIndex = $index;
            }
          }
        }
        if ($bestIndex >= 0) {
          $matchedItem = $catalog[$bestIndex];
        }
      }

      if ($matchedItem && !isset($matchedIds[$matchedItem['id']])) {
        $matchedIds[$matchedItem['id']] = true;
        $matchedIngredients[] = [
          'id' => $matchedItem['id'],
          'name' => $matchedItem['name'],
          'img' => $matchedItem['img'],
          'detected' => $detectedName
        ];
      } else if (!$matchedItem) {
        $unmatchedIngredients[] = $detectedName;
      }
    }
  } catch (Exception $e) {
    $matchedIngredients = [];
    $unmatchedIngredients = [];
  }
}

echo json_encode([
  'ingredients' => $ingredients,
  'matches' => $matchedIngredients,
  'unmatched' => $unmatchedIngredients,
]);
