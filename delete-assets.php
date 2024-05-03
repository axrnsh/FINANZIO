<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $server = "127.0.0.1:3306";
    $username = "root";
    $password = "";
    $database = "finanzio_db";

    try {
        $pdo = new PDO("mysql:host=$server;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $user_id = $_SESSION['user_id'];
        $delete_id = $_POST['delete_id'];

        $statement = $pdo->prepare("DELETE FROM assets WHERE assets_id = :assets_id AND id_users = :user_id");
        $statement->bindParam(':assets_id', $delete_id);
        $statement->bindParam(':user_id', $user_id);
        $statement->execute();

        header("Location: assets.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>