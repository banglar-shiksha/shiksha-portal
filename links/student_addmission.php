<?php 
// Start the session at the very beginning.
session_start();

// --- Security Check ---
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: /banglar-shiksha/login.php");
    exit;
}

// Define BASE_URL for robust pathing
define('BASE_URL', '/banglar-shiksha/');

// Include the database connection file.
require_once __DIR__ . '/../db_connect.php'; 

$success_message = '';
$error_message = '';

// Verify the database connection was successful before proceeding.
if (!isset($conn) || $conn->connect_error) {
    $error_message = "Error: Could not connect to the database. Please contact the administrator.";
} 
else if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- NEW STUDENT ID GENERATION LOGIC ---
    function generateNewStudentID($db_conn) {
        // IMPORTANT: Change this prefix to your school's unique code.
        $school_prefix = '07996820'; 
        $sequence_length = 6;

        // Find the highest existing student ID with the same prefix
        $sql = "SELECT MAX(student_id) AS last_id FROM students WHERE student_id LIKE ?";
        $stmt = $db_conn->prepare($sql);
        $prefix_search = $school_prefix . '%';
        $stmt->bind_param("s", $prefix_search);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $last_id = $result['last_id'];

        $new_sequence_num = 1; // Default for the first student
        if ($last_id !== null) {
            // Extract the sequence part from the last ID, convert to number, and increment
            $last_sequence_part = substr($last_id, strlen($school_prefix));
            $new_sequence_num = intval($last_sequence_part) + 1;
        }

        // Pad the new sequence number with leading zeros
        $new_sequence_part = str_pad($new_sequence_num, $sequence_length, '0', STR_PAD_LEFT);

        return $school_prefix . $new_sequence_part;
    }

    // Helper function to process file uploads securely
    function handle_upload($file_input_name, $student_id) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . BASE_URL . 'uploads/';
        if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
            $file_extension = pathinfo($_FILES[$file_input_name]['name'], PATHINFO_EXTENSION);
            $new_file_name = $student_id . '_' . $file_input_name . '_' . time() . '.' . $file_extension;
            $dest_path = $upload_dir . $new_file_name;
            if(move_uploaded_file($_FILES[$file_input_name]['tmp_name'], $dest_path)) {
                return BASE_URL . 'uploads/' . $new_file_name;
            }
        }
        return null;
    }
    
    // Generate the new sequential Student ID
    $student_id = generateNewStudentID($conn);

    // Collect all POST data
    $student_name = $_POST['student_name'] ?? '';
    $dob = $_POST['dob'] ?? '';
    // ... (all other $_POST variables are collected here, same as your original file)
    $gender = $_POST['gender'] ?? '';
    $aadhaar = $_POST['aadhaar'] ?? null;
    $mother_tongue = $_POST['mother_tongue'] ?? '';
    $religion = $_POST['religion'] ?? '';
    $caste = $_POST['caste'] ?? '';
    $father_name = $_POST['father_name'] ?? '';
    $father_occupation = $_POST['father_occupation'] ?? null;
    $mother_name = $_POST['mother_name'] ?? '';
    $mother_occupation = $_POST['mother_occupation'] ?? null;
    $contact_number = $_POST['contact_number'] ?? '';
    $annual_income = !empty($_POST['annual_income']) ? $_POST['annual_income'] : null;
    $address = $_POST['address'] ?? '';
    $admission_class = $_POST['admission_class'] ?? '';
    $previous_school = $_POST['previous_school'] ?? null;
    $previous_school_udise = $_POST['previous_school_udise'] ?? null;
    $tc_number = $_POST['tc_number'] ?? null;
    $last_class = $_POST['last_class'] ?? null;
    $blood_group = $_POST['blood_group'] ?? null;
    $height_cm = !empty($_POST['height_cm']) ? $_POST['height_cm'] : null;
    $weight_kg = !empty($_POST['weight_kg']) ? $_POST['weight_kg'] : null;
    $medical_conditions = $_POST['medical_conditions'] ?? null;
    $bank_name = $_POST['bank_name'] ?? null;
    $account_number = $_POST['account_number'] ?? null;
    $ifsc_code = $_POST['ifsc_code'] ?? null;

    // Process File Uploads
    $student_photo_path = handle_upload('student_photo', $student_id);
    $birth_cert_path = handle_upload('birth_cert', $student_id);
    $aadhaar_card_path = handle_upload('aadhaar_card', $student_id);
    $tc_file_path = handle_upload('tc_file', $student_id);

    // Database Insertion
    $sql = "INSERT INTO students (
                student_id, full_name, dob, gender, aadhaar_number, mother_tongue, religion, caste_category,
                father_name, father_occupation, mother_name, mother_occupation, contact_number, annual_income, full_address,
                current_class, previous_school_name, previous_school_udise, tc_number, last_class_attended,
                blood_group, height_cm, weight_kg, medical_conditions,
                bank_name, account_number, ifsc_code,
                student_photo_path, birth_cert_path, aadhaar_card_path, tc_file_path
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        // The bind_param string should match your columns exactly
        $stmt->bind_param("sssssssssssssissssssiiissssssss",
            $student_id, $student_name, $dob, $gender, $aadhaar, $mother_tongue, $religion, $caste,
            $father_name, $father_occupation, $mother_name, $mother_occupation, $contact_number, $annual_income, $address,
            $admission_class, $previous_school, $previous_school_udise, $tc_number, $last_class,
            $blood_group, $height_cm, $weight_kg, $medical_conditions,
            $bank_name, $account_number, $ifsc_code,
            $student_photo_path, $birth_cert_path, $aadhaar_card_path, $tc_file_path
        );
        
        if ($stmt->execute()) {
            $success_message = "Student admitted successfully! The new Student ID is: " . htmlspecialchars($student_id);
        } else {
            $error_message = "Error: Could not execute the query. " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Error: Could not prepare the query. " . $conn->error;
    }
    $conn->close();
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --bs-primary-rgb: 0,123,255; --bs-body-bg: #f4f7fc; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bs-body-bg); }
        .sidebar { width: 260px; position: fixed; top: 0; left: 0; height: 100vh; background-color: #fff; border-right: 1px solid #e0e0e0; z-index: 100; }
        .main-content { margin-left: 260px; }
        .sidebar-nav .nav-link { color: #6c757d; border-left: 4px solid transparent; }
        .sidebar-nav .nav-link:hover, .sidebar-nav .nav-link.active { color: var(--bs-primary); background-color: #e9f3ff; border-left-color: var(--bs-primary); }
        .sidebar-nav .nav-link i { width: 20px; text-align: center; }
        .submenu { padding-left: 20px; }
        .submenu .nav-link { font-size: 0.9rem; }
        .form-progress-bar { position: relative; display: flex; justify-content: space-between; margin-bottom: 2.5rem; }
        .form-progress-bar::before { content: ''; position: absolute; top: 50%; left: 0; transform: translateY(-50%); width: 100%; height: 4px; background-color: #e9ecef; z-index: 1; }
        .progress-line { position: absolute; top: 50%; left: 0; transform: translateY(-50%); width: 0%; height: 4px; background-color: var(--bs-primary); z-index: 2; transition: width 0.4s ease; }
        .progress-step { z-index: 3; text-align: center; }
        .progress-step .step-icon { width: 30px; height: 30px; border-radius: 50%; background-color: #e9ecef; color: #6c757d; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 3px solid #e9ecef; transition: all 0.4s ease; margin: 0 auto; }
        .progress-step.active .step-icon { background-color: var(--bs-primary); color: #fff; border-color: var(--bs-primary); }
        .progress-step .step-label { margin-top: 0.5rem; font-size: 0.85rem; color: #6c757d; }
        .progress-step.active .step-label { color: var(--bs-primary); }
        .form-step { display: none; animation: fadeIn 0.5s; }
        .form-step.active { display: block; }
        .mandatory { color: var(--bs-danger); }
        .image-preview { width: 100%; height: 150px; border: 2px dashed #ccc; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; background-color: #f8f9fa; position: relative; overflow: hidden; }
        .image-preview.invalid { border-color: var(--bs-danger); }
        .image-preview img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>
    <div class="d-flex">
        <aside class="sidebar d-flex flex-column p-3">
            <div class="text-center py-3 border-bottom">
                <img src="<?php echo BASE_URL; ?>assets/img/banglar-shiksha-logo.png" alt="Banglar Shiksha Logo" style="max-width: 150px;">
                <h3 class="h5 mt-2 text-primary">Teacher Panel</h3>
            </div>
            <nav class="sidebar-nav nav nav-pills flex-column mt-3">
                <a class="nav-link d-flex align-items-center" href="<?php echo BASE_URL; ?>Teacher_dashboard.php"><i class="fas fa-tachometer-alt me-3"></i> Dashboard</a>
                <a class="nav-link d-flex align-items-center active" data-bs-toggle="collapse" href="#studentSubmenu"><i class="fas fa-users me-3"></i> Student Management</a>
                <div class="collapse show" id="studentSubmenu">
                    <div class="submenu nav flex-column">
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>links/student_addmission.php"><i class="fas fa-user-plus me-2"></i> Student Admission</a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>links/rectify_student_details.php"><i class="fas fa-user-edit me-2"></i> Rectify Details</a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>links/view-student.php"><i class="fas fa-list-ul me-2"></i> View Student List</a>
                    </div>
                </div>
                 <a class="nav-link d-flex align-items-center" href="#"><i class="fas fa-calendar-check me-3"></i> Attendance</a>
            </nav>
        </aside>

        <main class="main-content flex-grow-1">
            <header class="bg-white p-3 d-flex justify-content-between align-items-center border-bottom">
                <div>
                    <h1 class="h4 mb-0"><?php echo $page_title; ?></h1>
                    <p class="text-muted small mb-0">Siliguri, West Bengal | <span id="current-time"></span></p>
                </div>
                <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-primary"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </header>

            <section class="p-4">
                <div class="container-fluid">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <div class="form-progress-bar">
                                <div class="progress-line"></div>
                                <div class="progress-step active" data-step="1"><div class="step-icon">1</div><p class="step-label">Personal</p></div>
                                <div class="progress-step" data-step="2"><div class="step-icon">2</div><p class="step-label">Guardian</p></div>
                                <div class="progress-step" data-step="3"><div class="step-icon">3</div><p class="step-label">Academics</p></div>
                                <div class="progress-step" data-step="4"><div class="step-icon">4</div><p class="step-label">Health/Bank</p></div>
                                <div class="progress-step" data-step="5"><div class="step-icon">5</div><p class="step-label">Documents</p></div>
                            </div>

                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success text-center"><?php echo $success_message; ?></div>
                            <?php endif; ?>
                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger text-center"><?php echo $error_message; ?></div>
                            <?php endif; ?>

                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="studentAdmissionForm" enctype="multipart/form-data">
                                <p class="text-center text-muted mb-4">Fields marked with <span class="mandatory">*</span> are required.</p>
                                
                                <div class="form-step active" data-step="1">
                                    <h3 class="mb-4 border-bottom pb-2">Student's Personal Details</h3>
                                    <div class="row g-3">
                                        <div class="col-md-4"><label class="form-label">Full Name <span class="mandatory">*</span></label><input type="text" name="student_name" class="form-control" required></div>
                                        <div class="col-md-4"><label class="form-label">Date of Birth <span class="mandatory">*</span></label><input type="date" name="dob" class="form-control" required></div>
                                        <div class="col-md-4"><label class="form-label">Gender <span class="mandatory">*</span></label><select name="gender" class="form-select" required><option value="">Select...</option><option>Male</option><option>Female</option><option>Other</option></select></div>
                                        <div class="col-md-3"><label class="form-label">Aadhaar Number</label><input type="text" name="aadhaar" class="form-control"></div>
                                        <div class="col-md-3"><label class="form-label">Mother Tongue <span class="mandatory">*</span></label><input type="text" name="mother_tongue" class="form-control" required></div>
                                        <div class="col-md-3"><label class="form-label">Religion <span class="mandatory">*</span></label><input type="text" name="religion" class="form-control" required></div>
                                        <div class="col-md-3"><label class="form-label">Caste Category <span class="mandatory">*</span></label><select name="caste" class="form-select" required><option value="">Select...</option><option>General</option><option>SC</option><option>ST</option><option>OBC</option></select></div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="button" class="btn btn-primary btn-next">Next <i class="fas fa-arrow-right ms-2"></i></button>
                                    </div>
                                </div>

                                <div class="form-step" data-step="2">
                                    <h3 class="mb-4 border-bottom pb-2">Guardian & Address Details</h3>
                                    <div class="row g-3">
                                        <div class="col-md-6"><label class="form-label">Father's Name <span class="mandatory">*</span></label><input type="text" name="father_name" class="form-control" required></div>
                                        <div class="col-md-6"><label class="form-label">Father's Occupation</label><input type="text" name="father_occupation" class="form-control"></div>
                                        <div class="col-md-6"><label class="form-label">Mother's Name <span class="mandatory">*</span></label><input type="text" name="mother_name" class="form-control" required></div>
                                        <div class="col-md-6"><label class="form-label">Mother's Occupation</label><input type="text" name="mother_occupation" class="form-control"></div>
                                        <div class="col-md-6"><label class="form-label">Contact Number <span class="mandatory">*</span></label><input type="tel" name="contact_number" class="form-control" required></div>
                                        <div class="col-md-6"><label class="form-label">Annual Family Income</label><input type="number" name="annual_income" class="form-control"></div>
                                        <div class="col-12"><label class="form-label">Correspondence Address <span class="mandatory">*</span></label><textarea name="address" rows="3" class="form-control" required></textarea></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary btn-prev"><i class="fas fa-arrow-left me-2"></i> Previous</button>
                                        <button type="button" class="btn btn-primary btn-next">Next <i class="fas fa-arrow-right ms-2"></i></button>
                                    </div>
                                </div>
                                
                                <div class="form-step" data-step="3">
                                   <h3 class="mb-4 border-bottom pb-2">Academic History</h3>
                                   <div class="row g-3">
                                        <div class="col-md-12"><label class="form-label">Admission Class <span class="mandatory">*</span></label><select id="admission-class" name="admission_class" class="form-select" required><option value="">Select Class</option><?php for ($i = 1; $i <= 12; $i++) echo "<option value='Class $i'>Class $i</option>"; ?></select></div>
                                        <div class="col-md-6"><label id="label-previous-school" class="form-label">Previous School Name</label><input type="text" id="previous-school" name="previous_school" class="form-control"></div>
                                        <div class="col-md-6"><label id="label-previous-school-udise" class="form-label">Previous School UDISE</label><input type="text" id="previous-school-udise" name="previous_school_udise" class="form-control"></div>
                                        <div class="col-md-6"><label id="label-tc-number" class="form-label">Transfer Certificate No.</label><input type="text" id="tc-number" name="tc_number" class="form-control"></div>
                                        <div class="col-md-6"><label id="label-last-class" class="form-label">Last Class Attended</label><input type="text" id="last-class" name="last_class" class="form-control"></div>
                                   </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary btn-prev"><i class="fas fa-arrow-left me-2"></i> Previous</button>
                                        <button type="button" class="btn btn-primary btn-next">Next <i class="fas fa-arrow-right ms-2"></i></button>
                                    </div>
                                </div>

                                <div class="form-step" data-step="4">
                                    <h3 class="mb-4 border-bottom pb-2">Health & Bank Information</h3>
                                    <div class="row g-3">
                                        <div class="col-md-4"><label class="form-label">Blood Group</label><input type="text" name="blood_group" class="form-control"></div>
                                        <div class="col-md-4"><label class="form-label">Height (cm)</label><input type="number" name="height_cm" class="form-control"></div>
                                        <div class="col-md-4"><label class="form-label">Weight (kg)</label><input type="number" name="weight_kg" class="form-control"></div>
                                        <div class="col-12"><label class="form-label">Known Allergies / Medical Conditions</label><textarea name="medical_conditions" rows="2" class="form-control"></textarea></div>
                                    </div>
                                    <hr class="my-4">
                                    <h4 class="h5">Bank Details <small class="text-muted">(for scholarships)</small></h4>
                                    <div class="row g-3 mt-2">
                                        <div class="col-md-4"><label class="form-label">Bank Name</label><input type="text" name="bank_name" class="form-control"></div>
                                        <div class="col-md-4"><label class="form-label">Account Number</label><input type="text" name="account_number" class="form-control"></div>
                                        <div class="col-md-4"><label class="form-label">IFSC Code</label><input type="text" name="ifsc_code" class="form-control"></div>
                                    </div>
                                     <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary btn-prev"><i class="fas fa-arrow-left me-2"></i> Previous</button>
                                        <button type="button" class="btn btn-primary btn-next">Next <i class="fas fa-arrow-right ms-2"></i></button>
                                    </div>
                                </div>
                                
                                <div class="form-step" data-step="5">
                                    <h3 class="mb-4 border-bottom pb-2">Upload Documents</h3>
                                    <div class="row g-3 text-center">
                                        <div class="col-md-3">
                                            <label class="form-label">Student Photo <span class="mandatory">*</span></label>
                                            <input type="file" id="student-photo" name="student_photo" class="d-none" accept="image/*" required>
                                            <div class="image-preview" data-for="student-photo"><i class="fas fa-cloud-upload-alt fs-1 text-muted"></i><p class="small text-muted mt-2">Click to upload</p></div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Birth Certificate <span class="mandatory">*</span></label>
                                            <input type="file" id="birth-cert" name="birth_cert" class="d-none" accept="image/*,application/pdf" required>
                                            <div class="image-preview" data-for="birth-cert"><i class="fas fa-cloud-upload-alt fs-1 text-muted"></i><p class="small text-muted mt-2">Click to upload</p></div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Aadhaar Card</label>
                                            <input type="file" id="aadhaar-card" name="aadhaar_card" class="d-none" accept="image/*,application/pdf">
                                            <div class="image-preview" data-for="aadhaar-card"><i class="fas fa-cloud-upload-alt fs-1 text-muted"></i><p class="small text-muted mt-2">Click to upload</p></div>
                                        </div>
                                        <div class="col-md-3">
                                            <label id="label-tc-file" class="form-label">Transfer Certificate</label>
                                            <input type="file" id="tc-file" name="tc_file" class="d-none" accept="image/*,application/pdf">
                                            <div class="image-preview" data-for="tc-file"><i class="fas fa-cloud-upload-alt fs-1 text-muted"></i><p class="small text-muted mt-2">Click to upload</p></div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary btn-prev"><i class="fas fa-arrow-left me-2"></i> Previous</button>
                                        <button type="button" id="finalSubmitBtn" class="btn btn-success btn-lg">Submit Admission <i class="fas fa-check ms-2"></i></button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Re-using your robust JS logic with minor tweaks for Bootstrap ---
            const studentForm = document.getElementById('studentAdmissionForm');
            const finalSubmitBtn = document.getElementById('finalSubmitBtn');
            const nextButtons = document.querySelectorAll('.btn-next');
            const prevButtons = document.querySelectorAll('.btn-prev');
            const formSteps = document.querySelectorAll('.form-step');
            const progressSteps = document.querySelectorAll('.progress-step');
            const progressLine = document.querySelector('.progress-line');
            const admissionClassSelect = document.getElementById('admission-class');
            const timeElement = document.getElementById('current-time');
            let currentStep = 1;
            
            const conditionalFields = [
                { input: document.getElementById('previous-school'), label: document.getElementById('label-previous-school') },
                { input: document.getElementById('previous-school-udise'), label: document.getElementById('label-previous-school-udise') },
                { input: document.getElementById('tc-number'), label: document.getElementById('label-tc-number') },
                { input: document.getElementById('last-class'), label: document.getElementById('label-last-class') },
                { input: document.getElementById('tc-file'), label: document.getElementById('label-tc-file') }
            ];

            nextButtons.forEach(button => {
                button.addEventListener('click', () => {
                    if (validateStep(currentStep)) {
                        if (currentStep < formSteps.length) { currentStep++; updateForm(); }
                    }
                });
            });

            prevButtons.forEach(button => {
                button.addEventListener('click', () => {
                    if (currentStep > 1) { currentStep--; updateForm(); }
                });
            });

            finalSubmitBtn.addEventListener('click', function() {
                if (validateStep(currentStep)) { studentForm.submit(); }
            });

            if (admissionClassSelect) {
                admissionClassSelect.addEventListener('change', handleConditionalFields);
            }
            
            function handleConditionalFields() {
                const isClassOne = admissionClassSelect.value === 'Class 1';
                conditionalFields.forEach(field => {
                    if (field.input && field.label) {
                        const mandatorySpan = field.label.querySelector('.mandatory');
                        if (isClassOne) {
                            field.input.removeAttribute('required');
                            if (mandatorySpan) mandatorySpan.remove();
                        } else {
                            field.input.setAttribute('required', 'required');
                            if (!mandatorySpan) { field.label.insertAdjacentHTML('beforeend', ' <span class="mandatory">*</span>'); }
                        }
                    }
                });
            }

            function validateStep(stepNumber) {
                let isValid = true;
                const currentFormStep = document.querySelector(`.form-step[data-step="${stepNumber}"]`);
                currentFormStep.querySelectorAll('[required]').forEach(input => {
                    input.classList.remove('is-invalid');
                    const previewBox = document.querySelector(`.image-preview[data-for="${input.id}"]`);
                    if(previewBox) previewBox.classList.remove('invalid');
                    
                    let isInputInvalid = false;
                    if (input.type === 'file' && input.files.length === 0) isInputInvalid = true;
                    else if (input.type !== 'file' && !input.value.trim()) isInputInvalid = true;

                    if(isInputInvalid) {
                        isValid = false;
                        if (input.type === 'file') {
                            if(previewBox) previewBox.classList.add('invalid');
                        } else {
                            input.classList.add('is-invalid');
                        }
                    }
                });
                if (!isValid) alert('Please fill out all mandatory fields (marked with *) before proceeding.');
                return isValid;
            }

            function updateForm() {
                formSteps.forEach(step => step.classList.remove('active'));
                document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.add('active');
                progressSteps.forEach((step, index) => {
                    step.classList.toggle('active', index + 1 <= currentStep);
                });
                const progressPercentage = ((currentStep - 1) / (progressSteps.length - 1)) * 100;
                progressLine.style.width = `${progressPercentage}%`;
            }

            document.querySelectorAll('.image-preview').forEach(preview => {
                preview.addEventListener('click', () => {
                    document.getElementById(preview.getAttribute('data-for')).click();
                });
            });

            document.querySelectorAll('input[type="file"]').forEach(input => {
                input.addEventListener('change', function() {
                    const previewContainer = document.querySelector(`.image-preview[data-for="${this.id}"]`);
                    if(previewContainer) previewContainer.classList.remove('invalid');
                    if (this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = e => {
                            previewContainer.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                        }
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            });

            function updateTime() {
                if(timeElement) { timeElement.textContent = new Date().toLocaleString('en-IN', { dateStyle: 'full', timeStyle: 'medium' }); }
            }
            updateTime();
            setInterval(updateTime, 1000);
            if (admissionClassSelect) handleConditionalFields();
        });
    </script>
</body>
</html>