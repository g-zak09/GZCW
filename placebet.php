<?php
// placebet.php validates a bet, records it and subtracts the stake from the user's balance.

include_once("connection.php");
include_once("functions.php");
requireLogin();

if (isAdmin()) {
    setFlash("error", "Admin accounts cannot place bets.");
    header("Location: admin_dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: sports.php");
    exit;
}

$eventID = filter_input(INPUT_POST, "event_id", FILTER_VALIDATE_INT);
$betAmount = filter_input(INPUT_POST, "bet_amount", FILTER_VALIDATE_INT);
$prediction = $_POST["prediction"] ?? "";
$validPredictions = ["Home", "Draw", "Away"];

if (!$eventID || !$betAmount || $betAmount <= 0 || !in_array($prediction, $validPredictions, true)) {
    setFlash("error", "The bet information was invalid.");
    header("Location: sports.php");
    exit;
}

try {
    $conn->beginTransaction();

    // Lock the event while its current state and odds are checked.
    $stmtEvent = $conn->prepare(
        "SELECT * FROM tblevents WHERE EventID = :event_id FOR UPDATE"
    );
    $stmtEvent->execute([":event_id" => $eventID]);
    $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception("Event not found.");
    }

    if ($event["Result"] !== "Pending" || eventHasStarted($event["EventDate"], $event["EventTime"])) {
        throw new Exception("Betting is closed for this event.");
    }

    if ($prediction === "Draw" && $event["DrawOdds"] === null) {
        throw new Exception("A draw is not available for this event.");
    }

    if ($prediction === "Home") {
        $betOdds = $event["HomeWinOdds"];
    }
    elseif ($prediction === "Draw") {
        $betOdds = $event["DrawOdds"];
    }
    else {
        $betOdds = $event["AwayWinOdds"];
    }

    // Lock the user row so simultaneous bets cannot spend the same points twice.
    $stmtUser = $conn->prepare(
        "SELECT Balance FROM tblusers WHERE UserID = :user_id FOR UPDATE"
    );
    $stmtUser->execute([":user_id" => $_SESSION["loggedinuser"]]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user || $betAmount > $user["Balance"]) {
        throw new Exception("You do not have enough points for that stake.");
    }

    $stmtBet = $conn->prepare(
        "INSERT INTO tblbets
        (UserID, SportID, EventID, BetAmount, UserBetPrediction, UserBetResult, BetDateTime, BetOdds, Payout)
        VALUES
        (:user_id, :sport_id, :event_id, :bet_amount, :prediction, 'Pending', NOW(), :bet_odds, 0)"
    );
    $stmtBet->execute([
        ":user_id" => $_SESSION["loggedinuser"],
        ":sport_id" => $event["SportID"],
        ":event_id" => $eventID,
        ":bet_amount" => $betAmount,
        ":prediction" => $prediction,
        ":bet_odds" => $betOdds
    ]);

    $stmtBalance = $conn->prepare(
        "UPDATE tblusers
         SET Balance = Balance - :bet_amount
         WHERE UserID = :user_id"
    );
    $stmtBalance->execute([
        ":bet_amount" => $betAmount,
        ":user_id" => $_SESSION["loggedinuser"]
    ]);

    $conn->commit();
    setFlash("success", "Bet placed successfully for " . $betAmount . " points.");
    header("Location: dashboard.php");
    exit;
}
catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    setFlash("error", $e->getMessage());
    header("Location: event.php?id=" . urlencode((string)$eventID));
    exit;
}
?>
