<?php
include_once("connection.php");
include_once("functions.php");
requireLogin();

if (isAdmin()) {
    header("Location: admin_dashboard.php");
    exit;
}

$user = getCurrentUser($conn);

$stmtStats = $conn->prepare(
    "SELECT COUNT(*) AS TotalBets,
            SUM(CASE WHEN UserBetResult = 'Win' THEN 1 ELSE 0 END) AS Wins,
            SUM(CASE WHEN UserBetResult = 'Loss' THEN 1 ELSE 0 END) AS Losses,
            SUM(CASE WHEN UserBetResult = 'Pending' THEN 1 ELSE 0 END) AS PendingBets
     FROM tblbets
     WHERE UserID = :user_id"
);
$stmtStats->execute([":user_id" => $user["UserID"]]);
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

$settled = (int)$stats["Wins"] + (int)$stats["Losses"];
$winRate = $settled > 0 ? round(((int)$stats["Wins"] / $settled) * 100) : 0;

$stmtRecent = $conn->prepare(
    "SELECT b.*, s.SportName, e.EventDate,
            home.TeamName AS HomeTeam, away.TeamName AS AwayTeam
     FROM tblbets b
     INNER JOIN tblevents e ON b.EventID = e.EventID
     INNER JOIN tblsports s ON b.SportID = s.SportID
     INNER JOIN tblteams home ON e.HomeTeamID = home.TeamID
     INNER JOIN tblteams away ON e.AwayTeamID = away.TeamID
     WHERE b.UserID = :user_id
     ORDER BY b.BetDateTime DESC
     LIMIT 5"
);
$stmtRecent->execute([":user_id" => $user["UserID"]]);
$recentBets = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Dashboard";
include("header.php");
?>
<section class="page-heading dashboard-heading">
    <span class="eyebrow">YOUR ACCOUNT</span>
    <h1><?= h($user["Forename"]) ?>'s dashboard</h1>
    <p>Track your points, current bets and completed results.</p>
</section>

<div class="stat-grid">
    <div class="stat-card featured-stat">
        <span>Current balance</span>
        <strong><?= h($user["Balance"]) ?></strong>
        <small>points</small>
    </div>
    <div class="stat-card">
        <span>Total bets</span>
        <strong><?= h($stats["TotalBets"]) ?></strong>
    </div>
    <div class="stat-card">
        <span>Wins</span>
        <strong><?= h($stats["Wins"] ?? 0) ?></strong>
    </div>
    <div class="stat-card">
        <span>Win rate</span>
        <strong><?= h($winRate) ?>%</strong>
    </div>
</div>

<section class="section-heading">
    <div>
        <span class="eyebrow">LATEST ACTIVITY</span>
        <h2>Recent bets</h2>
    </div>
    <a href="bet_history.php">Full history →</a>
</section>

<div class="table-card">
    <?php if (!$recentBets): ?>
        <div class="empty-state">You have not placed any bets yet. <a href="sports.php">Browse sports</a>.</div>
    <?php else: ?>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Prediction</th>
                        <th>Stake</th>
                        <th>Odds</th>
                        <th>Result</th>
                        <th>Payout</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBets as $bet): ?>
                        <tr>
                            <td>
                                <strong><?= h($bet["HomeTeam"]) ?> vs <?= h($bet["AwayTeam"]) ?></strong><br>
                                <small><?= h($bet["SportName"]) ?> · <?= h(date("d M Y", strtotime($bet["EventDate"]))) ?></small>
                            </td>
                            <td><?= h($bet["UserBetPrediction"]) ?></td>
                            <td><?= h($bet["BetAmount"]) ?></td>
                            <td><?= h($bet["BetOdds"]) ?></td>
                            <td><span class="status-pill <?= strtolower(h($bet["UserBetResult"])) ?>"><?= h($bet["UserBetResult"]) ?></span></td>
                            <td><?= h($bet["Payout"]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php include("footer.php"); ?>
