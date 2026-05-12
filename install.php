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
$stmtUsers=$conn->prepare("DROP TABLE IF EXISTS tblusers;
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
$stmtUsers->execute();
echo("tblusers created<br>");

// Add users test data
$hashedpassword1=password_hash("password",PASSWORD_DEFAULT);
echo($hashedpassword1."<br>");
$stmtUsersTD=$conn->prepare("INSERT INTO tblusers
    (UserID,AccountType,Forename,Surname,Username,Password,DateOfBirth,Balance)
    VALUES
    (NULL,'Admin','gabriel','zakrzewski','zakrzewski.g', :Password, '2000-01-01', 999999999)
    ");
$stmtUsersTD->bindParam(":Password", $hashedpassword1);
$stmtUsersTD->execute();

$hashedpassword2=password_hash("password",PASSWORD_DEFAULT);
echo($hashedpassword2."<br>");
$stmtUsersTD=$conn->prepare("INSERT INTO tblusers
    (UserID,AccountType,Forename,Surname,Username,Password,DateOfBirth,Balance)
    VALUES
    (NULL,'User','james','depree','depree.j', :Password, '2000-01-01', 1000)
    ");
$stmtUsersTD->bindParam(":Password", $hashedpassword2);
$stmtUsersTD->execute();
echo("tblusers test data added<br>");

// Create sports table
$stmtSports=$conn->prepare("DROP TABLE IF EXISTS tblsports;
CREATE TABLE tblsports
(SportID INT(3) UNSIGNED AUTO_INCREMENT PRIMARY  KEY,
SportName VARCHAR(30) NOT NULL
);
");
$stmtSports->execute();
echo("tblsports created<br>");

// Add sports test data
$stmtSportsTD=$conn->prepare("INSERT INTO tblsports
    (SportID,SportName)
    VALUES
    (NULL, 'Basketball')
    ");
$stmtSportsTD->execute();
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
$stmtTeamsTD=$conn->prepare("INSERT INTO tblteams
    (TeamID,SportID,TeamName)
    VALUES
    (NULL, 1, 'Los Angeles Lakers'),
    (NULL, 1, 'San Antonio Spurs')
    ");
$stmtTeamsTD->execute();
echo("tblteams test data added<br>");

// Create sporting events table
$stmtEvents=$conn->prepare("DROP TABLE IF EXISTS tblevents;
CREATE TABLE tblevents
(EventID INT(5) UNSIGNED AUTO_INCREMENT PRIMARY  KEY,
SportID INT(3) UNSIGNED NOT NULL,
HomeTeamID INT(4) UNSIGNED NOT NULL,
AwayTeamID INT(4) UNSIGNED NOT NULL,
EventDate DATE NOT NULL,
EventTime TIME NOT NULL,
HomeWinOdds DEC(4,2) NOT NULL,
AwayWinOdds DEC(4,2) NOT NULL,
Result ENUM('Home', 'Away', 'Pending') NOT NULL
);
");
$stmtEvents->execute();
echo("tblevents created<br>");

// Add sporting events test data
$stmtEventsTD=$conn->prepare("INSERT INTO tblevents
    (EventID,SportID,HomeTeamID,AwayTeamID,EventDate,EventTime,HomeWinOdds,AwayWinOdds,Result)
    VALUES
    (NULL, 1, 1, 2, '2026-07-01', '15:00:00', 1.50, 2.50, 'Home'),
    (NULL, 1, 2, 1, '2026-07-02', '18:00:00', 1.80, 2.20, 'Home')
    ");
$stmtEventsTD->execute();
echo("tblevents test data added<br>");


// Create bets table
$stmtBets=$conn->prepare("DROP TABLE IF EXISTS tblbets;
CREATE TABLE tblbets
(BetID INT(6) UNSIGNED AUTO_INCREMENT PRIMARY  KEY,
SportID INT(3) UNSIGNED NOT NULL,
EventID INT(5) UNSIGNED NOT NULL,
BetAmount INT(6) NOT NULL,
UserBetPrediction ENUM('Home', 'Away') NOT NULL,
UserBetResult ENUM('Win', 'Loss'),
BetDateTime DATETIME(0) NOT NULL,
BetOdds DEC(4,2) NOT NULL
);
");
$stmtBets->execute();
echo("tblbets created<br>");

// Add bets test data
$stmtBetsTD=$conn->prepare("INSERT INTO tblbets
    (BetID,SportID,EventID,BetAmount,UserBetPrediction,UserBetResult,BetDateTime,BetOdds)
    VALUES
    (NULL, 1, 1, 100, 'Home', 'Win', '2026-04-10', '00:00:00', 1.50),
    (NULL, 1, 1, 50, 'Away', 'Loss', '2026-04-10', '00:00:00', 2.50),
    (NULL, 1, 2, 100, 'Home', 'Win', '2026-04-10', '00:00:00', 1.80),
    (NULL, 1, 2, 200, 'Away', 'Loss', '2026-04-10', '00:00:00', 2.20)
    ");
$stmtBetsTD->execute();
echo("tblbets test data added<br>");

?>
