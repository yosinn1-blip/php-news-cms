-- お知らせ管理（ミニCMS）のテーブル定義
-- 文字コードは日本語と絵文字を扱える utf8mb4
SET NAMES utf8mb4;  -- 読み込むときの文字コードを指定（これが無いと日本語が化ける）

CREATE TABLE IF NOT EXISTS news (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title        VARCHAR(100) NOT NULL,
    body         TEXT         NOT NULL,
    is_published TINYINT(1)   NOT NULL DEFAULT 0,  -- 1 = 公開 / 0 = 下書き
    published_at DATE         NULL,                -- 公開日（この日になるまで公開ページに出さない）
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_public (is_published, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS admin_users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    login_id      VARCHAR(50)  NOT NULL,
    password_hash VARCHAR(255) NOT NULL,  -- パスワードはそのまま保存せず、password_hash() の結果だけを保存
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_login_id (login_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
