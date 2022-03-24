<?php
require_once('config.php');
require_once('functions.php');

session_start();

// ログアウト処理
$_SESSION = array();

session_destroy();

header('Location:'.SITE_URL.'admin_login.php');
?>