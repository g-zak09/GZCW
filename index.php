<?php
$pageTitle = "Home";
include_once("connection.php");
include_once("functions.php");

$stmtUpcoming = $conn->prepare(
    "SELECT e.EventID, e.EventDate, e.EventTime, e.HomeWinOdds, e.DrawOdds, e.AwayWinOdds,
            s.SportName, home.TeamName AS HomeTeam, away.TeamName AS AwayTeam
     FROM tblevents e
     INNER JOIN tblsports s ON e.SportID = s.SportID
     INNER JOIN tblteams home ON e.HomeTeamID = home.TeamID
     INNER JOIN tblteams away ON e.AwayTeamID = away.TeamID
     WHERE e.Result = 'Pending'
       AND TIMESTAMP(e.EventDate, e.EventTime) > NOW()
     ORDER BY e.EventDate ASC, e.EventTime ASC
     LIMIT 4"
);
$stmtUpcoming->execute();
$upcomingEvents = $stmtUpcoming->fetchAll(PDO::FETCH_ASSOC);

include("header.php");
?>
<section class="hero">
    <div>
        <span class="eyebrow">POINTS-ONLY SPORTS PREDICTIONS</span>
        <h1>Back your sports knowledge without risking real money.</h1>
        <p>Start with 1,000 points, choose an outcome and track your performance across football and basketball.</p>
        <div class="hero-actions">
            <?php if (!isLoggedIn()): ?>
                <a class="button" href="signup.php">Start with 1,000 points</a>
                <a class="button button-secondary" href="loginpage.php">Log in</a>
            <?php else: ?>
                <a class="button" href="sports.php">View events</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="hero-panel">
        <strong>No deposits. No withdrawals.</strong>
        <p>All balances are fictional points used for this coursework simulation.</p>
    </div>
</section>

<section class="section-heading">
    <div>
        <span class="eyebrow">UPCOMING</span>
        <h2>Featured events</h2>
    </div>
    <a href="sports.php">View all sports →</a>
</section>

<div class="event-grid">
    <?php if (!$upcomingEvents): ?>
        <div class="empty-state">No future events have been added yet.</div>
    <?php endif; ?>

    <?php foreach ($upcomingEvents as $event): ?>
        <article class="event-card">
            <div class="event-meta">
                <span><?= h($event["SportName"]) ?></span>
                <span><?= h(date("d M", strtotime($event["EventDate"]))) ?> · <?= h(substr($event["EventTime"], 0, 5)) ?></span>
            </div>
            <h3><?= h($event["HomeTeam"]) ?> <span>vs</span> <?= h($event["AwayTeam"]) ?></h3>
            <div class="odds-row">
                <span>Home <strong><?= h($event["HomeWinOdds"]) ?></strong></span>
                <?php if ($event["DrawOdds"] !== null): ?>
                    <span>Draw <strong><?= h($event["DrawOdds"]) ?></strong></span>
                <?php endif; ?>
                <span>Away <strong><?= h($event["AwayWinOdds"]) ?></strong></span>
            </div>
            <a class="button button-small full-width" href="event.php?id=<?= h($event["EventID"]) ?>">View event</a>
        </article>
    <?php endforeach; ?>
</div>
<?php include("footer.php"); ?>
