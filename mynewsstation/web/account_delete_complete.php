<?php
require_once('config.php');
require_once('functions.php');

// レイアウト関連の変数
$page_title = '退会完了';

?>

<?php include 'layouts/head.php'; ?>
  <body class="bg-dark text-center">

    <div class="container pt-5 ">
      <h1 class="mt-5 font-weight-bold text-white">退会完了</h1>
      <div class="row mt-2">

          <div class="col pt-3 bg-light rounded">
            <p>退会処理が完了しました。</p>
          </div>

      </div>
      <div class="my-5">
        <a href="index.php">TOPへ</a>
      </div>

    </div><!-- container -->

    <?php include 'layouts/footer.php'; ?>
    
  </body>
</html>
