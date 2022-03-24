<?php
require_once('config.php');
require_once('functions.php');
require_once('lib/password.php');

// レイアウト関連の変数
$page_title = 'ログイン画面';

session_start();
$pdo = connectDb();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  // ログイン画面を表示する前にまずCookieがあるかをチェックする。
  if(isset($_COOKIE[COOKIE_NAME])){ // Cookieがある場合
    $auto_login_key = $_COOKIE[COOKIE_NAME];

    $sql = "SELECT * FROM auto_login WHERE c_key = :c_key AND expire >= :expire LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":c_key" => $auto_login_key, ":expire" => date('Y-m-d H:i:s')));
    $row = $stmt->fetch();

  // DBにも存在しており、かつ有効期間内であれば認証OKとみなして自動ログインさせる。
    if ($row){
      // 照合成功、セッションにユーザー情報を入れる(自動ログイン)
      $user = getUserbyUserId($row['user_id'], $pdo);
      // セッションハイジャック対策
      session_regenerate_id(true);
      // 登録したユーザー情報をセッションに保存
      $_SESSION['USER'] = $user;
      unset($pdo);
      header('Location:'.SITE_URL.'index.php');
      exit;
    }
  }

  setToken(); // CSRF 対策

} else {
  checkToken(); // CSRF 対策

  // 入力データを変数に格納する
  $user_email = $_POST['user_email'];         // メールアドレス
  $user_password = $_POST['user_password'];   // パスワード
  $auto_login = $_POST['auto_login'];         // 自動ログイン

  // DBに接続する
  $pdo = connectDb();

  // エラーチェック
  $err = array();

  // [メールアドレス]未入力チェック
  if ($user_email == '') {
      $err['user_email'] = 'メールアドレスを入力して下さい。';
  } else {
      // [メールアドレス]形式チェック
      if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
          $err['user_email'] = 'メールアドレスが不正です。';
      } else {
          // ログイン認証
          $user = getUserByEmail($user_email, $pdo);
          if (!$user || !password_verify($user_password, $user['user_password'])) {
              $err['user_password'] = 'パスワードが正しくありません。';
          }
      }
  }
  // [パスワード]未入力チェック
  if ($user_password == '') {
      $err['user_password'] = 'パスワードを入力して下さい。';
  }

    // もし$err配列に何もエラーメッセージが保存されていなかったら
    if (empty($err)) {
      // セッション変数にログイン状態を書き込む前に、セッションハイジャック対策
      session_regenerate_id(true);

      // ログインに成功したのでセッションにユーザデータを保存する
      $_SESSION['USER'] = $user;

      // 自動ログイン情報を一度クリアする。
      if (isset($_COOKIE['CONTENTSMAKER'])) {
          $auto_login_key = $_COOKIE['CONTENTSMAKER'];

          // Cookie情報をクリア
          setcookie('CONTENTSMAKER', '', time()-86400, COOKIE_PATH);

          // DB情報をクリア
          $sql = "DELETE FROM auto_login WHERE c_key = :c_key";
          $stmt = $pdo->prepare($sql);
          $stmt->execute(array(":c_key" => $auto_login_key));
      }

              // チェックボックスにチェックが入っていた場合
              if ($auto_login) {

                  // 自動ログインキーを生成
                  $auto_login_key = sha1(uniqid(mt_rand(), true));

                  // Cookie登録処理
                  setcookie('CONTENTSMAKER', $auto_login_key, time()+3600*24*365, COOKIE_PATH);
                  // DB登録処理
                  $sql = "INSERT INTO auto_login (user_id, c_key, expire, created_at, updated_at)
                  VALUES (:user_id, :c_key, :expire, now(), now())";
                  $stmt = $pdo->prepare($sql);
                  $params = array(":user_id" => $user['id'], ":c_key" => $auto_login_key, ":expire" => date('Y-m-d H:i:s', time()+3600*24*365));
                  $stmt->execute($params);
              }

              // HOME画面に遷移する。
              unset($pdo);
              header('Location:'.SITE_URL.'./index.php');
              exit;
          }

          unset($pdo);
      }
?>

<?php include 'layouts/head.php'; ?>

  <body class="bg-dark text-center">

    <div class="container pt-5">
      <h1 class="mt-5 font-weight-bold text-white"> <?php echo TITLE; ?> </h1>
      <div class="row mt-2">

          <div class="col-lg-8 bg-dark py-4 border rounded">
            <h2 class="text-light">世界中の記事(News)を取りまとめて</h2>
            <h2 class="text-light">ニュースの中継点(Station)を作りましょう。</h2>
            <a class="btn btn-success btn-lg mt-4" href="signup.php">新規ユーザー登録</a>
          </div>

          <div class="col-lg-4 pt-3 border rounded">
            <form class="form" method="POST" >

              <div class="form-group">
                <input type="email" class="form-control" name="user_email" value="" placeholder="メールアドレス" required>
                <span class="text-danger"><?php echo h($err['user_email']); ?></span>
              </div>

              <div class="form-group">
                <input type="password" class="form-control" name="user_password" value="" placeholder="パスワード" required>
                <span class="text-danger"><?php echo h($err['user_password']); ?></span>
              </div>

              <div class="form-group text-center text-white">
                <label for="auto_login">
                  <input id="auto_login" type="checkbox" name="auto_login"> 次回から自動でログイン
                </label>

               <p><a href="generate.php">パスワードを忘れた場合はこちら</a></p>
              </div>

              <div class="form-group">
                <input type="submit" value="ログイン" class="btn btn-primary btn-block">
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
