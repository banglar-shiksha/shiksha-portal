<?php
session_start();

// --- Authentication and Authorization Check ---
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'other') {
    header("location: login.php");
    exit;
}

// --- Logout Logic ---
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    $_SESSION = array();
    session_destroy();
    header("location: login.php");
    exit;
}

// Get user information from the session. Assumes these are set during login.
$full_name = $_SESSION["full_name"] ?? 'Jyoti Sharma'; // Example Name
$user_email = $_SESSION["email"] ?? 'jyoti@gmail.com'; // Example Email
// Assuming 'designation' is stored in the session after signup/login.
$user_designation = $_SESSION["designation"] ?? 'Official Contributor'; // Example Designation

$message = '';
$message_type = '';

// --- Handle Form Submissions from this page (e.g., password change) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    if (empty($new_password) || empty($confirm_password)) {
        $message = "Please fill in both password fields.";
        $message_type = 'danger';
    } elseif ($new_password !== $confirm_password) {
        $message = "New passwords do not match.";
        $message_type = 'danger';
    } else {
        // In a real application, you would hash and update the password in the database for the logged-in user.
        $message = "Your password has been updated successfully.";
        $message_type = 'success';
    }
}

// --- Placeholder Data (to be replaced with database queries) ---
$student_data = [
    ['id' => 201, 'name' => 'Sunita Roy', 'class' => 'VII', 'roll' => 1, 'dob' => '2014-05-10', 'guardian' => 'Anil Roy'],
    ['id' => 202, 'name' => 'Bikash Ghosh', 'class' => 'VIII', 'roll' => 2, 'dob' => '2013-02-20', 'guardian' => 'Bimal Ghosh'],
    ['id' => 203, 'name' => 'Anjali Sen', 'class' => 'VII', 'roll' => 3, 'dob' => '2014-08-15', 'guardian' => 'Chirag Sen'],
    ['id' => 204, 'name' => 'Rajib Barman', 'class' => 'IX', 'roll' => 1, 'dob' => '2012-11-30', 'guardian' => 'Dipak Barman'],
];

// Check for success message from student_addmission.php
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    $message_type = 'success';
    unset($_SESSION['success_message']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Stakeholder Dashboard | Banglar Shiksha</title>
    <link rel="shortcut icon" href="https://banglarshiksha.wb.gov.in/assets/admin/images/favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --sidebar-width: 280px; }
        body { background-color: #f0f2f5; }
        .wrapper { display: flex; width: 100%; }
        #sidebar {
            width: var(--sidebar-width);
            position: fixed; top: 0; left: 0; height: 100vh;
            z-index: 999; background: #2c3e50; color: #fff; transition: all 0.3s;
        }
        #sidebar .sidebar-header { padding: 20px; text-align: center; background: #233140;}
        #sidebar ul.components { padding: 20px 0; }
        #sidebar ul li a { padding: 15px 25px; font-size: 1.1em; display: block; color: #ecf0f1; text-decoration: none; transition: all 0.3s; border-left: 3px solid transparent; }
        #sidebar ul li a:hover { color: #fff; background: #34495e; border-left-color: #3498db; }
        #sidebar ul li.active>a { color: #fff; background: #34495e; border-left-color: #3498db; }
        #content { width: 100%; padding-left: var(--sidebar-width); min-height: 100vh; transition: all 0.3s; }
        .main-content-section { display: none; }
        .main-content-section.active { display: block; }
        .stat-card { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .profile-header { background: linear-gradient(135deg, #3498db, #2980b9); color: white; padding: 1rem; border-radius: 8px; }
        .time-date-box { font-size: 1.2rem; text-align: center; }
        .map-container { overflow:hidden; padding-bottom:56.25%; position:relative; height:0; border-radius: 8px; }
        .map-container iframe { left:0; top:0; height:100%; width:100%; position:absolute; }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <img src="https://banglarshiksha.wb.gov.in/assets/admin/layout/images/logo.png" alt="Logo" style="height: 50px;">
                <h5 class="mt-2">Stakeholder Backend</h5>
            </div>
            <ul class="list-unstyled components">
                <li class="active"><a href="dashboard.php" class="nav-link"><i class="fas fa-home me-2"></i>Dashboard</a></li>
                <li><a href="student_addmission.php"><i class="fas fa-user-plus me-2"></i>Add Student Admission</a></li>
                <li><a href="#view-students" class="nav-link"><i class="fas fa-users-viewfinder me-2"></i>View Student Data</a></li>
                <li><a href="#settings" class="nav-link"><i class="fas fa-user-cog me-2"></i>Account Settings</a></li>
                <li><a href="?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
                <div class="container-fluid d-flex justify-content-end">
                    <div class="text-end">
                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($full_name); ?></h6>
                        <small class="text-muted"><?php echo htmlspecialchars($user_email); ?> | <?php echo htmlspecialchars($user_designation); ?></small>
                    </div>
                </div>
            </nav>

            <main class="p-4">
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- Dashboard Section -->
                <section id="dashboard" class="main-content-section active">
                    <h2 class="pb-2 border-bottom mb-4">Dashboard Home</h2>
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="stat-card h-100">
                                <h5 class="mb-3">School Location (Siliguri)</h5>
                                <div class="map-container">
                                    <iframe src="https://maps.google.com/maps?q=Siliguri,%20West%20Bengal&t=&z=13&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="stat-card mb-4">
                                <h5 class="text-muted">Current Time & Date</h5>
                                <div id="time-date-box" class="time-date-box fw-bold text-primary"></div>
                            </div>
                            <div class="stat-card">
                                <h5 class="text-muted">Total Students</h5>
                                <p class="display-5 fw-bold"><?php echo count($student_data); ?></p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- View Student Data Section -->
                <section id="view-students" class="main-content-section">
                     <h2 class="pb-2 border-bottom mb-4">Student Information Database</h2>
                    <div class="card shadow-sm"><div class="card-body">
                        <input type="text" id="studentSearch" class="form-control mb-3" placeholder="Search for students by name...">
                        <div class="table-responsive">
                            <table class="table table-hover" id="studentTable">
                                <thead><tr><th>Student ID</th><th>Name</th><th>Class</th><th>Roll No.</th><th>Action</th></tr></thead>
                                <tbody>
                                    <?php foreach ($student_data as $student): ?>
                                    <tr>
                                        <td><?php echo $student['id']; ?></td>
                                        <td><?php echo $student['name']; ?></td>
                                        <td><?php echo $student['class']; ?></td>
                                        <td><?php echo $student['roll']; ?></td>
                                        <td><button class="btn btn-sm btn-outline-primary view-details-btn" data-bs-toggle="modal" data-bs-target="#studentModal" data-id="<?php echo $student['id']; ?>" data-name="<?php echo $student['name']; ?>" data-class="<?php echo $student['class']; ?>" data-roll="<?php echo $student['roll']; ?>" data-dob="<?php echo $student['dob']; ?>" data-guardian="<?php echo $student['guardian']; ?>"><i class="fas fa-eye"></i> View</button></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div></div>
                </section>

                <!-- Account Settings Section -->
                <section id="settings" class="main-content-section">
                    <h2 class="pb-2 border-bottom mb-4">Account Settings</h2>
                    <div class="row">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <div class="card shadow-sm h-100"><div class="card-body profile-header">
                                <h5 class="card-title text-white">Your Profile Information</h5>
                                <p class="mb-1"><strong>Full Name:</strong> <?php echo htmlspecialchars($full_name); ?></p>
                                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($user_email); ?></p>
                                <p class="mb-0"><strong>Designation:</strong> <?php echo htmlspecialchars($user_designation); ?></p>
                            </div></div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card shadow-sm h-100"><div class="card-body">
                                <h5 class="card-title">Change Your Password</h5>
                                <form method="POST">
                                    <div class="mb-3"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" required></div>
                                    <div class="mb-3"><label class="form-label">Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
                                    <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                                </form>
                            </div></div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
    
    <!-- Student Details Modal -->
    <div class="modal fade" id="studentModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="studentModalTitle">Student Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="studentModalBody"></div>
        </div></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navLinks = document.querySelectorAll('#sidebar .nav-link');
            const sections = document.querySelectorAll('.main-content-section');
            const timeDateBox = document.getElementById('time-date-box');

            // --- Real-time Clock ---
            function updateTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
                const dateString = now.toLocaleDateString('en-IN', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                timeDateBox.innerHTML = `${timeString}<br><small>${dateString}</small>`;
            }
            setInterval(updateTime, 1000);
            updateTime();

            // --- Navigation Logic ---
            navLinks.forEach(link => {
                // Check if the link is for an internal section
                if (link.getAttribute('href').startsWith('#')) {
                    link.addEventListener('click', function (e) {
                        e.preventDefault();
                        document.querySelector('#sidebar li.active').classList.remove('active');
                        this.parentElement.classList.add('active');
                        const targetId = this.getAttribute('href').substring(1);
                        sections.forEach(section => {
                            section.classList.remove('active');
                            if (section.id === targetId) section.classList.add('active');
                        });
                    });
                }
            });

            // --- Student Data Search ---
            document.getElementById('studentSearch').addEventListener('keyup', function() {
                const filter = this.value.toUpperCase();
                document.querySelectorAll('#studentTable tbody tr').forEach(row => {
                    const nameCell = row.cells[1];
                    row.style.display = nameCell.textContent.toUpperCase().includes(filter) ? '' : 'none';
                });
            });

            // --- Student Details Modal ---
            document.getElementById('studentModal').addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                this.querySelector('#studentModalTitle').textContent = `Details for ${button.dataset.name}`;
                this.querySelector('#studentModalBody').innerHTML = `
                    <p><strong>Student ID:</strong> ${button.dataset.id}</p>
                    <p><strong>Name:</strong> ${button.dataset.name}</p>
                    <p><strong>Class:</strong> ${button.dataset.class}</p>
                    <p><strong>Roll No:</strong> ${button.dataset.roll}</p>
                    <p><strong>Date of Birth:</strong> ${button.dataset.dob}</p>
                    <p><strong>Guardian's Name:</strong> ${button.dataset.guardian}</p>
                `;
            });
        });
    </script>
</body>
</html>

