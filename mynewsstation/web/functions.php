<?php
require_once('config.php');

// データベースに接続する
function connectDb() {
  $host = HOST_NAME; //データベースサーバ名
  $user = DATABASE_USER_NAME; //データベースユーザー名
  $pass = DATABASE_PASSWORD; //パスワード
  $db = DATABASE_NAME; //データベース名
  $param= 'mysql:dbname='.$db.';host='.$host;

  // 例外処理は”起きることが期待されない問題”で、多くの場合、プログラムの実行を停止しても構わない場合に使う
  try{
    // 例外処理：以下の処理でエラーが発生したら
    $pdo = new PDO('mysql:host='.$host.';dbname='.$db, $user, $pass);
    $pdo->query('SET NAMES utf8;');
    return $pdo;

  } catch (PDOException $e){
    // 例外処理：エラー内容をエコーして処理を終了
    echo $e->getMessage();
    exit;
  }

}

function h($original_str) {
    return htmlspecialchars($original_str, ENT_QUOTES, "UTF-8");
}

// トークンを発行する処理
function setToken() {
    $token = sha1(uniqid(mt_rand(), true));
    $_SESSION['sstoken'] = $token;
}

// トークンをチェックする処理
function checkToken() {
    if (empty($_SESSION['sstoken']) || ($_SESSION['sstoken'] != $_POST['token'])) {
        echo '<html><head><meta charset="utf-8"></head><body>不正なアクセスです。</body></html>';
        exit;
    }
}

// メールアドレスとパスワードからuserを検索する
function getUser($user_email, $user_password, $pdo) {
    $sql = "SELECT * FROM user WHERE user_email = :user_email AND user_password = :user_password LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":user_email" => $user_email, ":user_password" => $user_password));
    $user = $stmt->fetch();
    return $user ? $user : false;
}

// メールアドレスからuserを検索する
function getUserByEmail($user_email, $pdo) {
    $sql = "SELECT * FROM user WHERE user_email = :user_email LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":user_email" => $user_email));
    $user = $stmt->fetch();
    return $user ? $user : false;
}

// メールアドレスの存在チェック
function checkEmail($user_email, $pdo) {
    $sql = "SELECT * FROM user WHERE user_email = :user_email LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":user_email" => $user_email));
    $user = $stmt->fetch();
    return $user ? true : false;
}

// ユーザIDからuserを検索する
function getUserbyUserId($user_id, $pdo) {
    $sql = "SELECT * FROM user WHERE id = :user_id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":user_id" => $user_id));
    $user = $stmt->fetch();

    return $user ? $user : false;
}

// ユーザIDからnewsを検索する
function getNewsbyUserId($user_id, $pdo) {
    $sql = "SELECT * FROM news WHERE user_id = :user_id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":user_id" => $user_id));
    $news = $stmt->fetch();

    return $news ? $news : false;
}

// ランダム文字列生成 (英数字)
function makeRandStr($length) {
    $str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
    $r_str = null;
    for ($i = 0; $i < $length; $i++) {
        $r_str .= $str[rand(0, count($str))];
    }
    return $r_str;
}

// 配列からプルダウンメニューを生成する
function arrayToSelect($inputName, $srcArray, $selectedIndex = "") {

    $temphtml = '<select class="form-control" name="'.$inputName.'">'."\n";

    foreach ($srcArray as $key => $val) {
        if ($selectedIndex == $key) {
            $selectedText = ' selected="selected"';
        } else {
            $selectedText = '';
        }
        $temphtml .= '<option value="'.$key.'"'.$selectedText.'>'.$val.'</option>'."\n";
    }

    $temphtml .= '</select>'."\n";
    return $temphtml;
}

// ログテーブルに保存する
function saveCronLog($user_id, $message, $pdo) {
    $sql = "INSERT INTO cron_log (user_id, message, created_at, updated_at) VALUES (:user_id, :message, now(), now())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":user_id" => $user_id, ":message" => $message));
}
?>
