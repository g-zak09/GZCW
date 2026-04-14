<?php

// Create database
$servername="localhost";
$username="root";
$password="root";
$conn=new PDO("mysql:host=$servername",$username,$password);
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
AccountType ENUM('Admin', 'User') NOT NULL,
Forename VARCHAR(30) NOT NULL,
Surname VARCHAR(30) NOT NULL,
Username VARCHAR(30) NOT NULL,
Password VARCHAR(255) NOT NULL,
DateOfBirth DATE NOT NULL,
Balance INT(9) NOT NULL
);
");
$stmt->execute();
echo("tblusers created<br>");

// Add users test data
$hashedpassword1=password_hash("password",PASSWORD_DEFAULT);
echo($hashedpassword1."<br>");
$stmt=$conn->prepare("INSERT INTO tblusers
    (UserID,AccountType,Forename,Surname,Username,Password,DateOfBirth,Balance)
    VALUES
    (NULL,'Admin','gabriel','zakrzewski','zakrzewski.g', :Password, '2000-01-01', 999999999)
    ");
$stmt->bindParam(":Password", $hashedpassword1);
$stmt->execute();

$hashedpassword2=password_hash("password",PASSWORD_DEFAULT);
echo($hashedpassword2."<br>");
$stmt=$conn->prepare("INSERT INTO tblusers
    (UserID,AccountType,Forename,Surname,Username,Password,DateOfBirth,Balance)
    VALUES
    (NULL,'User','james','depree','depree.j', :Password, '2000-01-01', 100000)
    ");
$stmt->bindParam(":Password", $hashedpassword2);
$stmt->execute();
echo("tblusers test data added<br>");

// Create sports table
$stmt=$conn->prepare("DROP TABLE IF EXISTS tblsports;
CREATE TABLE tblsports
(SportID INT(3) UNSIGNED AUTO_INCREMENT PRIMARY  KEY,
SportName VARCHAR(30) NOT NULL
);
");
$stmt->execute();
echo("tblsports created<br>");

// Add sports test data
$stmt=$conn->prepare("INSERT INTO tblsports
    (SportID,SportName)
    VALUES
    (NULL, 'Football'),
    (NULL, 'Basketball')
    ");
$stmt->execute();
echo("tblsports test data added<br>");

// Create teams table
$stmt=$conn->prepare("DROP TABLE IF EXISTS tblteams;
CREATE TABLE tblteams
(TeamID INT(4) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
SportID INT(3) UNSIGNED NOT NULL,
TeamName VARCHAR(50) NOT NULL
);
");
$stmt->execute();
echo("tblteams created<br>");

// Add teams test data
$stmt=$conn->prepare("INSERT INTO tblteams
    (TeamID,SportID,TeamName)
    VALUES
    (NULL, 1, 'Tottenham'),
    (NULL, 1, 'Arsenal'),
    (NULL, 2, 'Los Angeles Lakers'),
    (NULL, 2, 'San Antonio Spurs')
    ");
$stmt->execute();
echo("tblteams test data added<br>");

// Create sporting events table
$stmt=$conn->prepare("DROP TABLE IF EXISTS tblevents;
CREATE TABLE tblevents
(EventID INT(5) UNSIGNED AUTO_INCREMENT PRIMARY  KEY,
SportID INT(3) UNSIGNED NOT NULL,
HomeTeamID INT(4) UNSIGNED NOT NULL,
AwayTeamID INT(4) UNSIGNED NOT NULL,
EventDate DATE NOT NULL,
EventTime TIME NOT NULL,
HomeWinOdds DEC(4,2) NOT NULL,
AwayWinOdds DEC(4,2) NOT NULL,
Result ENUM('Home', 'Away', 'Draw', 'Pending') NOT NULL
);
");
$stmt->execute();
echo("tblevents created<br>");

// Add sporting events test data
$stmt=$conn->prepare("INSERT INTO tblevents
    (EventID,SportID,HomeTeamID,AwayTeamID,EventDate,EventTime,HomeWinOdds,AwayWinOdds,Result)
    VALUES
    (NULL, 1, 1, 2, '2024-07-01', '15:00:00', 1.50, 2.50, 'Home'),
    (NULL, 2, 3, 4, '2024-07-02', '18:00:00', 1.80, 2.20, 'Away'),
    (NULL, 1, 2, 1, '2024-07-03', '20:00:00', 2.50, 1.50, 'Draw'),
    (NULL, 2, 4, 3, '2024-07-04', '19:00:00', 2.20, 1.80, 'Pending')

    ");
$stmt->execute();
echo("tblevents test data added<br>");


// Create bets table
$stmt=$conn->prepare("DROP TABLE IF EXISTS tblbets;
CREATE TABLE tblbets
(BetID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY  KEY,
SportID INT(3) UNSIGNED NOT NULL,
EventID INT(5) UNSIGNED NOT NULL,
BetAmount INT(6) NOT NULL,
UserBetPrediction ENUM('Home', 'Away', 'Draw') NOT NULL,
UserBetResult ENUM('Win', 'Loss'),
BetDateTime DATETIME(0) NOT NULL,
BetOdds DEC(4,2) NOT NULL
);
");
$stmt->execute();
echo("tblbets created<br>");



?>
