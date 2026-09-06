<?php
$pageTitle = "Sign up";
include("header.php");

// The newest allowed date of birth is exactly 18 years ago.
$maxDateOfBirth = (new DateTime())->modify("-18 years")->format("Y-m-d");
?>
<section class="form-card narrow-card">
    <h1>Create an account</h1>
    <p class="muted">Every new user starts with 1,000 points. Accounts are for users aged 18 or over.</p>

    <form action="adduser.php" method="post">
        <label for="forename">Forename</label>
        <input type="text" id="forename" name="forename" maxlength="30" required>

        <label for="surname">Surname</label>
        <input type="text" id="surname" name="surname" maxlength="30" required>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" maxlength="30" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" minlength="8" required>
        <p class="field-help">Minimum 8 characters.</p>

        <label for="dateofbirth">Date of birth</label>
        <input type="date" id="dateofbirth" name="dateofbirth" max="<?= h($maxDateOfBirth) ?>" required>

        <button type="submit" class="button full-width">Create account</button>
    </form>

    <p class="form-link">Already registered? <a href="loginpage.php">Log in</a>.</p>
</section>
<?php include("footer.php"); ?>
