<?php
session_start();

// DB接続情報（DBに接続するためのパスワード）
$host = "db";
$dbname = "ph2drill";
$dbUser = "root";
$dbPassword = "root";

// ログイン処理
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $inputPassword = $_POST['password'] ?? ''; // ユーザーがフォームで入力したパスワード

    // // バリデーション
    // if (trim($email) === '' || trim($inputPassword) === '') {
    //     $error = 'メールアドレスとパスワードは必須です。';
    // } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //     $error = 'メールアドレスの形式が正しくありません。';
    // } else {



    //    if (trim($email) === '') {
//     echo 'Eメールアドレスは必須項目です。';
//   } else {
//     echo 'Eメールアドレスが入力されています。';
//   }
// }
// if (trim($password) === ""){
//     echo"パスワードは必須項目です。";
// } else{
//     echo'パスワードが入力されています。'
// }
// }
// )





    // バリデーション
    if (trim($email) === '' || trim($inputPassword) === '') {
        $error = 'メールアドレスとパスワードは必須です。';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'メールアドレスの形式が正しくありません。';
    } else {
        try {
            // データベース接続（ユーザーのパスワードではなく、DB接続用のパスワードを使用）
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbUser, $dbPassword);

            // 入力されたメールアドレスに一致するユーザーを取得
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindValue(":email", $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // パスワード照合（セキュリティ的には password_verify を使うべき）
            // if ($user && $inputPassword == $user['password']) {
            //     $_SESSION['id'] = $user['id'];
            //     header('Location: /admin/index.php');
            //     exit;
            // } else {
            //     $error = "メールアドレスまたはパスワードが正しくありません。";
            // }



            if ($user && password_verify($inputPassword, $user['password'])) {
                $_SESSION['id'] = $user['id'];
                header('Location: /admin/index.php');
                exit;
            } else {
                $error = "メールアドレスまたはパスワードが正しくありません。";
            }



        } catch (PDOException $e) {
            exit('DBエラー: ' . $e->getMessage());
        }
    }
}
var_dump("hello"); exit;
?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <title>ログイン</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css" />
    <style>
        body {
            margin: 0;
            font-family: sans-serif;
            background-color: #fcecd8;
        }

        .header {
            background-color: #4ecdc4;
            color: black;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* .logo {
            font-size: 24px;
            font-weight: bold;
        } */

        .logout {
            font-size: 14px;
        }

        .login-container {
            max-width: 400px;
            margin: 80px auto;
            padding: 20px;
            border-radius: 8px;
        }

        .login-title {
            font-size: 28px;
            margin-bottom: 20px;
        }

        .login-form {
            display: flex;
            flex-direction: column;
        }

        .login-form label {
            margin: 10px 0 5px;
            font-size: 14px;
        }

        .login-form input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .login-form button {
            margin-top: 20px;
            padding: 10px;
            background-color: #4ecdc4;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .login-form button:hover {
            background-color: #34a6a1;
        }
    </style>

</head>

<body>
    <header class="header">
        <img src="../assets/img/logo.svg" alt="posse-logo" class="logo">
        <div class="logout">
            <?php if (isset($_SESSION['id'])): ?>
                <a href="./signout.php">ログアウト</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="login-container">
        <h1 class="login-title">ログイン</h1>
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form class="login-form" action="signin.php" method="POST">
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required />
            </div>
            <div>
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" required />
            </div>
            <!-- <button type="submit">ログイン</button> -->
            <input type="submit" value="ログイン">
        </form>
    </main>
</body>

</html>