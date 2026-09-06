<?php
include_once("connection.php");
include_once("functions.php");
requireAdmin();

$stats = [];
$stats["users"] = (int)$conn->query("SELECT COUNT(*) FROM tblusers WHERE AccountType = 'User'")->fetchColumn();
$stats["events"] = (int)$conn->query("SELECT COUNT(*) FROM tblevents")->fetchColumn();
$stats["pending"] = (int)$conn->query("SELECT COUNT(*) FROM tblevents WHERE Result = 'Pending'")->fetchColumn();
$stats["bets"] = (int)$conn->query("SELECT COUNT(*) FROM tblbets")->fetchColumn();

$stmtNeedsResult = $conn->prepare(
    "SELECT e.EventID, e.EventDate, e.EventTime, s.SportName,
            home.TeamName AS HomeTeam, away.TeamName AS AwayTeam,
            COUNT(b.BetID) AS BetCount
     FROM tblevents e
     INNER JOIN tblsports s ON e.SportID = s.SportID
     INNER JOIN tblteams home ON e.HomeTeamID = home.TeamID
     INNER JOIN tblteams away ON e.AwayTeamID = away.TeamID
     LEFT JOIN tblbets b ON e.EventID = b.EventID
     WHERE e.Result = 'Pending'
       AND TIMESTAMP(e.EventDate, e.EventTime) <= NOW()
     GROUP BY e.EventID, e.EventDate, e.EventTime, s.SportName, home.TeamName, away.TeamName
     ORDER BY e.EventDate ASC, e.EventTime ASC"
);
$stmtNeedsResult->execute();
$needsResults = $stmtNeedsResult->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Admin dashboard";
include("header.php");
?>
<section class="page-heading">
    <span class="eyebrow">ADMINISTRATION</span>
    <h1>Admin dashboard</h1>
    <p>Create events, update odds, enter results and view user accounts.</p>
</section>

<div class="stat-grid">
    <div class="stat-card"><span>Users</span><strong><?= h($stats["users"]) ?></strong></div>
    <div class="stat-card"><span>Events</span><strong><?= h($stats["events"]) ?></strong></div>
    <div class="stat-card"><span>Pending results</span><strong><?= h($stats["pending"]) ?></strong></div>
    <div class="stat-card"><span>Total bets</span><strong><?= h($stats["bets"]) ?></strong></div>
</div>

<div class="admin-actions">
    <a class="admin-action-card" href="admin_add_event.php">
        <strong>Create event</strong>
        <span>Add teams, date, time and odds.</span>
    </a>
    <a class="admin-action-card" href="admin_events.php">
        <strong>Manage events</strong>
        <span>Edit odds or enter a final result.</span>
    </a>
    <a class="admin-action-card" href="admin_users.php">
        <strong>Manage users</strong>
        <span>View accounts and reset a user's test points.</span>
    </a>
</div>

<section class="section-heading">
    <div>
        <span class="eyebrow">ACTION REQUIRED</span>
        <h2>Events waiting for a result</h2>
    </div>
</section>

<div class="table-card">
    <?php if (!$needsResults): ?>
        <div class="empty-state">There are no started events waiting for a result.</div>
    <?php else: ?>
        <div class="table-scroll">
            <table>
                <thead><tr><th>Sport</th><th>Event</th><th>Date</th><th>Bets</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($needsResults as $event): ?>
                    <tr>
                        <td><?= h($event["SportName"]) ?></td>
                        <td><strong><?= h($event["HomeTeam"]) ?> vs <?= h($event["AwayTeam"]) ?></strong></td>
                        <td><?= h(date("d M Y", strtotime($event["EventDate"]))) ?> <?= h(substr($event["EventTime"], 0, 5)) ?></td>
                        <td><?= h($event["BetCount"]) ?></td>
                        <td><a class="button button-small" href="admin_result.php?id=<?= h($event["EventID"]) ?>">Enter result</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php include("footer.php"); ?>
