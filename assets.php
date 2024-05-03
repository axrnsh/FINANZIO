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
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

try {
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
    $assetsStatement = $pdo->prepare("SELECT * FROM assets WHERE id_users = :user_id");
    $assetsStatement->bindParam(':user_id', $user_id);
    $assetsStatement->execute();
    $assets = $assetsStatement->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINANZIO - Assets</title>
    <link rel="stylesheet" href="style/assets_style.css">
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
            <a href="assets.php"><img src="image/assets_icon1.png" alt="Assets"><span>Assets</span></a>
            <a href="upcoming_bills.php"><img src="image/upcoming_bills_icon2.png" alt="Upcoming Bills"><span>Upcoming
                    Bills</span></a>
            <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')"><img
                    src="image/logout_icon.png" alt="Logout"><span>Log Out</span></a>
        </div>
    </div>

    <div class="main">
        <div class="topbar">
            <div class="search-bar">
                <img src="image/search_icon.png" alt="Search">
                <input id="searchInput" type="text" placeholder="Search">
            </div>
            <script>
                function search() {
                    var searchText = document.getElementById("searchInput").value.toLowerCase();
                    var assetsItems = document.querySelectorAll(".assets .rectangle");

                    assetsItems.forEach(function (item) {
                        var text = item.textContent.toLowerCase();
                        if (text.includes(searchText)) {
                            item.style.display = "";
                        } else {
                            item.style.display = "none";
                        }
                    });
                }

                document.getElementById("searchInput").addEventListener("input", search);
            </script>
            <div class="settings">
                <img src="image/settings_icon.png" alt="Settings">
            </div>
            <div class="profile">
                <img src="image/profile_image.png" alt="Profile Image">
                <span><a href="profile.php"><?php echo $username; ?></a></span>
                <img src="image/profile_icon.png" alt="Profile Icon" style="width: 20px;">
            </div>
        </div>
        <div class="content">
            <div class="welcome">
                <h1>Assets</h1>
                <h2>Welcome back, <?php echo $username; ?></h2>
            </div>

            <div class="assets">
                <?php foreach ($assets as $asset): ?>
                    <div class="rectangle">
                        <h3><?php echo $asset['assets_name']; ?></h3>
                        <h4>Rp <?php echo number_format($asset['assets_amount']); ?></h4>
                        <h5>Total amount</h5>
                        <div class="circle"></div>
                        <div class="action">
                            <form action="add-assets.php" method="GET">
                                <input type="hidden" name="edit_id" value="<?php echo $asset['assets_id']; ?>">
                                <button type="submit" class="edit">
                                    <img src="image/edit_icon.png" alt="Edit Image">
                                </button>
                            </form>
                            <form action="delete-assets.php" method="POST">
                                <input type="hidden" name="delete_id" value="<?php echo $asset['assets_id']; ?>">
                                <button type="submit" class="delete"
                                    onclick="return confirm('Are you sure you want to delete this item?');">
                                    <img src="image/delete_icon.png" alt="Delete Image">
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="rectangle-new">
                    <div class="add-new">
                        <a href="add-assets.php"><button class="new-assets"><img src="image/new_icon.png"
                                    alt="New Account"><span>New Asset</span></button></a>
                    </div>
                </div>
            </div>
        </div>
</body>

</html>