<?php
// Reusable utility functions used by several pages.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function isLoggedIn() {
    return isset($_SESSION["loggedinuser"]);
}

function isAdmin() {
    return isset($_SESSION["accounttype"]) && $_SESSION["accounttype"] === "Admin";
}

function requireLogin() {
    if (!isLoggedIn()) {
        setFlash("error", "You must log in before viewing that page.");
        header("Location: loginpage.php");
        exit;
    }
}

function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        setFlash("error", "You do not have permission to view that page.");
        header("Location: index.php");
        exit;
    }
}

function setFlash($type, $message) {
    $_SESSION["flash"] = [
        "type" => $type,
        "message" => $message
    ];
}

function getFlash() {
    if (!isset($_SESSION["flash"])) {
        return null;
    }

    $flash = $_SESSION["flash"];
    unset($_SESSION["flash"]);
    return $flash;
}

function getCurrentUser($conn) {
    if (!isLoggedIn()) {
        return null;
    }

    $stmt = $conn->prepare(
        "SELECT UserID, AccountType, Forename, Surname, Username, DateOfBirth, Balance
         FROM tblusers
         WHERE UserID = :user_id"
    );
    $stmt->execute([":user_id" => $_SESSION["loggedinuser"]]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function eventHasStarted($eventDate, $eventTime) {
    $eventDateTime = new DateTime($eventDate . " " . $eventTime);
    $now = new DateTime();
    return $eventDateTime <= $now;
}

function calculateAge($dateOfBirth) {
    $dob = DateTime::createFromFormat("Y-m-d", $dateOfBirth);
    if (!$dob) {
        return -1;
    }

    $today = new DateTime();
    return $today->diff($dob)->y;
}
?>
