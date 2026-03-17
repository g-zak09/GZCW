<?php

// Create database
$servername = "localhost";
$username = "root";
$password = "root";
$conn= new PDO("mysql:host=$servername",$username,$password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql="CREATE DATABASE IF NOT EXISTS GZCW";
$conn->exec($sql);
$sql="USE GZCW";
$conn->exec($sql);
echo("DB created successfully<br>");

// Create users table
$stmt=$conn->prepare("DROP TABLE IF EXISTS tblusers;
CREATE TABLE tblusers
(UserID INT(4) UNSIGNED AUTO_INCREMENT PRIMARY  KEY,
AccountType ENUM(Admin, User) NOT NULL
Forename VARCHAR(30) NOT NULL,
Surname VARCHAR(30) NOT NULL,
Username VARCHAR(30) NOT NULL,
Password VARCHAR(225) NOT NULL,
DateOfBirth DATE NOT NULL,
Balance INT(9) NOT NULL,
);
");
$stmt->execute();
echo("tblusers created<br>");

// Add users test data
$hashedpassword=password_hash("password",PASSWORD_DEFAULT);
echo($hashedpassword);
$stmt=$conn->prepare("INSERT INTO tblusers
    (UserID,AccountType,Forename,Surname,Username,Password,DateOfBirth,Balance)
    VALUES
    (NULL,'admin','gabriel','zakrzewski','zakrzewski.g', :Password, 01:01:2000, 999999999)
    (NULL,'user','james','de pree','depree.j', :Password, 01:01:2000, 100000)
    ");
$stmt->bindParam(":Password", $hashedpassword);
$stmt->execute();

// Create sports table
$stmt=$conn->prepare("DROP TABLE IF EXISTS tblsports;
CREATE TABLE tblsports
(SportID INT(3) UNSIGNED AUTO_INCREMENT PRIMARY  KEY,
SportName VARCHAR(30) NOT NULL,
);
");
$stmt->execute();
echo("tblsports created<br>");

// Add sports test data
$stmt=$conn->prepare("INSERT INTO tblsports
    (SportID,SportName)
    VALUES
    (NULL, 'Football')
    (NULL, 'Basketball')
    ");

// Create teams table
$stmt=$conn->prepare("DROP TABLE IF EXISTS tblteams;
CREATE TABLE tblteams
(TeamID
SportID INT(3) UNSIGNED AUTO_INCREMENT PRIMARY  KEY,
TeamName VARCHAR(50) NOT NULL,
);
");
$stmt->execute();
echo("tblteams created<br>");

// Create sporting events table
$stmt=$conn->prepare("DROP TABLE IF EXISTS tblevents;
CREATE TABLE tblevents
(EventID INT(5) UNSIGNED AUTO_INCREMENT PRIMARY  KEY,
SportID INT(3) UNSIGNED AUTO_INCREMENT FOREIGN KEY,
HomeTeamID INT(4) UNSIGNED AUTO_INCREMENT FOREIGN KEY,
AwayTeamID INT(4) UNSIGNED AUTO_INCREMENT FOREIGN KEY,
EventDate DATE NOT NULL,
EventTime TIME NOT NULL,
HomeWinOdds DEC(4,2) NOT NULL,
AwayWinOdds DEC(4,2) NOT NULL,
Result ENUM(Home, Away, Draw, Pending) NOT NULL,
);
");
$stmt->execute();
echo("tblevents created<br>");

// Create bets table
$stmt=$conn->prepare("DROP TABLE IF EXISTS tblbets;
CREATE TABLE tblbets
(BetID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY  KEY,
SportID INT(3) UNSIGNED AUTO_INCREMENT FOREIGN KEY,
EventID INT(5) UNSIGNED AUTO_INCREMENT FOREIGN KEY,
BetAmount INT(6) NOT NULL,
UserBetPrediction ENUM(Home, Away, Draw) NOT NULL,
UserBetResult ENUM(Win, Loss)
BetDateTime DATETIME(0) NOT NULL,
BetOdds DEC(4,2) NOT NULL,
);
");
$stmt->execute();
echo("tblbets created<br>");

?>
