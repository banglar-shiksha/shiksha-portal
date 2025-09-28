<?php
// --- db_connect.php using mysqli ---

// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); // Default is an empty password
define('DB_NAME', 'banglar_shiksha_db'); // Your database name

// Attempt to connect to MySQL database using mysqli
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// The connection check is now handled in your student_addmission.php file,
// so we don't need to put a die() or exit() statement here.
// If $conn->connect_error exists, the main script will catch it.

?>