<?php include 'partials/login-header.php'; ?>
3.  In the `<head>` of your `login.php`, **link the new stylesheet**:
```html
<link rel="stylesheet" href="css/header-style.css">
4.  Just before the closing `</body>` tag of `login.php`, **link the new script**:
```html
<script src="js/header-script.js"></script>

<header class="login-header">
    <div class="logo-container">
        <img src="assets/images/banglar-shiksha-logo.png" alt="Banglar Shiksha Logo" class="logo">
        <img src="assets/img/shikha-logo.png" alt="Shikha Logo" class="logo">
        <img src="assets/images/educatio_first-logo.png" alt="Education First Logo" class="logo">
    </div>
    <nav class="role-navigation">
        <button class="role-btn active" data-role="teacher">Teacher</button>
        <button class="role-btn" data-role="admin">Admin</button>
        <button class="role-btn" data-role="headmaster">Headmaster (H.O.)</button>
        <button class="role-btn" data-role="principal">Principal</button>
        <button class="role-btn" data-role="secretary">Secretary</button>
        <button class="role-btn" data-role="other">Other Stakeholders</button>
    </nav>
</header>
