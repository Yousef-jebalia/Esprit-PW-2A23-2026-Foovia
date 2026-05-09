<?php
header('Content-Type: application/json; charset=utf-8');

session_start();

require_once __DIR__ . '/../../Model/config.php';
require_once __DIR__ . '/../../Controller/menu_module/controle_Menu.php';

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

function split_list(string $value): array {
  $value = strtolower(trim($value));
  if ($value === '' || $value === 'none' || $value === 'n/a') {
    return [];
  }

  $parts = preg_split('/[,;|]+/', $value) ?: [];
  $clean = [];
  foreach ($parts as $part) {
    $part = trim((string) $part);
    if ($part !== '') {
      $clean[] = $part;
    }
  }

  return array_values(array_unique($clean));
}

function guess_category(array $categories, string $name): string {
  $text = strtolower(trim($name . ' ' . implode(' ', $categories)));
  if (preg_match('/\b(breakfast|petit|dejeuner|morning|brunch)\b/', $text)) {
    return 'breakfast';
  }
  if (preg_match('/\b(lunch|dejeuner|noon|midday)\b/', $text)) {
    return 'lunch';
  }
  if (preg_match('/\b(dinner|diner|supper|evening)\b/', $text)) {
    return 'dinner';
  }
  if (preg_match('/\b(snack|collation)\b/', $text)) {
    return 'snack';
  }
  return 'lunch';
}

function is_gluten_free(array $ingredients): bool {
  if (empty($ingredients)) {
    return true;
  }
  $glutenSources = ['wheat', 'flour', 'pasta', 'bread', 'semolina', 'barley', 'rye'];
  foreach ($ingredients as $ingredient) {
    $name = strtolower($ingredient);
    foreach ($glutenSources as $source) {
      if (strpos($name, $source) !== false) {
        return false;
      }
    }
  }
  return true;
}

function recipe_has_allergen(array $ingredients, array $allergies, string $recipeName): bool {
  if (empty($allergies)) {
    return false;
  }
  $recipeName = strtolower($recipeName);
  foreach ($allergies as $allergy) {
    $needle = strtolower($allergy);
    if ($needle === '') {
      continue;
    }
    if (strpos($recipeName, $needle) !== false) {
      return true;
    }
    foreach ($ingredients as $ingredient) {
      if (strpos(strtolower($ingredient), $needle) !== false) {
        return true;
      }
    }
  }
  return false;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  send_json_response(['success' => false, 'error' => 'Method not allowed.'], 405);
}

if (empty($_SESSION['user_id'])) {
  send_json_response(['success' => false, 'error' => 'Authentication required.'], 401);
}

$payload = json_decode(file_get_contents('php://input') ?: '', true);
$slotDefaults = ['breakfast', 'morning-snack', 'lunch', 'afternoon-snack', 'dinner'];
$slots = $slotDefaults;
if (is_array($payload) && isset($payload['slots']) && is_array($payload['slots'])) {
  $filtered = array_values(array_intersect($payload['slots'], $slotDefaults));
  if (!empty($filtered)) {
    $slots = $filtered;
  }
}

$avoidIds = [];
if (is_array($payload) && isset($payload['avoid_ids']) && is_array($payload['avoid_ids'])) {
  $avoidIds = array_map('intval', $payload['avoid_ids']);
}

$apiKeyPath = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'mealplan.txt';
$apiKey = read_api_key_from_file($apiKeyPath);
if ($apiKey === '') {
  send_json_response(['success' => false, 'error' => 'AI API key is missing.'], 500);
}

$db = config::getConnexion();
$userId = (int) $_SESSION['user_id'];
$userRow = [];
try {
  $userQuery = $db->prepare('SELECT allergie_user, illness_user FROM user WHERE id_user = :id_user LIMIT 1');
  $userQuery->execute(['id_user' => $userId]);
  $userRow = $userQuery->fetch() ?: [];
} catch (Exception $e) {
  $userRow = [];
}

$allergies = split_list((string) ($userRow['allergie_user'] ?? ''));
$illnesses = split_list((string) ($userRow['illness_user'] ?? ''));

$goals = [
  'kcal' => 2000,
  'prot' => 150,
  'carb' => 200,
  'fat' => 65,
];
try {
  $goalQuery = $db->prepare("SELECT obj_cal_obj, obj_prot_obj, obj_carb_obj, obj_fat_obj FROM objectiflongterme WHERE id_user = :id_user ORDER BY (status_obj = 'en_cours') DESC, date_deb_obj DESC, id_obj DESC LIMIT 1");
  $goalQuery->execute(['id_user' => $userId]);
  $goalRow = $goalQuery->fetch();
  if (is_array($goalRow)) {
    $goals = [
      'kcal' => (float) ($goalRow['obj_cal_obj'] ?? $goals['kcal']),
      'prot' => (float) ($goalRow['obj_prot_obj'] ?? $goals['prot']),
      'carb' => (float) ($goalRow['obj_carb_obj'] ?? $goals['carb']),
      'fat' => (float) ($goalRow['obj_fat_obj'] ?? $goals['fat']),
    ];
  }
} catch (Exception $e) {
  // Keep defaults.
}

$controller = new Controller_menu();
$recipesRaw = $controller->list_recipe();

$ingredientsByRecipe = [];
try {
  $ingredientQuery = $db->query('SELECT ct.id_rec, i.name_ing FROM contenir ct LEFT JOIN ingrediant i ON i.id_ing = ct.id_ing ORDER BY ct.id_rec ASC');
  foreach ($ingredientQuery as $row) {
    $idRec = (int) ($row['id_rec'] ?? 0);
    $nameIng = trim((string) ($row['name_ing'] ?? ''));
    if ($idRec > 0 && $nameIng !== '') {
      $ingredientsByRecipe[$idRec][] = $nameIng;
    }
  }
} catch (Exception $e) {
  $ingredientsByRecipe = [];
}

$recipes = [];
$recipeById = [];
foreach ($recipesRaw as $row) {
  $id = (int) ($row['id_rec'] ?? 0);
  $name = trim((string) ($row['name_rec'] ?? ''));
  if ($id <= 0 || $name === '') {
    continue;
  }
  $ingredients = $ingredientsByRecipe[$id] ?? [];
  if (recipe_has_allergen($ingredients, $allergies, $name)) {
    continue;
  }

  $categoryList = array_filter(array_map('trim', explode(',', (string) ($row['categorie_rec'] ?? ''))));
  $category = guess_category($categoryList, $name);
  $tags = [];
  $prot = (float) ($row['prot_rec'] ?? 0);
  $carb = (float) ($row['carb_rec'] ?? 0);
  if ($prot >= 25) {
    $tags[] = 'high-protein';
  }
  if ($carb <= 20) {
    $tags[] = 'low-carb';
  }
  if (is_gluten_free($ingredients)) {
    $tags[] = 'gluten-free';
  }

  $recipes[] = [
    'id' => $id,
    'name' => $name,
    'cat' => $category,
    'kcal' => (float) ($row['cal_rec'] ?? 0),
    'prot' => $prot,
    'carb' => $carb,
    'fat' => (float) ($row['fat_rec'] ?? 0),
    'tags' => array_values(array_unique($tags)),
  ];
  $recipeById[$id] = true;
}

if (empty($recipes)) {
  send_json_response(['success' => false, 'error' => 'No recipes available for planning.'], 422);
}

$filteredRecipes = [];
foreach ($recipes as $r) {
  if (!in_array($r['id'], $avoidIds, true)) {
    $filteredRecipes[] = $r;
  }
}
if (count($filteredRecipes) < count($slots)) {
  $filteredRecipes = $recipes; // Fallback if filtering leaves too few recipes
}

$recipePromptList = array_slice($filteredRecipes, 0, 80);
$allergyText = $allergies ? implode(', ', $allergies) : 'None';
$illnessText = $illnesses ? implode(', ', $illnesses) : 'None';
$recipesJson = json_encode($recipePromptList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$requestBody = [
  'contents' => [[
    'role' => 'user',
    'parts' => [[
      'text' => "You are a nutrition planner. Build a one-day meal plan using ONLY the recipe IDs provided in the database list below. DO NOT invent or hallucinate IDs.\n\nUser daily goals:\n- Calories: {$goals['kcal']}\n- Protein: {$goals['prot']}\n- Carbs: {$goals['carb']}\n- Fat: {$goals['fat']}\n\nAllergies to avoid: {$allergyText}\nHealth conditions to consider: {$illnessText}\n\nSlots to fill: " . implode(', ', $slots) . "\n\nDatabase Recipes (choose only from these 'id' values):\n{$recipesJson}\n\nReturn ONLY a JSON object, no markdown and no extra text. Use this exact shape:\n{\n  \"breakfast\": number,\n  \"morning-snack\": number,\n  \"lunch\": number,\n  \"afternoon-snack\": number,\n  \"dinner\": number\n}\nPrefer variety but you may reuse a recipe if needed."
    ]],
  ]],
  'generationConfig' => [
    'temperature' => 0.2,
    'maxOutputTokens' => 512,
    'responseMimeType' => 'application/json',
  ],
];

$candidateModels = [
  'gemini-2.5-flash',
  'gemini-2.0-flash',
  'gemini-1.5-flash',
  'gemini-1.5-flash-latest',
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
if (!is_array($parsed) && preg_match('/\{.*\}/s', $content, $match)) {
  $parsed = json_decode($match[0], true);
}

if (!is_array($parsed)) {
  send_json_response(['success' => false, 'error' => 'AI returned invalid JSON.'], 502);
}

$plan = [];
foreach ($slots as $slot) {
  $id = (int) ($parsed[$slot] ?? 0);
  if ($id > 0 && isset($recipeById[$id])) {
    $plan[$slot] = $id;
  }
}

if (empty($plan)) {
  send_json_response(['success' => false, 'error' => 'AI response did not match available recipes.'], 502);
}

send_json_response([
  'success' => true,
  'plan' => $plan,
  'source' => 'ai',
]);
