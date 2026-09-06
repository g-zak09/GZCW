<?php
$pageTitle = "Sports";
include_once("connection.php");
include_once("functions.php");

$stmt = $conn->prepare(
    "SELECT s.SportID, s.SportName,
            SUM(CASE WHEN e.Result = 'Pending' AND TIMESTAMP(e.EventDate, e.EventTime) > NOW() THEN 1 ELSE 0 END) AS UpcomingEvents
     FROM tblsports s
     LEFT JOIN tblevents e ON s.SportID = e.SportID
     GROUP BY s.SportID, s.SportName
     ORDER BY s.SportID"
);
$stmt->execute();
$sports = $stmt->fetchAll(PDO::FETCH_ASSOC);

include("header.php");
?>
<section class="page-heading">
    <span class="eyebrow">SPORTS</span>
    <h1>Choose a sport</h1>
    <p>Browse current and completed events. Formula 1 is stored as planned test data but is not yet part of the working team-v-team betting system.</p>
</section>

<div class="sport-grid">
    <?php foreach ($sports as $sport): ?>
        <article class="sport-card">
            <div>
                <span class="sport-icon"><?= h(substr($sport["SportName"], 0, 1)) ?></span>
                <h2><?= h($sport["SportName"]) ?></h2>
                <?php if ($sport["SportName"] === "Formula 1"): ?>
                    <p>Future extension: race and driver markets require a different event structure.</p>
                    <span class="status-pill pending">Planned</span>
                <?php else: ?>
                    <p><?= h($sport["UpcomingEvents"]) ?> upcoming event(s)</p>
                    <a class="button button-small" href="sport.php?id=<?= h($sport["SportID"]) ?>">View <?= h($sport["SportName"]) ?></a>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php include("footer.php"); ?>
