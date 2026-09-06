<?php
include_once("connection.php");
include_once("functions.php");
requireAdmin();

$stmt = $conn->prepare(
    "SELECT e.*, s.SportName, home.TeamName AS HomeTeam, away.TeamName AS AwayTeam,
            COUNT(b.BetID) AS BetCount
     FROM tblevents e
     INNER JOIN tblsports s ON e.SportID = s.SportID
     INNER JOIN tblteams home ON e.HomeTeamID = home.TeamID
     INNER JOIN tblteams away ON e.AwayTeamID = away.TeamID
     LEFT JOIN tblbets b ON e.EventID = b.EventID
     GROUP BY e.EventID, e.SportID, e.HomeTeamID, e.AwayTeamID, e.EventDate, e.EventTime,
              e.HomeWinOdds, e.DrawOdds, e.AwayWinOdds, e.Result,
              s.SportName, home.TeamName, away.TeamName
     ORDER BY e.EventDate DESC, e.EventTime DESC"
);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Manage events";
include("header.php");
?>
<section class="page-heading heading-with-action">
    <div>
        <a class="back-link" href="admin_dashboard.php">← Admin dashboard</a>
        <span class="eyebrow">ADMIN</span>
        <h1>Manage events</h1>
    </div>
    <a class="button" href="admin_add_event.php">+ Create event</a>
</section>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Sport</th><th>Event</th><th>Date</th><th>Odds H/D/A</th><th>Bets</th><th>Result</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?= h($event["SportName"]) ?></td>
                    <td><strong><?= h($event["HomeTeam"]) ?> vs <?= h($event["AwayTeam"]) ?></strong></td>
                    <td><?= h(date("d M Y", strtotime($event["EventDate"]))) ?><br><small><?= h(substr($event["EventTime"], 0, 5)) ?></small></td>
                    <td><?= h($event["HomeWinOdds"]) ?> / <?= $event["DrawOdds"] === null ? "-" : h($event["DrawOdds"]) ?> / <?= h($event["AwayWinOdds"]) ?></td>
                    <td><?= h($event["BetCount"]) ?></td>
                    <td><span class="status-pill <?= strtolower(h($event["Result"])) ?>"><?= h($event["Result"]) ?></span></td>
                    <td class="action-links">
                        <?php if ($event["Result"] === "Pending"): ?>
                            <a href="admin_edit_event.php?id=<?= h($event["EventID"]) ?>">Edit</a>
                            <a href="admin_result.php?id=<?= h($event["EventID"]) ?>">Result</a>
                        <?php else: ?>
                            <span class="muted">Settled</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include("footer.php"); ?>
