<?php
require_once __DIR__ . '/../../Model/config.php';

function ai_workout_strip_code_fences(string $text): string {
    $text = trim($text);
    if (strpos($text, '```') === 0) {
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
    }

    return trim($text);
}

function ai_workout_extract_json_candidate(string $text): ?string {
    $text = ai_workout_strip_code_fences($text);

    $start = strpos($text, '{');
    $end = strrpos($text, '}');

    if ($start === false) {
        return null;
    }

    if ($end !== false && $end > $start) {
        return substr($text, $start, $end - $start + 1);
    }

    return substr($text, $start);
}

function ai_workout_repair_truncated_json(string $jsonText): ?string {
    $jsonText = trim($jsonText);
    if ($jsonText === '') {
        return null;
    }

    $jsonText = preg_replace('/,\s*([}\]])/', '$1', $jsonText);

    $openStack = [];
    $inString = false;
    $escaped = false;
    $length = strlen($jsonText);

    for ($i = 0; $i < $length; $i++) {
        $char = $jsonText[$i];

        if ($inString) {
            if ($escaped) {
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $escaped = true;
                continue;
            }

            if ($char === '"') {
                $inString = false;
            }

            continue;
        }

        if ($char === '"') {
            $inString = true;
            continue;
        }

        if ($char === '{' || $char === '[') {
            $openStack[] = $char;
            continue;
        }

        if ($char === '}' || $char === ']') {
            if (empty($openStack)) {
                continue;
            }

            $lastOpen = array_pop($openStack);
            if (($char === '}' && $lastOpen !== '{') || ($char === ']' && $lastOpen !== '[')) {
                return null;
            }
        }
    }

    if ($inString) {
        $jsonText .= '"';
    }

    if (empty($openStack)) {
        return $jsonText;
    }

    while (!empty($openStack)) {
        $lastOpen = array_pop($openStack);
        $jsonText = rtrim($jsonText);
        $jsonText = preg_replace('/,\s*$/', '', $jsonText);
        $jsonText .= $lastOpen === '{' ? '}' : ']';
    }

    return $jsonText;
}

function generateAIWorkout($workoutName, $targetMuscles, $aiService = 'gemini') {
    $keyFilePath = realpath(__DIR__ . '/../../../sport_api');
    if (empty($workoutName) || empty($targetMuscles)) return null;

    // Fetch exercises from database
    $db = config::getConnexion();
    $stmt = $db->query("SELECT id_ex, name_ex, cal_ex, type_ex, muscle_ex FROM exercice");
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($exercises)) {
        return ['error' => 'Database error: No exercises found in database'];
    }

    // Build exercise list for AI
    $exerciseList = "Available exercises:\n";
    $calorieMap = [];

    foreach ($exercises as $ex) {
        $id = $ex['id_ex'];
        $name = $ex['name_ex'];
        $cal = $ex['cal_ex'];
        $muscle = $ex['muscle_ex'];

        $exerciseList .= "- ID: $id, Name: $name, Muscle: $muscle\n";
        $calorieMap[$id] = (float)$cal;
    }

    $muscles = implode(', ', $targetMuscles);

    // Build prompt
    $prompt = "Create workout targeting: $muscles\n\n$exerciseList\n";
    $prompt .= "Return JSON with duration_minutes (20-120) and exercises array with: exercise_id, sets, reps, weight, time\n";
    $prompt .= "For cardio: sets=0, reps=0, weight=0, time=minutes\n";
    $prompt .= "For strength: time=0\n";
    $prompt .= "JSON only, no markdown.";

    // Read API key from file
    if (!file_exists($keyFilePath)) {
        return ['error' => "API key file not found at: $keyFilePath"];
    }

    $key = trim(file_get_contents($keyFilePath));
    if (empty($key)) {
        return ['error' => "API key file is empty: $keyFilePath"];
    }

    $payload = json_encode([
        'contents' => [
            ['parts' => [['text' => $prompt]]]
        ],
        'generationConfig' => [
            'responseMimeType' => 'application/json',
            'temperature' => 0.2,
            'maxOutputTokens' => 4096,
        ]
    ]);

    $modelsToTry = [
        'gemini-2.5-flash',
        'gemini-2.0-flash',
    ];

    $response = null;
    $code = 0;
    $curlError = '';
    $lastModel = '';

    foreach ($modelsToTry as $modelName) {
        $lastModel = $modelName;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/$modelName:generateContent?key=$key");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_TIMEOUT => 30
            ]);

            $response = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($response && $code < 400) {
                break 2;
            }

            if ($code !== 503 || $attempt === 3) {
                break;
            }

            usleep(250000 * $attempt);
        }
    }

    if ($code >= 400 || !$response) {
        $friendlyMessage = $code === 503
            ? 'Gemini is temporarily overloaded. Please try again in a minute.'
            : "Gemini API error: HTTP $code. Response: $response. Curl error: $curlError";

        return [
            'error' => $friendlyMessage,
            'debug' => [
                'model' => $lastModel,
                'http_code' => $code,
                'curl_error' => $curlError,
            ]
        ];
    }

    $data = json_decode($response, true);
    if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        return ['error' => "Gemini API invalid response format. Response: " . substr($response, 0, 200)];
    }

    $text = trim($data['candidates'][0]['content']['parts'][0]['text']);
    $json = json_decode($text, true);

    if (!is_array($json)) {
        $candidate = ai_workout_extract_json_candidate($text);
        if ($candidate !== null) {
            $json = json_decode($candidate, true);
        }

        if (!is_array($json) && $candidate !== null) {
            $repaired = ai_workout_repair_truncated_json($candidate);
            if ($repaired !== null) {
                $json = json_decode($repaired, true);
            }
        }

        if (!is_array($json)) {
            return ['error' => 'JSON parsing failed: ' . json_last_error_msg(), 'debug' => substr($text, 0, 500)];
        }
    }

    if (!isset($json['duration_minutes']) || !isset($json['exercises'])) {
        return ['error' => "JSON missing required fields. Got: " . json_encode(array_keys($json))];
    }

    // Calculate calories
    $totalCal = 0;
    foreach ($json['exercises'] as $ex) {
        if ($ex['type_ex'] !== 'cardio') {
            $exId = $ex['exercise_id'];
            $sets = max(1, $ex['sets'] ?? 1);
            $reps = max(1, $ex['reps'] ?? 1);
            $cal = $calorieMap[$exId] ?? 0;
            $totalCal += $sets * $reps * $cal;
        }
    }

    return [
        'duration_minutes' => (int)$json['duration_minutes'],
        'calories' => (int)round($totalCal),
        'exercises' => $json['exercises']
    ];
}

function getAIWorkoutCategoryId(PDO $db): int {
    $categoryName = 'Custom by AI';

    $stmt = $db->prepare(
        "SELECT id_cat
         FROM work_categorie
         WHERE LOWER(TRIM(name_cat)) IN ('custom by ai', 'custom ai', 'ai generated', 'ai workout')
         ORDER BY id_cat
         LIMIT 1"
    );
    $stmt->execute();
    $existingId = $stmt->fetchColumn();

    if ($existingId !== false) {
        return (int) $existingId;
    }

    $insert = $db->prepare("INSERT INTO work_categorie (name_cat) VALUES (:name_cat)");
    $insert->execute([':name_cat' => $categoryName]);

    return (int) $db->lastInsertId();
}

function getNextWorkoutId(PDO $db): int {
    $stmt = $db->query("SELECT COALESCE(MAX(id_work), 0) + 1 AS next_id FROM workout");
    $nextId = $stmt ? (int)$stmt->fetchColumn() : 1;

    return $nextId > 0 ? $nextId : 1;
}

function saveAIWorkout($workoutName, $aiOutput, $userId, $picWork = null) {
    require_once __DIR__ . '/../../Model/SPORT_MOULE/workout.php';

    if (!$aiOutput || empty($aiOutput['exercises'])) return null;

    $db = config::getConnexion();
    $aiCategoryId = getAIWorkoutCategoryId($db);
    $picWork = $picWork ?? '';
    $workoutId = getNextWorkoutId($db);

    // Create workout object
    $workout = new Workout(
        $workoutName,
        $picWork,
        $aiOutput['calories'],
        $aiOutput['duration_minutes'],
        $userId,
        $aiCategoryId
    );

    $db->beginTransaction();

    // Insert workout with an explicit ID to avoid relying on AUTO_INCREMENT state.
    $stmt = $db->prepare("INSERT INTO workout (id_work, name_work, pic_work, cal_work, duree_work, id_user, id_cat)
                         VALUES (:id_work, :name, :pic, :cal, :duree, :user, :cat)");
    $stmt->bindValue(':id_work', $workoutId, PDO::PARAM_INT);
    $stmt->bindValue(':name', $workout->getNameWork());
    $stmt->bindValue(':pic', $workout->getPicWork(), PDO::PARAM_LOB);
    $stmt->bindValue(':cal', $workout->getCalWork(), PDO::PARAM_INT);
    $stmt->bindValue(':duree', $workout->getDureeWork(), PDO::PARAM_INT);
    $stmt->bindValue(':user', $workout->getIdUser(), PDO::PARAM_INT);
    $stmt->bindValue(':cat', $workout->getIdCat(), PDO::PARAM_INT);
    $stmt->execute();

    // Insert exercises into belong table
    $stmt = $db->prepare("INSERT INTO belong (id_work, id_ex, sets, weight, time, reps)
                         VALUES (:work, :ex, :sets, :weight, :time, :reps)");

    foreach ($aiOutput['exercises'] as $ex) {
        $stmt->execute([
            ':work' => $workoutId,
            ':ex' => $ex['exercise_id'],
            ':sets' => $ex['sets'] ?? 0,
            ':weight' => $ex['weight'] ?? 0,
            ':time' => $ex['time'] ?? 0,
            ':reps' => $ex['reps'] ?? 0
        ]);
    }

    $db->commit();

    return [
        'id_work' => $workoutId,
        'name_work' => $workoutName,
        'cal_work' => $aiOutput['calories'],
        'duree_work' => $aiOutput['duration_minutes'],
        'id_cat' => $aiCategoryId
    ];
}
