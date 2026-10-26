<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/functions.php';
require_once __DIR__ . '/../../src/view.php';

require_login();

$newsList = find_all_news();
$message = flash();

render_header('お知らせの一覧', true);
?>
<div class="page-head">
    <h1>お知らせの一覧</h1>
    <a class="button" href="/admin/edit.php">＋ 新しく書く</a>
</div>

<?php if ($message !== null): ?>
    <p class="message message-success"><?= h($message) ?></p>
<?php endif; ?>

<?php if ($newsList === []): ?>
    <p class="empty">まだお知らせがありません。</p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>タイトル</th>
                <th>状態</th>
                <th>公開日</th>
                <th>更新日時</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($newsList as $news): ?>
            <tr>
                <td><?= h($news['title']) ?></td>
                <td>
                    <?php if ((int) $news['is_published'] === 1): ?>
                        <span class="badge badge-public">公開</span>
                    <?php else: ?>
                        <span class="badge badge-draft">下書き</span>
                    <?php endif; ?>
                </td>
                <td><?= h(format_date($news['published_at'])) ?></td>
                <td><?= h(date('Y/m/d H:i', strtotime($news['updated_at']))) ?></td>
                <td class="actions">
                    <a class="button button-small" href="/admin/edit.php?id=<?= (int) $news['id'] ?>">編集</a>
                    <form method="post" action="/admin/delete.php" class="js-delete-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $news['id'] ?>">
                        <button type="submit" class="button button-small button-danger"
                                data-title="<?= h($news['title']) ?>">削除</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<p class="note"><a href="/" target="_blank" rel="noopener">公開ページを見る ↗</a></p>
<?php
render_footer(true);
