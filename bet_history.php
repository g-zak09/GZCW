<?php
include_once("connection.php");
include_once("functions.php");
requireLogin();

if (isAdmin()) {
    header("Location: admin_dashboard.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT b.*, s.SportName, e.EventDate, e.EventTime,
            home.TeamName AS HomeTeam, away.TeamName AS AwayTeam
     FROM tblbets b
     INNER JOIN tblevents e ON b.EventID = e.EventID
     INNER JOIN tblsports s ON b.SportID = s.SportID
     INNER JOIN tblteams home ON e.HomeTeamID = home.TeamID
     INNER JOIN tblteams away ON e.AwayTeamID = away.TeamID
     WHERE b.UserID = :user_id
     ORDER BY b.BetDateTime DESC"
);
$stmt->execute([":user_id" => $_SESSION["loggedinuser"]]);
$bets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Bet history";
include("header.php");
?>
<section class="page-heading">
    <span class="eyebrow">ACCOUNT HISTORY</span>
    <h1>Bet history</h1>
    <p>Each bet stores the odds at the time it was placed, so later odds changes do not change an existing bet.</p>
</section>

<div class="table-card">
    <?php if (!$bets): ?>
        <div class="empty-state">No bets recorded yet.</div>
    <?php else: ?>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Placed</th>
                        <th>Sport / event</th>
                        <th>Prediction</th>
                        <th>Stake</th>
                        <th>Odds</th>
                        <th>Status</th>
                        <th>Payout</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bets as $bet): ?>
                        <tr>
                            <td><?= h(date("d M Y H:i", strtotime($bet["BetDateTime"]))) ?></td>
                            <td><strong><?= h($bet["HomeTeam"]) ?> vs <?= h($bet["AwayTeam"]) ?></strong><br><small><?= h($bet["SportName"]) ?></small></td>
                            <td><?= h($bet["UserBetPrediction"]) ?></td>
                            <td><?= h($bet["BetAmount"]) ?> pts</td>
                            <td><?= h($bet["BetOdds"]) ?></td>
                            <td><span class="status-pill <?= strtolower(h($bet["UserBetResult"])) ?>"><?= h($bet["UserBetResult"]) ?></span></td>
                            <td><?= h($bet["Payout"]) ?> pts</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php include("footer.php"); ?>
