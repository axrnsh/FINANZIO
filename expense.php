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
    $statement = $pdo->prepare("SELECT * FROM expense WHERE id_users = :id_users ORDER BY expense_date DESC LIMIT 10");
    $statement->bindParam(':id_users', $user_id);
    $statement->execute();
    $expenses = $statement->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINANZIO - Expense</title>
    <link rel="stylesheet" href="style/expense_style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
</head>

<body>
    <div class="sidebar">
        <a href="dashboard.php"><img src="image/finanzio_logo.png" alt="FINANZIO Logo" class="logo"></a>
        <a href="add-income.php"><button class="new"><img src="image/new_icon.png"
                    alt="New"><span>New</span></button></a>
        <div class="menu">
            <a href="dashboard.php"><img src="image/dashboard_icon2.png" alt="Dashboard"><span>Dashboard</span></a>
            <a href="income.php"><img src="image/income_icon2.png" alt="Income"><span>Income</span></a>
            <a href="expense.php"><img src="image/expense_icon1.png" alt="Expense"><span>Expense</span></a>
            <a href="assets.php"><img src="image/assets_icon2.png" alt="Assets"><span>Assets</span></a>
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
                    var expenseItems = document.querySelectorAll(".overview-content .expense-item");

                    expenseItems.forEach(function (item) {
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
                <h1>Expense</h1>
                <h2>Welcome back, <?php echo $username; ?></h2>
            </div>

            <div class="expense">
                <div class="chart-section">
                    <canvas id="expenseChart"></canvas>
                </div>
                <script>
                    var monthlyExpenses = new Array(12).fill(0);

                    <?php foreach ($expenses as $expense): ?>
                        var expenseMonth = new Date("<?php echo $expense['expense_date']; ?>").getMonth();
                        monthlyExpenses[expenseMonth] += <?php echo $expense['expense_amount']; ?>;
                    <?php endforeach; ?>

                    var monthLabels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

                    var chartData = {
                        labels: monthLabels,
                        datasets: [{
                            label: 'Monthly Expenses',
                            backgroundColor: 'rgba(58,120,241, 0.5)',
                            borderColor: 'rgba(58,120,241, 1)',
                            borderWidth: 1,
                            data: monthlyExpenses
                        }]
                    };

                    var chartOptions = {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    };

                    var ctx = document.getElementById('expenseChart').getContext('2d');
                    var expenseChart = new Chart(ctx, {
                        type: 'bar',
                        data: chartData,
                        options: chartOptions
                    });
                </script>

                <div class="stats-section">
                    <div class="top-stats">
                        <div class="rectangle1">
                            <div class="circle1"></div>
                            <h3>Last week</h3>
                            <h4>Rp <span id="lastWeekTotal">0</span></h4>
                        </div>
                        <div class="rectangle2">
                            <div class="circle2"></div>
                            <h3>Last month</h3>
                            <h4>Rp <span id="lastMonthTotal">0</span></h4>
                        </div>
                    </div>
                    <div class="bottom-stats">
                        <div class="rectangle1">
                            <div class="circle3"></div>
                            <h3>Last 6 months</h3>
                            <h4>Rp <span id="lastSixMonthsTotal">0</span></h4>
                        </div>
                        <div class="rectangle2">
                            <div class="circle4"></div>
                            <h3>Last year</h3>
                            <h4>Rp <span id="lastYearTotal">0</span></h4>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                var lastWeekTotal = 0;
                var lastMonthTotal = 0;
                var lastSixMonthsTotal = 0;
                var lastYearTotal = 0;

                <?php foreach ($expenses as $expense): ?>
                    var expenseDate = new Date("<?php echo $expense['expense_date']; ?>");

                    var today = new Date();

                    var difference = today - expenseDate;

                    var differenceInDays = Math.floor(difference / (1000 * 60 * 60 * 24));

                    if (differenceInDays <= 7) { // Last week
                        lastWeekTotal += <?php echo $expense['expense_amount']; ?>;
                    }
                    if (differenceInDays <= 30) { // Last month
                        lastMonthTotal += <?php echo $expense['expense_amount']; ?>;
                    }
                    if (differenceInDays <= 180) { // Last 6 months
                        lastSixMonthsTotal += <?php echo $expense['expense_amount']; ?>;
                    }
                    if (differenceInDays <= 365) { // Last year
                        lastYearTotal += <?php echo $expense['expense_amount']; ?>;
                    }
                <?php endforeach; ?>

                function numberWithCommas(number) {
                    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                }

                document.getElementById("lastWeekTotal").innerText = numberWithCommas(lastWeekTotal);
                document.getElementById("lastMonthTotal").innerText = numberWithCommas(lastMonthTotal);
                document.getElementById("lastSixMonthsTotal").innerText = numberWithCommas(lastSixMonthsTotal);
                document.getElementById("lastYearTotal").innerText = numberWithCommas(lastYearTotal);
            </script>
            <div class="overview">
                <h1>Overview</h1>
                <div class="overview-content">
                    <?php
                    $groupedExpenses = [];
                    foreach ($expenses as $expense) {
                        $expenseDate = date('Y-m-d', strtotime($expense['expense_date']));
                        $expenseDay = date('d', strtotime($expense['expense_date']));
                        $expenseMonth = date('M', strtotime($expense['expense_date']));

                        $period = '';
                        if ($expenseDate == date('Y-m-d')) {
                            $groupedExpenses['today'][] = $expense;
                        } elseif ($expenseDate == date('Y-m-d', strtotime('-1 day'))) {
                            $groupedExpenses['yesterday'][] = $expense;
                        } elseif (date('W', strtotime($expenseDate)) == date('W')) {
                            $groupedExpenses['this_week'][] = $expense;
                        } elseif (date('m', strtotime($expenseDate)) == date('m')) {
                            $groupedExpenses['this_month'][] = $expense;
                        } elseif (date('Y', strtotime($expenseDate)) == date('Y')) {
                            $groupedExpenses['this_year'][] = $expense;
                        }
                    }

                    $count = 0;
                    foreach ($groupedExpenses as $period => $expenses) {
                        if (!empty($expenses)) {
                            echo "<h2>" . ucfirst(str_replace('_', ' ', $period)) . "</h2>";
                            foreach ($expenses as $expense) {
                                echo "<div class=\"expense-item\">";
                                echo "<p class=\"category\">" . $expense['expense_category'] . "</p>";
                                echo "<p class=\"date\">" . date('d F Y ', strtotime($expense['expense_date'])) . "</p>";
                                echo "<p class=\"assets\">" . $expense['expense_assets'] . "</p>";
                                echo "<p class=\"amount\">-Rp" . number_format($expense['expense_amount']) . "</p>";
                                echo '<form action="add-expense.php" method="GET">';
                                echo '<input type="hidden" name="edit_id" value="' . $expense['expense_id'] . '">';
                                echo '<button type="submit" class="edit">';
                                echo '<img src="image/edit_icon.png" alt="Edit Image">';
                                echo '<span>Edit</span>';
                                echo '</button>';
                                echo '</form>';
                                echo '</form>';
                                echo '<form action="delete-expense.php" method="POST">';
                                echo '<input type="hidden" name="delete_id" value="' . $expense['expense_id'] . '">';
                                echo '<button type="submit" class="delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">';
                                echo '<img src="image/delete_icon.png" alt="Delete Image">';
                                echo '<span>Delete</span>';
                                echo '</button>';
                                echo '</form>';
                                echo "</div>";
                                $count++;
                                if ($count >= 20) {
                                    break 2;
                                }
                            }
                        }
                    }

                    if ($count == 0) {
                        echo "<h2>Nothing's here....</h2>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>