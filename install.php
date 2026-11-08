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

// Create sports table
$conn->exec(
    "CREATE TABLE tblsports (
        SportID INT(3) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        SportName VARCHAR(30) NOT NULL UNIQUE
    )"
);
echo("tblsports created<br>");

// Create teams table
$conn->exec(
    "CREATE TABLE tblteams (
        TeamID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        SportID INT UNSIGNED NOT NULL,
        TeamName VARCHAR(50) NOT NULL
    )"
);

echo("tblteams created<br>");

// Create events table
$conn->exec(
    "CREATE TABLE tblevents (
        EventID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        SportID INT UNSIGNED NOT NULL,
        HomeTeamID INT UNSIGNED NOT NULL,
        AwayTeamID INT UNSIGNED NOT NULL,
        EventDate DATE NOT NULL,
        EventTime TIME NOT NULL,
        HomeWinOdds DECIMAL(4, 2) NOT NULL,
        DrawOdds DECIMAL(4, 2) DEFAULT NULL,
        AwayWinOdds DECIMAL(4, 2) NOT NULL,
        Result ENUM('Home', 'Draw', 'Away', 'Pending') NOT NULL DEFAULT 'Pending'
    )"
);

echo("tblevents created <br>");

// Create bets table
$conn->exec(
    "CREATE TABLE tblbets (
        BetID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        UserID INT UNSIGNED NOT NULL,
        SportID INT UNSIGNED NOT NULL,
        EventID INT UNSIGNED NOT NULL,
        BetAmount INT UNSIGNED NOT NULL,
        UserBetPrediction ENUM('Home', 'Draw', 'Away') NOT NULL,
        UserBetResult ENUM('Win', 'Loss', 'Pending') NOT NULL DEFAULT 'Pending',
        BetDateTime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        BetOdds DECIMAL(4, 2) NOT NULL,
        Payout INT UNSIGNED NOT NULL DEFAULT 0,
    )"
);

echo("tblbets created<br>");

?>