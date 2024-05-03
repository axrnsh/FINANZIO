<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

try {
    $server = "127.0.0.1:3306"; //bisa disesuaikan
    $username = "root";
    $password = "";
    $database = "finanzio_db";
    $pdo = new PDO("mysql:host=$server;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Koneksi Gagal: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $statement = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $statement->bindParam(':email', $email);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id_users'];
                $_SESSION['username'] = $user['name'];

                header("Location: dashboard.php");
                exit();
            } else {
                $login_error = "Invalid email or password.";
            }
        } else {
            $login_error = "Invalid email or password.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!-- html -->
<!-- kenapa digabung? karena aku males buat file baru, sudah cukup buanyak -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINANZIO - Log In</title>
    <link rel="stylesheet" href="style/login.css">
</head>

<body>
    <img id="bottom-left-img" src="image/finanzio_big2.png" alt="Bottom Left Image">
    <img id="top-right-img" src="image/finanzio_big1.png" alt="Top Right Image">

    <div class="rectangle-container">
        <div class="left-rectangle">
            <div class="left-content">
                <img id="logo-login" src="image/finanzio_logo.png" alt="logo">
                <h1>The simplest way to manage your money</h1>
                <h2>Effortlessly track your expenses, manage budgets, and gain insights into your spending habits with
                    unparalleled ease and simplicity.</h2>
                <img id="preview" src="image/sementara.png" alt="Preview" class="preview">
            </div>
        </div>

        <div class="right-rectangle">
            <h2>Log In</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>

                <div class="checkbox-class">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember" id="text-remember">Remember Me</label>
                </div>

                <input type="submit" value="Log In">
            </form>

            <p>Don’t have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>

    <script>
        // untuk keluar alert kalau email atau passwordnya salah
        let failedLogin = <?php echo isset($login_error) ? 'true' : 'false'; ?>;
        window.onload = function () {
            if (failedLogin) {
                alert("Invalid email or password.");
            }
        };
    </script>
</body>

</html>