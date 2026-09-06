<?php
include_once("connection.php");
include_once("functions.php");
requireAdmin();

// A simple account-management function for the base version: reset a user's test points.
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "reset_balance") {
    $userID = filter_input(INPUT_POST, "user_id", FILTER_VALIDATE_INT);

    if ($userID) {
        $stmtReset = $conn->prepare(
            "UPDATE tblusers SET Balance = 1000 WHERE UserID = :user_id AND AccountType = 'User'"
        );
        $stmtReset->execute([":user_id" => $userID]);
        setFlash("success", "User balance reset to 1,000 points for testing.");
    }
    else {
        setFlash("error", "Invalid user selected.");
    }

    header("Location: admin_users.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT u.UserID, u.AccountType, u.Forename, u.Surname, u.Username, u.DateOfBirth, u.Balance,
            COUNT(b.BetID) AS BetCount
     FROM tblusers u
     LEFT JOIN tblbets b ON u.UserID = b.UserID
     GROUP BY u.UserID, u.AccountType, u.Forename, u.Surname, u.Username, u.DateOfBirth, u.Balance
     ORDER BY u.AccountType ASC, u.Username ASC"
);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Manage users";
include("header.php");
?>
<section class="page-heading">
    <a class="back-link" href="admin_dashboard.php">← Admin dashboard</a>
    <span class="eyebrow">ADMIN</span>
    <h1>Manage users</h1>
    <p>The base version allows the admin to view accounts and reset a standard user's test balance.</p>
</section>

<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead><tr><th>User</th><th>Username</th><th>Type</th><th>Date of birth</th><th>Balance</th><th>Bets</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= h($user["Forename"]) ?> <?= h($user["Surname"]) ?></td>
                    <td><?= h($user["Username"]) ?></td>
                    <td><?= h($user["AccountType"]) ?></td>
                    <td><?= h(date("d M Y", strtotime($user["DateOfBirth"]))) ?></td>
                    <td><?= h($user["Balance"]) ?> pts</td>
                    <td><?= h($user["BetCount"]) ?></td>
                    <td>
                        <?php if ($user["AccountType"] === "User"): ?>
                            <form method="post" class="inline-form" onsubmit="return confirm('Reset this user to 1,000 points? Their bet history will remain unchanged.');">
                                <input type="hidden" name="action" value="reset_balance">
                                <input type="hidden" name="user_id" value="<?= h($user["UserID"]) ?>">
                                <button class="text-button" type="submit">Reset points</button>
                            </form>
                        <?php else: ?>
                            <span class="muted">Admin</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include("footer.php"); ?>
