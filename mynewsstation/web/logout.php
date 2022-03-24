<?php
require_once('config.php');
require_once('functions.php');

session_start();

// データベースに接続する（PDOを使う）
$pdo = connectDb();

// 自動ログイン情報クリア
if (isset($_COOKIE['CONTENTSMAKER'])) {

	$auto_login_key = $_COOKIE['CONTENTSMAKER'];

	// Cookie情報をクリア
	setcookie('CONTENTSMAKER', '', time()-86400, COOKIE_PATH);

	// DB情報をクリア
	$sql = "DELETE FROM auto_login WHERE c_key = :c_key";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(array(":c_key" => $auto_login_key));
}

unset($pdo);

// ログアウト処理
$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
	setcookie(session_name(), '', time()-86400, COOKIE_PATH);
}

session_destroy();

header('Location:'.SITE_URL.'login.php');
?>
