<?php
require_once __DIR__ . '/../model/config.php';

function generateAIWorkout($workoutName, $targetMuscles, $aiService = 'gemini') {
    $keyFilePath = 'C:\\API keys\\foovia_api_keys.txt';
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
        return ['error' => "API key file is empty at: $keyFilePath"];
    }

    $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=$key");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ]),
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($code >= 400 || !$response) {
        return ['error' => "Gemini API error: HTTP $code. Response: $response. Curl error: $curlError"];
    }

    $data = json_decode($response, true);
    if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        return ['error' => "Gemini API invalid response format. Response: " . substr($response, 0, 200)];
    }

    $text = trim($data['candidates'][0]['content']['parts'][0]['text']);
    
    // Remove markdown if present
    if (strpos($text, '```') === 0) {
        $text = preg_replace('/```json?\n?/', '', $text);
    }
    
    $json = json_decode($text, true);
    if (!$json) {
        return ['error' => "JSON parsing failed. AI response: " . substr($text, 0, 300)];
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

function saveAIWorkout($workoutName, $aiOutput, $userId, $picWork = null) {
    require_once __DIR__ . '/../model/workout.php';
    
    if (!$aiOutput || empty($aiOutput['exercises'])) return null;
    
    $db = config::getConnexion();
    
    // Create workout object
    $workout = new Workout(
        $workoutName,
        $picWork,
        $aiOutput['calories'],
        $aiOutput['duration_minutes'],
        $userId,
        6 // id_cat for "custom by ai"
    );
    
    // Insert workout
    $stmt = $db->prepare("INSERT INTO workout (name_work, pic_work, cal_work, duree_work, id_user, id_cat) 
                         VALUES (:name, :pic, :cal, :duree, :user, :cat)");
    $stmt->bindValue(':name', $workout->getNameWork());
    $stmt->bindValue(':pic', $workout->getPicWork(), PDO::PARAM_LOB);
    $stmt->bindValue(':cal', $workout->getCalWork(), PDO::PARAM_INT);
    $stmt->bindValue(':duree', $workout->getDureeWork(), PDO::PARAM_INT);
    $stmt->bindValue(':user', $workout->getIdUser(), PDO::PARAM_INT);
    $stmt->bindValue(':cat', $workout->getIdCat(), PDO::PARAM_INT);
    $stmt->execute();
    
    $workoutId = $db->lastInsertId();
    
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
    
    return [
        'id_work' => $workoutId,
        'name_work' => $workoutName,
        'cal_work' => $aiOutput['calories'],
        'duree_work' => $aiOutput['duration_minutes'],
        'id_cat' => 6
    ];
}
