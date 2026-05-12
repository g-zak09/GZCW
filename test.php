<?php
// admin.php

session_start();

// Define  admin password securely, in production, store hashed in DB
define('ADMIN_PASSWORD', 'Secret123'); // Example only change it

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if (hash_equals(ADMIN_PASSWORD, $password)) {
        $_SESSION['is_admin'] = true;
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
</head>
<body>

<div class="modal">
    <div class="modal-content">
        <h2>Admin Login</h2>
        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="password" name="password" placeholder="Enter admin password" required>
            <br>
            <button type="submit">Login</button>
        </form>
    </div>
</div>

</body>
</html>