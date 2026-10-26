<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/functions.php';
require_once __DIR__ . '/../src/view.php';

$newsList = find_published_news();

render_header('お知らせ一覧');
?>
<h1>お知らせ</h1>

<?php if ($newsList === []): ?>
    <p class="empty">現在お知らせはありません。</p>
<?php else: ?>
    <ul class="news-list">
        <?php foreach ($newsList as $news): ?>
            <li class="news-item">
                <time datetime="<?= h($news['published_at']) ?>"><?= h(format_date($news['published_at'])) ?></time>
                <a href="/news.php?id=<?= (int) $news['id'] ?>"><?= h($news['title']) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php
render_footer();
