<?php
session_start();

// If user is already logged in, redirect based on their role
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $role = $_SESSION["role"];
    switch ($role) {
        case 'teacher':
            header("location: Teacher_dashboard.php");
            break;
        case 'principal-secretary':
            header("location: principle_dashboard.php");
            break;
        case 'chief-secretary':
            header("location: chief-dashboard.php");
            break;
        case 'chief-minister':
            header("location: chief_minister-dashboard.php");
            break;
        case 'education-minister':
            header("location: honble_dashboard.php");
            break;
        default:
            header("location: dashboard.php");
            break;
    }
    exit;
}

// Include database connection
require_once 'db_connect.php'; 

$error_message = "";

// Display any flash messages from other pages (like successful registration)
if (isset($_SESSION['flash_message'])) {
    $success_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate inputs
    if (empty(trim($_POST["username"])) || empty(trim($_POST["password"])) || empty(trim($_POST["user_role"]))) {
        $error_message = "Please select a role and enter all credentials.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id, username, password, role FROM users WHERE username = :username AND role = :role";
        
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":role", $param_role, PDO::PARAM_STR);
            
            // Set parameters
            $param_username = trim($_POST["username"]);
            $param_role = trim($_POST["user_role"]);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Check if username and role combination exists
                if($stmt->rowCount() == 1){
                    if($row = $stmt->fetch()){
                        $id = $row["id"];
                        $username = $row["username"];
                        $hashed_password = $row["password"];
                        $role = $row["role"];
                        if(password_verify(trim($_POST["password"]), $hashed_password)){
                            // Password is correct, so start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;                            
                            
                            // Redirect user based on role
                            switch ($role) {
                                case 'teacher':
                                    header("location: Teacher_dashboard.php");
                                    break;
                                case 'principal-secretary':
                                    header("location: principle_dashboard.php");
                                    break;
                                case 'chief-secretary':
                                    header("location: chief-dashboard.php");
                                    break;
                                case 'chief-minister':
                                    header("location: chief_minister-dashboard.php");
                                    break;
                                case 'education-minister':
                                    header("location: honble_dashboard.php");
                                    break;
                                default: // For 'other' and any other roles
                                    header("location: dashboard.php");
                                    break;
                            }
                            exit;
                        } else{
                            // Display an error message if password is not valid
                            $error_message = "The password you entered was not valid.";
                        }
                    }
                } else{
                    // Display an error message if username/role doesn't exist
                    $error_message = "No account found with that username and role.";
                }
            } else{
                $error_message = "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            unset($stmt);
        }
    }
    
    // Close connection
    unset($pdo);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banglar Shiksha | Login</title>
    <link rel="shortcut icon" href="https://banglarshiksha.wb.gov.in/assets/admin/images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/login-style.css">
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
                    <button class="role-btn" data-role="principal-secretary">Principal Secretary, School Education</button>
                    <button class="role-btn" data-role="teacher">Teacher</button>
                    <button class="role-btn" data-role="other">Other Stakeholders</button>
                </nav>
                <div class="form-container">
                    <div class="placeholder-image">
                        <img src="assets/img/login_placeholder.jpg" alt="User Icon">
                    </div>
                    <div class="form-content">
                        <h2>LOGIN</h2>
                        
                        <?php if(!empty($error_message)): ?>
                            <div class="alert-error"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>
                        <?php if(!empty($success_message)): ?>
                            <div class="alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                        <?php endif; ?>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="user_role" id="user_role_input" value="chief-minister">

                            <div class="input-group">
                                <input type="text" name="username" placeholder="User Name" required>
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="input-group">
                                <input type="password" name="password" placeholder="Password" required>
                                <i class="fas fa-lock"></i>
                            </div>
                            <div class="captcha-group">
                                <span class="captcha-code">75887</span>
                                <i class="fas fa-sync-alt captcha-refresh"></i>
                                <input type="text" name="captcha" placeholder="Captcha" required>
                            </div>
                            <div class="button-group">
                                <button type="button" class="btn btn-home" onclick="window.location.href='signup.php'"><i class="fas fa-user-plus"></i> Sign Up</button>
                                <button type="submit" class="btn btn-signin"><i class="fas fa-sign-in-alt"></i> Sign In</button>
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
    <script src="js/login-script.js"></script>
</body>
</html>
