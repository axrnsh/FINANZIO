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

//Dashboard total income last 7 days
$incomeTotal = 0;
try {
    $statement = $pdo->prepare("SELECT SUM(income_amount) AS total FROM income WHERE id_users = :user_id AND income_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    $statement->bindParam(':user_id', $user_id);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);
    $incomeTotal = $result['total'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

//Dashboard total expense last 7 days
$expenseTotal = 0;
try {
    $statement = $pdo->prepare("SELECT SUM(expense_amount) AS total FROM expense WHERE id_users = :user_id AND expense_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    $statement->bindParam(':user_id', $user_id);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);
    $expenseTotal = $result['total'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

//Dashboard total assets
$totalAssets = 0;
try {
    $assetsStatement = $pdo->prepare("SELECT SUM(assets_amount) AS total FROM assets WHERE id_users = :user_id");
    $assetsStatement->bindParam(':user_id', $user_id);
    $assetsStatement->execute();
    $result = $assetsStatement->fetch(PDO::FETCH_ASSOC);
    if ($result && $result['total']) {
        $totalAssets = $result['total'];
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

//Overview both income and expense (last 10)
$incomeStatement = $pdo->prepare("SELECT * FROM income WHERE id_users = :id_users ORDER BY income_date DESC LIMIT 10");
$incomeStatement->bindParam(':id_users', $user_id);
$incomeStatement->execute();
$incomes = $incomeStatement->fetchAll(PDO::FETCH_ASSOC);

$expenseStatement = $pdo->prepare("SELECT * FROM expense WHERE id_users = :id_users ORDER BY expense_date DESC LIMIT 10");
$expenseStatement->bindParam(':id_users', $user_id);
$expenseStatement->execute();
$expenses = $expenseStatement->fetchAll(PDO::FETCH_ASSOC);
// Gabungin overview income and expense 
$overviewRecords = array_merge($incomes, $expenses);
// Urutkan berdasarkan tanggal
usort($overviewRecords, function ($a, $b) {
    return strtotime($b['income_date'] ?? $b['expense_date']) - strtotime($a['income_date'] ?? $a['expense_date']);
});
$overviewRecords = array_slice($overviewRecords, 0, 10);

//Untuk upcoming bills
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
    <title>FINANZIO - Dashboard</title>
    <link rel="stylesheet" href="style/dashboard_style.css">
</head>

<body>
    <div class="sidebar">
        <a href="dashboard.php"><img src="image/finanzio_logo.png" alt="FINANZIO Logo" class="logo"></a>
        <a href="add-income.php"><button class="new"><img src="image/new_icon.png"
                    alt="New"><span>New</span></button></a>
        <div class="menu">
            <a href="dashboard.php"><img src="image/dashboard_icon1.png" alt="Dashboard"><span>Dashboard</span></a>
            <a href="income.php"><img src="image/income_icon2.png" alt="Income"><span>Income</span></a>
            <a href="expense.php"><img src="image/expense_icon2.png" alt="Expense"><span>Expense</span></a>
            <a href="assets.php"><img src="image/assets_icon2.png" alt="Assets"><span>Assets</span></a>
            <a href="upcoming_bills.php"><img src="image/upcoming_bills_icon2.png" alt="Upcoming Bills"><span>Upcoming
                    Bills</span></a>
            <a href="logout.php"><img src="image/logout_icon.png" alt="Logout"><span>Log Out</span></a>
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
                    var overviewItems = document.querySelectorAll(".overview-content .overview-item");
                    var upcomingItems = document.querySelectorAll(".upcoming-content .upcoming-item");

                    overviewItems.forEach(function (item) {
                        var text = item.textContent.toLowerCase();
                        if (text.includes(searchText)) {
                            item.style.display = "flex";
                        } else {
                            item.style.display = "none";
                        }
                    });

                    upcomingItems.forEach(function (item) {
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
                <h1>Dashboard</h1>
                <h2>Welcome back, <?php echo $username; ?></h2>
            </div>

            <div class="dashboard">
                <div class="stats-section">
                    <div class="stats">
                        <div class="rectangle1">
                            <h3>Total Income</h3>
                            <h4>Rp <?php echo number_format($incomeTotal, 0); ?></h4>
                            <h5>Last 7 days</h5>
                            <div class="circle1"></div>
                        </div>
                        <div class="rectangle2">
                            <h3>Total Expense</h3>
                            <h4>Rp <?php echo number_format($expenseTotal, 0); ?></h4>
                            <h5>Last 7 days</h5>
                            <div class="circle2"></div>
                        </div>
                        <div class="rectangle3">
                            <h3>Total Assets</h3>
                            <h4>Rp <?php echo number_format($totalAssets, 0); ?></h4>
                            <h5>Total</h5>
                            <div class="circle3"></div>
                        </div>
                    </div>
                </div>
                <div class="container">
                    <div class="overview">
                        <h1>Overview</h1>
                        <div class="overview-content">
                            <?php
                            $groupedRecords = [];
                            foreach ($overviewRecords as $record) {
                                $recordDate = isset($record['income_date']) ? $record['income_date'] : $record['expense_date'];
                                $recordDay = date('d', strtotime($recordDate));
                                $recordMonth = date('M', strtotime($recordDate));
                                $recordType = isset($record['income_category']) ? 'income' : 'expense';

                                $period = '';
                                if ($recordDate == date('Y-m-d')) {
                                    $period = 'today';
                                } elseif ($recordDate == date('Y-m-d', strtotime('-1 day'))) {
                                    $period = 'yesterday';
                                } elseif (date('W', strtotime($recordDate)) == date('W')) {
                                    $period = 'this_week';
                                } elseif (date('m', strtotime($recordDate)) == date('m')) {
                                    $period = 'this_month';
                                } elseif (date('Y', strtotime($recordDate)) == date('Y')) {
                                    $period = 'this_year';
                                }
                                $groupedRecords[$period][$recordType][] = $record;
                            }

                            foreach ($groupedRecords as $period => $records) {
                                if (!empty($records)) {
                                    echo "<h2>" . ucfirst(str_replace('_', ' ', $period)) . "</h2>";
                                    $mergedItems = [];
                                    foreach ($records as $type => $items) {
                                        $mergedItems = array_merge($mergedItems, $items);
                                    }

                                    usort($mergedItems, function ($a, $b) {
                                        return strtotime($b['income_date'] ?? $b['expense_date']) - strtotime($a['income_date'] ?? $a['expense_date']);
                                    });

                                    foreach ($mergedItems as $item) {
                                        $type = isset($item['income_category']) ? 'income' : 'expense';
                                        echo "<div class=\"overview-item\">";
                                        echo "<p class=\"category\">" . ($type === 'income' ? $item['income_category'] : $item['expense_category']) . "</p>";
                                        echo "<p class=\"date\">" . date('d M ', strtotime($item['income_date'] ?? $item['expense_date'])) . "</p>";
                                        echo "<p class=\"assets\">" . ($type === 'income' ? $item['income_assets'] : $item['expense_assets']) . "</p>";
                                        echo "<p class=\"amount " . ($type === 'income' ? 'income-amount' : 'expense-amount') . "\">" . ($type === 'income' ? '+Rp' : '-Rp') . number_format($item['income_amount'] ?? $item['expense_amount']) . "</p>";
                                        echo "</div>";
                                    }
                                }
                            }

                            if (empty($groupedRecords)) {
                                echo "<h2>Nothing's here....</h2>";
                            }
                            ?>
                        </div>
                    </div>
                    <div class="upcoming">
                        <h1>Upcoming Bills</h1>
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
                                            echo "<div class=\"left\">";
                                            echo "<p class=\"name\">" . $bill['upcoming_bills_name'] . "</p>";
                                            echo "<p class=\"due-date\">" . $dueDateText . " - " . $bill['upcoming_bills_status'] . "</p>";
                                            echo "</div>";
                                            echo "<div class=\"right\">";
                                            echo "<p class=\"amount\">Rp" . number_format($bill['upcoming_bills_amount']) . "</p>";
                                            echo "</div>";
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
                </div>
            </div>
        </div>
    </div>
</body>

</html>