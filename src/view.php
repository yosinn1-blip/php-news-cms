<?php
declare(strict_types=1);

/** 全ページ共通の上部 */
function render_header(string $pageTitle, bool $admin = false): void
{
    $siteName = $admin ? 'お知らせ管理' : 'お知らせ';
    ?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle) ?> | <?= h($siteName) ?></title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="<?= $admin ? 'is-admin' : 'is-public' ?>">
<header class="site-header">
    <div class="container header-inner">
        <a class="site-name" href="<?= $admin ? '/admin/' : '/' ?>"><?= h($siteName) ?></a>
        <?php if ($admin && current_admin() !== null): ?>
            <form method="post" action="/admin/logout.php" class="logout-form">
                <?= csrf_field() ?>
                <span class="login-user"><?= h(current_admin()) ?> さん</span>
                <button type="submit" class="button button-plain">ログアウト</button>
            </form>
        <?php endif; ?>
    </div>
</header>
<main class="container">
    <?php
}

/** 全ページ共通の下部 */
function render_footer(bool $admin = false): void
{
    ?>
</main>
<footer class="site-footer">
    <div class="container">PHP 8 ＋ MySQL のお知らせ管理（学習用の作品）</div>
</footer>
<?php if ($admin): ?>
<script src="/assets/vendor/jquery-3.7.1.min.js"></script>
<script src="/assets/admin.js"></script>
<?php endif; ?>
</body>
</html>
    <?php
}

function format_date(?string $date): string
{
    if ($date === null || $date === '') {
        return '—';
    }
    return date('Y年n月j日', strtotime($date));
}
