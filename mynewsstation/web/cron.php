<?php
require_once('config.php');
require_once('functions.php');
// TwitterAPI用ライブラリ(Composerでインストールすること)
require_once 'vendor/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;


if ($_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "不正なアクセスです。";
    exit;
} else {

    $pdo = connectDb();

    // 処理設定ユーザーを取得
    $stmt = $pdo->prepare("SELECT * FROM user WHERE process_hour != 99");
    $stmt->execute();

    // 抽出したユーザでループ
    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // ログメッセージをクリア
        $log_message = NULL;

		// 現在の時間を取得（Y-m-d H:00:00形式）
		$now_date = date('Y-m-d H:00:00');

		if ($user["last_process_time"]) {
			// 次に処理すべき時間（前回の処理時刻から指定時間経過した時刻）
			$send_date = date('Y-m-d H:00:00', strtotime("+".$user["process_hour"]." hour", strtotime($user["last_process_time"])));
		}

		// 未処理の場合（初回処理）、または処理すべき時刻だった場合は処理を行う
		if ($user["last_process_time"] == '0000-00-00 00:00:00' || ($now_date === $send_date)) {

            // API系のパラメータを取得
            $twitter_consumer_key = $user['twitter_consumer_key'];
            $twitter_consumer_secret = $user['twitter_consumer_secret'];
            $twitter_access_token = $user['twitter_access_token'];
            $twitter_access_token_secret = $user['twitter_access_token_secret'];

            $news = getNewsbyUserId($user['id'], $pdo);

            $newsapi_query = $news['query'];
            $newsapi_from = $news['date_from'];
            $newsapi_to = $news['date_to'];
            $newsapi_language = $news['language'];
            $newsapi_sort_by = $news['sort_by'];
            $newsapi_key = $news['news_api'];

            if (!$newsapi_query || !$newsapi_key || !$twitter_consumer_key || !$twitter_consumer_secret || !$twitter_access_token || !$twitter_access_token_secret) {
                // いずれかの設定が不足していたら処理を行わない
                $log_message .= '未設定の項目があります。';
            } else {

                // -----------------------------------------
                // NewsAPI 連動処理
                // -----------------------------------------

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

                $articles = $arr['articles'];

                // 50件から1件をランダムに抽出
                $rand_no = array_rand($articles);
                $target_article = $articles[$rand_no];
                // 抽出した記事のタイトルを取得
                $article_title = '定期配信ニュース：'.$target_article['title'];
                // 抽出した記事のURLの取得
                $article_url = $target_article['url'];
                // 抽出した記事の説明文を取得
                $article_description = $target_article['description'];
                // 抽出した記事の投稿日を取得
                $article_published_at = substr($target_article['publishedAt'],0,10);
                // 抽出した記事の掲載元を取得
                $article_source_name = $target_article['source']['name'];
                // 抽出した記事の関連画像を取得
                $article_image = $target_article['urlToImage'];

                print_r($target_article);
                // -----------------------------------------
                // Twitter API 連動処理
                // -----------------------------------------

                // Twitterにログインするための事前準備（OAuthオブジェクト生成）
                $connection = new TwitterOAuth($twitter_consumer_key, $twitter_consumer_secret, $twitter_access_token, $twitter_access_token_secret);

                // ツイート
                $tweet_message = $article_title.": ".$article_url;
                $res = $connection->post("statuses/update", array("status"=>$tweet_message));

                $body = $connection->getLastBody();
                if ($connection->getLastHttpCode() != 200) {
                    // エラーメッセージ群を取り出しループして画面に表示
                    $errors = $body->errors;
                    foreach ($errors as $error) {
                        $log_message .= 'Twitter投稿エラー：'.$error->message;
                    }
                }

                if (!$log_message) {
                    $log_message = '投稿は正常に終了しました。';
                }


            }

			// 対象ユーザーのlast_process_timeを更新
			$sql = "UPDATE user SET last_process_time = :last_process_time where id = :id";
			$stmt2 = $pdo->prepare($sql);
			$params = array(":last_process_time" => date('Y-m-d H:00:00'), ":id" => $user['id']);
			$stmt2->execute($params);

            // ログを保存
            saveCronLog($user['id'], $log_message, $pdo);
        }
    }
    unset($pdo);
    exit;
}
?>
