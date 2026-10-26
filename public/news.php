<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/functions.php';
require_once __DIR__ . '/../src/view.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$news = $id ? find_published_news_by_id($id) : null;

if ($news === null) {
    http_response_code(404);
    render_header('見つかりません');
    echo '<h1>お知らせが見つかりません</h1><p><a href="/">一覧へ戻る</a></p>';
    render_footer();
    exit;
}

render_header($news['title']);
?>
<article class="news-detail">
    <time datetime="<?= h($news['published_at']) ?>"><?= h(format_date($news['published_at'])) ?></time>
    <h1><?= h($news['title']) ?></h1>
    <div class="news-body"><?= nl2br(h($news['body'])) ?></div>
</article>
<p><a href="/">← お知らせ一覧へ戻る</a></p>
<?php
render_footer();
