<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get the wish from POST data
$wish = isset($_POST['wish']) ? trim($_POST['wish']) : '';

// Validate the wish
if (empty($wish)) {
    echo json_encode(['success' => false, 'message' => 'Wish cannot be empty']);
    exit;
}

if (strlen($wish) < 150) {
    echo json_encode(['success' => false, 'message' => 'Wish must be at least 150 characters long']);
    exit;
}

if (strlen($wish) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Wish cannot exceed 1000 characters']);
    exit;
}

try {
    // Initialize SQLite database
    $dbPath = 'wishes.db';
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create wishes table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS wishes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            wish_text TEXT NOT NULL,
            is_correct BOOLEAN DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Insert the wish into the database
    $stmt = $pdo->prepare("
        INSERT INTO wishes (wish_text, is_correct) 
        VALUES (:wish_text, :is_correct)
    ");
    
    // Determine if the wish is "correct" based on the 150+ character rule
    $isCorrect = strlen($wish) >= 150;
    
    $stmt->execute([
        ':wish_text' => $wish,
        ':is_correct' => $isCorrect ? 1 : 0
    ]);
    
    $wishId = $pdo->lastInsertId();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Wish submitted successfully',
        'wish_id' => $wishId,
        'is_correct' => $isCorrect,
        'character_count' => strlen($wish)
    ]);
    
} catch (PDOException $e) {
    // Log the error (in a production environment, you'd want proper logging)
    error_log("Database error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred. Please try again later.'
    ]);
} catch (Exception $e) {
    // Log the error
    error_log("General error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false, 
        'message' => 'An unexpected error occurred. Please try again later.'
    ]);
}
?> 