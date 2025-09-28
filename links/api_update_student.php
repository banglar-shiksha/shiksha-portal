<?php
// Set the content type to JSON for API responses
header('Content-Type: application/json');

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Include your database connection file
require_once __DIR__ . '/../db_connect.php';

// Check for database connection errors
if (!$conn || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Get data from the POST request
$student_id = $_POST['student_id'] ?? '';
$full_name = $_POST['full_name'] ?? '';
$dob = $_POST['dob'] ?? '';
$gender = $_POST['gender'] ?? '';
$contact_number = $_POST['contact_number'] ?? '';
$full_address = $_POST['full_address'] ?? '';

// Basic validation
if (empty($student_id) || empty($full_name) || empty($dob) || empty($gender) || empty($contact_number) || empty($full_address)) {
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
    exit;
}

// SQL query to update student details
$sql = "UPDATE students SET 
            full_name = ?, 
            dob = ?, 
            gender = ?, 
            contact_number = ?, 
            full_address = ? 
        WHERE student_id = ?";

$stmt = $conn->prepare($sql);

if ($stmt) {
    // Bind parameters: ssssss -> string, string, string, string, string, string
    $stmt->bind_param('ssssss', $full_name, $dob, $gender, $contact_number, $full_address, $student_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Student details updated successfully!']);
        } else {
            echo json_encode(['success' => true, 'message' => 'No changes were made.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to execute the update.']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare the update query.']);
}

$conn->close();
?>