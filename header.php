<?php
include_once("connection.php");
include_once("functions.php");

if (!isset($pageTitle)) {
    $pageTitle = "PointPlay";
}

$currentUser = getCurrentUser($conn);
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> | PointPlay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="index.php">PointPlay</a>

        <nav class="main-nav">
            <a href="index.php">Home</a>
            <a href="sports.php">Sports</a>

            <?php if (isLoggedIn() && !isAdmin()): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="bet_history.php">Bet History</a>
            <?php endif; ?>

            <?php if (isAdmin()): ?>
                <a href="admin_dashboard.php">Admin</a>
            <?php endif; ?>
        </nav>

        <div class="account-nav">
            <?php if ($currentUser): ?>
                <?php if (!isAdmin()): ?>
                    <span class="points-badge"><?= h($currentUser["Balance"]) ?> pts</span>
                <?php endif; ?>
                <span class="welcome">Hi, <?= h($currentUser["Forename"]) ?></span>
                <a class="button button-small button-secondary" href="logout.php">Log out</a>
            <?php else: ?>
                <a href="loginpage.php">Log in</a>
                <a class="button button-small" href="signup.php">Sign up</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if ($flash): ?>
    <div class="flash <?= h($flash["type"]) ?>">
        <?= h($flash["message"]) ?>
    </div>
<?php endif; ?>

<main class="page-container">
