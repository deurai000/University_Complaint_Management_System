<?php

$host = "localhost";
$dbname = "university_complaint_system";
$username = "root";
$password = "";

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // Set PDO error mode
    $conn->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    // Fetch database results as associative arrays
    $conn->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {
    // Log the actual error; do not expose database details
    error_log($e->getMessage());
    die("Database connection failed. Please try again later.");
}

