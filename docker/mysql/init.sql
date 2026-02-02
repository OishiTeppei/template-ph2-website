-- データベース作成＆選択
CREATE DATABASE IF NOT EXISTS posse;

USE posse;

-- 既存のテーブル削除
DROP TABLE IF EXISTS choices;

DROP TABLE IF EXISTS questions;

-- 問題テーブルの作成（正規化済み）
CREATE TABLE questions (
    -- 各行を一意に識別する自動採番の整数IDを作成し、説明文をつけた
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '問題ID',
    -- content という名前の列に、長めのテキスト（問題文）を必ず入力するようにし、
    -- 管理画面上では“問題内容”という説明を表示する。
    content TEXT NOT NULL COMMENT '問題内容',
    -- 各問題に対応する画像のファイル名を保存するための文字列カラム
    image VARCHAR(255) COMMENT '画像ファイル名',
    supplement TEXT COMMENT '補足情報',
    -- ここから下はどのサイトにも入れる
-- created_at は、データが新しく登録された瞬間に、
-- MySQLが自動で「現在の日時」を記録するカラム
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '作成日',
-- updated_at は、データを変更した瞬間に、MySQLが自動でその時刻を更新してくれるカラム
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日'
);

-- 選択肢テーブルの作成（外部キーで質問と連携）
CREATE TABLE choices (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '選択肢ID',
    question_id INT NOT NULL COMMENT '紐づく問題ID',
    name VARCHAR(255) NOT NULL COMMENT '選択肢の文言',
    -- この選択肢が正解なら1、不正解なら0。空はダメ。指定がなければ自動で0にする
    valid TINYINT(1) NOT NULL DEFAULT 0 COMMENT '正解=1 / 不正解=0',
    -- ここから下はどのサイトにも入れる
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '作成日',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日'
);

INSERT INTO
    questions (content, image, supplement)
VALUES
    (
        '日本のIT人材が2030年には最大どれくらい不足すると言われているでしょうか？',
        'img-quiz01.png',
        '経済産業省 2019年3月 ─ IT 人材需給に関する調査'
    ),
    (
        '既存業界のビジネスと、先進的なテクノロジーを結びつけて生まれた、新しいビジネスのことをなんと言うでしょう？',
        'img-quiz02.png',
        NULL
    ),
    ('IoTとは何の略でしょう？', 'img-quiz03.png', NULL),
    (
        'サイバー空間とフィジカル空間を高度に融合させたシステムにより、経済発展と社会的課題の解決を両立する、人間中心の社会のことをなんと言うでしょう？',
        'img-quiz04.png',
        'Society5.0 - 科学技術政策 - 内閣府'
    ),
    (
        'イギリスのコンピューター科学者であるギャビン・ウッド氏が提唱した、ブロックチェーン技術を活用した「次世代分散型インターネット」のことをなんと言うでしょう？',
        'img-quiz05.png',
        NULL
    ),
    (
        '先進テクノロジー活用企業と出遅れた企業の収益性の差はどれくらいあると言われているでしょうか？',
        'img-quiz06.png',
        'Accenture Technology Vision 2021'
    );

INSERT INTO
    choices (question_id, name, valid)
VALUES
    -- Q1
    (1, '約28万人', 0),
    (1, '約79万人', 1),
    (1, '約183万人', 0),
    -- Q2
    (2, 'INTECH', 0),
    (2, 'BIZZTECH', 0),
    (2, 'X-TECH', 1),
    -- Q3
    (3, 'Internet of Things', 1),
    (3, 'Integrate into Technology', 0),
    (3, 'Information on Tool', 0),
    -- Q4
    (4, 'Society 5.0', 1),
    (4, 'CyPhy', 0),
    (4, 'SDGs', 0),
    -- Q5
    (5, 'Web3.0', 1),
    (5, 'NFT', 0),
    (5, 'メタバース', 0),
    -- Q6
    (6, '約2倍', 0),
    (6, '約5倍', 1),
    (6, '約11倍', 0);

-- ララベルでマイグレーション
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
    name VARCHAR(255) NOT NULL COMMENT '名前',
    email VARCHAR(255) NOT NULL UNIQUE COMMENT 'メールアドレス',
    password VARCHAR(255) NOT NULL COMMENT 'パスワード'
);
-- ララベルでシーダーの処理をする
INSERT INTO
    users (name, email, password)
VALUES
    (
        '大石哲平',
        'teppei0108@docomo.ne.jp',
        '$2y$10$CyGQ1EIbjdJPmb7EuVLV8Of85q72bwGGjwzxxNciotJ/Ywg1ED8oS'
    );



-- $2y$10$CyGQ1EIbjdJPmb7EuVLV8Of85q72bwGGjwzxxNciotJ/Ywg1ED8oS


-- UPDATE
--     users
-- SET
--     name = '大石哲平',
--     password = '$2y$10$CyGQ1EIbjdJPmb7EuVLV8Of85q72bwGGjwzxxNciotJ/Ywg1ED8oS'
-- WHERE
--     email = 'teppei0108@docomo.ne.jp';




-- /email が 'teppei0108@docomo.ne.jp' のユーザーを探し、
-- -- password を上書き（更新）します。/
UPDATE users
SET password = '$2y$10$CyGQ1EIbjdJPmb7EuVLV8Of85q72bwGGjwzxxNciotJ/Ywg1ED8oS'
WHERE email = 'teppei0108@docomo.ne.jp';
