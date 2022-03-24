<?php
require_once('config.php');
require_once('functions.php');

session_start();

$pdo = connectDb();

// ログインチェック
if (!isset($_SESSION['USER'])) {
    header('Location: '.SITE_URL.'login.php');
    exit;
}

$user = $_SESSION['USER'];

$id = $_GET['id'];

if ($user['id'] != $id) {
    echo '<html><head><meta charset="utf-8"></head><body>不正なアクセスです。</body></html>';
    exit;
}

$sql = "DELETE FROM user WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":id" => $id));

$sql = "DELETE FROM news WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":user_id" => $user['id']));

unset($pdo);

// ログアウト処理
$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
	setcookie(session_name(), '', time()-86400, COOKIE_PATH);
}

session_destroy();

header('Location: '.SITE_URL.'account_delete_complete.php');

exit;
?>
