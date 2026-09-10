<?php
$servername = "localhost";
$username = "root";
$password = "root";

try {
    $conn = new PDO(
        "mysql:host=$servername;dbname=$dbname",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES => false]
    );
}
catch (PDOException $e) {
    // Do not display database details to normal users
    die("Database connection failed. Run install.php first or check connection.php.");
}
?>
