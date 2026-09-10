<?php
// login.php authenticates the submitted username and password.

include_once("connection.php");
include_once("functions.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: loginpage.php");
    exit;
}

$usernameInput = trim($_POST["username"] ?? "");
$passwordAttempt = $_POST["password"] ?? "";

if ($usernameInput === "" || $passwordAttempt === "") {
    setFlash("error", "Enter both a username and password.");
    header("Location: loginpage.php");
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM tblusers WHERE Username = :username LIMIT 1");
    $stmt->execute([":username" => $usernameInput]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !password_verify($passwordAttempt, $row["Password"])) {
        setFlash("error", "Incorrect username or password.");
        header("Location: loginpage.php");
        exit;
    }

    // Regenerating the session ID reduces the risk of session fixation
    session_regenerate_id(true);
    $_SESSION["firstname"] = $row["Forename"];
    $_SESSION["loggedinuser"] = $row["UserID"];
    $_SESSION["accounttype"] = $row["AccountType"];

    if ($row["AccountType"] === "Admin") {
        header("Location: admin_dashboard.php");
    }
    else {
        header("Location: dashboard.php");
    }
    exit;
}
catch (PDOException $e) {
    setFlash("error", "Login could not be completed. Please try again.");
    header("Location: loginpage.php");
    exit;
}
?>
