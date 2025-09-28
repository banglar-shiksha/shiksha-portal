<?php
session_start();

// Define the base URL for the entire application.
// This makes all links and redirects robust and reliable.
define('BASE_URL', '/banglar-shiksha');

// Check if the user is logged in and is a teacher, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: " . BASE_URL . "/login.php");
    exit;
}

// Get the current page to highlight the active menu item
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : "Teacher Dashboard"; ?> - Banglar Shiksha</title>
    <link rel="shortcut icon" href="https://banglarshiksha.wb.gov.in/assets/admin/images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Single, reliable, absolute path to the CSS file -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dashboard-style.css">

</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="<?php echo BASE_URL; ?>/assets/img/banglar-shiksha-logo.png" alt="Banglar Shiksha Logo" class="logo">
                <h3>Teacher Panel</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <!-- All links now use BASE_URL to ensure they work correctly -->
                    <li><a href="<?php echo BASE_URL; ?>/Teacher_dashboard.php" class="<?php echo ($current_page == 'Teacher_dashboard.php') ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    
                    <li class="has-submenu <?php echo in_array($current_page, ['student_addmission.php', 'rectify_student_details.php', 'view-student.php', 'promot_detain-student.php', 'student_transfer.php']) ? 'open' : ''; ?>">
                        <a href="#" class="submenu-toggle"><i class="fas fa-users"></i> Student Management <i class="fas fa-chevron-down arrow"></i></a>
                        <ul class="submenu">
                            <li><a href="<?php echo BASE_URL; ?>/links/student_addmission.php" class="<?php echo ($current_page == 'student_addmission.php') ? 'sub-active' : ''; ?>"><i class="fas fa-user-plus"></i> Student Admission</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/links/rectify_student_details.php" class="<?php echo ($current_page == 'rectify_student_details.php') ? 'sub-active' : ''; ?>"><i class="fas fa-user-edit"></i> Rectify Student Details</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/links/view-student.php" class="<?php echo ($current_page == 'view-student.php') ? 'sub-active' : ''; ?>"><i class="fas fa-list-ul"></i> View Student List</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/links/promot_detain-student.php" class="<?php echo ($current_page == 'promot_detain-student.php') ? 'sub-active' : ''; ?>"><i class="fas fa-check-circle"></i> Promote / Detain</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/links/student_transfer.php" class="<?php echo ($current_page == 'student_transfer.php') ? 'sub-active' : ''; ?>"><i class="fas fa-exchange-alt"></i> Student Transfer</a></li>
                        </ul>
                    </li>
                    
                    <li><a href="#"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                    <li><a href="#"><i class="fas fa-marker"></i> Marks Entry</a></li>
                    <li><a href="#"><i class="fas fa-book"></i> Learning Materials</a></li>
                    <li><a href="#"><i class="fas fa-bullhorn"></i> School Notices</a></li>
                    <li><a href="#"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                 <div class="header-title">
                     <h1><?php echo isset($page_title) ? $page_title : "Dashboard"; ?></h1>
                    <p>Siliguri, West Bengal, India | <span id="current-time"></span></p>
                </div>
                <div class="header-actions">
                    <a href="#" class="icon-btn"><i class="fas fa-bell"></i><span class="notification-badge">3</span></a>
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </header>

