<?php
echo "Let-wish Setup Script\n";
echo "====================\n\n";

// Check PHP version
echo "1. Checking PHP version...\n";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "✓ PHP version " . PHP_VERSION . " is compatible\n";
} else {
    echo "✗ PHP version " . PHP_VERSION . " is too old. Please upgrade to PHP 7.4 or higher\n";
    exit(1);
}

// Check PDO SQLite extension
echo "\n2. Checking PDO SQLite extension...\n";
if (extension_loaded('pdo_sqlite')) {
    echo "✓ PDO SQLite extension is available\n";
} else {
    echo "✗ PDO SQLite extension is not available. Please install it\n";
    exit(1);
}

// Check if we can write to the current directory
echo "\n3. Checking write permissions...\n";
if (is_writable('.')) {
    echo "✓ Current directory is writable\n";
} else {
    echo "✗ Current directory is not writable. Please check permissions\n";
    exit(1);
}

// Initialize database
echo "\n4. Initializing database...\n";
try {
    $dbPath = 'wishes.db';
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create wishes table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS wishes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            wish_text TEXT NOT NULL,
            is_correct BOOLEAN DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    echo "✓ Database initialized successfully\n";
    echo "✓ Wishes table created\n";
    
    // Check if table was created
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='wishes'");
    if ($stmt->fetch()) {
        echo "✓ Table verification successful\n";
    } else {
        echo "✗ Table creation failed\n";
        exit(1);
    }
    
} catch (PDOException $e) {
    echo "✗ Database initialization failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test database operations
echo "\n5. Testing database operations...\n";
try {
    // Test insert
    $stmt = $pdo->prepare("INSERT INTO wishes (wish_text, is_correct) VALUES (:wish_text, :is_correct)");
    $testWish = "This is a test wish to verify the database is working correctly. It should be long enough to pass validation.";
    $stmt->execute([
        ':wish_text' => $testWish,
        ':is_correct' => strlen($testWish) >= 150 ? 1 : 0
    ]);
    
    // Test select
    $stmt = $pdo->query("SELECT COUNT(*) FROM wishes");
    $count = $stmt->fetchColumn();
    
    echo "✓ Database operations test successful\n";
    echo "✓ Test wish inserted (ID: " . $pdo->lastInsertId() . ")\n";
    echo "✓ Total wishes in database: $count\n";
    
    // Clean up test data
    $pdo->exec("DELETE FROM wishes WHERE wish_text LIKE '%test wish%'");
    echo "✓ Test data cleaned up\n";
    
} catch (PDOException $e) {
    echo "✗ Database operations test failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n6. Checking web server configuration...\n";
if (isset($_SERVER['SERVER_SOFTWARE'])) {
    echo "✓ Web server detected: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
} else {
    echo "⚠ Running in CLI mode\n";
}

echo "\n✅ Setup completed successfully!\n\n";
echo "Next steps:\n";
echo "1. Start your web server in this directory\n";
echo "2. Open http://localhost/ in your browser\n";
echo "3. Submit your first wish!\n";
echo "4. View submitted wishes at http://localhost/view_wishes.php\n\n";
echo "Files created:\n";
echo "- index.html (main wish submission page)\n";
echo "- submit_wish.php (wish processing backend)\n";
echo "- view_wishes.php (wish viewer for development)\n";
echo "- wishes.db (SQLite database)\n";
echo "- setup.php (this setup script)\n\n";
?> 