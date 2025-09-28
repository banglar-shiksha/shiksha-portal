<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}

// Include your database connection file
require_once 'db_connect.php'; 

$error_message = "";
$success_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_role = trim($_POST["user_role"]);
    $username = ($user_role === 'teacher') ? trim($_POST["udise_code"]) : trim($_POST["username"]);

    // General validation for required fields
    if (empty($username) || empty(trim($_POST["password"])) || empty(trim($_POST["confirm_password"])) || empty($user_role)) {
        $error_message = "Please fill out all required fields.";
    } elseif (trim($_POST["password"]) !== trim($_POST["confirm_password"])) {
        $error_message = "Passwords do not match.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $error_message = "Password must have at least 6 characters.";
    } else {
        // Teacher-specific validation
        if ($user_role === 'teacher') {
            if (empty(trim($_POST["full_name"])) || empty(trim($_POST["email"])) || empty(trim($_POST["qualification"]))) {
                $error_message = "Please fill out all teacher information.";
            } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
                $error_message = "Invalid email format.";
            }
        }
    }

    // Proceed if there are no errors so far
    if (empty($error_message)) {
        // Prepare an insert statement
        if ($user_role === 'teacher') {
            $sql = "INSERT INTO users (username, password, role, full_name, email, qualification, udise_code) VALUES (:username, :password, :role, :full_name, :email, :qualification, :udise_code)";
        } else {
            $sql = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
        }
    
        if ($stmt = $pdo->prepare($sql)) {
            // Bind common variables
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            $stmt->bindParam(":role", $param_role, PDO::PARAM_STR);
            
            // Set common parameters
            $param_username = $username;
            $param_password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT); 
            $param_role = $user_role;

            // Bind and set teacher-specific parameters
            if ($user_role === 'teacher') {
                $stmt->bindParam(":full_name", $param_full_name, PDO::PARAM_STR);
                $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
                $stmt->bindParam(":qualification", $param_qualification, PDO::PARAM_STR);
                $stmt->bindParam(":udise_code", $param_udise_code, PDO::PARAM_STR);

                $param_full_name = trim($_POST["full_name"]);
                $param_email = trim($_POST["email"]);
                $param_qualification = trim($_POST["qualification"]);
                $param_udise_code = trim($_POST["udise_code"]);
            }
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $_SESSION['flash_message'] = "Registration successful. Please log in.";
                header("location: login.php");
                exit;
            } else {
                if($stmt->errorCode() == 23000){
                     $error_message = "This username or email is already taken.";
                } else{
                     $error_message = "Something went wrong. Please try again later.";
                }
            }
            unset($stmt);
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
    <link rel="stylesheet" href="css/login-style.css">
    <style>
        /* Add style for the fields container for smooth transition */
        #teacher-fields, #common-username-field {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease-in-out;
        }
    </style>
</head>
<body>
    <div class="page-background">
        <div class="login-page-container">
            <header class="top-header">
                 <img src="assets/img/banglar-shiksha-logo.png" alt="Banglar Shiksha Logo" class="logo">
                 <img src="assets/img/shikha-logo.png" alt="Shikha Logo" class="logo">
                 <img src="assets/img/education-logo.png" alt="Education First Logo" class="logo">
            </header>
            <main class="login-box">
                <nav class="role-selection">
                    <button class="role-btn active" data-role="chief-minister">Hon'ble Chief Minister</button>
                    <button class="role-btn" data-role="education-minister">Hon'ble Education Minister</button>
                    <button class="role-btn" data-role="chief-secretary">Chief Secretary</button>
                    <button class="role-btn" data-role="principal-secretary">Principal Secretary</button>
                    <button class="role-btn" data-role="teacher">Teacher</button>
                </nav>
                <div class="form-container">
                    <div class="placeholder-image">
                        <img src="assets/img/login_placeholder.jpg" alt="User Icon">
                    </div>
                    <div class="form-content">
                        <h2>SIGN UP</h2>
                        
                        <?php if(!empty($error_message)): ?>
                            <div class="alert-error"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="user_role" id="user_role_input" value="chief-minister">

                            <!-- Common Fields for All Users -->
                            <div id="common-username-field">
                                <div class="input-group">
                                    <input type="text" name="username" placeholder="User Name">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="input-group">
                                <input type="password" name="password" placeholder="Password" required>
                                <i class="fas fa-lock"></i>
                            </div>
                             <div class="input-group">
                                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                                <i class="fas fa-lock"></i>
                            </div>

                            <!-- Teacher-Specific Fields -->
                            <div id="teacher-fields">
                                <div class="input-group">
                                    <input type="text" name="full_name" placeholder="Full Name">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="input-group">
                                    <input type="email" name="email" placeholder="Email Address">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="input-group">
                                    <input type="text" name="qualification" placeholder="Highest Qualification">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="input-group">
                                    <input type="text" name="udise_code" placeholder="School UDISE Code (will be username)">
                                    <i class="fas fa-school"></i>
                                </div>
                            </div>

                            <div class="button-group">
                                <button type="button" class="btn btn-home" onclick="window.location.href='login.php'"><i class="fas fa-arrow-left"></i> Back to Login</button>
                                <button type="submit" class="btn btn-signin"><i class="fas fa-user-plus"></i> Sign Up</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
            <div class="bottom-left-decoration">
                 <img src="assets/images/students_illustration.png" alt="Students Illustration">
            </div>
            <div class="bottom-right-decoration">
                <img src="assets/images/students_in_class.png" alt="Students in Classroom">
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleButtons = document.querySelectorAll('.role-btn');
        const userRoleInput = document.getElementById('user_role_input');
        
        const teacherFields = document.getElementById('teacher-fields');
        const teacherInputs = teacherFields.querySelectorAll('input');
        
        const commonUsernameField = document.getElementById('common-username-field');
        const commonUsernameInput = commonUsernameField.querySelector('input');

        function toggleFields(role) {
            if (role === 'teacher') {
                // Show teacher fields and make them required
                teacherFields.style.maxHeight = teacherFields.scrollHeight + 'px';
                teacherInputs.forEach(input => input.required = true);

                // Hide common username field and make it not required
                commonUsernameField.style.maxHeight = '0';
                commonUsernameInput.required = false;

            } else {
                // Hide teacher fields and make them not required
                teacherFields.style.maxHeight = '0';
                teacherInputs.forEach(input => input.required = false);

                // Show common username field and make it required
                commonUsernameField.style.maxHeight = commonUsernameField.scrollHeight + 'px';
                commonUsernameInput.required = true;
            }
        }

        roleButtons.forEach(button => {
            button.addEventListener('click', function() {
                // UI active state
                roleButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Set hidden input value
                const selectedRole = this.getAttribute('data-role');
                userRoleInput.value = selectedRole;

                // Show/hide relevant fields
                toggleFields(selectedRole);
            });
        });

        // Set initial state on page load
        const initialRole = document.querySelector('.role-btn.active').getAttribute('data-role');
        toggleFields(initialRole);
    });
    </script>
</body>
</html>

