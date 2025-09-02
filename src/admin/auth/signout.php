<?php
session_start();
session_unset(); // セッション変数を全て削除
session_destroy(); // セッションを完全に破棄

// トップページにリダイレクト
header('Location: ../index.php');
exit;


?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>トップページ</title>
</head>
<body>
  <h1>ようこそ、POSSEのトップページです</h1>
  <p><a href="/auth/signin.php">ログインはこちら</a></p>
</body>
</html>
