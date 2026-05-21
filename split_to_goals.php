<?php

$api_config = require_once 'api_config.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if wish ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid wish ID provided']);
    exit;
}

$wishId = (int)$_GET['id'];

try {
    // Initialize SQLite database
    $dbPath = 'wishes.db';
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the wish from database
    $stmt = $pdo->prepare("SELECT id, wish_text, is_correct FROM wishes WHERE id = :id");
    $stmt->execute([':id' => $wishId]);
    $wish = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$wish) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Wish not found']);
        exit;
    }

    // Check if wish is correct (150+ characters)
    if (!$wish['is_correct']) {
        echo json_encode([
            'success' => false,
            'message' => 'Wish is too short for decomposition. Please provide a more detailed wish (at least 150 characters).'
        ]);
        exit;
    }

    $apiKey = $api_config['openai']['api_key'] ?? null;
    $apiUrl = $api_config['openai']['api_url'] ?? null;

    // Prepare the prompt for AI decomposition
    $prompt = "Please analyze the following wish and break it down into clear, achievable goals and specific steps. 
    
    Wish: \"{$wish['wish_text']}\"
    
    Please provide a structured response in the following JSON format:
    {
        \"goals\": [
            {
                \"title\": \"Goal title\",
                \"description\": \"Clear description of the goal\",
                \"priority\": \"high/medium/low\",
                \"estimated_time\": \"time estimate\",
                \"steps\": [
                    {
                        \"title\": \"Step title\",
                        \"description\": \"Specific action to take\",
                        \"estimated_time\": \"time estimate\",
                        \"difficulty\": \"easy/medium/hard\"
                    }
                ]
            }
        ],
        \"summary\": \"Brief summary of the decomposition\"
    }
    
    Make sure each goal is specific, measurable, and achievable. Each step should be a small, actionable task that can be completed in a reasonable time frame.";

    // Prepare the request to AI API
    $requestData = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are an expert goal-setting and planning assistant. Your task is to help users break down their wishes into clear, achievable goals and specific actionable steps. Always provide practical, realistic advice.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens' => 2000,
        'temperature' => 0.7
    ];
    // Make the API request
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($requestData),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new Exception("cURL error: " . $curlError);
    }

    if ($httpCode !== 200) {
        throw new Exception("API request failed with HTTP code: " . $httpCode . " and response: " . $response);
    }

    $apiResponse = json_decode($response, true);

    if (!$apiResponse || !isset($apiResponse['choices'][0]['message']['content'])) {
        print_r($apiResponse);
        throw new Exception("Invalid API response format");
    }

    $aiContent = $apiResponse['choices'][0]['message']['content'];

    // Try to parse the JSON response from AI
    $decomposition = json_decode($aiContent, true);

    if (!$decomposition) {
        // If JSON parsing fails, return the raw AI response
        $decomposition = [
            'raw_response' => $aiContent,
            'goals' => [],
            'summary' => 'AI response could not be parsed as JSON'
        ];
    }

    // Store the decomposition result in database
    $stmt = $pdo->prepare("
        UPDATE wishes 
        SET decomposition_result = :result, 
            decomposed_at = CURRENT_TIMESTAMP 
        WHERE id = :id
    ");

    $stmt->execute([
        ':result' => json_encode($decomposition),
        ':id' => $wishId
    ]);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Wish successfully decomposed into goals and steps',
        'wish_id' => $wishId,
        'wish_text' => $wish['wish_text'],
        'decomposition' => $decomposition,
        'api_usage' => [
            'model' => $apiResponse['model'] ?? 'unknown',
            'tokens_used' => $apiResponse['usage']['total_tokens'] ?? 0
        ]
    ]);
} catch (PDOException $e) {
    error_log("Database error in split_to_goals.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again later.'
    ]);
} catch (Exception $e) {
    error_log("Error in split_to_goals.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error processing request: ' . $e->getMessage()
    ]);
}
