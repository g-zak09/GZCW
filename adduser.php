<?php
// Completes validation checks and inserts user data into database
//include_once("connection.php");
//include_once("functions.php");

//if ($_SERVER["REQUEST_METHOD"] !== "POST") {
//    header("Location: signup.php");
//    exit;
//}

$forename = $_POST["forename"];
$surname = $_POST["surname"];
$usernameInput = $_POST["username"];
$passwordInput = $_POST["password"];
$dateOfBirth = $_POST["dateofbirth"];
echo($forename . "<br>"); 
echo($surname . "<br>");
echo($usernameInput . "<br>");
echo($passwordInput . "<br>");
echo($dateOfBirth . "<br>");
$accountType = "User";
$startingBalance = 1000;
$hashedPassword = password_hash($passwordInput, PASSWORD_DEFAULT);
echo($accountType . "<br>");
echo($startingBalance . "<br>");
echo($hashedPassword . "<br>");

$stmtAdd = $conn->prepare(
    "INSERT INTO tblusers
    (AccountType, Forename, Surname, Username, Password, DateOfBirth, Balance)
    VALUES
    (:account_type, :forename, :surname, :username, :password, :date_of_birth, :balance)"
);

$stmtAdd->execute([
    "account_type" => $accountType,
    "forename" => $forename,
    "surname" => $surname,
    "username" => $usernameInput,
    "password" => $hashedPassword,
    "date_of_birth" => $dateOfBirth,
    "balance" => $startingBalance
]);

setFlash("success", "Account created successfully. You can now log in.");
//header("Location: loginpage.php");
exit;
?>