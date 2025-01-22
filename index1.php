<?php
// Database connection parameters from environment variables for security
$servername = getenv('POSTGRES_HOST') ?: 'localhost'; // Default to localhost if not set
$username = getenv('POSTGRES_USER') ?: '';      // Use environment variable or empty string
$password = getenv('POSTGRES_PASSWORD') ?: '';  // Use environment variable or empty string
$dbname = getenv('POSTGRES_DB') ?: '';          // Use environment variable or empty string

// Maximum number of retries before giving up
$maxRetries = 10;
$retryInterval = 5; // seconds
$currentRetry = 0;

// Function to attempt a connection to PostgreSQL using pg_connect
function connectToDatabase($servername, $username, $password, $dbname)
{
    $connectionString = "host=$servername dbname=$dbname user=$username password=$password";
    return pg_connect($connectionString);
}

// Handle /status endpoint
if ($_SERVER['REQUEST_URI'] == '/status') {
    header('Content-Type: application/json');
    while ($currentRetry < $maxRetries) {
        $conn = connectToDatabase($servername, $username, $password, $dbname);
        if ($conn) {
            // Connection successful
            http_response_code(200);
            echo json_encode(["message" => "Sve radi!"]);
            pg_close($conn);
            exit;
        } else {
            // Retry logic
            error_log("Attempt $currentRetry: Connection failed: " . pg_last_error());
            sleep($retryInterval);
            $currentRetry++;
        }
    }

    // Failed after retries
    http_response_code(500);
    echo json_encode(["error" => "Ne mogu se povezati s bazom."]);
    exit;
}

// Handle / endpoint (fetching current time with timezone adjustment)
header('Content-Type: text/html');
$conn = connectToDatabase($servername, $username, $password, $dbname);

if ($conn) {
    // Set timezone and fetch current time
    $timezoneQuery = "SET TIMEZONE TO 'Europe/Zagreb'";
    $timeQuery = "SELECT NOW() AS current_time";

    $timezoneResult = pg_query($conn, $timezoneQuery); // Set timezone
    if ($timezoneResult) {
        $queryResult = pg_query($conn, $timeQuery);

        if ($queryResult) {
            // Fetch and display the result
            $row = pg_fetch_assoc($queryResult);
            echo "<h2>Current time from Database:</h2>";
            echo "<p>" . htmlspecialchars($row['current_time']) . "</p>";
        } else {
            error_log("Error executing time query: " . pg_last_error($conn));
            echo "Error executing query: " . htmlspecialchars(pg_last_error($conn));
        }
    } else {
        error_log("Error setting timezone: " . pg_last_error($conn));
        echo "Error setting timezone: " . htmlspecialchars(pg_last_error($conn));
    }

    pg_close($conn);
} else {
    http_response_code(500);
    echo "<h2>Connection failed!</h2>";
    error_log("Failed to connect to database: " . pg_last_error());
}
?>
