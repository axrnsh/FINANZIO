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
} catch(PDOException $e) {
    echo "Koneksi Gagal: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $statement = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
        $statement->bindParam(':name', $name);
        $statement->bindParam(':email', $email);
        $statement->bindParam(':password', $hashed_password);
        $statement->execute();

        header("Location: login.php");
        exit();
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!-- html -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINANZIO - Sign Up</title>
    <link rel="stylesheet" href="style/signup.css">
</head>

<body>
    <img id="bottom-left-img" src="image/finanzio_big2.png" alt="Bottom Left Image">
    <img id="top-right-img" src="image/finanzio_big1.png" alt="Top Right Image">

    <div class="rectangle-container">
        <div class="left-rectangle">
            <div class="left-content">
                <img id="logo-signup" src="image/finanzio_logo.png" alt="logo">
                <h1>The simplest way to manage your money</h1>
                <h2>Effortlessly track your expenses, manage budgets, and gain insights into your spending habits with
                    unparalleled ease and simplicity.</h2>
                <img id="preview" src="image/sementara.png" alt="Preview" class="preview">
            </div>
        </div>

        <div class="right-rectangle">
            <h2>Create your account</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your name" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create password" required>
                
                <div class="checkbox-class">
                    <input type="checkbox" id="agree" name="agree" required>
                    <label for="agree" id="text-agree">I agree to all <a href="">Terms & Condition</a></label>
                </div>

                <input type="submit" value="Sign Up">
            </form>

            <p>Already have an account? <a href="login.php">Log In</a></p>
        </div>
    </div>
</body>

</html>