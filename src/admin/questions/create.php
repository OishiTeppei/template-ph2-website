<?php
// Composerでインストールしたライブラリを読み込む。
require "../../vendor/autoload.php";
// Verot\Upload 名前空間にある Upload クラスを簡単に Upload として使えるようにする。
use Verot\Upload\Upload;
require_once dirname(__DIR__, 2) . '/dbconnect.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $content = trim($_POST['content'] ?? '');
  $choice1 = trim($_POST['choice1'] ?? '');
  $choice2 = trim($_POST['choice2'] ?? '');
  $choice3 = trim($_POST['choice3'] ?? '');
  $correct = intval($_POST['correct'] ?? 1); // 1,2,3
  $supplement = trim($_POST['supplement'] ?? '');

  if ($content === '')
    $errors[] = '問題文を入力してください。';
  if ($choice1 === '' || $choice2 === '' || $choice3 === '')
    $errors[] = '選択肢を3つすべて入力してください。';
  if (!in_array($correct, [1, 2, 3], true))
    $errors[] = '正解の選択肢を選んでください。';






  $uploadFile = $_FILES['image'] ?? null;
  if ($uploadFile && is_uploaded_file($uploadFile['tmp_name'])) {
    // 画像サイズ制限（5MB）
    if ($uploadFile['size'] > 5 * 1024 * 1024) {
      $errors[] = '画像のサイズは5MB以下にしてください。';
    }

    // 拡張子チェック
    $ext = strtolower(pathinfo($uploadFile['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true)) {
      $errors[] = '画像は jpg / jpeg / png / gif のいずれかにしてください。';
    }

    // MIMEチェック（本当に画像か）
    $check = getimagesize($uploadFile['tmp_name']);
    if ($check === false) {
      $errors[] = 'アップロードされたファイルは画像ではありません。';
    }

    // エラーがなければ保存処理へ
    if (!$errors) {
      $uploadDir = dirname(__DIR__, 2) . '/assets/img/quiz/';
      if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
      }

      // 一意なファイル名生成
      $savedFileName = 'q_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

      // 実際に保存
      if (!move_uploaded_file($uploadFile['tmp_name'], $uploadDir . $savedFileName)) {
        $errors[] = '画像の保存に失敗しました。';
      }
    }
  } else {
    $errors[] = '画像のアップロードに失敗しました。';
  }



  if (!$errors) {
    try {
      // $file = $_FILES['image'];
      // $lang = 'ja_JP';

      // // アップロードされたファイルを渡す
      // $handle = new Upload($file, $lang);

      // if ($handle->uploaded) {
      //   // アップロードディレクトリを指定して保存
      //   $handle->process('../../assets/img/quiz/');
      //   if ($handle->processed) {
      //     // アップロード成功
      //     $image_name = $handle->file_dst_name;
      //   } else {
      //     // アップロード処理失敗
      //     throw new Exception($handle->error);
      //   }
      // } else {
      //   // アップロード失敗
      //   throw new Exception($handle->error);
      // }
      $dbh->beginTransaction();
      $sqlQ = 'INSERT INTO questions (content, image, supplement) VALUES (:content, :image, :supplement)';
      $stmtQ = $dbh->prepare($sqlQ);
      $stmtQ->bindValue(':content', $content, PDO::PARAM_STR);
      $stmtQ->bindValue(':image', $savedFileName, $savedFileName ? PDO::PARAM_STR : PDO::PARAM_NULL);
      $stmtQ->bindValue(':supplement', $supplement !== '' ? $supplement : null, $supplement !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
      $stmtQ->execute();

      $questionId = (int) $dbh->lastInsertId();

      $choices = [$choice1, $choice2, $choice3];
      $sqlC = 'INSERT INTO choices (question_id, name, valid) VALUES (:qid, :name, :valid)';
      $stmtC = $dbh->prepare($sqlC);
      foreach ($choices as $i => $name) {
        $stmtC->bindValue(':qid', $questionId, PDO::PARAM_INT);
        $stmtC->bindValue(':name', $name, PDO::PARAM_STR);
        $stmtC->bindValue(':valid', ($i + 1) === $correct ? 1 : 0, PDO::PARAM_INT);
        $stmtC->execute();
      }

      $dbh->commit();
      header('Location: /admin/index.php');
      exit;
    } catch (Exception $e) {
      if ($dbh->inTransaction())
        $dbh->rollBack();
      $errors[] = '保存に失敗しました：' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
  }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>問題作成 | POSSE 管理者</title>
  <link rel="stylesheet" href="/assets/styles/common.css">
  <style>
    /* ベースとテーマ */
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      background: #F6EFE3;
      color: #333;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Hiragino Kaku Gothic ProN", "Noto Sans JP", Meiryo, sans-serif;
    }

    /* ヘッダー */
    .header {
      height: 60px;
      background: #48B8B9;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 16px;
    }

    .header__logo img {
      height: 32px;
      display: block;
    }

    .header__right a {
      color: #fff;
      text-decoration: none;
      font-size: 14px;
    }

    .header__right a:hover {
      text-decoration: underline;
    }

    /* レイアウト外枠 */
    .container {
      max-width: 1200px;
      margin: 24px auto;
      padding: 0 16px;
      position: relative;
    }

    /* サイドバー（左端に固定） */
    .sidebar {
      position: fixed;
      top: 60px;
      left: 16px;
      width: 200px;
      height: calc(100vh - 60px);
      overflow: auto;
      background: #F3E5CF;
      border-radius: 8px;
      padding: 16px;
    }

    .sidebar ul {
      list-style: none;
      margin: 0;
      padding: 0;
    }

    .sidebar li {
      margin: 12px 0;
    }

    .sidebar a {
      color: #0A7EA4;
      text-decoration: none;
    }

    .sidebar a:hover {
      text-decoration: underline;
    }

    /* メインカード（フォーム） */
    .main {
      margin-left: 224px;
      /* 200 + 24の感覚 */
      background: #F5E7D7;
      border-radius: 8px;
      padding: 24px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, .06);
    }

    .main h1 {
      margin: 0 0 16px;
      font-size: 28px;
    }

    .field {
      margin: 14px 0;
    }

    .label {
      display: block;
      font-weight: 700;
      margin-bottom: 6px;
    }

    .text {
      width: 100%;
      height: 38px;
      padding: 8px 10px;
      border: 1px solid #cfcfcf;
      border-radius: 4px;
      background: #fff;
      outline: none;
    }

    .text:focus {
      border-color: #64a89a;
      box-shadow: 0 0 0 3px rgba(100, 168, 154, .15);
    }

    .choices {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 10px;
    }

    .radios {
      display: flex;
      gap: 18px;
      align-items: center;
    }

    .note {
      font-size: 12px;
      color: #666;
    }

    .errors {
      background: #ffe9e9;
      color: #a40000;
      padding: 12px;
      border-radius: 6px;
      margin-bottom: 12px;
    }

    .submit {
      margin-top: 18px;
      text-align: center;
    }

    .btn {
      width: 100%;
      height: 44px;
      background: #64a89a;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 15px;
    }

    .btn:hover {
      opacity: .92;
    }

    /* スマホ対応 */
    @media (max-width: 767px) {
      .sidebar {
        position: static;
        width: auto;
        height: auto;
        margin-bottom: 16px;
      }

      .main {
        margin-left: 0;
      }

      .choices {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <header class="header">
    <div class="header__logo"><img src="/assets/img/logo.svg" alt="POSSE"></div>
    <div class="header__right"><a href="./auth/signout.php">ログアウト</a></div>
  </header>

  <div class="container">
    <aside class="sidebar">
      <nav aria-label="管理ナビ">
        <ul>
          <li><a href="/admin/user_invite.php">ユーザー招待</a></li>
          <li><a href="/admin/index.php">問題一覧</a></li>
          <li><a href="/admin/questions/create.php">問題作成</a></li>
        </ul>
      </nav>
    </aside>

    <main class="main">
      <h1>問題作成</h1>

      <?php if (!empty($errors)): ?>
        <div class="errors">
          <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data">
        <div class="field">
          <label class="label">問題文：</label>
          <input class="text" type="text" name="content" placeholder="問題文を入力してください"
            value="<?= htmlspecialchars($_POST['content'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="field">
          <label class="label">選択肢：</label>
          <div class="choices">
            <input class="text" type="text" name="choice1" placeholder="選択肢1を入力してください"
              value="<?= htmlspecialchars($_POST['choice1'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input class="text" type="text" name="choice2" placeholder="選択肢2を入力してください"
              value="<?= htmlspecialchars($_POST['choice2'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input class="text" type="text" name="choice3" placeholder="選択肢3を入力してください"
              value="<?= htmlspecialchars($_POST['choice3'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>
        </div>

        <div class="field">
          <label class="label">正解の選択肢：</label>
          <div class="radios">
            <label><input type="radio" name="correct" value="1" <?= (($_POST['correct'] ?? '1') === '1') ? 'checked' : ''; ?>>
              選択肢1</label>
            <label><input type="radio" name="correct" value="2" <?= (($_POST['correct'] ?? '') === '2') ? 'checked' : ''; ?>>
              選択肢2</label>
            <label><input type="radio" name="correct" value="3" <?= (($_POST['correct'] ?? '') === '3') ? 'checked' : ''; ?>>
              選択肢3</label>
          </div>
        </div>

        <div class="field">
          <label class="label">問題の画像 <span class="note">(任意 / jpg・png・gif)</span></label>
          <input type="file" name="image" accept="image/*">
        </div>

        <div class="field">
          <label class="label">補足：</label>
          <input class="text" type="text" name="supplement" placeholder="補足を入力してください"
            value="<?= htmlspecialchars($_POST['supplement'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="submit"><button class="btn" type="submit">作成</button></div>
      </form>
    </main>
  </div>
</body>

</html>