<?php
session_start();

// If a user is already logged in, redirect them to the dashboard.
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}

// --- Database Connection ---
// IMPORTANT: Replace the placeholder credentials below with your actual database details.
$dsn = 'mysql:host=localhost;dbname=banglar_shiksha_db'; // Replace with your DB name
$db_username = 'root'; // Your DB username, e.g., 'root'
$db_password = ''; // Your DB password

$pdo = null;
try {
    $pdo = new PDO($dsn, $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // In a real application, log this error and show a user-friendly message.
    die("ERROR: Could not connect to the database. " . $e->getMessage());
}

$error_message = "";

// --- Form Submission Handling ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- STRICT FIELD VALIDATION for ALL ROLES ---
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $qualification = trim($_POST["qualification"]);
    $udise_code = trim($_POST["udise_code"]); // This is the username for ALL roles
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $user_role = trim($_POST["user_role"]);

    // 1. Check for ANY empty fields. All are mandatory.
    if (empty($full_name) || empty($email) || empty($qualification) || empty($udise_code) || empty($password) || empty($confirm_password) || empty($user_role)) {
        $error_message = "Error: All fields are strictly required for registration.";
    } 
    // 2. Validate email format.
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Error: The email address provided is not in a valid format.";
    }
    // 3. Check if passwords match.
    elseif ($password !== $confirm_password) {
        $error_message = "Error: The passwords you entered do not match.";
    } 
    // 4. Strictly enforce new password complexity rules.
    else {
        $password_regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()-_=+{};:,<.>]).{8,12}$/';
        if (!preg_match($password_regex, $password)) {
            $error_message = "Error: Password does not meet the requirements. It must be 8-12 characters and include uppercase, lowercase, number, and special characters.";
        } else {
            // --- STRICT DATABASE CHECKS ---
            $error_found = false;

            // 5. Check if UDISE code (username) is already registered.
            $sql_check_username = "SELECT id FROM users WHERE username = :username";
            if ($stmt_check_username = $pdo->prepare($sql_check_username)) {
                $stmt_check_username->bindParam(":username", $udise_code, PDO::PARAM_STR);
                if ($stmt_check_username->execute()) {
                    if ($stmt_check_username->rowCount() > 0) {
                        $error_message = "Error: This School UDISE code is already registered. Please login.";
                        $error_found = true;
                    }
                }
                unset($stmt_check_username);
            }

            // 6. Strictly check for unique password if no other error has occurred.
            if (!$error_found) {
                $sql_get_passwords = "SELECT password FROM users";
                $stmt_get_passwords = $pdo->query($sql_get_passwords);
                $all_hashed_passwords = $stmt_get_passwords->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($all_hashed_passwords as $hashed_password) {
                    if (password_verify($password, $hashed_password)) {
                        $error_message = "Error: This password has already been used. For security, please choose a unique one.";
                        $error_found = true;
                        break;
                    }
                }
            }

            // --- Insert Data ONLY if all strict checks pass ---
            if (!$error_found) {
                $sql_insert = "INSERT INTO users (username, password, role, full_name, email, qualification, udise_code) VALUES (:username, :password, :role, :full_name, :email, :qualification, :udise_code)";
        
                if ($stmt_insert = $pdo->prepare($sql_insert)) {
                    $param_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Bind all parameters
                    $stmt_insert->bindParam(":username", $udise_code, PDO::PARAM_STR);
                    $stmt_insert->bindParam(":password", $param_password, PDO::PARAM_STR);
                    $stmt_insert->bindParam(":role", $user_role, PDO::PARAM_STR);
                    $stmt_insert->bindParam(":full_name", $full_name, PDO::PARAM_STR);
                    $stmt_insert->bindParam(":email", $email, PDO::PARAM_STR);
                    $stmt_insert->bindParam(":qualification", $qualification, PDO::PARAM_STR);
                    $stmt_insert->bindParam(":udise_code", $udise_code, PDO::PARAM_STR);
                    
                    if ($stmt_insert->execute()) {
                        // On success, set a message and redirect to login page.
                        $_SESSION['flash_message'] = "Registration successful! You can now log in with your UDISE code.";
                        header("location: login.php");
                        exit;
                    } else {
                        $error_message = "A system error occurred. Please try again later.";
                    }
                    unset($stmt_insert);
                }
            }
        }
    }
    unset($pdo);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banglar Shiksha | Sign Up</title>
    <link rel="shortcut icon" href="https://banglarshiksha.wb.gov.in/assets/admin/images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-blue: #007bff;
            --secondary-gray: #6c757d;
            --sidebar-bg: #1e7e34; /* Dark Green */
            --background-color: #e9f0f5;
            --form-bg-color: #ffffff;
            --text-color: #333;
            --border-color: #ced4da;
            --error-color: #dc3545;
            --success-color: #28a745;
        }

        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            height: 100%;
            background-color: var(--background-color);
        }

        .page-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 50px;
            background-color: var(--background-color);
            flex-shrink: 0;
        }

        .top-header .logo {
            max-height: 40px;
        }
        
        .top-header .logo.left {
            font-size: 1.2em;
            color: var(--sidebar-bg);
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        .top-header .logo.left img {
            margin-right: 10px;
            max-height: 35px;
        }
        
        .top-header .logo-group {
            display: flex;
            align-items: center;
            gap: 30px;
        }
        
        .main-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .signup-container {
            width: 100%;
            max-width: 1000px;
            display: flex;
            background-color: var(--form-bg-color);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .role-sidebar {
            background-color: var(--sidebar-bg);
            padding: 20px;
            width: 300px;
        }

        .role-btn {
            display: block;
            width: 100%;
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            background-color: transparent;
            color: white;
            cursor: pointer;
            border-radius: 8px;
            text-align: left;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .role-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .role-btn.active {
            background-color: white;
            color: var(--sidebar-bg);
            font-weight: bold;
        }
        
        .form-section {
            padding: 40px;
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .form-wrapper {
            width: 100%;
            max-width: 400px;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .form-header img {
             width: 80px;
             height: 80px;
             object-fit: cover;
             border-radius: 50%;
             margin-bottom: 10px;
             opacity: 0.7;
        }

        .form-header h2 {
            font-size: 1.5em;
            color: var(--text-color);
            margin: 0;
            font-weight: normal;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 0.9em;
        }

        .input-group {
            position: relative;
            margin-bottom: 15px;
        }

        .input-group input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            box-sizing: border-box;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }
        
        .password-requirements {
            font-size: 0.8rem;
            list-style-type: none;
            padding-left: 0;
            margin: -5px 0 15px 0;
            color: var(--secondary-gray);
        }
        .password-requirements li.valid {
            color: var(--success-color);
        }
        .password-requirements li.valid i {
            color: var(--success-color);
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-secondary {
            background-color: var(--secondary-gray);
        }
        .btn-primary {
            background-color: var(--primary-blue);
        }

        @media (max-width: 992px) {
            .signup-container {
                flex-direction: column;
            }
            .role-sidebar {
                width: auto;
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }
            .role-btn {
                width: auto;
                flex-grow: 1;
                margin: 5px;
                text-align: center;
            }
        }
         @media (max-width: 768px) {
            .top-header {
                padding: 10px 20px;
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <header class="top-header">
            <div class="logo left">
                <img src="https://banglarshiksha.wb.gov.in/assets/admin/images/favicon.ico" alt="Icon">
                <span>বাংলার শিক্ষা</span>
            </div>
            <div class="logo-group">
                 <img src="https://banglarshiksha.wb.gov.in/assets/admin/layout/images/logo.png" alt="Banglar Shiksha Logo" class="logo">
                 <img src="https://seeklogo.com/images/W/west-bengal-board-of-secondary-education-logo-0145524328-seeklogo.com.png" alt="WBBSE Logo" class="logo">
            </div>
        </header>
        
        <main class="main-content">
            <div class="signup-container">
                <nav class="role-sidebar">
                    <button class="role-btn active" data-role="chief-minister">Hon'ble Chief Minister</button>
                    <button class="role-btn" data-role="education-minister">Hon'ble Education Minister</button>
                    <button class="role-btn" data-role="chief-secretary">Chief Secretary</button>
                    <button class="role-btn" data-role="principal-secretary">Principal Secretary, School Education</button>
                    <button class="role-btn" data-role="teacher">Teacher</button>
                    <button class="role-btn" data-role="other">Other Stakeholders</button>
                </nav>
                <section class="form-section">
                     <div class="form-wrapper">
                        <div class="form-header">
                            <img src="https://static.vecteezy.com/system/resources/thumbnails/009/292/244/small/default-avatar-icon-of-social-media-user-vector.jpg" alt="User Icon">
                            <h2>SIGN UP</h2>
                        </div>
                        
                        <?php if(!empty($error_message)): ?>
                            <div class="alert-error"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" novalidate>
                            <input type="hidden" name="user_role" id="user_role_input" value="chief-minister">

                            <div class="input-group">
                                <i class="fas fa-user-circle"></i>
                                <input type="text" name="full_name" placeholder="Full Name" required>
                            </div>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" placeholder="Email Address" required>
                            </div>
                            <div class="input-group">
                                <i class="fas fa-graduation-cap"></i>
                                <input type="text" name="qualification" placeholder="Highest Qualification" required>
                            </div>
                            <div class="input-group">
                                <i class="fas fa-school"></i>
                                <input type="text" name="udise_code" placeholder="School UDISE Code (Username)" required>
                            </div>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Password" required>
                            </div>
                             <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                            </div>

                            <ul class="password-requirements">
                                <li id="req-length"><i class="fas fa-times-circle"></i> 8-12 characters long</li>
                                <li id="req-upper"><i class="fas fa-times-circle"></i> At least one uppercase letter (A-Z)</li>
                                <li id="req-lower"><i class="fas fa-times-circle"></i> At least one lowercase letter (a-z)</li>
                                <li id="req-num"><i class="fas fa-times-circle"></i> At least one number (0-9)</li>
                                <li id="req-spec"><i class="fas fa-times-circle"></i> At least one special character (!@#...)</li>
                            </ul>

                            <div class="button-group">
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='login.php'">
                                    <i class="fas fa-arrow-left"></i> Login
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus"></i> Create Account
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleButtons = document.querySelectorAll('.role-btn');
        const userRoleInput = document.getElementById('user_role_input');
        const passwordInput = document.getElementById('password');
        
        // --- Role Selection Logic ---
        roleButtons.forEach(button => {
            button.addEventListener('click', function() {
                roleButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                userRoleInput.value = this.getAttribute('data-role');
            });
        });

        // --- Live Password Validation Logic ---
        if (passwordInput) {
            const reqs = {
                length: document.getElementById('req-length'),
                upper: document.getElementById('req-upper'),
                lower: document.getElementById('req-lower'),
                num: document.getElementById('req-num'),
                spec: document.getElementById('req-spec')
            };

            passwordInput.addEventListener('input', function() {
                const value = this.value;
                
                validate(reqs.length, value.length >= 8 && value.length <= 12);
                validate(reqs.upper, /[A-Z]/.test(value));
                validate(reqs.lower, /[a-z]/.test(value));
                validate(reqs.num, /\d/.test(value));
                validate(reqs.spec, /[!@#$%^&*()-_=+{};:,<.>]/.test(value));
            });

            function validate(element, isValid) {
                const icon = element.querySelector('i');
                if (isValid) {
                    element.classList.add('valid');
                    icon.classList.remove('fa-times-circle');
                    icon.classList.add('fa-check-circle');
                } else {
                    element.classList.remove('valid');
                    icon.classList.remove('fa-check-circle');
                    icon.classList.add('fa-times-circle');
                }
            }
        }
    });
    </script>
</body>
</html>

