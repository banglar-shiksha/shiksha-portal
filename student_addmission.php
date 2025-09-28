<?php 
session_start();

// --- Security Check ---
// Redirect to login if not logged in or not an 'other' stakeholder.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'other') {
    header("location: login.php");
    exit;
}

// In a real application, you would include your database connection here.
// require_once 'db_connect.php'; 

$error_message = "";
$success_message = "";

// --- FORM PROCESSING LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // For this backend example, we'll just confirm the submission.
    // In a real application, you would sanitize all inputs and save them to the database.
    $student_name = trim($_POST['student_name'] ?? '');

    if (!empty($student_name)) {
        // Set a success message in the session and redirect back to the dashboard.
        $_SESSION['success_message'] = "Admission request for '{$student_name}' has been submitted successfully.";
        header("location: dashboard.php");
        exit;
    } else {
        $error_message = "Error: Student's Full Name is a required field.";
    }
}

$page_title = "Student Admission";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Banglar Shiksha</title>
    <link rel="shortcut icon" href="https://banglarshiksha.wb.gov.in/assets/admin/images/favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; }
        .form-container { max-width: 900px; margin: 2rem auto; }
        .content-card { background-color: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        .mandatory { color: #dc3545; font-weight: bold; margin-left: 2px; }
    </style>
</head>
<body>
    <div class="container form-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
             <h2 class="mb-0">New Student Admission</h2>
             <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
        </div>
       
        <div class="content-card">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="studentAdmissionForm" enctype="multipart/form-data">
                <p class="text-muted">Fields marked with <span class="mandatory">*</span> are required.</p>
                
                <h4 class="mt-4 border-bottom pb-2">Personal Details</h4>
                <div class="row g-3 mt-2">
                    <div class="col-md-6"><label class="form-label">Full Name <span class="mandatory">*</span></label><input type="text" name="student_name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Date of Birth <span class="mandatory">*</span></label><input type="date" name="dob" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Gender <span class="mandatory">*</span></label><select name="gender" class="form-select" required><option value="">Select Gender</option><option value="Male">Male</option><option value="Female">Female</option><option value="Other">Other</option></select></div>
                    <div class="col-md-6"><label class="form-label">Aadhaar Number</label><input type="text" name="aadhaar" class="form-control"></div>
                </div>

                <h4 class="mt-4 border-bottom pb-2">Guardian & Address</h4>
                <div class="row g-3 mt-2">
                    <div class="col-md-6"><label class="form-label">Father's Name <span class="mandatory">*</span></label><input type="text" name="father_name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Mother's Name <span class="mandatory">*</span></label><input type="text" name="mother_name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Contact Number <span class="mandatory">*</span></label><input type="tel" name="contact_number" class="form-control" required></div>
                    <div class="col-12"><label class="form-label">Full Address <span class="mandatory">*</span></label><textarea name="address" rows="3" class="form-control" required></textarea></div>
                </div>
                
                <h4 class="mt-4 border-bottom pb-2">Academic Details</h4>
                 <div class="row g-3 mt-2">
                    <div class="col-md-6"><label class="form-label">Admission Class <span class="mandatory">*</span></label><input type="text" name="admission_class" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Previous School Name (if any)</label><input type="text" name="previous_school" class="form-control"></div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Submit Admission</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
