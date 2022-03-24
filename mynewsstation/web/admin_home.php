<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = 'ダッシュボード';

session_start();

$pdo = connectDB();

if (!isset($_SESSION['ADMIN'])) {
    header('Location:'.SITE_URL.'admin_login.php');
    exit;
}

$admin = $_SESSION['ADMIN'];

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $notice = $admin['notice'];

    // CSRF対策
    setToken();
} else {

    // CSRF対策
    checkToken();

    $notice = $_POST['notice'];

    // 入力された内容でデータベースを更新
    $sql = 'UPDATE admin SET notice = :notice, updated_at = now() WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $params = array(':notice' => $notice, ':id' => $admin['id']);
    $stmt->execute($params);

    // セッションの内容を更新
    $admin['notice'] = $notice;
    $_SESSION['ADMIN'] = $admin;

    $complete_msg = "お知らせ内容を更新しました。";

}
unset($pdo);

?>

<?php include 'layouts/head.php'; ?>

<body id="main" class="bg-dark text-light">

    <?php include 'layouts/admin_header.php'; ?>

    <div class="container">
        <h1>運営側からのお知らせ編集</h1>

        <?php if ($complete_msg): ?>
            <div class="text-success">
                <?php echo $complete_msg; ?>
            </div>
        <?php endif; ?>

        <div class="panel panel-default">
            <div class="panel-body">
                <form method="POST">
                    <div class="form-group">
                        <label>お知らせ</label>
                        <textarea class="form-control" rows="3" name="notice"><?php echo h($notice); ?></textarea>
                        <span class="help-block"><?php echo h($err['notice']); ?></span>
                    </div>

                    <div class="form-group">
                        <input type="submit" class="btn btn-success btn-block" value="お知らせ変更" >
                    </div>
                    <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
                </form>
            </div>
        </div>

    </div>

    <?php include 'layouts/footer.php'; ?>

</body>
</html>
