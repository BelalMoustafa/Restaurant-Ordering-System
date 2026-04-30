<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'restaurant_db');
define('DB_USER', 'root');
define('DB_PASS', '');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    error_log('[DB Connection Error] ' . $conn->connect_error);
    die(
        '<div style="font-family:sans-serif;padding:40px;text-align:center;">'
      . '<h2>Service Unavailable</h2>'
      . '<p>We could not connect to the database. Please try again later.</p>'
      . '</div>'
    );
}

$conn->set_charset('utf8mb4');
