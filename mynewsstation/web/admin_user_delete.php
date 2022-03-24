<?php
require_once('config.php');
require_once('functions.php');

session_start();

$pdo = connectDB();

if (!isset($_SESSION['ADMIN'])) {
    header('Location:'.SITE_URL.'admin_login.php');
    exit;
}

$admin = $_SESSION['ADMIN'];

$id = $_GET['id'];

$sql = "DELETE FROM user WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":id" => $id));

unset($pdo);

header('Location: '.SITE_URL.'admin_user_list.php');

exit;
?>
