<?php
session_start();

// --- Authentication and Authorization Check ---
// 1. Check if the user is logged in.
// 2. Check if the user's role is 'chief-minister'.
// If either check fails, redirect them to the login page.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'chief-minister') {
    header("location: login.php");
    exit;
}

// --- Logout Logic ---
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // Unset all of the session variables
    $_SESSION = array();
    // Destroy the session.
    session_destroy();
    // Redirect to login page
    header("location: login.php");
    exit;
}

// Get user information from the session
$username = $_SESSION["username"] ?? 'Hon\'ble Chief Minister';

// --- Placeholder Data (to be replaced with database queries) ---
$kpi_data = [
    'total_schools' => '95,768',
    'total_teachers' => '4,89,215',
    'total_students' => '1.85 Cr',
    'literacy_rate' => '87.5%'
];

$enrollment_by_district = [
    'labels' => ['Kolkata', 'Howrah', 'Hooghly', 'North 24 Parganas', 'South 24 Parganas', 'Murshidabad', 'Darjeeling'],
    'data' => [120500, 185230, 165800, 250100, 295600, 310500, 95200]
];

$school_types = [
    'labels' => ['Primary', 'Secondary', 'Higher Secondary', 'Integrated'],
    'data' => [60, 25, 12, 3] // Percentages
];

$recent_announcements = [
    ['date' => '2024-09-15', 'title' => 'Launch of "Digital Classroom" Initiative Phase II', 'status' => 'Active'],
    ['date' => '2024-09-10', 'title' => 'State-wide Teacher Training on New Curriculum', 'status' => 'Completed'],
    ['date' => '2024-09-01', 'title' => 'Introduction of Mid-Day Meal Menu Enhancements', 'status' => 'Active'],
    ['date' => '2024-08-25', 'title' => 'Sanction of Funds for School Infrastructure Upgrade', 'status' => 'In Progress']
];

$district_performance = [
    ['district' => 'Kolkata', 'student_teacher_ratio' => '25:1', 'pass_percentage' => '92.5%', 'avg_attendance' => '95%'],
    ['district' => 'Darjeeling', 'student_teacher_ratio' => '22:1', 'pass_percentage' => '91.8%', 'avg_attendance' => '94%'],
    ['district' => 'Howrah', 'student_teacher_ratio' => '30:1', 'pass_percentage' => '88.2%', 'avg_attendance' => '91%'],
    ['district' => 'Murshidabad', 'student_teacher_ratio' => '35:1', 'pass_percentage' => '85.4%', 'avg_attendance' => '88%']
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chief Minister's Dashboard | Banglar Shiksha</title>
    <link rel="shortcut icon" href="https://banglarshiksha.wb.gov.in/assets/admin/images/favicon.ico" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-bg: #212529;
            --sidebar-link-color: #adb5bd;
            --sidebar-link-hover: #fff;
        }

        body {
            background-color: #f8f9fa;
        }

        .wrapper {
            display: flex;
            width: 100%;
        }

        #sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 999;
            background: var(--sidebar-bg);
            color: #fff;
            transition: all 0.3s;
        }
        
        #sidebar.collapsed {
            margin-left: calc(-1 * var(--sidebar-width));
        }

        #sidebar .sidebar-header {
            padding: 20px;
            background: #343a40;
            text-align: center;
        }
        
        #sidebar .sidebar-header img {
            max-height: 50px;
        }
        
        #sidebar ul.components {
            padding: 20px 0;
            border-bottom: 1px solid #47748b;
        }

        #sidebar ul p {
            color: #fff;
            padding: 10px;
        }

        #sidebar ul li a {
            padding: 15px 20px;
            font-size: 1.1em;
            display: block;
            color: var(--sidebar-link-color);
            text-decoration: none;
            transition: all 0.3s;
        }

        #sidebar ul li a:hover {
            color: var(--sidebar-link-hover);
            background: #343a40;
        }

        #sidebar ul li.active>a,
        a[aria-expanded="true"] {
            color: #fff;
            background: #0d6efd;
        }

        #content {
            width: 100%;
            padding-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }
        
         #content.full-width {
            padding-left: 0;
        }

        .kpi-card {
            border-left: 5px solid;
        }
        
        @media (max-width: 768px) {
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
             #sidebar.collapsed {
                margin-left: 0;
            }
             #content {
                padding-left: 0;
            }
             #content.full-width {
                padding-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <img src="https://banglarshiksha.wb.gov.in/assets/admin/layout/images/logo.png" alt="Banglar Shiksha Logo">
                <h5 class="mt-2">Banglar Shiksha</h5>
            </div>

            <ul class="list-unstyled components">
                <p class="ms-3">Welcome, <?php echo htmlspecialchars($username); ?></p>
                <li class="active">
                    <a href="#"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                </li>
                <li>
                    <a href="#"><i class="fas fa-chart-bar me-2"></i>Analytics</a>
                </li>
                 <li>
                    <a href="#"><i class="fas fa-bullhorn me-2"></i>Announcements</a>
                </li>
                <li>
                    <a href="#"><i class="fas fa-file-alt me-2"></i>Reports</a>
                </li>
                <li>
                    <a href="#"><i class="fas fa-cog me-2"></i>Settings</a>
                </li>
                 <li>
                    <a href="?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow-sm">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-primary">
                        <i class="fas fa-align-left"></i>
                    </button>
                    <h4 class="ms-3 mb-0">Chief Minister's Dashboard</h4>
                </div>
            </nav>

            <main class="p-4">
                <!-- KPI Cards -->
                <section class="mb-4">
                    <div class="row g-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="card shadow-sm h-100 kpi-card" style="border-left-color: #0d6efd;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-subtitle mb-2 text-muted">Total Schools</h6>
                                            <h4 class="card-title"><?php echo $kpi_data['total_schools']; ?></h4>
                                        </div>
                                        <i class="fas fa-school fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                         <div class="col-lg-3 col-md-6">
                            <div class="card shadow-sm h-100 kpi-card" style="border-left-color: #198754;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-subtitle mb-2 text-muted">Total Teachers</h6>
                                            <h4 class="card-title"><?php echo $kpi_data['total_teachers']; ?></h4>
                                        </div>
                                        <i class="fas fa-chalkboard-teacher fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                         <div class="col-lg-3 col-md-6">
                            <div class="card shadow-sm h-100 kpi-card" style="border-left-color: #ffc107;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-subtitle mb-2 text-muted">Total Students</h6>
                                            <h4 class="card-title"><?php echo $kpi_data['total_students']; ?></h4>
                                        </div>
                                        <i class="fas fa-user-graduate fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                         <div class="col-lg-3 col-md-6">
                            <div class="card shadow-sm h-100 kpi-card" style="border-left-color: #fd7e14;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-subtitle mb-2 text-muted">Literacy Rate</h6>
                                            <h4 class="card-title"><?php echo $kpi_data['literacy_rate']; ?></h4>
                                        </div>
                                        <i class="fas fa-percentage fa-2x text-orange"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Charts -->
                <section class="mb-4">
                     <div class="row g-4">
                        <div class="col-lg-8">
                             <div class="card shadow-sm h-100">
                                <div class="card-body">
                                     <h5 class="card-title">Student Enrollment by District</h5>
                                     <canvas id="enrollmentChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                             <div class="card shadow-sm h-100">
                                <div class="card-body">
                                     <h5 class="card-title">School Types Distribution</h5>
                                      <canvas id="schoolTypeChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- Tables -->
                 <section>
                     <div class="row g-4">
                        <div class="col-lg-7">
                             <div class="card shadow-sm">
                                <div class="card-header">
                                    Recent Announcements
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr><th>Date</th><th>Title</th><th>Status</th></tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($recent_announcements as $announcement): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($announcement['date']); ?></td>
                                                    <td><?php echo htmlspecialchars($announcement['title']); ?></td>
                                                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($announcement['status']); ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                             <div class="card shadow-sm">
                                <div class="card-header">
                                    District Performance
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                           <thead>
                                                <tr><th>District</th><th>Ratio</th><th>Pass %</th><th>Attendance</th></tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($district_performance as $perf): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($perf['district']); ?></td>
                                                    <td><?php echo htmlspecialchars($perf['student_teacher_ratio']); ?></td>
                                                    <td><?php echo htmlspecialchars($perf['pass_percentage']); ?></td>
                                                    <td><?php echo htmlspecialchars($perf['avg_attendance']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            </main>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- Sidebar Toggle Logic ---
            const sidebarCollapse = document.getElementById('sidebarCollapse');
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');

            sidebarCollapse.addEventListener('click', function () {
                sidebar.classList.toggle('collapsed');
                content.classList.toggle('full-width');
            });
            
            // --- Chart.js Initializations ---
            // 1. Enrollment Chart (Bar)
            const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
            const enrollmentChart = new Chart(enrollmentCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($enrollment_by_district['labels']); ?>,
                    datasets: [{
                        label: 'Number of Students',
                        data: <?php echo json_encode($enrollment_by_district['data']); ?>,
                        backgroundColor: 'rgba(13, 110, 253, 0.7)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // 2. School Type Chart (Doughnut)
            const schoolTypeCtx = document.getElementById('schoolTypeChart').getContext('2d');
            const schoolTypeChart = new Chart(schoolTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($school_types['labels']); ?>,
                    datasets: [{
                        label: 'Distribution %',
                        data: <?php echo json_encode($school_types['data']); ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)'
                        ],
                        hoverOffset: 4
                    }]
                },
                 options: {
                    responsive: true,
                }
            });

        });
    </script>
</body>
</html>
