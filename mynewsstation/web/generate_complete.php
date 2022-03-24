<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = 'パスワードリセット完了';
?>

<?php include 'layouts/head.php'; ?>
  <body class="bg-dark text-center">

    <div class="container pt-5 ">
      <h1 class="mt-5 font-weight-bold text-white">パスワードリセット完了</h1>
      <div class="row mt-2">

          <div class="col pt-3 bg-light rounded">
            <p>パスワードのリセットが完了しました。</p>
            <p>メールをご確認ください。</p>
          </div>

      </div>
      <a href="<?php echo SITE_URL ?>">TOPへ</a>

    </div><!-- container -->

    <?php include 'layouts/footer.php'; ?>

  </body>
</html>
