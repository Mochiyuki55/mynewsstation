<?php

$api_key = 'bd0b461716c342719a1b02c255be0381'; // 自分のNewsAPIキー

// エンドポイント
$type = 'everything';

// パラメータの設定
$q = 'python'; // 記事のタイトルと本文で検索するキーワードまたはフレーズ。
$domains =''; // 検索を制限するドメインのコンマ区切りの文字列（例：bbc.co.uk、techcrunch.com、engadget.com）。
$exclude_domains = ''; // 結果から削除するドメインのコンマ区切りの文字列（例：bbc.co.uk、techcrunch.com、engadget.com）。
$from = ''; // 許可されている最も古い記事 の日付とオプションの時刻。例:2022-03-16T12:23:21
$to = ''; // 許可されている最新の記事 の日付とオプションの時刻。例:2022-03-16
$language = 'jp'; // 見出しを取得したい言語の2文字のISO-639-1コード。可能なオプション：jp,ar,de,en,es,frなど。
$sort_by = ''; // 記事を並べ替える順序。publishedAt:発行順(デフォルト)、popularity:人気順、relevancy:関連度順
$page_size = ''; // 返される記事の数。
$page = ''; // ページ目を表示

// ------------------------------------------------
$api_url = 'https://newsapi.org/v2/'.$type.'?';

// エラーチェック
if(!$api_key){
    echo 'apiKeyを記入してください。';
}

//検索条件設定(共通)
if($q){$api_url .= 'q='.$q;}
if($sources){$api_url .= '&sources='.$sources;}
if($domains){$api_url .= '&domains='.$domains;}
if($exclude_domains){$api_url .= '&exclude_domains='.$exclude_domains;}
if($from){$api_url .= '&from='.$from;}
if($to){$api_url .= '&to='.$to;}
if($language){$api_url .= '&language='.$language;}
if($sort_by){$api_url .= '&sortBy='.$sort_by;}
if($page_size){$api_url .= '&page_size='.$page_size;}
if($page){$api_url .= '&page='.$page;}

// api_keyを設定
// $api_url .= '&apiKey='.$api_key;

echo $api_url;
echo '<hr>';

// レスポンスを取得し、デコードして配列に格納
$response = file_get_contents($api_url);
$arr = json_decode($response, true);

if(empty($arr)){echo '検索エラーです。設定を確認してください。';}
// ------------------------------------------------
echo 'ステータス：'.$arr['status'].'<br>';
echo 'エラーコード：'.$arr['code'].'<br>';
echo '該当件数：'.$arr['totalResults'].'件';
echo '<hr>';

foreach ($arr['articles'] as $article) {
    // echo print_r($article);
    echo 'title: '.$article['title'];
    echo '<br>';
    echo 'description: '.$article['description'];
    echo '<br>';
    echo 'url: '.$article['url'];
    echo '<br>';
    echo 'urlToImage: '.$article['urlToImage'];
    echo '<br>';
    echo 'publishedAt: '.$article['publishedAt'];

    echo '<hr>';

}
// レスポンスを表示する
// echo var_dump($arr['articles']);
?>
