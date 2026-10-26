<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/news.php';

/**
 * 画面に出す文字をエスケープする（XSS対策）。
 * ユーザーが入力した文字は、必ずこれを通してから表示する。
 */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_start([
        'cookie_httponly' => true,   // JavaScript からクッキーを読めないようにする
        'cookie_samesite' => 'Lax',  // 他サイトからの送信でクッキーを付けない
        'use_strict_mode' => true,
    ]);
}

/**
 * CSRF対策のトークン。フォームに埋め込み、送信時に一致するかを確かめる。
 */
function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function verify_csrf(): void
{
    start_session();
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
        http_response_code(400);
        exit('不正なリクエストです（CSRFトークンが一致しません）。');
    }
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('POST で送信してください。');
    }
}

function login(string $loginId, string $password): bool
{
    $stmt = db()->prepare('SELECT id, login_id, password_hash FROM admin_users WHERE login_id = :login_id');
    $stmt->execute([':login_id' => $loginId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    start_session();
    session_regenerate_id(true); // ログインの前後でセッションIDを変える（セッション固定攻撃の対策）
    $_SESSION['admin_id'] = (int) $user['id'];
    $_SESSION['admin_login_id'] = $user['login_id'];
    return true;
}

function logout(): void
{
    start_session();
    $_SESSION = [];
    session_destroy();
}

function current_admin(): ?string
{
    start_session();
    return $_SESSION['admin_login_id'] ?? null;
}

/**
 * 管理画面の各ページの先頭で呼ぶ。ログインしていなければログイン画面へ戻す。
 */
function require_login(): void
{
    if (current_admin() === null) {
        redirect('/admin/login.php');
    }
}

function redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

/**
 * 1回だけ表示するメッセージ（「保存しました」など）。
 */
function flash(?string $message = null): ?string
{
    start_session();
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }
    $stored = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $stored;
}
