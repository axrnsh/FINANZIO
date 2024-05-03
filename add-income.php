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

    $checkDefaultAssets = $pdo->prepare("SELECT COUNT(*) FROM assets WHERE id_users = :user_id");
    $checkDefaultAssets->bindParam(':user_id', $user_id);
    $checkDefaultAssets->execute();
    $count = $checkDefaultAssets->fetchColumn();
    if ($count === 0) {
        $defaultAssets = [
            ['Cash', 0],
            ['Card', 0],
            ['E-wallet', 0]
        ];
        $insertDefaultAssets = $pdo->prepare("INSERT INTO assets (id_users, assets_name, assets_amount) VALUES (:user_id, :name, :amount)");

        foreach ($defaultAssets as $asset) {
            $insertDefaultAssets->bindParam(':user_id', $user_id);
            $insertDefaultAssets->bindParam(':name', $asset[0]);
            $insertDefaultAssets->bindParam(':amount', $asset[1]);
            $insertDefaultAssets->execute();
        }
    }

    $assetsQuery = $pdo->prepare("SELECT * FROM assets WHERE id_users = :user_id");
    $assetsQuery->bindParam(':user_id', $user_id);
    $assetsQuery->execute();
    $assets = $assetsQuery->fetchAll(PDO::FETCH_ASSOC);

    $is_edit = false;

    if (isset($_GET['edit_id'])) {
        $edit_id = $_GET['edit_id'];
        $statement = $pdo->prepare("SELECT * FROM income WHERE income_id = :income_id AND id_users = :user_id");
        $statement->bindParam(':income_id', $edit_id);
        $statement->bindParam(':user_id', $user_id);
        $statement->execute();
        $income_data = $statement->fetch(PDO::FETCH_ASSOC);
        $is_edit = true;
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

$category = $income_data['income_category'] ?? '';
$assets_name = $income_data['income_assets'] ?? '';
$date = $income_data['income_date'] ?? '';
$amount = $income_data['income_amount'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST['category'];
    $assets = $_POST['assets'];
    $date = $_POST['date'];
    $amount = $_POST['amount'];

    try {
        if ($is_edit) {
            $statement = $pdo->prepare("UPDATE income SET income_amount = :amount, income_date = :income_date, income_category = :category, income_assets = :assets WHERE income_id = :edit_id AND id_users = :user_id");
            $statement->bindParam(':edit_id', $edit_id);
        } else {
            $statement = $pdo->prepare("INSERT INTO income (id_users, income_amount, income_date, income_category, income_assets) VALUES (:user_id, :amount, :income_date, :category, :assets)");
        }
        $statement->bindParam(':user_id', $user_id);
        $statement->bindParam(':amount', $amount);
        $statement->bindParam(':income_date', $date);
        $statement->bindParam(':category', $category);
        $statement->bindParam(':assets', $assets);
        $statement->execute();

        header("Location: income.php");
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
    <title>FINANZIO - <?php echo $is_edit ? 'Edit' : 'New'; ?> Income</title>
    <link rel="stylesheet" href="style/add-income_style.css">
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
            <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')"><img
                    src="image/logout_icon.png" alt="Logout"><span>Log Out</span></a>
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
            <a href="add-income.php"><button class="add-income"><img src="image/income_icon1.png" alt="Income"><span>Add
                        Income</span></button></a>
            <a href="add-expense.php"><button class="add-expense"><img src="image/expense_icon2.png"
                        alt="Expense"><span>Add Expense</span></button></a>
            <a href="add-assets.php"><button class="add-assets"><img src="image/assets_icon2.png" alt="Assets"><span>Add
                        Assets</span></button></a>
            <a href="add-upcoming_bills.php"><button class="add-upcoming_bills"><img
                        src="image/upcoming_bills_icon2.png" alt="Upcoming Bills"><span>Add
                        Upcoming Bills</span></button></a>
        </div>
        <div class="new-income">
            <form
                action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . (isset($_GET['edit_id']) ? '?edit_id=' . $_GET['edit_id'] : '')); ?>"
                method="POST">
                <label for="category">Category:</label>
                <select name="category" id="category" required>
                    <option value="Cash" <?php if ($category === 'Cash')
                        echo 'selected'; ?>>Cash</option>
                    <option value="Deposit" <?php if ($category === 'Deposit')
                        echo 'selected'; ?>>Deposit</option>
                    <option value="Interest" <?php if ($category === 'Interest')
                        echo 'selected'; ?>>Interest</option>
                    <option value="Salary" <?php if ($category === 'Salary')
                        echo 'selected'; ?>>Salary</option>
                    <option value="Bonus" <?php if ($category === 'Bonus')
                        echo 'selected'; ?>>Bonus</option>
                    <option value="Bank" <?php if ($category === 'Bank')
                        echo 'selected'; ?>>Bank</option>
                    <option value="Pocket Money" <?php if ($category === 'Pocket Money')
                        echo 'selected'; ?>>Pocket Money
                    </option>
                    <option value="Others" <?php if ($category === 'Others')
                        echo 'selected'; ?>>Others</option>
                </select>

                <label for="assets">Income to which assets:</label>
                <select name="assets" id="assets" required>
                    <?php foreach ($assets as $asset): ?>
                        <option value="<?php echo $asset['assets_name']; ?>" <?php if ($asset['assets_name'] === $assets_name)
                               echo 'selected'; ?>>
                            <?php echo $asset['assets_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="date">Date:</label>
                <input type="date" id="date" name="date" value="<?php echo $date; ?>" required>

                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" value="<?php echo $amount; ?>" min="0" required>

                <input type="submit" value="<?php echo $is_edit ? 'Update' : 'Save'; ?>">
            </form>
        </div>
    </div>
</body>

</html>