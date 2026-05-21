<?php
// Check if wish ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "Invalid wish ID provided";
    exit;
}

$wishId = (int)$_GET['id'];

try {
    // Initialize SQLite database
    $dbPath = 'wishes.db';
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get the wish and decomposition from database
    $stmt = $pdo->prepare("
        SELECT id, wish_text, is_correct, decomposition_result, decomposed_at, goals_count, steps_count 
        FROM wishes 
        WHERE id = :id
    ");
    $stmt->execute([':id' => $wishId]);
    $wish = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$wish) {
        http_response_code(404);
        echo "Wish not found";
        exit;
    }
    
    $decomposition = null;
    if ($wish['decomposition_result']) {
        $decomposition = json_decode($wish['decomposition_result'], true);
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Let-wish - Wish Decomposition</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .wish-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }

        .wish-text {
            font-size: 1.2em;
            line-height: 1.6;
            color: #333;
            margin-bottom: 15px;
        }

        .wish-meta {
            font-size: 0.9em;
            color: #666;
        }

        .decomposition-section {
            margin-top: 30px;
        }

        .decomposition-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .decomposition-header h2 {
            color: #333;
            font-size: 2em;
            margin-bottom: 10px;
        }

        .goals-container {
            display: grid;
            gap: 20px;
            margin-bottom: 30px;
        }

        .goal-card {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #667eea;
        }

        .goal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .goal-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #333;
        }

        .goal-priority {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
        }

        .priority-high {
            background: #ffebee;
            color: #c62828;
        }

        .priority-medium {
            background: #fff3e0;
            color: #ef6c00;
        }

        .priority-low {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .goal-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .goal-time {
            font-size: 0.9em;
            color: #888;
            margin-bottom: 20px;
        }

        .steps-container {
            margin-top: 20px;
        }

        .steps-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .step-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 3px solid #667eea;
        }

        .step-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .step-description {
            color: #666;
            line-height: 1.5;
            margin-bottom: 10px;
        }

        .step-meta {
            display: flex;
            gap: 15px;
            font-size: 0.8em;
            color: #888;
        }

        .step-difficulty {
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        .difficulty-easy {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .difficulty-medium {
            background: #fff3e0;
            color: #ef6c00;
        }

        .difficulty-hard {
            background: #ffebee;
            color: #c62828;
        }

        .no-decomposition {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .no-decomposition h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .decompose-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            transition: transform 0.2s ease;
        }

        .decompose-btn:hover {
            transform: translateY(-2px);
        }

        .summary-section {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }

        .summary-title {
            font-weight: 600;
            color: #1976d2;
            margin-bottom: 10px;
        }

        .summary-text {
            color: #333;
            line-height: 1.6;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Let-wish - Wish Decomposition</h1>
            <p>AI-powered goal and step breakdown</p>
        </div>

        <a href="view_wishes.php" class="back-link">← Back to Wishes</a>

        <div class="wish-card">
            <div class="wish-text">
                <?php echo htmlspecialchars($wish['wish_text']); ?>
            </div>
            <div class="wish-meta">
                <strong>Wish ID:</strong> #<?php echo htmlspecialchars($wish['id']); ?> |
                <strong>Character count:</strong> <?php echo strlen($wish['wish_text']); ?> |
                <strong>Status:</strong> <?php echo $wish['is_correct'] ? 'Correct' : 'Incorrect'; ?>
                <?php if ($wish['decomposed_at']): ?>
                    | <strong>Decomposed:</strong> <?php echo htmlspecialchars($wish['decomposed_at']); ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="decomposition-section">
            <?php if (!$decomposition): ?>
                <div class="no-decomposition">
                    <h3>No decomposition available</h3>
                    <p>This wish hasn't been processed by AI yet.</p>
                    <a href="split_to_goals.php?id=<?php echo $wish['id']; ?>" class="decompose-btn" id="decomposeBtn">
                        Decompose to Goals
                    </a>
                </div>
            <?php else: ?>
                <div class="decomposition-header">
                    <h2>Goals & Steps Breakdown</h2>
                    <?php if (isset($decomposition['summary'])): ?>
                        <div class="summary-section">
                            <div class="summary-title">Summary</div>
                            <div class="summary-text"><?php echo htmlspecialchars($decomposition['summary']); ?></div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="goals-container">
                    <?php if (isset($decomposition['goals']) && is_array($decomposition['goals'])): ?>
                        <?php foreach ($decomposition['goals'] as $goal): ?>
                            <div class="goal-card">
                                <div class="goal-header">
                                    <div class="goal-title"><?php echo htmlspecialchars($goal['title'] ?? 'Untitled Goal'); ?></div>
                                    <?php if (isset($goal['priority'])): ?>
                                        <div class="goal-priority priority-<?php echo strtolower($goal['priority']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($goal['priority'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (isset($goal['description'])): ?>
                                    <div class="goal-description"><?php echo htmlspecialchars($goal['description']); ?></div>
                                <?php endif; ?>
                                
                                <?php if (isset($goal['estimated_time'])): ?>
                                    <div class="goal-time">
                                        <strong>Estimated time:</strong> <?php echo htmlspecialchars($goal['estimated_time']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (isset($goal['steps']) && is_array($goal['steps'])): ?>
                                    <div class="steps-container">
                                        <div class="steps-title">Steps:</div>
                                        <?php foreach ($goal['steps'] as $step): ?>
                                            <div class="step-item">
                                                <div class="step-title"><?php echo htmlspecialchars($step['title'] ?? 'Untitled Step'); ?></div>
                                                <?php if (isset($step['description'])): ?>
                                                    <div class="step-description"><?php echo htmlspecialchars($step['description']); ?></div>
                                                <?php endif; ?>
                                                <div class="step-meta">
                                                    <?php if (isset($step['estimated_time'])): ?>
                                                        <span><strong>Time:</strong> <?php echo htmlspecialchars($step['estimated_time']); ?></span>
                                                    <?php endif; ?>
                                                    <?php if (isset($step['difficulty'])): ?>
                                                        <span class="step-difficulty difficulty-<?php echo strtolower($step['difficulty']); ?>">
                                                            <?php echo ucfirst(htmlspecialchars($step['difficulty'])); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-decomposition">
                            <h3>No goals found</h3>
                            <p>The AI response couldn't be parsed properly.</p>
                            <?php if (isset($decomposition['raw_response'])): ?>
                                <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; margin-top: 15px;">
                                    <strong>Raw AI Response:</strong><br>
                                    <pre><?php echo htmlspecialchars($decomposition['raw_response']); ?></pre>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Handle decomposition button click
        document.getElementById('decomposeBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            
            const btn = this;
            const originalText = btn.textContent;
            
            // Show loading state
            btn.textContent = 'Processing...';
            btn.style.pointerEvents = 'none';
            
            // Make AJAX request
            fetch(btn.href)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page to show results
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                        btn.textContent = originalText;
                        btn.style.pointerEvents = 'auto';
                    }
                })
                .catch(error => {
                    alert('Error processing request: ' + error.message);
                    btn.textContent = originalText;
                    btn.style.pointerEvents = 'auto';
                });
        });
    </script>
</body>
</html>
