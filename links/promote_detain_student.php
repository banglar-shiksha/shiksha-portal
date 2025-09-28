<?php
$page_title = "Promote / Detain Student";
include $_SERVER['DOCUMENT_ROOT'] . '/banglar-shiksha/links/teacher_header.php';

// ** FIX: Explicitly include the database connection file **
require_once __DIR__ . '/../db_connect.php';

// Initialize variables
$student_id = $_GET['id'] ?? '';
$student = null;
$error_message = '';
$success_message = '';
$current_class_number = 0;

// --- Handle Form Submission (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve POST data
    $student_id = $_POST['student_id'] ?? '';
    $action = $_POST['action'] ?? '';
    $new_class = $_POST['new_class'] ?? '';
    $remarks = trim($_POST['remarks'] ?? '');
    $session_year = date('Y');

    if (empty($student_id) || empty($action) || empty($new_class)) {
        $error_message = "Invalid data submitted. Please try again.";
    } else {
        // Use a transaction for data integrity
        $conn->begin_transaction();
        try {
            // 1. Update the student's current class in the main table
            $sql_update = "UPDATE students SET current_class = ? WHERE student_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param('ss', $new_class, $student_id);
            $stmt_update->execute();

            // 2. Log the action in an academic history table for record-keeping
            $sql_log = "INSERT INTO student_academic_history (student_id, session_year, action, result_class, remarks) VALUES (?, ?, ?, ?, ?)";
            $stmt_log = $conn->prepare($sql_log);
            $stmt_log->bind_param('sssss', $student_id, $session_year, $action, $new_class, $remarks);
            $stmt_log->execute();

            // If both queries succeed, commit the transaction
            $conn->commit();
            $success_message = "Student has been successfully " . htmlspecialchars($action) . "d to " . htmlspecialchars($new_class) . "!";

        } catch (mysqli_sql_exception $exception) {
            $conn->rollback(); // Roll back changes on error
            $error_message = "Database error: Could not process the request. " . $exception->getMessage();
        }
    }
}

// --- Fetch Student Data for Display (GET Request) ---
// We add a check to ensure $conn exists before using it
if ($conn && !empty($student_id) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $sql = "SELECT student_id, full_name, current_class FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();
        // Extract the number from the class string, e.g., "Class 5" -> 5
        preg_match('/\d+/', $student['current_class'], $matches);
        $current_class_number = isset($matches[0]) ? (int)$matches[0] : 0;
    } else {
        $error_message = "Student not found with the provided ID.";
    }
    $stmt->close();
} elseif (!$conn) {
    $error_message = "Database connection failed. Please check your configuration.";
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<section class="dashboard-overview p-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Promote or Detain Student</h5>
                    </div>
                    <div class="card-body p-4">

                        <?php if ($success_message): ?>
                            <div class="alert alert-success">
                                <?php echo $success_message; ?>
                                <a href="view-student.php" class="btn btn-sm btn-outline-success ms-3">Back to Student List</a>
                            </div>
                        <?php endif; ?>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <?php if ($student && !$success_message): ?>
                            <div class="card bg-light border mb-4">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($student['full_name']); ?></h5>
                                    <p class="card-text mb-0">
                                        <strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id']); ?><br>
                                        <strong>Current Class:</strong> <span id="currentClassLabel"><?php echo htmlspecialchars($student['current_class']); ?></span>
                                    </p>
                                </div>
                            </div>

                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo htmlspecialchars($student_id); ?>" method="POST">
                                <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Select Action</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="action" id="actionPromote" value="Promote" checked>
                                            <label class="form-check-label" for="actionPromote">Promote</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="action" id="actionDetain" value="Detain">
                                            <label class="form-check-label" for="actionDetain">Detain</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_class" class="form-label fw-bold">New Class</label>
                                    <select class="form-select" id="new_class" name="new_class">
                                        <?php 
                                            // Pre-populate for next 2 potential classes
                                            for ($i = $current_class_number; $i <= $current_class_number + 2 && $i <= 12; $i++) {
                                                echo "<option value='Class $i'>Class $i</option>"; 
                                            }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="remarks" class="form-label fw-bold">Remarks (Optional)</label>
                                    <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="e.g., Promoted based on excellent performance."></textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">Submit Action</button>
                                </div>
                            </form>
                        <?php elseif (!$success_message): ?>
                            <div class="text-center">
                                <p>Return to the <a href="view-student.php">student list</a> to select a student.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    // Store the original current class number from PHP
    const currentClassNumber = <?php echo $current_class_number; ?>;

    function updateNewClass() {
        const action = $('input[name="action"]:checked').val();
        let newClassNumber;

        if (action === 'Promote') {
            newClassNumber = currentClassNumber + 1;
        } else { // Detain
            newClassNumber = currentClassNumber;
        }
        
        // Don't promote beyond class 12
        if (newClassNumber > 12) {
            newClassNumber = 12;
        }

        $('#new_class').val('Class ' + newClassNumber);
    }

    // Listen for changes on the radio buttons
    $('input[name="action"]').on('change', function() {
        updateNewClass();
    });

    // Set the initial state when the page loads
    updateNewClass();
});
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/banglar-shiksha/links/teacher_footer.php'; ?>