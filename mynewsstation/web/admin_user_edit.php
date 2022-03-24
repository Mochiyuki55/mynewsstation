<?php
require_once('config.php');
require_once('functions.php');
require_once('lib/password.php');

// レイアウト関連の変数
$page_title = 'ユーザー情報修正';

session_start();

$pdo = connectDB();

if (!isset($_SESSION['ADMIN'])) {
    header('Location:'.SITE_URL.'admin_login.php');
    exit;
}

$admin = $_SESSION['ADMIN'];

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // 対象ユーザーのデータを取得
    $sql = 'SELECT * FROM user WHERE id = :id LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':id' => $id));
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

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
    $user_name = $_POST['user_name'];
    $user_email = $_POST['user_email'];
    $user_password = $_POST['user_password'];

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
    // [氏名]未入力、文字数チェック
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

    if (empty($err)) {
        $sql = 'UPDATE user SET user_name = :user_name, user_email = :user_email, ';
        if ($user_password) {
            // パスワードは入力された場合のみ更新する
            $sql.= 'user_password = :user_password, ';
        }
        $sql.= 'twitter_consumer_key = :twitter_consumer_key, twitter_consumer_secret = :twitter_consumer_secret, twitter_access_token = :twitter_access_token, twitter_access_token_secret = :twitter_access_token_secret,
        process_hour = :process_hour, updated_at = now()
        where id = :id';
        $stmt = $pdo->prepare($sql);

        $params = array(":user_name" => $user_name, ":user_email" => $user_email,
            ":twitter_consumer_key" => $twitter_consumer_key, ":twitter_consumer_secret" => $twitter_consumer_secret, ":twitter_access_token" => $twitter_access_token, ":twitter_access_token_secret" => $twitter_access_token_secret,
            ":process_hour" => $process_hour, ":id" => $user['id']);

        if ($user_password) {
            $params[':user_password'] = password_hash($user_password, PASSWORD_DEFAULT);
        }
        $params[':id'] = $id;
        $stmt->execute($params);

        $complete_msg = '変更しました。';
    }
}
?>

<?php include 'layouts/head.php'; ?>

<body id="main" class="bg-dark text-light">

    <?php include 'layouts/admin_header.php'; ?>

    <div class="container">
        <h1><?php echo h($page_title); ?></h1>

        <?php if ($complete_msg): ?>
            <div class="text-success">
                <?php echo h($complete_msg); ?>
            </div>
        <?php endif; ?>

        <div class="panel panel-default">
            <div class="panel-body">
                <form method="POST">

                    <!-- ユーザー情報 -->
                    <div class="row mt-2 border rounded text-light">
                        <div class="col pt-3">

                            <div class="form-group">
                                <label for="">ユーザーネーム</label>
                                <input type="text" class="form-control" name="user_name" value="<?php echo $user_name; ?>">
                                <span class="text-danger"><?php echo h($err['user_name']); ?></span>
                            </div>

                            <div class="form-group">
                                <label for="">メールアドレス</label>
                                <input type="email" class="form-control" name="user_email" value="<?php echo $user_email; ?>">
                                <span class="text-danger"><?php echo h($err['user_email']); ?></span>
                            </div>

                            <div class="form-group">
                                <label for="">パスワード</label>
                                <input type="password" class="form-control" name="user_password" value="" placeholder="未入力の場合は変更されません。">
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
                                    <input type="datatime" class="form-control" name="newsapi_from" value="<?php echo h($newsapi_from); ?>" placeholder="例：<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="">検索対象最新日</label>
                                    <input type="datetime" class="form-control" name="newsapi_to" value="<?php echo h($newsapi_to); ?>" placeholder="例：<?php echo date('Y-m-d'); ?>">
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
                                    <input type="text" class="form-control" name="twitter_consumer_key" value="<?php echo h($twitter_consumer_key); ?>" placeholder="">
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="">Consumer Secret</label>
                                    <input type="text" class="form-control" name="twitter_consumer_secret" value="<?php echo h($twitter_consumer_secret); ?>" placeholder="">
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="">Access Token</label>
                                    <input type="text" class="form-control" name="twitter_access_token" value="<?php echo h($twitter_access_token); ?>" placeholder="">
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="">Access Token Secret</label>
                                    <input type="text" class="form-control" name="twitter_access_token_secret" value="<?php echo h($twitter_access_token_secret); ?>" placeholder="">
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

                    <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

                    <div class="form-group mt-3">
                        <input type="submit" value="修正" class="btn btn-success btn-block">
                    </div>

                    <a class="btn btn-secondary" href="./admin_user_list.php">戻る</a>　
                    <a href="javascript:void(0);" class="btn btn-secondary" onclick="var ok=confirm('退会しても宜しいですか?'); if (ok) location.href='admin_user_delete.php?id=<?php echo h($id); ?>'; return false;">退会</a>


                </form>
            </div>
        </div>



    </div>

    <?php include 'layouts/footer.php'; ?>

</body>
</html>
