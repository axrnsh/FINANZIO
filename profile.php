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
    $statement = $pdo->prepare("SELECT name, email FROM users WHERE id_users = :user_id");
    $statement->bindParam(':user_id', $user_id);
    $statement->execute();
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    $username = $user['name'];
    $email = $user['email'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $new_name = $_POST['name'];

        $update_statement = $pdo->prepare("UPDATE users SET name = :name WHERE id_users = :user_id");
        $update_statement->bindParam(':name', $new_name);
        $update_statement->bindParam(':user_id', $user_id);
        $update_statement->execute();

        $username = $new_name;
        header("Location: dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINANZIO - Profile</title>
    <link rel="stylesheet" href="style/profile.css">
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
        <div class="profile-form">
            <h2>Edit Profile</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo $username; ?>" required>

                <label for="email">Email:</label>
                <input type="text" name="email" value="<?php echo $email; ?>" readonly class="gray-text">

                <input type="submit" value="Save Change">
            </form>
        </div>
    </div>
</body>

</html>