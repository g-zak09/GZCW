<?php
//include("header.php");
?>
<!-- Log in form -->
<section class="form-card narrow-card">
<h1>Log in</h1>
<p class="muted">Access your points and bets.</p>

<form action="login.php" method="post">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" maxlength="30" required><br>    
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required><br>
    <button type="submit" class="button full-width">Log in</button>
</form>

<!-- Link to sign up page for users that need to create an account -->
<p class="form-link">Need to create an account? <a href="signup.php">Sign up</a></p>
</section>
<?php 
//include("footer.php"); 
?>