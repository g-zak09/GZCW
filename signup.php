<?php
// Variable to set a min 18 age and max age of 100
$maxDOB = (new DateTime())->modify("-18 years")->format("Y-m-d");
$minDOB = (new DateTime())->modify("-100 years")->format("Y-m-d");
?>
<!-- Sign up form -->
<section class="form-card narrow-card">
<h1>Create an account</h1>
    
<form action="adduser.php" method="post">
    <label for="forename"> Forename </label>
    <input type="text" id="forename" name="forename" maxlength="30" required><br>
    <label for="surname"> Surname </label>
    <input type="text" id="surname" name="surname" maxlength="30" required><br>
    <label for="username"> Username </label>
    <input type="text" id="username" name="username" minlength="3" maxlength="30" required><br>
    <label for="password"> Password </label>
    <input type="password" id="password" name="password" minlength="8" required>
    <p class="field-help">Minimum 8 characters.</p>
    <label for="dateofbirth"> Date of birth </label>
    <input type="date" id="dateofbirth" name="dateofbirth" max="<?php echo($maxDOB);?>" min="<?php echo($minDOB);?>" required><br>
    <button type="submit" class="button full-width">Create account</button>
</form>

<!-- Rules for all users -->
<p class="muted">Start with 1000 points. Users have to be 18+ to create an account.</p>
<!-- Link to login page for users that already have an account -->
<p class="form-link">Already have an account?<a href="loginpage.php">Log in</a></p>
</section>
