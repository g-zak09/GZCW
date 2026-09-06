<?php
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

if ($event["Result"] !== "Pending") {
    setFlash("error", "A settled event cannot be edited.");
    header("Location: admin_events.php");
    exit;
}

if (eventHasStarted($event["EventDate"], $event["EventTime"])) {
    setFlash("error", "This event has already started. Enter its result instead of editing it.");
    header("Location: admin_events.php");
    exit;
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $eventDate = $_POST["event_date"] ?? "";
    $eventTime = $_POST["event_time"] ?? "";
    $homeOdds = filter_input(INPUT_POST, "home_odds", FILTER_VALIDATE_FLOAT);
    $awayOdds = filter_input(INPUT_POST, "away_odds", FILTER_VALIDATE_FLOAT);
    $drawOddsInput = trim($_POST["draw_odds"] ?? "");

    $eventDateTime = DateTime::createFromFormat("Y-m-d H:i", $eventDate . " " . $eventTime);
    if (!$eventDateTime || $eventDateTime <= new DateTime()) {
        $errors[] = "The event date and time must remain in the future.";
    }

    if ($homeOdds === false || $awayOdds === false || $homeOdds <= 1 || $awayOdds <= 1 || $homeOdds > 99.99 || $awayOdds > 99.99) {
        $errors[] = "Home and away odds must be greater than 1 and no more than 99.99.";
    }

    if ($event["SportName"] === "Football") {
        $drawOdds = filter_var($drawOddsInput, FILTER_VALIDATE_FLOAT);
        if ($drawOdds === false || $drawOdds <= 1 || $drawOdds > 99.99) {
            $errors[] = "Football events require valid draw odds greater than 1.";
        }
    }
    else {
        $drawOdds = null;
    }

    if (!$errors) {
        $stmtUpdate = $conn->prepare(
            "UPDATE tblevents
             SET EventDate = :event_date,
                 EventTime = :event_time,
                 HomeWinOdds = :home_odds,
                 DrawOdds = :draw_odds,
                 AwayWinOdds = :away_odds
             WHERE EventID = :event_id AND Result = 'Pending'"
        );
        $stmtUpdate->execute([
            ":event_date" => $eventDate,
            ":event_time" => $eventTime,
            ":home_odds" => $homeOdds,
            ":draw_odds" => $drawOdds,
            ":away_odds" => $awayOdds,
            ":event_id" => $eventID
        ]);

        setFlash("success", "Event updated. Existing bets keep the odds stored when they were placed.");
        header("Location: admin_events.php");
        exit;
    }
}

$pageTitle = "Edit event";
include("header.php");
?>
<section class="page-heading">
    <a class="back-link" href="admin_events.php">← Manage events</a>
    <span class="eyebrow">ADMIN</span>
    <h1>Edit event</h1>
    <p><?= h($event["HomeTeam"]) ?> vs <?= h($event["AwayTeam"]) ?> · <?= h($event["SportName"]) ?></p>
</section>

<?php if ($errors): ?>
    <div class="validation-box">
        <strong>The event was not updated:</strong>
        <ul><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<section class="form-card wide-card">
    <form method="post">
        <div class="form-grid two-columns">
            <div>
                <label for="event_date">Event date</label>
                <input type="date" id="event_date" name="event_date" value="<?= h($eventDate ?? $event["EventDate"]) ?>" required>
            </div>
            <div>
                <label for="event_time">Event time</label>
                <input type="time" id="event_time" name="event_time" value="<?= h(isset($eventTime) ? $eventTime : substr($event["EventTime"], 0, 5)) ?>" required>
            </div>
            <div>
                <label for="home_odds">Home odds</label>
                <input type="number" id="home_odds" name="home_odds" min="1.01" max="99.99" step="0.01" value="<?= h($homeOdds ?? $event["HomeWinOdds"]) ?>" required>
            </div>
            <div>
                <label for="away_odds">Away odds</label>
                <input type="number" id="away_odds" name="away_odds" min="1.01" max="99.99" step="0.01" value="<?= h($awayOdds ?? $event["AwayWinOdds"]) ?>" required>
            </div>
            <?php if ($event["SportName"] === "Football"): ?>
                <div>
                    <label for="draw_odds">Draw odds</label>
                    <input type="number" id="draw_odds" name="draw_odds" min="1.01" max="99.99" step="0.01" value="<?= h($drawOddsInput ?? $event["DrawOdds"]) ?>" required>
                </div>
            <?php endif; ?>
        </div>
        <button class="button" type="submit">Save changes</button>
    </form>
</section>
<?php include("footer.php"); ?>
