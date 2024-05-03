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
        $statement = $pdo->prepare("SELECT * FROM assets WHERE assets_id = :assets_id AND id_users = :user_id");
        $statement->bindParam(':assets_id', $edit_id);
        $statement->bindParam(':user_id', $user_id);
        $statement->execute();
        $assets_data = $statement->fetch(PDO::FETCH_ASSOC);
        $is_edit = true;
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

$assets_name = $assets_data['assets_name'] ?? '';
$assets_amount = $assets_data['assets_amount'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assets_name = $_POST['assets_name'];
    $assets_amount = $_POST['assets_amount'];

    try {
        if ($is_edit) {
            $statement = $pdo->prepare("UPDATE assets SET assets_name = :assets_name, assets_amount = :assets_amount WHERE assets_id = :edit_id AND id_users = :user_id");
            $statement->bindParam(':edit_id', $edit_id);
        } else {
            $statement = $pdo->prepare("INSERT INTO assets (id_users, assets_name, assets_amount) VALUES (:user_id, :assets_name, :assets_amount)");
        }
        $statement->bindParam(':user_id', $user_id);
        $statement->bindParam(':assets_name', $assets_name);
        $statement->bindParam(':assets_amount', $assets_amount);
        $statement->execute();

        header("Location: assets.php");
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
    <title>FINANZIO - <?php echo $is_edit ? 'Edit' : 'New'; ?> Asset</title>
    <link rel="stylesheet" href="style/add-assets_style.css">
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
            <a href="add-assets.php"><button class="add-assets"><img src="image/assets_icon1.png" alt="Assets"><span>Add
                        Asset</span></button></a>
            <a href="add-upcoming_bills.php"><button class="add-upcoming_bills"><img
                        src="image/upcoming_bills_icon2.png" alt="Upcoming Bills"><span>Add
                        Upcoming Bills</span></button></a>
        </div>
        <div class="new-assets">
            <form
                action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . (isset($_GET['edit_id']) ? '?edit_id=' . $_GET['edit_id'] : '')); ?>"
                method="POST">
                <label for="assets_name">Asset Name:</label>
                <input type="text" id="assets_name" name="assets_name" value="<?php echo $assets_name; ?>" 
                    required><br><br>

                <label for="assets_amount">Asset Amount:</label>
                <input type="number" id="assets_amount" name="assets_amount" value="<?php echo $assets_amount; ?>" min="0"
                    required><br><br>

                <input type="submit" value="<?php echo $is_edit ? 'Update' : 'Save'; ?>">
            </form>
        </div>
    </div>
</body>

</html>