<?php
session_start();
// =====================
// 未ログインならサインイン画面にリダイレクト
// =====================
// var_dump($_SESSION); // ← 追加して確認
if (!isset($_SESSION['id'])) {
  header('Location: auth/signin.php');
  exit;
}
// =====================
// 1) データベースから問題を取ってくる
// =====================
require_once dirname(__DIR__) . '/dbconnect.php';  // DB接続情報を読み込み

$sql = "SELECT id, content FROM questions ORDER BY id ASC";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>問題一覧 | POSSE 管理者</title>

  <!-- サイト共通のCSS（必要なら残す） -->
  <link rel="stylesheet" href="../assets/styles/common.css" />

  <style>
    /* =========================================
       画面全体の基本設定（フォント・背景など）
    ========================================== */
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      color: #333;
      background: #F6EFE3;
      /* うすいベージュ */
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
        "Hiragino Kaku Gothic ProN", "Noto Sans JP", Meiryo, sans-serif;
    }

    /* =========================================
       ヘッダー（上の横長の帯）
    ========================================== */
    .header {
      background: #48B8B9;
      /* ティール色 */
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 60px;
      /* ヘッダーの高さ（サイドの位置計算に使う） */
      padding: 10px 16px;
      /* 上下10px、左右16pxの余白 */
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

    /* =========================================
       レイアウト（サイドバー固定＋メイン）
    ========================================== */
    /* 画面中央に最大1200pxで配置 */
    .container {
      max-width: 1200px;
      margin: 24px auto;
      /* 上下24px、左右は自動で中央寄せ */
      padding: 0 16px;
      /* 端にベタっとつかないよう左右に余白 */
    }

    /* サイドバー（左に固定） */
    .sidebar {
      position: fixed;
      /* スクロールしても固定 */
      top: 60px;
      /* ヘッダーぶん下げる（ヘッダーの高さと同じ） */
      left: 16px;
      /* 画面の左端から16px内側（見た目の余白） */
      width: 200px;
      /* サイドの幅 */
      height: calc(100vh - 60px);
      /* 画面の高さ - ヘッダーの高さ */
      overflow-y: auto;
      /* メニューが多いときスクロール */
      background: #F3E5CF;
      /* 淡いベージュ */
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
      /* 青みのリンク色 */
      text-decoration: none;
    }

    .sidebar a:hover {
      text-decoration: underline;
    }

    /* メイン（本文）はサイドバーの右側にずらす */
    .main {
      /* サイド幅200px + サイドと本文のすき間24px = 224px */
      margin-left: 224px;
    }

    /* タイトル */
    .main h1 {
      font-size: 28px;
      margin: 8px 0 16px;
    }

    /* =========================================
       テーブル（問題一覧）
    ========================================== */
    .q-table {
      width: 100%;
      border-collapse: collapse;
      /* セルのすき間をなくす */
      background: #fff;
      border-bottom: 3px solid #000;
      /* 下に太線（見本の雰囲気） */
    }

    .q-table th,
    .q-table td {
      padding: 10px 8px;
      text-align: left;
      border-bottom: 1px solid #000;
      /* 各行の区切り線 */
      vertical-align: top;
    }

    .q-table thead th {
      background: #F2F2F2;
      /* 見出しの薄いグレー */
      font-weight: 600;
    }

    .q-table .col-id {
      width: 72px;
    }

    /* ID列の幅固定 */
    .q-table .col-actions {
      width: 72px;
      text-align: right;
      white-space: nowrap;
    }

    /* テーブル内のリンク色（問題文リンク） */
    .q-table a {
      color: #007BFF;
      text-decoration: none;
    }

    .q-table a:hover {
      text-decoration: underline;
    }

    /* 削除リンクの見た目 */
    .delete-link {
      color: #666;
      text-decoration: none;
      font-size: 14px;
    }

    .delete-link:hover {
      text-decoration: underline;
    }

    /* =========================================
       スマホ対応（幅が狭いときは固定やめる）
    ========================================== */
    @media (max-width: 767px) {
      .sidebar {
        position: static;
        /* 固定を解除して通常の流れへ */
        width: auto;
        height: auto;
        overflow: visible;
        margin-bottom: 16px;
        /* メインとの間に余白 */
        left: 0;
        /* 念のためリセット */
        top: 0;
      }

      .main {
        margin-left: 0;
        /* もうサイドは固定じゃないので左マージン不要 */
      }

      .container {
        margin: 16px auto;
        /* 余白を少し詰める */
      }
    }
  </style>
</head>

<body>
  <!-- 2) ヘッダー -->
  <header class="header">
    <div class="header__logo">
      <img src="../assets/img/logo.svg" alt="POSSE">
    </div>
    <div class="header__right">
      <a href="auth/signout.php">ログアウト</a>
    </div>
  </header>

  <div class="container">
    <!-- 3) サイドバー（固定メニュー） -->
    <aside class="sidebar">
      <nav>
        <ul>
          <li><a href="/admin/user_invite.php">ユーザー招待</a></li>
          <li><a href="/admin/index.php">問題一覧</a></li>
          <li><a href="/admin/questions/create.php">問題作成</a></li>
        </ul>
      </nav>
    </aside>

    <!-- 4) メイン（問題一覧テーブル） -->
    <main class="main">
      <h1>問題一覧</h1>

      <table class="q-table">
        <thead>
          <tr>
            <th class="col-id">ID</th>
            <th>問題</th>
            <th class="col-actions"><!-- 操作列（見出しは空でOK） --></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($questions as $q): ?>
            <tr>
              <!-- htmlspecialchars でXSS対策（表示の安全化） -->
              <td><?= htmlspecialchars($q['id'], ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <a href="/admin/questions/edit.php?id=<?= htmlspecialchars($q['id'], ENT_QUOTES, 'UTF-8') ?>">
                  <?= htmlspecialchars($q['content'], ENT_QUOTES, 'UTF-8') ?>
                </a>
              </td>
              <td class="col-actions">
                <!-- 削除を押したときに確認ダイアログを出す -->
                <!-- <a class="delete-link"
                   href="/admin/questions/delete.php?id=<?= htmlspecialchars($q['id'], ENT_QUOTES, 'UTF-8') ?>"
                   onclick="return confirm('本当に削除しますか？');">
                  削除
                </a> -->
                <form method="POST" onsubmit="return confirm('本当に削除しますか？');">
                  <input type="hidden" name="delete_id" value="<?= htmlspecialchars($q['id'], ENT_QUOTES, 'UTF-8') ?>">
                  <button class="delete-link" type="submit">削除</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </main>
  </div>

  <?php
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int) $_POST['delete_id'];
    try {
      $dbh->beginTransaction();
      $sqlC = 'DELETE FROM choices WHERE question_id = :qid';
      $stmtC = $dbh->prepare($sqlC);
      $stmtC->bindValue(':qid', $deleteId, PDO::PARAM_INT);
      $stmtC->execute();

      $sqlQ = 'DELETE FROM questions WHERE id = :id';
      $stmtQ = $dbh->prepare($sqlQ);
      $stmtQ->bindValue(':id', $deleteId, PDO::PARAM_INT);
      $stmtQ->execute();

      $dbh->commit();
      header('Location: /admin/index.php');
      exit;
    } catch (Exception $e) {
      if ($dbh->inTransaction())
        $dbh->rollBack();
      $errorMsg = '削除に失敗しました: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
  }
  if (!isset($_SESSION['id'])) {
    // ログインしてなければサインイン画面にリダイレクト
    header('Location: ../auth/signin.php');
    exit;
  }

  ?>
</body>

</html>