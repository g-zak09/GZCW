<?php
include_once("connection.php");
include_once("functions.php");

$sportID = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$sportID) {
    setFlash("error", "Invalid sport selected.");
    header("Location: sports.php");
    exit;
}

$stmtSport = $conn->prepare("SELECT * FROM tblsports WHERE SportID = :sport_id");
$stmtSport->execute([":sport_id" => $sportID]);
$sport = $stmtSport->fetch(PDO::FETCH_ASSOC);

if (!$sport) {
    setFlash("error", "Sport not found.");
    header("Location: sports.php");
    exit;
}

if ($sport["SportName"] === "Formula 1") {
    setFlash("error", "Formula 1 is planned as a future extension and does not yet use the current event model.");
    header("Location: sports.php");
    exit;
}

$stmtEvents = $conn->prepare(
    "SELECT e.*, home.TeamName AS HomeTeam, away.TeamName AS AwayTeam
     FROM tblevents e
     INNER JOIN tblteams home ON e.HomeTeamID = home.TeamID
     INNER JOIN tblteams away ON e.AwayTeamID = away.TeamID
     WHERE e.SportID = :sport_id
     ORDER BY CASE WHEN e.Result = 'Pending' THEN 0 ELSE 1 END,
              e.EventDate ASC, e.EventTime ASC"
);
$stmtEvents->execute([":sport_id" => $sportID]);
$events = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = $sport["SportName"];
include("header.php");
?>
<section class="page-heading">
    <a class="back-link" href="sports.php">← All sports</a>
    <span class="eyebrow">SPORT</span>
    <h1><?= h($sport["SportName"]) ?></h1>
</section>

<div class="event-list">
    <?php if (!$events): ?>
        <div class="empty-state">No events have been added for this sport.</div>
    <?php endif; ?>

    <?php foreach ($events as $event): ?>
        <?php $isUpcoming = $event["Result"] === "Pending" && !eventHasStarted($event["EventDate"], $event["EventTime"]); ?>
        <article class="event-row-card">
            <div class="event-date-block">
                <strong><?= h(date("d", strtotime($event["EventDate"]))) ?></strong>
                <span><?= h(date("M", strtotime($event["EventDate"]))) ?></span>
                <small><?= h(substr($event["EventTime"], 0, 5)) ?></small>
            </div>
            <div class="event-row-main">
                <div class="event-meta">
                    <?php if ($isUpcoming): ?>
                        <span class="status-pill pending">Open</span>
                    <?php elseif ($event["Result"] === "Pending"): ?>
                        <span class="status-pill closed">Closed</span>
                    <?php else: ?>
                        <span class="status-pill settled">Result: <?= h($event["Result"]) ?></span>
                    <?php endif; ?>
                </div>
                <h2><?= h($event["HomeTeam"]) ?> <span>vs</span> <?= h($event["AwayTeam"]) ?></h2>
                <div class="odds-row">
                    <span>Home <strong><?= h($event["HomeWinOdds"]) ?></strong></span>
                    <?php if ($event["DrawOdds"] !== null): ?>
                        <span>Draw <strong><?= h($event["DrawOdds"]) ?></strong></span>
                    <?php endif; ?>
                    <span>Away <strong><?= h($event["AwayWinOdds"]) ?></strong></span>
                </div>
            </div>
            <div class="event-row-action">
                <a class="button button-small" href="event.php?id=<?= h($event["EventID"]) ?>">Details</a>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php include("footer.php"); ?>
