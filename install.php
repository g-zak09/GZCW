<?php
$servername="localhost";
$username="root";
$password="root";

// Connect to server
$conn=new PDO("mysql:host=$servername", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create database
$conn->exec("CREATE DATABASE IF NOT EXISTS GZCW");
$conn->exec("USE GZCW");
echo("DB created successfully<br>");

// Drop existing tables
$conn->exec("DROP TABLE IF EXISTS tblbets");
$conn->exec("DROP TABLE IF EXISTS tblevents");
$conn->exec("DROP TABLE IF EXISTS tblteams");
$conn->exec("DROP TABLE IF EXISTS tblsports");
$conn->exec("DROP TABLE IF EXISTS tblusers");

// Create users table
$conn->exec(
    "CREATE TABLE tblusers (
        UserID INT(4) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        AccountType ENUM('Admin', 'User') NOT NULL DEFAULT 'User',
        Forename VARCHAR(30) NOT NULL,
        Surname VARCHAR(30) NOT NULL,
        Username VARCHAR(30) NOT NULL UNIQUE,
        Password VARCHAR(255) NOT NULL,
        DateOfBirth DATE NOT NULL,
        Balance INT(9) NOT NULL DEFAULT 1000
    )"
);
echo("tblusers created<br>");

// Add users test data
$stmtUsers = $conn->prepare(
    "INSERT INTO tblusers(
        AccountType, Forename, Surname, Username, Password, DateOfBirth, Balance) 
    VALUES(
        :account_type, :forename, :surname, :username, :password, :date_of_birth, :balance)"
);

// add admin
$hashedpassword1=password_hash("password", PASSWORD_DEFAULT);
echo($hashedpassword1 . "<br>");
$stmtUsers->execute(
    [":account_type" => "Admin",
    ":forename" => "gabriel",
    ":surname" => "zakrzewski",
    ":username" => "zakrzewski.g",
    ":password" => $hashedpassword1,
    ":date_of_birth" => "2000-01-01",
    ":balance" => 999999999]
    );

// add user
$hashedpassword2=password_hash("password", PASSWORD_DEFAULT);
echo($hashedpassword2 . "<br>");
$stmtUsers->execute(
    [":account_type" => "User",
    ":forename" => "james",
    ":surname" => "depree",
    ":username" => "depree.j",
    ":password" => $hashedpassword2,
    ":date_of_birth" => "2000-01-01",
    ":balance" => 1000]
    );

echo("tblusers test data added<br>");

// Create sports table
$conn->exec(
    "CREATE TABLE tblsports (
        SportID INT(3) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        SportName VARCHAR(30) NOT NULL UNIQUE
    )"
);
echo("tblsports created<br>");

// Add sports test data
$conn->exec(
    "INSERT INTO tblsports (SportName)
        VALUES
        ('Football'),
        ('Basketball')"
);

echo("tblsports test data added<br>");

// Create teams table
$conn->exec(
    "CREATE TABLE tblteams (
        TeamID INT(4) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        SportID INT(3) UNSIGNED NOT NULL,
        TeamName VARCHAR(50) NOT NULL
    )"
);

echo("tblteams created<br>");

// Add teams test data
$conn->exec(
    "INSERT INTO tblteams (SportID, TeamName)
        VALUES
        (1, 'Arsenal'),
        (1, 'Manchester City'),
        (2, 'Los Angeles Lakers'),
        (2, 'San Antonio Spurs')"
);

echo("tblteams test data added<br>");

// Create events table
$conn->exec(
    "CREATE TABLE tblevents (
        EventID INT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        SportID INT(3) UNSIGNED NOT NULL,
        HomeTeamID INT(4) UNSIGNED NOT NULL,
        AwayTeamID INT(4) UNSIGNED NOT NULL,
        EventDate DATE NOT NULL,
        EventTime TIME NOT NULL,
        HomeWinOdds DEC(4, 2) NOT NULL,
        DrawOdds DEC(4, 2) DEFAULT NULL,
        AwayWinOdds DEC(4, 2) NOT NULL,
        Result ENUM('Home', 'Draw', 'Away', 'Pending') NOT NULL DEFAULT 'Pending'
    )"
);

echo("tblevents created <br>");

// Add events test data
$conn->exec(
    "INSERT INTO tblevents 
    (SportID,HomeTeamID,AwayTeamID,EventDate,EventTime,HomeWinOdds,DrawOdds,AwayWinOdds,Result)
    VALUES
    (1, 1, 2, '2026-08-20', '15:00:00', 2.10, 3.30, 3.00, 'Home'),
    (1, 1, 3, '2026-10-10', '15:00:00', 2.40, 3.20, 2.80, 'Pending'),
    (2, 5, 6, '2026-08-22', '18:00:00', 1.70, NULL, 2.10, 'Away'),
    (2, 7, 8, '2026-10-12', '19:30:00', 1.85, NULL, 1.95, 'Pending')"
    );

echo("tblevents test data added<br>");

// Create bets table
$conn->exec(
    "CREATE TABLE tblbets (
        BetID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        UserID INT(4) UNSIGNED NOT NULL,
        SportID INT(3) UNSIGNED NOT NULL,
        EventID INT(5) UNSIGNED NOT NULL,
        BetAmount INT(6) UNSIGNED NOT NULL,
        UserBetPrediction ENUM('Home', 'Draw', 'Away') NOT NULL,
        UserBetResult ENUM('Win', 'Loss', 'Pending') NOT NULL DEFAULT 'Pending',
        BetDateTime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        BetOdds DECIMAL(4, 2) NOT NULL,
        Payout INT UNSIGNED NOT NULL DEFAULT 0
    )"
);

echo("tblbets created<br>");

// Add bets test data
$conn->exec(
    "INSERT INTO tblbets 
    (UserID,SportID,EventID,BetAmount,UserBetPrediction,UserBetResult,BetDateTime,BetOdds,Payout) 
    VALUES
    (2, 1, 1, 100, 'Home', 'Win',  '2026-08-19 18:00:00', 2.10, 210),
    (2, 2, 3, 50,  'Home', 'Loss', '2026-08-21 17:00:00', 1.70, 0)"
);

echo("tblbets  test data created<br>");

?>