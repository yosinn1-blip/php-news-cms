<?php
declare(strict_types=1);

/*
 * 管理者ユーザーを作るコマンド。
 * 使い方: php bin/create_admin.php ログインID
 * パスワードは画面に出さずに入力する（ソースコードや履歴に残さないため）。
 */

require_once __DIR__ . '/../src/db.php';

if (PHP_SAPI !== 'cli') {
    exit('コマンドラインから実行してください。');
}

$loginId = $argv[1] ?? '';
if ($loginId === '') {
    fwrite(STDERR, "使い方: php bin/create_admin.php ログインID\n");
    exit(1);
}

fwrite(STDERR, 'パスワード（8文字以上）: ');
if (stream_isatty(STDIN)) {
    shell_exec('stty -echo');
}
$password = trim((string) fgets(STDIN));
if (stream_isatty(STDIN)) {
    shell_exec('stty echo');
    fwrite(STDERR, "\n");
}

if (mb_strlen($password) < 8) {
    fwrite(STDERR, "パスワードは8文字以上にしてください。\n");
    exit(1);
}

$stmt = db()->prepare('INSERT INTO admin_users (login_id, password_hash) VALUES (:login_id, :password_hash)');
$stmt->execute([
    ':login_id'      => $loginId,
    ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
]);

fwrite(STDOUT, "管理者「{$loginId}」を作成しました。\n");
