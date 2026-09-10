<?php

$servername = "localhost";
$username = "root";
$password = "root";

    // Connect to server
    $conn = new PDO(
        "mysql:host=$servername;",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES => false]
        );

    // Create database
    $conn->exec("CREATE DATABASE IF NOT EXISTS GZCW");
    $conn->exec("USE GZCW");
    echo("Database created successfully<br>");

    
     //Drop existing tables
     //Child tables are dropped before parent tables because of foreign keys

    $conn->exec("DROP TABLE IF EXISTS tblbets");
    $conn->exec("DROP TABLE IF EXISTS tblevents");
    $conn->exec("DROP TABLE IF EXISTS tblteams");
    $conn->exec("DROP TABLE IF EXISTS tblsports");
    $conn->exec("DROP TABLE IF EXISTS tblusers");

    // Create users table
    // Username is unique because the username is used to identify one account at login
    $conn->exec(
        "CREATE TABLE tblusers (
            UserID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            AccountType ENUM('Admin', 'User') NOT NULL DEFAULT 'User',
            Forename VARCHAR(30) NOT NULL,
            Surname VARCHAR(30) NOT NULL,
            Username VARCHAR(30) NOT NULL UNIQUE,
            Password VARCHAR(255) NOT NULL,
            DateOfBirth DATE NOT NULL,
            Balance INT UNSIGNED NOT NULL DEFAULT 1000
        ) ENGINE=InnoDB"
    );

    echo("tblusers created<br>");

    // Add users test data
    $stmtUsers = $conn->prepare(
        "INSERT INTO tblusers (
            AccountType,
            Forename,
            Surname,
            Username,
            Password,
            DateOfBirth,
            Balance
        ) VALUES (
            :account_type,
            :forename,
            :surname,
            :username,
            :password,
            :date_of_birth,
            :balance
        )"
    );

    $stmtUsers->execute([
        ":account_type" => "Admin",
        ":forename" => "gabriel",
        ":surname" => "zakrzewski",
        ":username" => "zakrzewski.g",
        ":password" => password_hash("password", PASSWORD_DEFAULT),
        ":date_of_birth" => "2000-01-01",
        ":balance" => 999999999
    ]);

    // This balance is 1060 because of the bets placed in the test data below.
    // Starting 1000 - 100 stake + 210 payout - 50 losing stake = 1060
    $stmtUsers->execute([
        ":account_type" => "User",
        ":forename" => "james",
        ":surname" => "depree",
        ":username" => "depree.j",
        ":password" => password_hash("password", PASSWORD_DEFAULT),
        ":date_of_birth" => "2000-01-01",
        ":balance" => 1060
    ]);

    echo("tblusers test data added<br>");

    // Create sports table.
    $conn->exec(
        "CREATE TABLE tblsports (
            SportID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            SportName VARCHAR(30) NOT NULL UNIQUE
        ) ENGINE=InnoDB"
    );

    echo("tblsports created<br>");

    // Add sports test data
    // Formula 1 is stored now as future test data, but the first working betting version
    // uses Football and Basketball because the current event model is team-v-team
    $conn->exec(
        "INSERT INTO tblsports (SportName)
         VALUES
            ('Football'),
            ('Basketball'),
            ('Formula 1')"
    );

    echo("tblsports test data added<br>");

    // Create teams table
    $conn->exec(
        "CREATE TABLE tblteams (
            TeamID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            SportID INT UNSIGNED NOT NULL,
            TeamName VARCHAR(50) NOT NULL,
            CONSTRAINT fk_team_sport
                FOREIGN KEY (SportID) REFERENCES tblsports(SportID)
                ON UPDATE CASCADE ON DELETE RESTRICT,
            UNIQUE KEY unique_team_per_sport (SportID, TeamName)
        ) ENGINE=InnoDB"
    );

    echo("tblteams created<br>");

    // Add teams / competitors test data
    $conn->exec(
        "INSERT INTO tblteams (SportID, TeamName)
         VALUES
            (1, 'Arsenal'),
            (1, 'Chelsea'),
            (1, 'Liverpool'),
            (1, 'Manchester City'),
            (2, 'Los Angeles Lakers'),
            (2, 'San Antonio Spurs'),
            (2, 'Boston Celtics'),
            (2, 'Golden State Warriors'),
            (3, 'Ferrari'),
            (3, 'McLaren'),
            (3, 'Mercedes'),
            (3, 'Red Bull Racing')"
    );

    echo("tblteams test data added<br>");

    // Create sporting events table
    // DrawOdds can be NULL for a sport such as basketball where the event must have a winner
    // Result now also includes Draw so that it matches the DrawOdds field
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
            Result ENUM('Home', 'Draw', 'Away', 'Pending') NOT NULL DEFAULT 'Pending',
            CONSTRAINT fk_event_sport
                FOREIGN KEY (SportID) REFERENCES tblsports(SportID)
                ON UPDATE CASCADE ON DELETE RESTRICT,
            CONSTRAINT fk_event_home_team
                FOREIGN KEY (HomeTeamID) REFERENCES tblteams(TeamID)
                ON UPDATE CASCADE ON DELETE RESTRICT,
            CONSTRAINT fk_event_away_team
                FOREIGN KEY (AwayTeamID) REFERENCES tblteams(TeamID)
                ON UPDATE CASCADE ON DELETE RESTRICT
        ) ENGINE=InnoDB"
    );

    echo("tblevents created<br>");

    // Add sporting events test data
    // The first and third events are completed so betting history can be tested
    // The second and fourth events are future/pending so new bets can be tested
    $conn->exec(
        "INSERT INTO tblevents (
            SportID,
            HomeTeamID,
            AwayTeamID,
            EventDate,
            EventTime,
            HomeWinOdds,
            DrawOdds,
            AwayWinOdds,
            Result
        ) VALUES
            (1, 1, 2, '2026-08-20', '15:00:00', 2.10, 3.30, 3.00, 'Home'),
            (1, 1, 3, '2026-10-10', '15:00:00', 2.40, 3.20, 2.80, 'Pending'),
            (2, 5, 6, '2026-08-22', '18:00:00', 1.70, NULL, 2.10, 'Away'),
            (2, 7, 8, '2026-10-12', '19:30:00', 1.85, NULL, 1.95, 'Pending')"
    );

    echo("tblevents test data added<br>");

    // Create bets table
    // UserID and Payout are included because they are required by the coursework table design
    // Pending is included because a bet needs a state before the event result is entered
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
            CONSTRAINT fk_bet_user
                FOREIGN KEY (UserID) REFERENCES tblusers(UserID)
                ON UPDATE CASCADE ON DELETE RESTRICT,
            CONSTRAINT fk_bet_sport
                FOREIGN KEY (SportID) REFERENCES tblsports(SportID)
                ON UPDATE CASCADE ON DELETE RESTRICT,
            CONSTRAINT fk_bet_event
                FOREIGN KEY (EventID) REFERENCES tblevents(EventID)
                ON UPDATE CASCADE ON DELETE RESTRICT
        ) ENGINE=InnoDB"
    );

    echo("tblbets created<br>");

    // Add bets test data
    // These bets belong to user 2 (James) and match the current balance of 1060 points
    $conn->exec(
        "INSERT INTO tblbets (
            UserID,
            SportID,
            EventID,
            BetAmount,
            UserBetPrediction,
            UserBetResult,
            BetDateTime,
            BetOdds,
            Payout
        ) VALUES
            (2, 1, 1, 100, 'Home', 'Win',  '2026-08-19 18:00:00', 2.10, 210),
            (2, 2, 3, 50,  'Home', 'Loss', '2026-08-21 17:00:00', 1.70, 0)"
    );

    echo("tblbets test data added<br>");
    echo("Installation completed successfully.<br>");
    echo("<br>Test login: depree.j / password<br>");
    echo("Admin login: zakrzewski.g / password<br>");
?>