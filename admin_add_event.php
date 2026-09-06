<?php
include_once("connection.php");
include_once("functions.php");
requireAdmin();

// The current event model is team-v-team, so Formula 1 is intentionally excluded for now.
$stmtSports = $conn->prepare("SELECT * FROM tblsports WHERE SportName <> 'Formula 1' ORDER BY SportName");
$stmtSports->execute();
$sports = $stmtSports->fetchAll(PDO::FETCH_ASSOC);

$stmtTeams = $conn->prepare(
    "SELECT t.TeamID, t.SportID, t.TeamName, s.SportName
     FROM tblteams t
     INNER JOIN tblsports s ON t.SportID = s.SportID
     WHERE s.SportName <> 'Formula 1'
     ORDER BY s.SportName, t.TeamName"
);
$stmtTeams->execute();
$teams = $stmtTeams->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sportID = filter_input(INPUT_POST, "sport_id", FILTER_VALIDATE_INT);
    $homeTeamID = filter_input(INPUT_POST, "home_team_id", FILTER_VALIDATE_INT);
    $awayTeamID = filter_input(INPUT_POST, "away_team_id", FILTER_VALIDATE_INT);
    $eventDate = $_POST["event_date"] ?? "";
    $eventTime = $_POST["event_time"] ?? "";
    $homeOdds = filter_input(INPUT_POST, "home_odds", FILTER_VALIDATE_FLOAT);
    $drawOddsInput = trim($_POST["draw_odds"] ?? "");
    $awayOdds = filter_input(INPUT_POST, "away_odds", FILTER_VALIDATE_FLOAT);

    $errors = [];

    if (!$sportID || !$homeTeamID || !$awayTeamID || $homeTeamID === $awayTeamID) {
        $errors[] = "Select a sport and two different teams.";
    }

    $eventDateTime = DateTime::createFromFormat("Y-m-d H:i", $eventDate . " " . $eventTime);
    if (!$eventDateTime || $eventDateTime <= new DateTime()) {
        $errors[] = "The event date and time must be in the future.";
    }

    if ($homeOdds === false || $awayOdds === false || $homeOdds <= 1 || $awayOdds <= 1 || $homeOdds > 99.99 || $awayOdds > 99.99) {
        $errors[] = "Home and away odds must be greater than 1 and no more than 99.99.";
    }

    // Confirm both selected teams belong to the selected sport.
    $stmtTeamCheck = $conn->prepare(
        "SELECT COUNT(*) FROM tblteams WHERE SportID = :sport_id AND TeamID IN (:home_id, :away_id)"
    );
    $stmtTeamCheck->execute([
        ":sport_id" => $sportID,
        ":home_id" => $homeTeamID,
        ":away_id" => $awayTeamID
    ]);
    if ((int)$stmtTeamCheck->fetchColumn() !== 2) {
        $errors[] = "Both teams must belong to the selected sport.";
    }

    $stmtSportName = $conn->prepare("SELECT SportName FROM tblsports WHERE SportID = :sport_id");
    $stmtSportName->execute([":sport_id" => $sportID]);
    $sportName = $stmtSportName->fetchColumn();

    if ($sportName === "Football") {
        $drawOdds = filter_var($drawOddsInput, FILTER_VALIDATE_FLOAT);
        if ($drawOdds === false || $drawOdds <= 1 || $drawOdds > 99.99) {
            $errors[] = "Football events require valid draw odds greater than 1.";
        }
    }
    else {
        $drawOdds = null;
    }

    if (!$errors) {
        $stmtInsert = $conn->prepare(
            "INSERT INTO tblevents
            (SportID, HomeTeamID, AwayTeamID, EventDate, EventTime, HomeWinOdds, DrawOdds, AwayWinOdds, Result)
            VALUES
            (:sport_id, :home_team_id, :away_team_id, :event_date, :event_time, :home_odds, :draw_odds, :away_odds, 'Pending')"
        );
        $stmtInsert->execute([
            ":sport_id" => $sportID,
            ":home_team_id" => $homeTeamID,
            ":away_team_id" => $awayTeamID,
            ":event_date" => $eventDate,
            ":event_time" => $eventTime,
            ":home_odds" => $homeOdds,
            ":draw_odds" => $drawOdds,
            ":away_odds" => $awayOdds
        ]);

        setFlash("success", "Event created successfully.");
        header("Location: admin_events.php");
        exit;
    }
}

$pageTitle = "Create event";
include("header.php");
?>
<section class="page-heading">
    <a class="back-link" href="admin_events.php">← Manage events</a>
    <span class="eyebrow">ADMIN</span>
    <h1>Create event</h1>
    <p>Football uses Home / Draw / Away. Basketball uses Home / Away because the match must produce a winner.</p>
</section>

<?php if (!empty($errors)): ?>
    <div class="validation-box">
        <strong>The event was not created:</strong>
        <ul>
            <?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<section class="form-card wide-card">
    <form method="post">
        <div class="form-grid two-columns">
            <div>
                <label for="sport_id">Sport</label>
                <select id="sport_id" name="sport_id" required>
                    <option value="">Select sport</option>
                    <?php foreach ($sports as $sport): ?>
                        <option value="<?= h($sport["SportID"]) ?>" <?= isset($sportID) && $sportID == $sport["SportID"] ? "selected" : "" ?>><?= h($sport["SportName"]) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div></div>

            <div>
                <label for="home_team_id">Home team</label>
                <select id="home_team_id" name="home_team_id" required>
                    <option value="">Select team</option>
                    <?php foreach ($teams as $team): ?>
                        <option data-sport="<?= h($team["SportID"]) ?>" value="<?= h($team["TeamID"]) ?>" <?= isset($homeTeamID) && $homeTeamID == $team["TeamID"] ? "selected" : "" ?>><?= h($team["TeamName"]) ?> (<?= h($team["SportName"]) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="away_team_id">Away team</label>
                <select id="away_team_id" name="away_team_id" required>
                    <option value="">Select team</option>
                    <?php foreach ($teams as $team): ?>
                        <option data-sport="<?= h($team["SportID"]) ?>" value="<?= h($team["TeamID"]) ?>" <?= isset($awayTeamID) && $awayTeamID == $team["TeamID"] ? "selected" : "" ?>><?= h($team["TeamName"]) ?> (<?= h($team["SportName"]) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="event_date">Event date</label>
                <input type="date" id="event_date" name="event_date" min="<?= h(date("Y-m-d")) ?>" value="<?= h($eventDate ?? "") ?>" required>
            </div>
            <div>
                <label for="event_time">Event time</label>
                <input type="time" id="event_time" name="event_time" value="<?= h($eventTime ?? "") ?>" required>
            </div>

            <div>
                <label for="home_odds">Home win odds</label>
                <input type="number" id="home_odds" name="home_odds" min="1.01" max="99.99" step="0.01" value="<?= h($homeOdds ?? "") ?>" required>
            </div>
            <div>
                <label for="away_odds">Away win odds</label>
                <input type="number" id="away_odds" name="away_odds" min="1.01" max="99.99" step="0.01" value="<?= h($awayOdds ?? "") ?>" required>
            </div>

            <div id="draw-odds-field">
                <label for="draw_odds">Draw odds</label>
                <input type="number" id="draw_odds" name="draw_odds" min="1.01" max="99.99" step="0.01" value="<?= h($drawOddsInput ?? "") ?>">
                <p class="field-help">Required for football; ignored for basketball.</p>
            </div>
        </div>

        <button class="button" type="submit">Create event</button>
    </form>
</section>

<script>
const sportSelect = document.getElementById('sport_id');
const homeSelect = document.getElementById('home_team_id');
const awaySelect = document.getElementById('away_team_id');

function filterTeams() {
    const sport = sportSelect.value;
    [homeSelect, awaySelect].forEach(select => {
        Array.from(select.options).forEach(option => {
            if (!option.value) return;
            option.hidden = sport !== '' && option.dataset.sport !== sport;
        });
        if (select.selectedOptions[0] && select.selectedOptions[0].hidden) select.value = '';
    });
}

sportSelect.addEventListener('change', filterTeams);
filterTeams();
</script>
<?php include("footer.php"); ?>
