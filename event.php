<?php
include_once("connection.php");
include_once("functions.php");

$eventID = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$eventID) {
    setFlash("error", "Invalid event selected.");
    header("Location: sports.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT e.*, s.SportName, home.TeamName AS HomeTeam, away.TeamName AS AwayTeam
     FROM tblevents e
     INNER JOIN tblsports s ON e.SportID = s.SportID
     INNER JOIN tblteams home ON e.HomeTeamID = home.TeamID
     INNER JOIN tblteams away ON e.AwayTeamID = away.TeamID
     WHERE e.EventID = :event_id"
);
$stmt->execute([":event_id" => $eventID]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    setFlash("error", "Event not found.");
    header("Location: sports.php");
    exit;
}

$pageTitle = $event["HomeTeam"] . " vs " . $event["AwayTeam"];
$bettingOpen = $event["Result"] === "Pending" && !eventHasStarted($event["EventDate"], $event["EventTime"]);
include("header.php");
?>
<section class="event-detail-card">
    <div class="event-meta">
        <span><?= h($event["SportName"]) ?></span>
        <span><?= h(date("d F Y", strtotime($event["EventDate"]))) ?> at <?= h(substr($event["EventTime"], 0, 5)) ?></span>
    </div>

    <div class="matchup">
        <div>
            <small>Home</small>
            <h1><?= h($event["HomeTeam"]) ?></h1>
        </div>
        <span class="versus">VS</span>
        <div>
            <small>Away</small>
            <h1><?= h($event["AwayTeam"]) ?></h1>
        </div>
    </div>

    <?php if ($event["Result"] !== "Pending"): ?>
        <div class="result-banner">Final result: <strong><?= h($event["Result"]) ?></strong></div>
    <?php elseif (!$bettingOpen): ?>
        <div class="result-banner">Betting is closed because this event has started.</div>
    <?php endif; ?>
</section>

<section class="betting-panel">
    <div class="section-heading compact">
        <div>
            <span class="eyebrow">MATCH RESULT</span>
            <h2>Choose an outcome</h2>
        </div>
    </div>

    <div class="market-options">
        <button type="button" class="market-option" data-prediction="Home" data-odds="<?= h($event["HomeWinOdds"]) ?>" <?= $bettingOpen ? "" : "disabled" ?>>
            <span><?= h($event["HomeTeam"]) ?></span>
            <strong><?= h($event["HomeWinOdds"]) ?></strong>
        </button>

        <?php if ($event["DrawOdds"] !== null): ?>
            <button type="button" class="market-option" data-prediction="Draw" data-odds="<?= h($event["DrawOdds"]) ?>" <?= $bettingOpen ? "" : "disabled" ?>>
                <span>Draw</span>
                <strong><?= h($event["DrawOdds"]) ?></strong>
            </button>
        <?php endif; ?>

        <button type="button" class="market-option" data-prediction="Away" data-odds="<?= h($event["AwayWinOdds"]) ?>" <?= $bettingOpen ? "" : "disabled" ?>>
            <span><?= h($event["AwayTeam"]) ?></span>
            <strong><?= h($event["AwayWinOdds"]) ?></strong>
        </button>
    </div>

    <?php if ($bettingOpen && isLoggedIn() && !isAdmin()): ?>
        <form action="placebet.php" method="post" id="bet-form" class="bet-slip-inline">
            <input type="hidden" name="event_id" value="<?= h($event["EventID"]) ?>">
            <input type="hidden" name="prediction" id="prediction" value="">

            <div>
                <label for="bet_amount">Stake (points)</label>
                <input type="number" id="bet_amount" name="bet_amount" min="1" max="<?= h($currentUser["Balance"]) ?>" required>
            </div>

            <div class="selection-summary">
                <small>Selection</small>
                <strong id="selection-text">Choose an outcome</strong>
            </div>

            <div class="selection-summary">
                <small>Potential return</small>
                <strong><span id="potential-return">0</span> pts</strong>
            </div>

            <button type="submit" class="button" id="place-bet-button" disabled>Place bet</button>
        </form>
    <?php elseif ($bettingOpen && !isLoggedIn()): ?>
        <div class="login-prompt">
            <p>Log in to place a points bet on this event.</p>
            <a class="button" href="loginpage.php">Log in</a>
        </div>
    <?php elseif ($bettingOpen && isAdmin()): ?>
        <div class="login-prompt">
            <p>Admin accounts manage events and results rather than placing bets.</p>
        </div>
    <?php endif; ?>
</section>

<script>
const options = document.querySelectorAll('.market-option:not([disabled])');
const predictionInput = document.getElementById('prediction');
const amountInput = document.getElementById('bet_amount');
const selectionText = document.getElementById('selection-text');
const potentialReturn = document.getElementById('potential-return');
const submitButton = document.getElementById('place-bet-button');
let selectedOdds = 0;

function updatePotentialReturn() {
    if (!amountInput || !potentialReturn) return;
    const amount = Number(amountInput.value || 0);
    potentialReturn.textContent = Math.round(amount * selectedOdds);
}

options.forEach(option => {
    option.addEventListener('click', function () {
        options.forEach(item => item.classList.remove('selected'));
        this.classList.add('selected');

        selectedOdds = Number(this.dataset.odds);
        if (predictionInput) predictionInput.value = this.dataset.prediction;
        if (selectionText) selectionText.textContent = this.dataset.prediction + ' @ ' + this.dataset.odds;
        if (submitButton) submitButton.disabled = false;
        updatePotentialReturn();
    });
});

if (amountInput) {
    amountInput.addEventListener('input', updatePotentialReturn);
}
</script>
<?php include("footer.php"); ?>
