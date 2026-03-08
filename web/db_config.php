<?php
/**
 * db_config.php
 * Database configuration for FragilityMetrics.org
 * 
 * IMPORTANT: Update these values with your actual database credentials
 * You can find these in your cPanel under "MySQL Databases"
 */

// Database credentials - UPDATE THESE!
define('DB_HOST', 'localhost');  // Usually 'localhost' on shared hosting
define('DB_NAME', 'fragilitymetrics');  // Your database name
define('DB_USER', 'evxlxzsuiljd');  // Your database username
define('DB_PASS', 'o3%bv16SVVHz9qsttkWK');  // Your database password
define('DB_CHARSET', 'utf8mb4');

/**
 * Get database connection
 * 
 * @return PDO Database connection
 * @throws PDOException if connection fails
 */
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, log this error instead of displaying it
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $pdo;
}
?>
