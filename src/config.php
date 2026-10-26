<?php
declare(strict_types=1);

// 接続情報は環境変数から読む（パスワードをソースコードに書かない）
return [
    'db_host' => getenv('DB_HOST') ?: '127.0.0.1',
    'db_port' => getenv('DB_PORT') ?: '3306',
    'db_name' => getenv('DB_NAME') ?: 'news_cms',
    'db_user' => getenv('DB_USER') ?: 'news_cms',
    'db_pass' => getenv('DB_PASS') ?: '',
];
