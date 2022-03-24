<?php
error_reporting(E_ALL & ~E_NOTICE);

// プロジェクト名
$project_name = 'mynewsstation';

// ローカル環境
  // define('HOST_NAME','localhost');
  // define('DATABASE_USER_NAME','root');
  // define('DATABASE_PASSWORD','');
  // define('DATABASE_NAME',$project_name);
  // define('SITE_URL', 'http://localhost/develop/'.$project_name.'/web/');

// サーバーが変わったときは以下の設定を変更するだけで良い
  define('HOST_NAME','mysql57.limesnake4.sakura.ne.jp');
  define('DATABASE_USER_NAME','limesnake4');
  define('DATABASE_PASSWORD','Yaguchi88');
  define('DATABASE_NAME','limesnake4_'.$project_name);
  define('SITE_URL', 'https://limesnake4.sakura.ne.jp/'.$project_name.'/web/');


// メールフォーム
define('ADMIN_EMAIL', 'yaguchi1061@gmail.com');

// アプリタイトル
define('TITLE', 'MyNewsStation');

// コピーライト
define('COPY_RIGHT', '&copy; Mochiyuki55');

// Cookieネーム
define('COOKIE_NAME','MYNEWSSTATION');

// 対象言語
define('ARRAY_LANGUAGE',array(
    '' => '全世界',
    'jp' => '日本',
    'ar' => 'アルゼンチン',
    'cn' => '中国',
    'de' => 'ドイツ',
    'en' => 'アメリカ',
    'es' => 'スペイン',
    'fr' => 'フランス',
    'it' => 'イタリア',
    'nl' => 'オランダ',
    'no' => 'ノルウェー',
    'pt' => 'ポルトガル',
    'ru' => 'ロシア',
    'se' => 'スウェーデン',
    'gb' => 'イギリス',

));

// ソート
define('ARRAY_SORT_BY',array(
    'publishedAt' => '投稿順',
    'relevancy' => '関連度順',
    'popularity' => '人気度順',
));

// Twitter投稿設定時間
define('ARRAY_PROCESS_HOUR',array(
  "99" => "しない",
  "1"   => "1時間ごと",
  "2"   => "2時間ごと",
  "3"   => "3時間ごと",
  "4"   => "4時間ごと",
  "5"   => "5時間ごと",
  "6"   => "6時間ごと",
  "7"   => "7時間ごと",
  "8"   => "8時間ごと",
  "9"   => "9時間ごと",
  "10" => "10時間ごと",
  "11" => "11時間ごと",
  "12" => "12時間ごと",
  "13" => "13時間ごと",
  "14" => "14時間ごと",
  "15" => "15時間ごと",
  "16" => "16時間ごと",
  "17" => "17時間ごと",
  "18" => "18時間ごと",
  "19" => "19時間ごと",
  "20" => "20時間ごと",
  "21" => "21時間ごと",
  "22" => "22時間ごと",
  "23" => "23時間ごと",
  "24" => "24時間ごと",
));

?>
