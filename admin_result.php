<?php
// admin_result.php settles an event and every pending bet attached to it.

include_once("connection.php");
include_once("functions.php");
requireAdmin();

$eventID = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$eventID) {
    setFlash("error", "Invalid event selected.");
    header("Location: admin_events.php");
    exit;
}

$stmtEvent = $conn->prepare(
    "SELECT e.*, s.SportName, home.TeamName AS HomeTeam, away.TeamName AS AwayTeam
     FROM tblevents e
     INNER JOIN tblsports s ON e.SportID = s.SportID
     INNER JOIN tblteams home ON e.HomeTeamID = home.TeamID
     INNER JOIN tblteams away ON e.AwayTeamID = away.TeamID
     WHERE e.EventID = :event_id"
);
$stmtEvent->execute([":event_id" => $eventID]);
$event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    setFlash("error", "Event not found.");
    header("Location: admin_events.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!eventHasStarted($event["EventDate"], $event["EventTime"])) {
        setFlash("error", "A result cannot be entered before the event has started.");
        header("Location: admin_events.php");
        exit;
    }

    $result = $_POST["result"] ?? "";
    $validResults = ["Home", "Away"];
    if ($event["DrawOdds"] !== null) {
        $validResults[] = "Draw";
    }

    if (!in_array($result, $validResults, true)) {
        setFlash("error", "Invalid result selected.");
        header("Location: admin_result.php?id=" . urlencode((string)$eventID));
        exit;
    }

    try {
        $conn->beginTransaction();

        // Lock the event and check it has not already been settled.
        $stmtLock = $conn->prepare("SELECT Result FROM tblevents WHERE EventID = :event_id FOR UPDATE");
        $stmtLock->execute([":event_id" => $eventID]);
        $currentResult = $stmtLock->fetchColumn();

        if ($currentResult !== "Pending") {
            throw new Exception("This event has already been settled.");
        }

        // Save the final event result.
        $stmtUpdateEvent = $conn->prepare(
            "UPDATE tblevents SET Result = :result WHERE EventID = :event_id"
        );
        $stmtUpdateEvent->execute([
            ":result" => $result,
            ":event_id" => $eventID
        ]);

        // Select all unresolved bets for this event.
        $stmtBets = $conn->prepare(
            "SELECT BetID, UserID, BetAmount, UserBetPrediction, BetOdds
             FROM tblbets
             WHERE EventID = :event_id AND UserBetResult = 'Pending'
             FOR UPDATE"
        );
        $stmtBets->execute([":event_id" => $eventID]);
        $bets = $stmtBets->fetchAll(PDO::FETCH_ASSOC);

        $winningBets = 0;
        $losingBets = 0;

        foreach ($bets as $bet) {
            if ($bet["UserBetPrediction"] === $result) {
                // The stake was already removed when the bet was placed.
                // A winning payout therefore returns stake x stored odds.
                $payout = (int)round($bet["BetAmount"] * $bet["BetOdds"]);

                $stmtWin = $conn->prepare(
                    "UPDATE tblbets
                     SET UserBetResult = 'Win', Payout = :payout
                     WHERE BetID = :bet_id"
                );
                $stmtWin->execute([
                    ":payout" => $payout,
                    ":bet_id" => $bet["BetID"]
                ]);

                $stmtPay = $conn->prepare(
                    "UPDATE tblusers
                     SET Balance = Balance + :payout
                     WHERE UserID = :user_id"
                );
                $stmtPay->execute([
                    ":payout" => $payout,
                    ":user_id" => $bet["UserID"]
                ]);

                $winningBets++;
            }
            else {
                $stmtLoss = $conn->prepare(
                    "UPDATE tblbets
                     SET UserBetResult = 'Loss', Payout = 0
                     WHERE BetID = :bet_id"
                );
                $stmtLoss->execute([":bet_id" => $bet["BetID"]]);
                $losingBets++;
            }
        }

        $conn->commit();
        setFlash("success", "Result saved. Settled " . count($bets) . " bet(s): " . $winningBets . " win(s), " . $losingBets . " loss(es).");
        header("Location: admin_events.php");
        exit;
    }
    catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        setFlash("error", $e->getMessage());
        header("Location: admin_events.php");
        exit;
    }
}

$stmtBetCount = $conn->prepare("SELECT COUNT(*) FROM tblbets WHERE EventID = :event_id AND UserBetResult = 'Pending'");
$stmtBetCount->execute([":event_id" => $eventID]);
$betCount = (int)$stmtBetCount->fetchColumn();

$pageTitle = "Enter result";
include("header.php");
?>
<section class="page-heading">
    <a class="back-link" href="admin_events.php">← Manage events</a>
    <span class="eyebrow">ADMIN RESULT</span>
    <h1><?= h($event["HomeTeam"]) ?> vs <?= h($event["AwayTeam"]) ?></h1>
    <p><?= h($event["SportName"]) ?> · <?= h(date("d F Y", strtotime($event["EventDate"]))) ?> · <?= h($betCount) ?> pending bet(s)</p>
</section>

<?php if ($event["Result"] !== "Pending"): ?>
    <div class="result-banner">This event has already been settled as <strong><?= h($event["Result"]) ?></strong>.</div>
<?php elseif (!eventHasStarted($event["EventDate"], $event["EventTime"])): ?>
    <div class="result-banner">This event has not started yet, so a final result cannot be entered.</div>
<?php else: ?>
    <section class="form-card wide-card">
        <h2>Select the final result</h2>
        <p class="muted">Saving the result will automatically mark every pending bet as Win or Loss and pay winning users.</p>

        <form method="post" class="result-options" onsubmit="return confirm('Settle this event and all of its bets? This should only be done once the final result is known.');">
            <button class="result-choice" type="submit" name="result" value="Home">
                <small>Home win</small>
                <strong><?= h($event["HomeTeam"]) ?></strong>
            </button>

            <?php if ($event["DrawOdds"] !== null): ?>
                <button class="result-choice" type="submit" name="result" value="Draw">
                    <small>Draw</small>
                    <strong>Draw</strong>
                </button>
            <?php endif; ?>

            <button class="result-choice" type="submit" name="result" value="Away">
                <small>Away win</small>
                <strong><?= h($event["AwayTeam"]) ?></strong>
            </button>
        </form>
    </section>
<?php endif; ?>
<?php include("footer.php"); ?>
