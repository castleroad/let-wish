<?php
try {
    // Initialize SQLite database
    $dbPath = 'wishes.db';
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all wishes
    $stmt = $pdo->query("
        SELECT id, wish_text, is_correct, created_at, updated_at 
        FROM wishes 
        ORDER BY created_at DESC
    ");
    
    $wishes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $wishes = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Let-wish - View Submitted Wishes</title>
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
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }

        .wish-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .wish-id {
            font-weight: bold;
            color: #667eea;
        }

        .wish-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: 600;
        }

        .status-correct {
            background: #d4edda;
            color: #155724;
        }

        .status-incorrect {
            background: #f8d7da;
            color: #721c24;
        }

        .wish-text {
            font-size: 1.1em;
            line-height: 1.6;
            color: #333;
            margin-bottom: 15px;
        }

        .wish-meta {
            font-size: 0.9em;
            color: #666;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .no-wishes {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 40px;
        }

        .stats {
            background: #e9ecef;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }

        .stats h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .stat-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Let-wish - Submitted Wishes</h1>
            <p>Development view of all submitted wishes</p>
        </div>

        <a href="index.html" class="back-link">← Back to Wish Submission</a>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($wishes)): ?>
            <div class="stats">
                <h3>Statistics</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($wishes); ?></div>
                        <div class="stat-label">Total Wishes</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count(array_filter($wishes, fn($w) => $w['is_correct'])); ?></div>
                        <div class="stat-label">Correct Wishes</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count(array_filter($wishes, fn($w) => !$w['is_correct'])); ?></div>
                        <div class="stat-label">Incorrect Wishes</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($wishes)): ?>
            <div class="no-wishes">
                <h3>No wishes submitted yet</h3>
                <p>Submit your first wish to see it here!</p>
            </div>
        <?php else: ?>
            <?php foreach ($wishes as $wish): ?>
                <div class="wish-card">
                    <div class="wish-header">
                        <span class="wish-id">Wish #<?php echo htmlspecialchars($wish['id']); ?></span>
                        <span class="wish-status <?php echo $wish['is_correct'] ? 'status-correct' : 'status-incorrect'; ?>">
                            <?php echo $wish['is_correct'] ? 'Correct' : 'Incorrect'; ?>
                        </span>
                    </div>
                    <div class="wish-text">
                        <?php echo htmlspecialchars($wish['wish_text']); ?>
                    </div>
                    <div class="wish-meta">
                        <strong>Character count:</strong> <?php echo strlen($wish['wish_text']); ?> |
                        <strong>Submitted:</strong> <?php echo htmlspecialchars($wish['created_at']); ?>
                        <strong style="float: right;">
                            <a href="split_to_goals.php?id=<?php echo $wish['id']; ?>">Split</a> |
                            <a href="view_decomposition.php?id=<?php echo $wish['id']; ?>">View</a>
                        </strong> 
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html> 