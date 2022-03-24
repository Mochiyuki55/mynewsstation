<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = 'パスワードをお忘れの方';

session_start();
$pdo = connectDb();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  setToken(); // CSRF 対策

} else {
  // checkToken(); // CSRF 対策

  $user_email = $_POST['user_email'];         // メールアドレス

  $err = array();

  // [メールアドレス]未入力チェック
  if ($user_email == '') {
      $err['user_email'] = 'メールアドレスを入力して下さい。';
  } else {
      // [メールアドレス]形式チェック
      if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
          $err['user_email'] = 'メールアドレスが不正です。';
      } else {
          // [メールアドレス]存在チェック
          if (!checkEmail($user_email, $pdo)) {
              $err['user_email'] = 'このメールアドレスは登録されていません。';
          }
      }
  }


  if (empty($err)) {

    // ランダムの文字列生成
    $str_rand = makeRandStr(8);

    // データベースのパスワードを更新
    $sql = "UPDATE user SET user_password = :user_password, updated_at = now() where user_email = :user_email";
    $stmt = $pdo->prepare($sql);
    $params = array(":user_password" => password_hash($str_rand, PASSWORD_DEFAULT), ":user_email" => $user_email);
    $stmt->execute($params);

    $flag = $stmt->execute();

    // メール送信
    mb_language("japanese");
    mb_internal_encoding("UTF-8");

    $mail_title = '【コンテンツメーカー】パスワード再設定メール';
    $mail_body = 'パスワードリセット要求があったため、パスワードを一時的に以下のものに変更しました。'.PHP_EOL;
    $mail_body.= 'パスワード：'.$str_rand.PHP_EOL.PHP_EOL;
    $mail_body.= 'セキュリティ向上のため、ログイン後にご自身でパスワードを変更して下さい。'.PHP_EOL;
    $mail_body.= SITE_URL;

    mb_send_mail($user_email, $mail_title, $mail_body);

    unset($pdo);
    header('Location: '.SITE_URL.'generate_complete.php');
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
            <form class="form" method="POST">
              <div class="form-group">
                <input type="email" class="form-control" name="user_email" value="" placeholder="メールアドレス" required>
                <span class="text-danger"><?php echo h($err['user_email']); ?></span>
              </div>

              <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

              <div class="form-group">
                <input type="submit" value="パスワードをリセットする" class="btn btn-primary btn-block">
              </div>
            </form>
          </div>

      </div>

    </div><!-- container -->

    <?php include 'layouts/footer.php'; ?>

  </body>
</html>
