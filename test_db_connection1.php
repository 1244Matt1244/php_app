<?php
declare(strict_types=1);
header("Content-Type: text/plain");

// Validate essential environment variables first
$requiredEnv = ['POSTGRES_USER', 'POSTGRES_PASSWORD', 'POSTGRES_DB'];
foreach ($requiredEnv as $var) {
    if (!getenv($var)) {
        http_response_code(500);
        error_log("Missing required environment variable: $var");
        exit("Configuration error. Please contact support.");
    }
}

// Configure connection parameters
$config = [
    'host'     => getenv('POSTGRES_HOST') ?: 'localhost',
    'port'     => (int)(getenv('POSTGRES_PORT') ?: 5432),
    'dbname'   => getenv('POSTGRES_DB'),
    'user'     => getenv('POSTGRES_USER'),
    'password' => getenv('POSTGRES_PASSWORD'),
    'connect_timeout' => 5  // 5-second connection timeout
];

try {
    // Establish connection with error suppression and proper error handling
    $connection = @pg_connect(
        "host={$config['host']} 
         port={$config['port']} 
         dbname={$config['dbname']} 
         user={$config['user']} 
         password={$config['password']} 
         connect_timeout={$config['connect_timeout']}"
    );

    if (!$connection) {
        throw new RuntimeException(pg_last_error() ?: 'Unknown connection error');
    }

    // Verify connection with simple query
    $result = @pg_query($connection, 'SELECT 1');
    if (!$result) {
        throw new RuntimeException(pg_last_error($connection));
    }

    echo "Successfully connected to PostgreSQL database!\n";
    echo "Database version: " . pg_version($connection)['server'] . "\n";
    
    pg_free_result($result);
    pg_close($connection);

} catch (RuntimeException $e) {
    http_response_code(503);
    error_log("Database connection failed: " . $e->getMessage());
    exit("Service unavailable. Please try again later.");
}
?>
