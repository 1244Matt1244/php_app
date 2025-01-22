<?php
// Database connection parameters from environment variables or defaults
$host = getenv('POSTGRES_HOST') ?: 'localhost';   // Use environment variable or fallback to localhost
$port = getenv('POSTGRES_PORT') ?: '5432';        // Default PostgreSQL port
$dbname = getenv('POSTGRES_DB') ?: '';    // Use the environment variable POSTGRES_DB, fallback to an empty string
$user = getenv('POSTGRES_USER') ?: '';    // Use the environment variable POSTGRES_USER, fallback to an empty string
$password = getenv('POSTGRES_PASSWORD') ?: '';  // Use the environment variable POSTGRES_PASSWORD, fallback to an empty string

// Check if essential environment variables are set
if (empty($user) || empty($password) || empty($dbname)) {
    // Log and provide an error message if any essential variable is missing
    error_log("Missing essential database environment variables (user, password, dbname).");
    echo "Database connection details are missing. Please check the environment variables.";
    exit;
}

// Establish a connection to PostgreSQL
$connection_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
$dbconn = pg_connect($connection_string);

// Check if the connection was successful
if ($dbconn) {
    echo "Successfully connected to the PostgreSQL database!";
} else {
    // Log the error to the PHP error log and provide a user-friendly message
    error_log("Failed to connect to the PostgreSQL database: " . pg_last_error());
    
    // More generic error message for the user, avoiding disclosing sensitive information
    echo "Failed to connect to the PostgreSQL database. Please try again later or contact support.";
}

// Close the connection
pg_close($dbconn);
?>
