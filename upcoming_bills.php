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
    $upcomingStatement = $pdo->prepare("SELECT * FROM upcoming_bills WHERE id_users = :user_id");
    $upcomingStatement->bindParam(':user_id', $user_id);
    $upcomingStatement->execute();
    $upcomingBills = $upcomingStatement->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINANZIO - Upcoming Bills</title>
    <link rel="stylesheet" href="style/upcoming_bills_style.css">
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
            <a href="upcoming_bills.php"><img src="image/upcoming_bills_icon1.png" alt="Upcoming Bills"><span>Upcoming
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
                    var upcomingItems = document.querySelectorAll(".upcoming-content .upcoming-item");
                    var paidItems = document.querySelectorAll(".paid-content .paid-item");

                    upcomingItems.forEach(function (item) {
                        var text = item.textContent.toLowerCase();
                        if (text.includes(searchText)) {
                            item.style.display = "flex";
                        } else {
                            item.style.display = "none";
                        }
                    });

                    paidItems.forEach(function (item) {
                        var text = item.textContent.toLowerCase();
                        if (text.includes(searchText)) {
                            item.style.display = "flex";
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
                <h1>Upcoming Bills</h1>
                <h2>Welcome back, <?php echo $username; ?></h2>
            </div>
            <div class="upcoming">
                <div class="upcoming-content">
                    <?php
                    $groupedBills = [];

                    foreach ($upcomingBills as $bill) {
                        $billMonth = date('m', strtotime($bill['upcoming_bills_due_date']));
                        $billYear = date('Y', strtotime($bill['upcoming_bills_due_date']));

                        $groupKey = $billMonth . '-' . $billYear;

                        if (!isset($groupedBills[$groupKey])) {
                            $groupedBills[$groupKey] = [
                                'month' => $billMonth,
                                'year' => $billYear,
                                'bills' => [],
                            ];
                        }

                        $groupedBills[$groupKey]['bills'][] = $bill;
                    }

                    ksort($groupedBills);

                    foreach ($groupedBills as $group) {
                        $hasPendingBills = false;
                        foreach ($group['bills'] as $bill) {
                            if ($bill['upcoming_bills_status'] == 'Pending') {
                                $hasPendingBills = true;
                                break;
                            }
                        }

                        if ($hasPendingBills) {
                            echo "<h2>" . date('F Y', mktime(0, 0, 0, $group['month'], 1, $group['year'])) . "</h2>";

                            usort($group['bills'], function ($a, $b) {
                                return strtotime($a['upcoming_bills_due_date']) - strtotime($b['upcoming_bills_due_date']);
                            });
                            foreach ($group['bills'] as $bill) {
                                if ($bill['upcoming_bills_status'] == 'Pending') {
                                    $dueDate = strtotime($bill['upcoming_bills_due_date']);
                                    $currentDate = time();
                                    $daysLeft = floor(($dueDate - $currentDate) / (60 * 60 * 24));
                                    if ($daysLeft < 0) {
                                        $dueDateText = "<span style=\"color: #C2170C;\">Late</span>";
                                    } else {
                                        $dueDateText = $daysLeft . " days left";
                                    }
                                    echo "<div class=\"upcoming-item\">";
                                    echo "<p class=\"name\">" . $bill['upcoming_bills_name'] . "</p>";
                                    echo "<p class=\"due-date\">" . $dueDateText . "</p>";
                                    echo "<p class=\"status\">" . $bill['upcoming_bills_status'] . "</p>";
                                    echo "<p class=\"amount\" >Rp" . number_format($bill['upcoming_bills_amount']) . "</p>";
                                    echo '<form action="add-upcoming_bills.php" method="GET">';
                                    echo '<input type="hidden" name="edit_id" value="' . $bill['upcoming_bills_id'] . '">';
                                    echo '<button type="submit" class="edit">';
                                    echo '<img src="image/edit_icon.png" alt="Edit Image">';
                                    echo '</button>';
                                    echo '</form>';
                                    echo '<form action="delete-upcoming_bills.php" method="POST">';
                                    echo '<input type="hidden" name="delete_id" value="' . $bill['upcoming_bills_id'] . '">';
                                    echo '<button type="submit" class="delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">';
                                    echo '<img src="image/delete_icon.png" alt="Delete Image">';
                                    echo '</button>';
                                    echo '</form>';
                                    echo "</div>";
                                }
                            }
                        }
                    }

                    $pendingBillsExist = false;
                    foreach ($upcomingBills as $bill) {
                        if ($bill['upcoming_bills_status'] == 'Pending') {
                            $pendingBillsExist = true;
                            break;
                        }
                    }

                    if (!$pendingBillsExist) {
                        echo "<h2>No pending upcoming bills.</h2>";
                    }
                    ?>
                </div>
            </div>

            <div class="paid">
                <h1>Paid Bills</h1>
                <div class="paid-content">
                    <?php
                    $groupedPaidBills = [];
                    foreach ($upcomingBills as $bill) {
                        if ($bill['upcoming_bills_status'] == "Paid") {
                            $groupedPaidBills[] = $bill;
                        }
                    }

                    foreach ($groupedPaidBills as $paidBill) {
                        echo "<div class=\"paid-item\">";
                        echo "<p class=\"name\">" . $paidBill['upcoming_bills_name'] . "</p>";
                        echo "<p class=\"due-date\">" . date('d M Y', strtotime($paidBill['upcoming_bills_due_date'])) . "</p>";
                        echo "<p class=\"status\"  style=\"color: #1EB34B;\">" . $paidBill['upcoming_bills_status'] . ": " . $paidBill['upcoming_bills_assets'] . "</p>";
                        echo "<p class=\"amount\">Rp" . number_format($paidBill['upcoming_bills_amount']) . "</p>";
                        echo '<form action="add-upcoming_bills.php" method="GET">';
                        echo '<input type="hidden" name="edit_id" value="' . $paidBill['upcoming_bills_id'] . '">';
                        echo '<button type="submit" class="edit">';
                        echo '<img src="image/edit_icon.png" alt="Edit Image">';
                        echo '</button>';
                        echo '</form>';
                        echo '<form action="delete-upcoming_bills.php" method="POST">';
                        echo '<input type="hidden" name="delete_id" value="' . $paidBill['upcoming_bills_id'] . '">';
                        echo '<button type="submit" class="delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">';
                        echo '<img src="image/delete_icon.png" alt="Delete Image">';
                        echo '</button>';
                        echo '</form>';
                        echo "</div>";
                    }

                    if (empty($groupedPaidBills)) {
                        echo "<h2>No paid bills.</h2>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>