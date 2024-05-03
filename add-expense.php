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
        $statement = $pdo->prepare("SELECT * FROM expense WHERE expense_id = :expense_id AND id_users = :user_id");
        $statement->bindParam(':expense_id', $edit_id);
        $statement->bindParam(':user_id', $user_id);
        $statement->execute();
        $expense_data = $statement->fetch(PDO::FETCH_ASSOC);
        $is_edit = true;
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

$category = $expense_data['expense_category'] ?? '';
$assets_name = $expense_data['expense_assets'] ?? '';
$date = $expense_data['expense_date'] ?? '';
$amount = $expense_data['expense_amount'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST['category'];
    $assets = $_POST['assets'];
    $date = $_POST['date'];
    $amount = $_POST['amount'];

    try {
        if ($is_edit) {
            $statement = $pdo->prepare("UPDATE expense SET expense_amount = :amount, expense_date = :expense_date, expense_category = :category, expense_assets = :assets WHERE expense_id = :edit_id AND id_users = :user_id");
            $statement->bindParam(':edit_id', $edit_id);
        } else {
            $statement = $pdo->prepare("INSERT INTO expense (id_users, expense_amount, expense_date, expense_category, expense_assets) VALUES (:user_id, :amount, :expense_date, :category, :assets)");
        }
        $statement->bindParam(':user_id', $user_id);
        $statement->bindParam(':amount', $amount);
        $statement->bindParam(':expense_date', $date);
        $statement->bindParam(':category', $category);
        $statement->bindParam(':assets', $assets);
        $statement->execute();

        header("Location: expense.php");
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
    <title>FINANZIO - <?php echo $is_edit ? 'Edit' : 'New'; ?> Expense</title>
    <link rel="stylesheet" href="style/add-expense_style.css">
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
            <a href="add-expense.php"><button class="add-expense"><img src="image/expense_icon1.png"
                        alt="Expense"><span>Add Expense</span></button></a>
            <a href="add-assets.php"><button class="add-assets"><img src="image/assets_icon2.png" alt="Assets"><span>Add
                        Assets</span></button></a>
            <a href="add-upcoming_bills.php"><button class="add-upcoming_bills"><img
                        src="image/upcoming_bills_icon2.png" alt="Upcoming Bills"><span>Add
                        Upcoming Bills</span></button></a>
        </div>
        <div class="new-expense">
            <form
                action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . (isset($_GET['edit_id']) ? '?edit_id=' . $_GET['edit_id'] : '')); ?>"
                method="POST">
                <label for="category">Category:</label>
                <select name="category" id="category" required>
                    <option value="Food" <?php if ($category === 'Food')
                        echo 'selected'; ?>>Food</option>
                    <option value="Transport" <?php if ($category === 'Transport')
                        echo 'selected'; ?>>Transport</option>
                    <option value="Utilities" <?php if ($category === 'Utilities')
                        echo 'selected'; ?>>Utilities</option>
                    <option value="Entertainment" <?php if ($category === 'Entertainment')
                        echo 'selected'; ?>>Entertainment</option>
                    <option value="Healthcare" <?php if ($category === 'Healthcare')
                        echo 'selected'; ?>>Healthcare
                    </option>
                    <option value="Education" <?php if ($category === 'Education')
                        echo 'selected'; ?>>Education</option>
                    <option value="Others" <?php if ($category === 'Others')
                        echo 'selected'; ?>>Others</option>
                </select>

                <label for="assets">Expense from which assets:</label>
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