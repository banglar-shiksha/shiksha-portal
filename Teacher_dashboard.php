<?php
session_start();

// Define BASE_URL for consistent linking
define('BASE_URL', '/banglar-shiksha/');

// Check if the user is logged in and is a teacher, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: " . BASE_URL . "login.php");
    exit;
}

// --- Fetching Dynamic Data (Placeholders) ---
$total_students = 450;
$present_today = 435;
$absent_today = 15;
$pending_tasks = 8;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Banglar Shiksha</title>
    <!-- Favicon and Fonts -->
    <link rel="shortcut icon" href="https://banglarshiksha.wb.gov.in/assets/admin/images/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap and Font Awesome CDN -->
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
        .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important; }
        .quick-actions .action-item { display: block; text-decoration: none; color: #343a40; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.5rem; padding: 1.25rem; text-align: center; transition: all 0.2s ease; }
        .quick-actions .action-item:hover { background-color: var(--bs-primary); color: #fff; border-color: var(--bs-primary); transform: scale(1.05); }
        .quick-actions .action-item i { font-size: 2rem; display: block; margin-bottom: 0.5rem; }
        #ip-details-card .list-group-item { background-color: transparent; }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar Navigation -->
        <aside class="sidebar d-flex flex-column p-3">
            <div class="text-center py-3 border-bottom">
                <img src="<?php echo BASE_URL; ?>assets/img/banglar-shiksha-logo.png" alt="Banglar Shiksha Logo" style="max-width: 150px;">
                <h3 class="h5 mt-2 text-primary">Teacher Panel</h3>
            </div>
            <nav class="sidebar-nav nav nav-pills flex-column mt-3">
                <a class="nav-link d-flex align-items-center active" href="<?php echo BASE_URL; ?>Teacher_dashboard.php"><i class="fas fa-tachometer-alt me-3"></i> Dashboard</a>
                <a class="nav-link d-flex align-items-center" data-bs-toggle="collapse" href="#studentSubmenu"><i class="fas fa-users me-3"></i> Student Management</a>
                <div class="collapse" id="studentSubmenu">
                    <div class="submenu nav flex-column">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>links/student_addmission.php"><i class="fas fa-user-plus me-2"></i> Admission</a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>links/rectify_student_details.php"><i class="fas fa-user-edit me-2"></i> Rectify Details</a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>links/view-student.php"><i class="fas fa-list-ul me-2"></i> View List</a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>links/promote_detain_student.php"><i class="fas fa-check-circle me-2"></i> Promote/Detain</a>
                        <a class="nav-link" href="<?php echo BASE_URL; ?>links/student_transfer.php"><i class="fas fa-exchange-alt me-2"></i> Transfer</a>
                    </div>
                </div>
                <a class="nav-link d-flex align-items-center" href="#"><i class="fas fa-calendar-check me-3"></i> Attendance</a>
                <a class="nav-link d-flex align-items-center" href="#"><i class="fas fa-marker me-3"></i> Marks Entry</a>
                <a class="nav-link d-flex align-items-center" href="#"><i class="fas fa-user-cog me-3"></i> Profile Settings</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content flex-grow-1">
            <header class="bg-white p-3 d-flex justify-content-between align-items-center border-bottom sticky-top">
                <div>
                    <h1 class="h4 mb-0">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
                    <p class="text-muted small mb-0">📍 Siliguri, West Bengal | 🕒 <span id="current-time"></span></p>
                </div>
                <div class="d-flex align-items-center">
                    <a href="#" class="text-secondary fs-5 me-3"><i class="fas fa-bell"></i></a>
                    <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-primary"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </div>
            </header>

            <section class="p-4">
                <div class="container-fluid">
                    <!-- Quick Stats Cards -->
                    <div class="row g-4">
                        <div class="col-md-6 col-xl-3">
                            <div class="card stat-card border-0 shadow-sm h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div class="bg-primary text-white p-3 rounded-3 me-3 fs-3"><i class="fas fa-user-graduate"></i></div>
                                    <div>
                                        <h4 class="h2 mb-0 fw-bold"><?php echo $total_students; ?></h4>
                                        <p class="text-muted mb-0">Total Students</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="card stat-card border-0 shadow-sm h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div class="bg-success text-white p-3 rounded-3 me-3 fs-3"><i class="fas fa-user-check"></i></div>
                                    <div>
                                        <h4 class="h2 mb-0 fw-bold"><?php echo $present_today; ?></h4>
                                        <p class="text-muted mb-0">Present Today</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                         <!-- IP Address Information Card -->
                        <div class="col-md-12 col-xl-6">
                            <div id="ip-details-card" class="card stat-card border-0 shadow-sm h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div class="bg-info text-white p-3 rounded-3 me-3 fs-3"><i class="fas fa-network-wired"></i></div>
                                    <div id="ip-details-content">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Loading IP...</span>
                                        </div>
                                        <span class="ms-2">Fetching IP Details...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Dashboard Content Row with Map -->
                    <div class="row g-4 mt-3">
                        <div class="col-lg-7">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-0 pt-3">
                                    <h5 class="mb-0">Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="quick-actions row row-cols-2 g-3">
                                        <div class="col"><a href="<?php echo BASE_URL; ?>links/student_addmission.php" class="action-item"><i class="fas fa-user-plus"></i><span>New Admission</span></a></div>
                                        <div class="col"><a href="#" class="action-item"><i class="fas fa-calendar-plus"></i><span>Mark Attendance</span></a></div>
                                        <div class="col"><a href="#" class="action-item"><i class="fas fa-marker"></i><span>Enter Exam Marks</span></a></div>
                                        <div class="col"><a href="#" class="action-item"><i class="fas fa-upload"></i><span>Upload Material</span></a></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-5">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-0 pt-3">
                                    <h5 class="mb-0">School Location</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Google Map Embed with your specific location -->
                                    <iframe 
                                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3563.821360183021!2d88.4357989752319!3d26.717649976763493!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39e4414a79b28a2b%3A0x8073b64ac7a86377!2sSiliguri%20Boys&#39;%20High%20School!5e0!3m2!1sen!2sin!4v1727507659551!5m2!1sen!2sin"
                                        width="100%" 
                                        height="300" 
                                        style="border:0; border-radius: 0.5rem;" 
                                        allowfullscreen="" 
                                        loading="lazy" 
                                        referrerpolicy="no-referrer-when-downgrade">
                                    </iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Live clock update
            const timeElement = document.getElementById('current-time');
            function updateTime() {
                 if(timeElement) {
                    const now = new Date();
                    timeElement.textContent = now.toLocaleString('en-IN', { dateStyle: 'long', timeStyle: 'medium' });
                 }
            }
            updateTime();
            setInterval(updateTime, 1000);

            // Fetch and display full IP details
            const ipDetailsContent = document.getElementById('ip-details-content');
            fetch('https://ipapi.co/json/')
                .then(response => response.json())
                .then(data => {
                    const html = `
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>IP Address:</strong> ${data.ip}</li>
                            <li class="list-group-item"><strong>Location:</strong> ${data.city}, ${data.region}, ${data.country_name}</li>
                            <li class="list-group-item"><strong>ISP:</strong> ${data.org}</li>
                        </ul>`;
                    ipDetailsContent.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error fetching IP details:', error);
                    ipDetailsContent.innerHTML = '<p class="text-danger mb-0">Could not fetch IP details.</p>';
                });
        });
    </script>
</body>
</html>