<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = 'ユーザ一覧';

session_start();

$pdo = connectDB();

if (!isset($_SESSION['ADMIN'])) {
    header('Location:'.SITE_URL.'admin_login.php');
    exit;
}

$admin = $_SESSION['ADMIN'];

// ユーザー一覧を取得
$sql = 'SELECT * FROM user';
$stmt = $pdo->query($sql);
$user_list = $stmt->fetchAll();
?>

<?php include 'layouts/head.php'; ?>

<body id="main" class="bg-dark text-light">

    <?php include 'layouts/admin_header.php'; ?>

    <div class="container">
        <h1><?php echo h($page_title); ?></h1>

		<table class="table table-bordered table-striped bg-light">
			<thead>
				<tr>
					<th>ID</th>
					<th>名前</th>
					<th>編集</th>
				</tr>
			</thead>
			<?php foreach ($user_list as $user): ?>
				<tr>
					<td><?php echo h($user['id']);?></td>
					<td><?php echo h($user['user_name']);?></td>
					<td><a href="./admin_user_edit.php?id=<?php echo h($user['id']); ?>">編集</a></td>
				</tr>
			<?php endforeach;?>
		</table>

    </div>

    <?php include 'layouts/footer.php'; ?>

</body>
</html>
