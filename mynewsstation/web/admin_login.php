<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = '管理者ログイン';

session_start();

$pdo = connectDB();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // CSRF対策
    setToken();
} else {
    // CSRF対策
    checkToken();

    $admin_account = $_POST['admin_account'];
    $admin_password = $_POST['admin_password'];

    $err = array();

    if ($admin_account == '') {
        $err['admin_account'] = '管理者アカウントを入力して下さい。';
    }
    if ($admin_password == '') {
        $err['admin_password'] = 'パスワードを入力して下さい。';
    }

    $sql = 'SELECT * FROM admin WHERE admin_account = :admin_account AND admin_password = :admin_password LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':admin_account' => $admin_account, ':admin_password' => $admin_password));
    $admin = $stmt->fetch();
    if (!$admin) {
        $err['admin_password'] = '認証に失敗しました。';
    }

    if (empty($err)) {
		// セッションハイジャック対策
		session_regenerate_id(true);
        $_SESSION['ADMIN'] = $admin;

		// HOME画面に遷移する。
        unset($pdo);
        header('Location:'.SITE_URL.'admin_home.php');
		exit;
    }
    unset($pdo);
}
?>

<?php include 'layouts/head.php'; ?>

<body id="main" class="bg-dark text-light">

    <?php include 'layouts/admin_header.php'; ?>

    <div class="container">
        <h1><?php echo h($page_title); ?></h1>

        <div class="panel panel-default">
            <div class="panel-body">
                <form method="POST">
                    <div class="form-group">
                        <label>管理者アカウント</label>
                        <input type="text" class="form-control" name="admin_account" value="<?php echo h($admin_account); ?>" placeholder="管理者アカウント" />
                        <span class="help-block"><?php echo h($err['admin_account']); ?></span>
                    </div>

                    <div class="form-group">
                        <label>パスワード</label>
                        <input type="password" class="form-control" name="admin_password" placeholder="パスワード" />
                        <span class="help-block"><?php echo h($err['admin_password']); ?></span>
                    </div>

                    <div class="form-group">
                        <input type="submit" class="btn btn-success btn-block" value="ログイン">
                    </div>
                    <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
                </form>
            </div>
        </div>

    </div>

    <?php include 'layouts/footer.php'; ?>

</body>
</html>
