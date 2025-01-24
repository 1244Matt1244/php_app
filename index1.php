<?php
// Enable strict error reporting
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Disable in production

// Database configuration with validation
$requiredEnvVars = ['POSTGRES_HOST', 'POSTGRES_USER', 'POSTGRES_PASSWORD', 'POSTGRES_DB'];
foreach ($requiredEnvVars as $var) {
    if (!getenv($var)) {
        http_response_code(500);
        error_log("Missing required environment variable: $var");
        exit(json_encode(['error' => 'Configuration error']));
    }
}

// Constants for connection parameters
define('MAX_RETRIES', 10);
define('RETRY_INTERVAL', 5); // seconds
define('TIMEZONE', 'Europe/Zagreb');

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Content-Security-Policy: default-src 'self'");

/**
 * Establishes a secure PostgreSQL connection with retry logic
 */
function connectToDatabase(): PgSql\Connection|false {
    static $retryCount = 0;
    
    $connection = pg_connect(
        sprintf("host=%s dbname=%s user=%s password=%s",
            getenv('POSTGRES_HOST'),
            getenv('POSTGRES_DB'),
            getenv('POSTGRES_USER'),
            getenv('POSTGRES_PASSWORD')
        ),
        PGSQL_CONNECT_FORCE_NEW
    );

    if (!$connection && $retryCount < MAX_RETRIES) {
        $retryCount++;
        error_log("Connection attempt $retryCount failed. Retrying...");
        sleep(RETRY_INTERVAL);
        return connectToDatabase();
    }

    return $connection;
}

// Route handling
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($requestPath === '/status') {
    handleStatusEndpoint();
} else {
    handleMainEndpoint();
}

/**
 * Handles /status endpoint with connection check
 */
function handleStatusEndpoint(): void {
    header('Content-Type: application/json');
    
    $conn = connectToDatabase();
    if ($conn) {
        http_response_code(200);
        echo json_encode([
            'status' => 'OK',
            'message' => 'Sve radi!',
            'timestamp' => time()
        ]);
        pg_close($conn);
        exit;
    }

    http_response_code(503);
    echo json_encode([
        'status' => 'ERROR',
        'error' => 'Ne mogu se povezati s bazom',
        'retries' => MAX_RETRIES
    ]);
    exit;
}

/**
 * Handles main endpoint with timezone-aware timestamp
 */
function handleMainEndpoint(): void {
    header('Content-Type: text/html; charset=UTF-8');
    
    $conn = connectToDatabase();
    if (!$conn) {
        http_response_code(500);
        echo "<h2>Greška u vezi s bazom podataka</h2>";
        exit;
    }

    try {
        // Set timezone and get current time in transaction
        pg_query($conn, "BEGIN");
        pg_query($conn, "SET TIMEZONE TO '" . TIMEZONE . "'");
        
        $result = pg_query($conn, "SELECT NOW() AS current_time");
        if (!$result) {
            throw new Exception(pg_last_error($conn));
        }

        pg_query($conn, "COMMIT");
        $row = pg_fetch_assoc($result);
        
        echo "<!DOCTYPE html>
            <html lang='hr'>
            <head>
                <meta charset='UTF-8'>
                <title>Vrijeme iz baze</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 2rem; }
                    .time { color: #2c3e50; font-size: 1.5rem; }
                </style>
            </head>
            <body>
                <h1>Aktualno vrijeme</h1>
                <div class='time'>" . htmlspecialchars($row['current_time']) . "</div>
            </body>
            </html>";
        
    } catch (Exception $e) {
        pg_query($conn, "ROLLBACK");
        error_log("Database error: " . $e->getMessage());
        http_response_code(500);
        echo "<h2>Greška u obradi zahtjeva</h2>";
    } finally {
        pg_close($conn);
    }
}
