<?php
$dsn = 'mysql:host=db;dbname=ph2drill;charset=utf8';
$user = 'root';
$password = 'root';

try {
    $dbh = new PDO($dsn, $user, $password);
    // echo 'Connection to DB';
} catch (PDOException $e) {
    // echo 'Connection failed: ' . $e->getMessage();
    exit;
}

// // questionsテーブル
// $sql = 'SELECT * FROM questions';
// $stmt = $dbh->query($sql);
// $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// echo "<h2>questionsテーブルの内容</h2>";
// echo '<pre>';
// var_dump($questions);
// echo '</pre>';

// // choicesテーブル
// $sql = 'SELECT * FROM choices';
// $stmt = $dbh->query($sql);
// $choices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// echo "<h2>choicesテーブルの内容</h2>";
// echo '<pre>';
// var_dump($choices);
// echo '</pre>';
