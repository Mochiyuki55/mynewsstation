<?php
require_once('config.php');
require_once('functions.php');
require_once('lib/password.php');

// レイアウト関連の変数
$page_title = '新規登録画面';


session_start();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  setToken(); // CSRF 対策

} else {
  checkToken(); // CSRF 対策

  // 入力データを変数に格納する
  $user_name = $_POST['user_name'];           // ユーザーネーム
  $user_email = $_POST['user_email'];         // メールアドレス
  $user_password = $_POST['user_password'];   // パスワード

  // DBに接続する
  $pdo = connectDb();

  // エラーチェック
  $err = array();

  // [氏名]未入力チェック
  if ($user_name == '') {
      $err['user_name'] = '氏名を入力して下さい。';
  }

  if (strlen(mb_convert_encoding($user_name, 'SJIS', 'UTF-8')) > 30) {
      $err['user_name'] = '氏名は30バイト以内で入力して下さい。';
  }

  // [パスワード]未入力チェック
  if ($user_password == '') {
      $err['user_password'] = 'パスワードを入力して下さい。';
  }

  if (strlen(mb_convert_encoding($user_password, 'SJIS', 'UTF-8')) > 30) {
      $err['user_password'] = 'パスワードは30バイト以内で入力して下さい。';
  }

  // [メールアドレス]未入力チェック
  if ($user_email == '') {
      $err['user_email'] = 'メールアドレスを入力して下さい。';
  } else {
      // [メールアドレス]形式チェック
      if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
          $err['user_email'] = 'メールアドレスが不正です。';
      } else {
          // [メールアドレス]存在チェック
          if (checkEmail($user_email, $pdo)) {
              $err['user_email'] = 'このメールアドレスは既に登録されています。';
          }
      }
  }
  // エラーがない場合、登録処理を行い、セッションに保存する
  if(empty($err)){
    // データベース（userテーブル）に新規登録する。
    $stmt = $pdo->prepare("INSERT INTO user (user_name, user_email, user_password, process_hour, created_at, updated_at)
        VALUES (:user_name, :user_email, :user_password, 99,  now(),  now())");
    $params = array(":user_name" => $user_name, ":user_email" => $user_email, ":user_password" => password_hash($user_password, PASSWORD_DEFAULT));
    $stmt->execute($params);

    // セッションIDを再生成
    session_regenerate_id(true);

    // 自動ログイン（$user_idが$pdo->lastInsertId()と対応する。INSERTした後に使用可能）
    $user = getUserbyUserId($pdo->lastInsertId(), $pdo);
    $_SESSION['USER'] = $user;

    // データベース（newsテーブル）に新規登録する。
    $stmt = $pdo->prepare("INSERT INTO news (user_id, created_at, updated_at)
        VALUES (:user_id, now(), now())");
    $params = array(":user_id" => $user['id']);
    $stmt->execute($params);

    // 管理者にメール
    mb_language("japanese");
    mb_internal_encoding("UTF-8");

    $mail_title = '【コンテンツメーカー】新規ユーザ登録がありました。';
    $mail_body = '氏名：'.$user['user_name'].PHP_EOL;
    $mail_body.= 'メールアドレス：'.$user['user_email'];

    mb_send_mail(ADMIN_EMAIL, $mail_title, $mail_body);


    // 画面遷移前にDB解放する
    unset($pdo);
    header('Location: '.SITE_URL.'signup_complete.php');
    exit;
  }
  // DBを解放する
  unset($pdo);
}
?>

<?php include 'layouts/head.php'; ?>

  <body class="bg-dark text-center">

    <div class="container pt-5">
      <h1 class="mt-5 font-weight-bold text-white"><?php echo h($page_title); ?></h1>
      <div class="row mt-2">

          <div class="col pt-3  rounded">

            <form class="form" method="POST" action="">

              <div class="form-group">
                <input type="text" class="form-control" name="user_name" value="" placeholder="ユーザーネーム" required>
                <span class="text-danger"><?php echo h($err['user_name']); ?></span>
              </div>

              <div class="form-group">
                <input type="email" class="form-control" name="user_email" value="" placeholder="メールアドレス" required>
                <span class="text-danger"><?php echo h($err['user_email']); ?></span>
              </div>

              <div class="form-group">
                <input type="password" class="form-control" name="user_password" value="" placeholder="パスワード" required>
                <span class="text-danger"><?php echo h($err['user_password']); ?></span>
              </div>


              <div class="form-group">
                <input type="submit" value="登録" class="btn btn-primary btn-block">
              </div>

              <!-- CSRF対策：index.phpがPOSTされて遷移してきた場合、次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
              <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

            </form>

          </div>
      </div>

    </div><!-- container -->

    <?php include 'layouts/footer.php'; ?>

  </body>
</html>
