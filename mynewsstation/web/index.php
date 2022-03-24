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
$page_title = 'HOME';
$pdo = connectDb();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  setToken(); // CSRF 対策

  // DBからNewsAPIの設定値を取得(なぜかセッションから取得できなかった。テーブルが違うから？)
  $news = getNewsbyUserId($user['id'], $pdo);

  $newsapi_query = $news['query'];
  $newsapi_from = $news['date_from'];
  $newsapi_to = $news['date_to'];
  $newsapi_language = $news['language'];
  $newsapi_sort_by = $news['sort_by'];
  $newsapi_key = $news['news_api'];

} else {
  checkToken(); // CSRF 対策

  // 入力データを変数に格納する
  $newsapi_query = $_POST['newsapi_query'];
  $newsapi_from = $_POST['newsapi_from'];
  $newsapi_to = $_POST['newsapi_to'];
  $newsapi_language = $_POST['newsapi_language'];
  $newsapi_page = $_POST['newsapi_page'];
  $newsapi_sort_by = $_POST['newsapi_sort_by'];
  $newsapi_key = $_POST['newsapi_key'];

  $err = array();

  if ($newsapi_query == '') {
      $err['newsapi_query'] = '検索ワードを入力して下さい。';
  }
  if ($newsapi_key == '') {
      $err['newsapi_key'] = '設定画面でNewsAPIキーを入力して下さい。';
  }

  // エラーがない場合、更新処理を行い、セッションに保存する
  if(empty($err)){

    // NewsAPI処理
    //検索条件設定
    $api_url = 'https://newsapi.org/v2/everything?';
    if($newsapi_query){$api_url .= 'q='.$newsapi_query;}
    if($newsapi_from){$api_url .= '&from='.$newsapi_from;}
    if($newsapi_to){$api_url .= '&to='.$newsapi_to;}
    if($newsapi_language){$api_url .= '&language='.$newsapi_language;}
    if($newsapi_sort_by){$api_url .= '&sortBy='.$newsapi_sort_by;}
    if($newsapi_page){$api_url .= '&page='.$newsapi_page;}
    $api_url .= '&apiKey='.$newsapi_key;

    // レスポンスを取得し、デコードして配列に格納
    $response = file_get_contents($api_url);
    $arr = json_decode($response, true);

    // 実行エラー処理
    if(empty($arr)){
        $err['newsapi_key'] = '検索エラーです。設定を確認してください。';
    }

  }
}
unset($pdo);

?>

<?php include 'layouts/head.php'; ?>
<body class="bg-dark">

    <?php include 'layouts/header.php'; ?>

    <!-- container -->
    <div class="container text-light">
        <h1><?php echo h($page_title); ?></h1>

        <!-- NewsAPI (記入が必須)-->
        <div class="row mt-2 text-center">
            <div class="col-md-12 pt-3 text-left">
                <form class="form" action="" method="post">

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
                        <div class="form-group col-md-3">
                            <label for="">対象言語</label>
                            <?php echo arrayToSelect('newsapi_language',ARRAY_LANGUAGE,$newsapi_language);?>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="">ページ目次</label>
                            <input type="number" class="form-control" name="newsapi_page" value="<?php echo h($newsapi_page); ?>" placeholder="例：1">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="">ソート</label>
                            <?php echo arrayToSelect('newsapi_sort_by',ARRAY_SORT_BY,$newsapi_sort_by);?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12">
                            <span class="text-danger"><?php echo h($err['newsapi_key']); ?></span>
                            <input type="hidden" name="newsapi_key" value="<?php echo h($newsapi_key); ?>" />

                            <!-- CSRF対策：index.phpがPOSTされて遷移してきた場合、次のphpにPOSTする際はトークンを引き継ぐ必要がある-->
                            <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

                            <input type="submit" class="form-control btn btn-success" value="ニュースを検索" >
                        </div>
                    </div>

                </form>
            </div><!-- col -->
        </div><!-- row -->

        <hr>

        <div class="row">
            <p><?php if(!$arr['totalResults']){ echo '該当する記事がありません。';} else { echo '該当件数：'.$arr['totalResults'].'件';}; ?></p>
        </div>
        <!-- 検索結果：記事 -->
        <?php foreach ($arr['articles'] as $article): ?>
        <div class="row rounded bg-light text-dark py-3 mb-3">
            <div class="col-md-8">
                <h5><a href="<?php echo h($article['url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo h($article['title']); ?></a></h5>
                <p>投稿日：<?php echo h(substr($article['publishedAt'],0,10)); ?> 　　掲載元：<?php echo h($article['source']['name']); ?></p>
                <p><?php echo h($article['description']); ?></p>
            </div>
            <div class="col-md-4">
                <img src="<?php echo h($article['urlToImage']); ?>" class="img-fluid img-thumbnail rounded mx-auto d-block" alt="画像はありません">
            </div>
        </div>
        <?php endforeach; ?>

    </div><!-- container -->

    <?php include 'layouts/footer.php'; ?>

  </body>
</html>
