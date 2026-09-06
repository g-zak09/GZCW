<?php
$pageTitle = "Log in";
include("header.php");
?>
<section class="form-card narrow-card">
    <h1>Log in</h1>
    <p class="muted">Access your points balance, bets and dashboard.</p>

    <form action="login.php" method="post">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" maxlength="30" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="button full-width">Log in</button>
    </form>

    <p class="form-link">Need an account? <a href="signup.php">Sign up</a>.</p>
</section>
<?php include("footer.php"); ?>
