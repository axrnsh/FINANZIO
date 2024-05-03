<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$server = "127.0.0.1:3306";
$username = "root";
$password = "";
$database = "finanzio_db";

try {
    $pdo = new PDO("mysql:host=$server;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'];
    $statement = $pdo->prepare("SELECT name FROM users WHERE id_users = :user_id");
    $statement->bindParam(':user_id', $user_id);
    $statement->execute();
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    $username = $user['name'];

    $assetsQuery = $pdo->prepare("SELECT * FROM assets WHERE id_users = :user_id");
    $assetsQuery->bindParam(':user_id', $user_id);
    $assetsQuery->execute();
    $assets = $assetsQuery->fetchAll(PDO::FETCH_ASSOC);

    $is_edit = false;

    if (isset($_GET['edit_id'])) {
        $edit_id = $_GET['edit_id'];
        $statement = $pdo->prepare("SELECT * FROM upcoming_bills WHERE upcoming_bills_id = :upcoming_bills_id AND id_users = :user_id");
        $statement->bindParam(':upcoming_bills_id', $edit_id);
        $statement->bindParam(':user_id', $user_id);
        $statement->execute();
        $upcoming_bills_data = $statement->fetch(PDO::FETCH_ASSOC);
        $is_edit = true;
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

$upcoming_bills_name = $upcoming_bills_data['upcoming_bills_name'] ?? '';
$upcoming_bills_due_date = $upcoming_bills_data['upcoming_bills_due_date'] ?? '';
$upcoming_bills_status = $upcoming_bills_data['upcoming_bills_status'] ?? 'pending';
$upcoming_bills_amount = $upcoming_bills_data['upcoming_bills_amount'] ?? '';
$assets_name = $upcoming_bills_data['assets_name'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $upcoming_bills_name = $_POST['upcoming_bills_name'];
    $upcoming_bills_due_date = $_POST['upcoming_bills_due_date'];
    $upcoming_bills_status = $_POST['upcoming_bills_status'];
    $upcoming_bills_amount = $_POST['upcoming_bills_amount'];
    $upcoming_bills_assets = $_POST['upcoming_bills_assets'];

    try {
        if ($is_edit) {
            $statement = $pdo->prepare("UPDATE upcoming_bills SET upcoming_bills_name = :upcoming_bills_name, upcoming_bills_due_date = :upcoming_bills_due_date, upcoming_bills_status = :upcoming_bills_status, upcoming_bills_amount = :upcoming_bills_amount, upcoming_bills_assets = :upcoming_bills_assets WHERE upcoming_bills_id = :edit_id AND id_users = :user_id");
            $statement->bindParam(':edit_id', $edit_id);
        } else {
            $statement = $pdo->prepare("INSERT INTO upcoming_bills (id_users, upcoming_bills_name, upcoming_bills_due_date, upcoming_bills_status, upcoming_bills_amount, upcoming_bills_assets) VALUES (:user_id, :upcoming_bills_name, :upcoming_bills_due_date, :upcoming_bills_status, :upcoming_bills_amount, :upcoming_bills_assets)");
        }
        $statement->bindParam(':user_id', $user_id);
        $statement->bindParam(':upcoming_bills_name', $upcoming_bills_name);
        $statement->bindParam(':upcoming_bills_due_date', $upcoming_bills_due_date);
        $statement->bindParam(':upcoming_bills_status', $upcoming_bills_status);
        $statement->bindParam(':upcoming_bills_amount', $upcoming_bills_amount);
        $statement->bindParam(':upcoming_bills_assets', $upcoming_bills_assets);
        $statement->execute();

        header("Location: upcoming_bills.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINANZIO - <?php echo $is_edit ? 'Edit' : 'New'; ?> Upcoming Bills</title>
    <link rel="stylesheet" href="style/add-upcoming_bills_style.css">
</head>

<body>
    <div class="sidebar">
        <a href="dashboard.php"><img src="image/finanzio_logo.png" alt="FINANZIO Logo" class="logo"></a>
        <a href="add-income.php"><button class="new"><img src="image/new_icon.png"
                    alt="New"><span>New</span></button></a>
        <div class="menu">
            <a href="dashboard.php"><img src="image/dashboard_icon2.png" alt="Dashboard"><span>Dashboard</span></a>
            <a href="income.php"><img src="image/income_icon2.png" alt="Income"><span>Income</span></a>
            <a href="expense.php"><img src="image/expense_icon2.png" alt="Expense"><span>Expense</span></a>
            <a href="assets.php"><img src="image/assets_icon2.png" alt="Assets"><span>Assets</span></a>
            <a href="upcoming_bills.php"><img src="image/upcoming_bills_icon2.png" alt="Upcoming Bills"><span>Upcoming
                    Bills</span></a>
            <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')"><img src="image/logout_icon.png" alt="Logout"><span>Log Out</span></a>
        </div>
    </div>
    <div class="main">
        <div class="topbar">
            <div class="settings">
                <img src="image/settings_icon.png" alt="Settings">
            </div>
            <div class="profile">
                <img src="image/profile_image.png" alt="Profile Image">
                <span><a href="profile.php"><?php echo $username; ?></a></span>
                <img src="image/profile_icon.png" alt="Profile Icon" style="width: 20px;">
            </div>
        </div>
        <div class="add-options">
            <a href="add-income.php"><button class="add-income"><img src="image/income_icon2.png" alt="Income"><span>Add
                        Income</span></button></a>
            <a href="add-expense.php"><button class="add-expense"><img src="image/expense_icon2.png"
                        alt="Expense"><span>Add Expense</span></button></a>
            <a href="add-assets.php"><button class="add-assets"><img src="image/assets_icon2.png" alt="Assets"><span>Add
                        Asset</span></button></a>
            <a href="add-upcoming_bills.php"><button class="add-upcoming_bills"><img
                        src="image/upcoming_bills_icon1.png" alt="Upcoming Bills"><span>Add
                        Upcoming Bills</span></button></a>
        </div>
        <div class="new-bills">
            <form
                action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . (isset($_GET['edit_id']) ? '?edit_id=' . $_GET['edit_id'] : '')); ?>"
                method="POST">
                <label for="upcoming_bills_name">Upcoming Bills Name:</label>
                <input type="text" id="upcoming_bills_name" name="upcoming_bills_name"
                    value="<?php echo $upcoming_bills_name; ?>" required>

                <label for="upcoming_bills_due_date">Due Date:</label>
                <input type="date" id="upcoming_bills_due_date" name="upcoming_bills_due_date"
                    value="<?php echo $upcoming_bills_due_date; ?>" required>

                <label for="upcoming_bills_status">Status:</label>
                <input type="radio" id="pending" name="upcoming_bills_status" value="Pending" <?php echo ($upcoming_bills_status == 'Pending') ? 'checked' : ''; ?>>
                <label for="pending">Pending</label>
                <input type="radio" id="paid" name="upcoming_bills_status" value="Paid" <?php echo ($upcoming_bills_status == 'Paid') ? 'checked' : ''; ?>>
                <label for="paid">Paid</label><br><br>

                <label for="upcoming_bills_amount">Amount:</label>
                <input type="number" id="upcoming_bills_amount" name="upcoming_bills_amount"
                    value="<?php echo $upcoming_bills_amount; ?>" min="0" required>

                <label for="assets" id="assetsLabel" style="display: none;">Paid using which asset:</label>
                <select name="upcoming_bills_assets" id="assets" style="display: none;" required>
                    <?php foreach ($assets as $asset): ?>
                        <option value="<?php echo $asset['assets_name']; ?>" <?php if ($asset['assets_name'] === $assets_name)
                               echo 'selected'; ?>>
                            <?php echo $asset['assets_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var paidRadio = document.getElementById('paid');
                        var assetsLabel = document.getElementById('assetsLabel');
                        var assetsDropdown = document.getElementById('assets');

                        assetsLabel.style.display = 'none';
                        assetsDropdown.style.display = 'none';

                        paidRadio.addEventListener('change', function () {
                            if (this.checked) {
                                assetsLabel.style.display = 'block';
                                assetsDropdown.style.display = 'block';
                            }
                        });

                        var pendingRadio = document.getElementById('pending');
                        pendingRadio.addEventListener('change', function () {
                            if (this.checked) {
                                assetsLabel.style.display = 'none';
                                assetsDropdown.style.display = 'none';
                            }
                        });
                    });
                </script>

                <input type="submit" value="<?php echo $is_edit ? 'Update' : 'Save'; ?>">
            </form>
        </div>
    </div>
</body>

</html>