<?php
require_once('config.php');
require_once('functions.php');

// 認証処理
session_start();
if (!isset($_SESSION['USER'])) {
    header('Location: '.SITE_URL.'login.php');
    exit;
}
$user = $_SESSION['USER'];

// 画面表示処理
// レイアウト関連の変数
$page_title = '設定画面';
$pdo = connectDb();

// 処理実行ログを取得（最新10件）
$cron_log_list = NULL;
$sql = "SELECT * FROM cron_log WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":user_id" => $user['id']));
if ($stmt) {
    $cron_log_list = $stmt->fetchAll();
}

// お知らせを取得
$notice = NULL;
$sql = "SELECT notice FROM admin LIMIT 1";
$stmt = $pdo->query($sql);
if ($stmt) {
    $admin = $stmt->fetch();
    $notice = $admin['notice'];
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  setToken(); // CSRF 対策

  // セッションから各設定値を取得
  $user_name = $user['user_name'];
  $user_email = $user['user_email'];

  $twitter_consumer_key = $user['twitter_consumer_key'];
  $twitter_consumer_secret = $user['twitter_consumer_secret'];
  $twitter_access_token = $user['twitter_access_token'];
  $twitter_access_token_secret = $user['twitter_access_token_secret'];

  // DBからNewsAPIの設定値を取得(なぜかセッションから取得できなかった。テーブルが違うから？)
  $news = getNewsbyUserId($user['id'], $pdo);

  $newsapi_query = $news['query'];
  $newsapi_from = $news['date_from'];
  $newsapi_to = $news['date_to'];
  $newsapi_language = $news['language'];
  $newsapi_page_size = $news['page_size'];
  $newsapi_sort_by = $news['sort_by'];
  $newsapi_key = $news['news_api'];

  $process_hour = $user['process_hour'];

} else {
  checkToken(); // CSRF 対策

  // 入力データを変数に格納する
  $user_name = $_POST['user_name'];           // ユーザーネーム
  $user_email = $_POST['user_email'];         // メールアドレス
  $user_password = $_POST['user_password'];   // パスワード

  $twitter_consumer_key = $_POST['twitter_consumer_key'];
  $twitter_consumer_secret = $_POST['twitter_consumer_secret'];
  $twitter_access_token = $_POST['twitter_access_token'];
  $twitter_access_token_secret = $_POST['twitter_access_token_secret'];

  $newsapi_query = $_POST['newsapi_query'];
  $newsapi_from = $_POST['newsapi_from'];
  $newsapi_to = $_POST['newsapi_to'];
  $newsapi_language = $_POST['newsapi_language'];
  $newsapi_page_size = $_POST['newsapi_page_size'];
  $newsapi_sort_by = $_POST['newsapi_sort_by'];
  $newsapi_key = $_POST['newsapi_key'];

  $process_hour = $_POST['process_hour'];

  $err = array();
  $complete_msg = "";

  // [氏名]未入力チェック
  if ($user_name == '') {
      $err['user_name'] = '氏名を入力して下さい。';
  } else {
      if (strlen(mb_convert_encoding($user_name, 'SJIS', 'UTF-8'))>30) {
          $err['user_name'] = '氏名は30字以内で入力して下さい。';
      }
  }
  // [氏名]文字数チェック
  if (strlen(mb_convert_encoding($user_name, 'SJIS', 'UTF-8')) > 30) {
      $err['user_name'] = '氏名は30バイト以内で入力して下さい。';
  }
  // [パスワード]未入力チェック
  if ($user_password == '') {
      $err['user_password'] = 'パスワードを入力して下さい。';
  }
  // [パスワード]文字数チェック
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
      }
  }

  if ($newsapi_query == '') {
      $err['newsapi_query'] = 'クエリを入力して下さい。';
  }
  if ($newsapi_key == '') {
      $err['newsapi_key'] = 'NewsAPIキーを入力して下さい。';
  }

  if ($process_hour == '') {
      $err['process_hour'] = '処理時間を指定して下さい。';
  }

  // エラーがない場合、更新処理を行い、セッションに保存する
  if(empty($err)){
    // userテーブルの更新
    $sql = "UPDATE user SET user_name = :user_name, user_email = :user_email, user_password = :user_password,
        twitter_consumer_key = :twitter_consumer_key, twitter_consumer_secret = :twitter_consumer_secret, twitter_access_token = :twitter_access_token, twitter_access_token_secret = :twitter_access_token_secret,
        process_hour = :process_hour, updated_at = now()
        where id = :id";
    $stmt = $pdo->prepare($sql);
    $params = array(":user_name" => $user_name, ":user_email" => $user_email, ":user_password" => password_hash($user_password, PASSWORD_DEFAULT),
        ":twitter_consumer_key" => $twitter_consumer_key, ":twitter_consumer_secret" => $twitter_consumer_secret, ":twitter_access_token" => $twitter_access_token, ":twitter_access_token_secret" => $twitter_access_token_secret,
        ":process_hour" => $process_hour, ":id" => $user['id']);
    $stmt->execute($params);

    // NewsAPIの更新
    $sql = "UPDATE news SET query = :query, date_from = :date_from,
        date_to = :date_to, language = :language, sort_by = :sort_by, news_api = :news_api, updated_at = now()
        where user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $params = array(":query" => $newsapi_query, ":date_from" => $newsapi_from,
        ":date_to" => $newsapi_to, ":language" => $newsapi_language, ":sort_by" => $newsapi_sort_by, ":news_api" => $newsapi_key,
        ":user_id" => $user['id']);
    $stmt->execute($params);
    $check = $stmt->errorInfo();

    // セッション上のユーザデータを更新
    $user['user_name'] = $user_name;
    $user['user_email'] = $user_email;
    $user['user_password'] = $user_password;

    $user['twitter_consumer_key'] = $twitter_consumer_key;
    $user['twitter_consumer_secret'] = $twitter_consumer_secret;
    $user['twitter_access_token'] = $twitter_access_token;
    $user['twitter_access_token_secret'] = $twitter_access_token_secret;

    // $user['newsapi_query'] = $newsapi_query;
    // $user['newsapi_from'] = $newsapi_from;
    // $user['newsapi_to'] = $newsapi_to;
    // $user['newsapi_language'] = $newsapi_language;
    // $user['newsapi_page_size'] = $newsapi_page_size;
    // $user['newsapi_sort_by'] = $newsapi_sort_by;
    // $user['newsapi_key'] = $newsapi_key;

    $user['process_hour'] = $process_hour;

    $_SESSION['USER'] = $user;

    // 完了メッセージ表示
    $complete_msg = "修正が完了しました。";

  }
  // DBを解放する
  unset($pdo);
}

?>

<?php include 'layouts/head.php'; ?>
  <body class="bg-dark text-light">

    <?php include 'layouts/header.php'; ?>

<!-- container -->
    <div class="container pt-5">
        <h1 class="text-light"><?php echo h($page_title); ?></h1>

        <div>
            <label>管理人からのお知らせ</label>
            <textarea class="form-control" rows="2" readonly name="information"><?php echo h($notice); ?></textarea>
        </div>

        <div class="text-dark">
            <label>cron処理の実行ログ（最新10件）</label>
            <?php if ($cron_log_list): ?>
            <ul class="list-group">
                <?php foreach ($cron_log_list as $cron_log): ?>
                <li class="list-group-item">
                    <?php echo h($cron_log['created_at']); ?> <?php echo h($cron_log['message']); ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>


        <?php if ($complete_msg): ?>
            <div class="text-success">
                <?php echo $complete_msg; ?>
            </div>
        <?php endif; ?>

        <form class="form" method="POST" >

            <div class="row mt-2 border rounded text-light">
                <div class="col pt-3">
                    <h2>ユーザー情報設定</h2>

                    <div class="form-group">
                        <label for="">ユーザーネーム</label>
                        <input type="text" class="form-control" name="user_name" value="<?php echo $user['user_name']; ?>">
                        <span class="text-danger"><?php echo h($err['user_name']); ?></span>
                    </div>

                    <div class="form-group">
                        <label for="">メールアドレス</label>
                        <input type="email" class="form-control" name="user_email" value="<?php echo $user['user_email']; ?>">
                        <span class="text-danger"><?php echo h($err['user_email']); ?></span>
                    </div>

                    <div class="form-group">
                        <label for="">パスワード</label>
                        <input type="password" class="form-control" name="user_password" value="">
                        <span class="text-danger"><?php echo h($err['user_password']); ?></span>
                    </div>

                </div>
            </div><!-- row -->

            <!-- NewsAPI (記入が必須)-->
            <div class="row mt-2 text-center">
                <div class="col-md-12 pt-3 border rounded text-left">
                    <h2>NewsAPI設定</h2>

                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="">検索ワード</label>
                            <input type="text" class="form-control" name="newsapi_query" value="<?php echo h($newsapi_query); ?>" placeholder="お好きな検索ワードを入力して下さい。">
                            <span class="text-danger"><?php echo h($err['newsapi_query']); ?></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="">検索対象最古日</label>
                            <input type="datatime" class="form-control" name="newsapi_from" value="<?php echo h(substr($newsapi_from,0,10)); ?>" placeholder="例：<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="">検索対象最新日</label>
                            <input type="datetime" class="form-control" name="newsapi_to" value="<?php echo h(substr($newsapi_to,0,10)); ?>" placeholder="例：<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="">対象言語</label>
                            <?php echo arrayToSelect('newsapi_language',ARRAY_LANGUAGE,$newsapi_language);?>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="">ソート</label>
                            <?php echo arrayToSelect('newsapi_sort_by',ARRAY_SORT_BY,$newsapi_sort_by);?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="">NewsAPIキー</label>
                            <input type="text" class="form-control" name="newsapi_key" value="<?php echo h($newsapi_key); ?>">
                            <span class="text-danger"><?php echo h($err['newsapi_key']); ?></span>
                        </div>
                    </div>

                </div><!-- col -->
            </div><!-- row -->


            <!-- TwitterAPI (未記入でも問題ない)-->
            <div class="row mt-2 text-center">
                <div class="col-md-12 pt-3 border rounded text-left">
                    <h2>Twitter設定</h2>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="">Consumer Key</label>
                            <input type="text" class="form-control" name="twitter_consumer_key" value="<?php echo h($user['twitter_consumer_key']); ?>" placeholder="">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="">Consumer Secret</label>
                            <input type="text" class="form-control" name="twitter_consumer_secret" value="<?php echo h($user['twitter_consumer_secret']); ?>" placeholder="">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="">Access Token</label>
                            <input type="text" class="form-control" name="twitter_access_token" value="<?php echo h($user['twitter_access_token']); ?>" placeholder="">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="">Access Token Secret</label>
                            <input type="text" class="form-control" name="twitter_access_token_secret" value="<?php echo h($user['twitter_access_token_secret']); ?>" placeholder="">
                        </div>
                    </div>

                </div><!-- col -->
            </div><!-- row -->


            <div class="row mt-2">
                <div class="col py-3 border rounded text-light">
                    <h2>処理時間設定</h2>
                    <?php echo arrayToSelect('process_hour',ARRAY_PROCESS_HOUR,$user['process_hour']);?>
                </div>
            </div>

            <!-- CSRF対策：index.phpがPOSTされて遷移してきた場合、次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
            <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

            <div class="form-group mt-3">
                <input type="submit" value="修正" class="btn btn-success btn-block">
            </div>

            <a href="javascript:void(0);" class="btn btn-secondary" onclick="var ok=confirm('退会しても宜しいですか?'); if (ok) location.href='account_delete.php?id=<?php echo h($user['id']); ?>'; return false;">退会</a>

        </form>
    </div><!-- container -->

    <?php include 'layouts/footer.php'; ?>

  </body>
</html>
