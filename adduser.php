<?php
// adduser.php validates the signup form and inserts a new user into tblusers

include_once("connection.php");
include_once("functions.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: signup.php");
    exit;
}

$forename = trim($_POST["forename"] ?? "");
$surname = trim($_POST["surname"] ?? "");
$usernameInput = trim($_POST["username"] ?? "");
$passwordInput = $_POST["password"] ?? "";
$dateOfBirth = $_POST["dateofbirth"] ?? "";

// Validation: all fields must contain data
if ($forename === "" || $surname === "" || $usernameInput === "" || $passwordInput === "" || $dateOfBirth === "") {
    setFlash("error", "All signup fields must be completed.");
    header("Location: signup.php");
    exit;
}

// Validation: names may contain letters, spaces, apostrophes and hyphens
if (!preg_match("/^[\\p{L} '-]+$/u", $forename) || !preg_match("/^[\\p{L} '-]+$/u", $surname)) {
    setFlash("error", "Forename and surname can only contain letters, spaces, apostrophes and hyphens.");
    header("Location: signup.php");
    exit;
}

// Validation: username length and allowed characters
if (strlen($usernameInput) < 3 || strlen($usernameInput) > 30 || !preg_match("/^[A-Za-z0-9._-]+$/", $usernameInput)) {
    setFlash("error", "Username must be 3-30 characters and can use letters, numbers, dots, underscores and hyphens.");
    header("Location: signup.php");
    exit;
}

// Validation: password must meet the requirement in the table design
if (strlen($passwordInput) < 8) {
    setFlash("error", "Password must contain at least 8 characters.");
    header("Location: signup.php");
    exit;
}

// Validation: date must be real and user must be at least 18
$dobObject = DateTime::createFromFormat("Y-m-d", $dateOfBirth);
$dobErrors = DateTime::getLastErrors();
$dateIsValid = $dobObject && ($dobErrors === false || ($dobErrors["warning_count"] === 0 && $dobErrors["error_count"] === 0));

if (!$dateIsValid || calculateAge($dateOfBirth) < 18) {
    setFlash("error", "You must enter a valid date of birth and be at least 18 years old.");
    header("Location: signup.php");
    exit;
}

try {
    // Check that the username is unique before trying to insert it
    $stmtCheck = $conn->prepare("SELECT UserID FROM tblusers WHERE Username = :username");
    $stmtCheck->execute([":username" => $usernameInput]);

    if ($stmtCheck->fetch()) {
        setFlash("error", "That username is already in use. Please choose another.");
        header("Location: signup.php");
        exit;
    }

    // Users cannot choose Admin or their starting balance themselves
    $accountType = "User";
    $startingBalance = 1000;
    $hashedPassword = password_hash($passwordInput, PASSWORD_DEFAULT);

    $stmtAdd = $conn->prepare(
        "INSERT INTO tblusers
        (AccountType, Forename, Surname, Username, Password, DateOfBirth, Balance)
        VALUES
        (:account_type, :forename, :surname, :username, :password, :date_of_birth, :balance)"
    );

    $stmtAdd->execute([
        ":account_type" => $accountType,
        ":forename" => $forename,
        ":surname" => $surname,
        ":username" => $usernameInput,
        ":password" => $hashedPassword,
        ":date_of_birth" => $dateOfBirth,
        ":balance" => $startingBalance
    ]);

    setFlash("success", "Account created successfully. You can now log in.");
    header("Location: loginpage.php");
    exit;
}
catch (PDOException $e) {
    setFlash("error", "The account could not be created. Please try again.");
    header("Location: signup.php");
    exit;
}
?>
