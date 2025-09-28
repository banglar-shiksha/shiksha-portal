<?php
// Set the content type to JSON for API responses
header('Content-Type: application/json');

// Include your database connection file
require_once __DIR__ . '/../db_connect.php';

// Check for database connection errors
if (!$conn || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Get search parameters from the GET request
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$class = isset($_GET['class']) ? trim($_GET['class']) : '';

// Base SQL query
$sql = "SELECT student_id, full_name, dob, gender, current_class, contact_number, full_address FROM students WHERE 1=1";

$params = [];
$types = '';

// Dynamically build the query based on provided filters
if (!empty($query)) {
    $sql .= " AND (full_name LIKE ? OR student_id LIKE ?)";
    $searchQuery = "%" . $query . "%";
    $params[] = $searchQuery;
    $params[] = $searchQuery;
    $types .= 'ss';
}

if (!empty($class)) {
    $sql .= " AND current_class = ?";
    $params[] = $class;
    $types .= 's';
}

$sql .= " ORDER BY full_name ASC LIMIT 100"; // Add a limit for performance

$stmt = $conn->prepare($sql);

if ($stmt) {
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    echo json_encode(['success' => true, 'students' => $students]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare the search query.']);
}

$conn->close();
?>