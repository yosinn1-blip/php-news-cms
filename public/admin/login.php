<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/functions.php';
require_once __DIR__ . '/../../src/view.php';

if (current_admin() !== null) {
    redirect('/admin/');
}

$error = null;
$loginId = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $loginId = trim((string) ($_POST['login_id'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (login($loginId, $password)) {
        redirect('/admin/');
    }
    // IDとパスワードのどちらが違うかは教えない
    $error = 'ログインIDまたはパスワードが違います。';
}

render_header('ログイン', true);
?>
<h1>ログイン</h1>

<?php if ($error !== null): ?>
    <p class="message message-error"><?= h($error) ?></p>
<?php endif; ?>

<form method="post" action="/admin/login.php" class="form narrow">
    <?= csrf_field() ?>
    <label>ログインID
        <input type="text" name="login_id" value="<?= h($loginId) ?>" required autocomplete="username">
    </label>
    <label>パスワード
        <input type="password" name="password" required autocomplete="current-password">
    </label>
    <button type="submit" class="button">ログイン</button>
</form>
<?php
render_footer(true);
