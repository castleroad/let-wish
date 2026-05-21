<?php
echo "Let-wish Database Migration\n";
echo "==========================\n\n";

try {
    // Initialize SQLite database
    $dbPath = 'wishes.db';
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "1. Checking current database structure...\n";
    
    // Check if decomposition_result column exists
    $stmt = $pdo->query("PRAGMA table_info(wishes)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'name');
    
    $migrations = [];
    
    // Check for decomposition_result column
    if (!in_array('decomposition_result', $columnNames)) {
        $migrations[] = "ALTER TABLE wishes ADD COLUMN decomposition_result TEXT";
    }
    
    // Check for decomposed_at column
    if (!in_array('decomposed_at', $columnNames)) {
        $migrations[] = "ALTER TABLE wishes ADD COLUMN decomposed_at DATETIME";
    }
    
    // Check for goals_count column
    if (!in_array('goals_count', $columnNames)) {
        $migrations[] = "ALTER TABLE wishes ADD COLUMN goals_count INTEGER DEFAULT 0";
    }
    
    // Check for steps_count column
    if (!in_array('steps_count', $columnNames)) {
        $migrations[] = "ALTER TABLE wishes ADD COLUMN steps_count INTEGER DEFAULT 0";
    }
    
    if (empty($migrations)) {
        echo "✓ Database is already up to date. No migrations needed.\n";
    } else {
        echo "2. Applying migrations...\n";
        
        foreach ($migrations as $migration) {
            echo "   Running: $migration\n";
            $pdo->exec($migration);
            echo "   ✓ Success\n";
        }
        
        echo "\n✓ All migrations completed successfully!\n";
    }
    
    // Verify the final structure
    echo "\n3. Verifying database structure...\n";
    $stmt = $pdo->query("PRAGMA table_info(wishes)");
    $finalColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current table structure:\n";
    foreach ($finalColumns as $column) {
        echo "  - {$column['name']} ({$column['type']})\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    echo "\nThe database now supports:\n";
    echo "- Storing AI decomposition results\n";
    echo "- Tracking when wishes were decomposed\n";
    echo "- Counting goals and steps\n";
    
} catch (PDOException $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Unexpected error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

