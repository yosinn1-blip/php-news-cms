<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/functions.php';
require_once __DIR__ . '/../../src/view.php';

require_login();

// id があれば「編集」、なければ「新規作成」
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    [$data, $errors] = validate_news($_POST);

    if ($errors === []) {
        if ($id === null) {
            create_news($data);
            flash('お知らせを作成しました。');
        } else {
            if (find_news($id) === null) {
                http_response_code(404);
                exit('お知らせが見つかりません。');
            }
            update_news($id, $data);
            flash('お知らせを更新しました。');
        }
        redirect('/admin/');
    }
    // エラーがあれば、入力した内容を残したままフォームを出し直す
    $news = $data;
} elseif ($id !== null) {
    $news = find_news($id);
    if ($news === null) {
        http_response_code(404);
        exit('お知らせが見つかりません。');
    }
} else {
    $news = ['title' => '', 'body' => '', 'is_published' => 0, 'published_at' => null];
}

$pageTitle = $id === null ? 'お知らせを書く' : 'お知らせを編集';
$action = '/admin/edit.php' . ($id === null ? '' : '?id=' . $id);

render_header($pageTitle, true);
?>
<h1><?= h($pageTitle) ?></h1>

<?php if ($errors !== []): ?>
    <div class="message message-error">
        <p>入力内容を確認してください。</p>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= h($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= h($action) ?>" class="form">
    <?= csrf_field() ?>

    <label>タイトル <span class="counter js-counter" data-max="<?= NEWS_TITLE_MAX ?>"></span>
        <input type="text" name="title" class="js-count-target" maxlength="<?= NEWS_TITLE_MAX ?>"
               value="<?= h($news['title']) ?>" required>
    </label>

    <label>本文
        <textarea name="body" rows="8" maxlength="<?= NEWS_BODY_MAX ?>" required><?= h($news['body']) ?></textarea>
    </label>

    <label class="checkbox">
        <input type="checkbox" name="is_published" value="1" class="js-publish-toggle"
            <?= (int) $news['is_published'] === 1 ? 'checked' : '' ?>>
        公開する
    </label>

    <label class="js-published-at">公開日（空なら今日）
        <input type="date" name="published_at" value="<?= h($news['published_at'] ?? '') ?>">
    </label>

    <div class="form-buttons">
        <button type="submit" class="button">保存する</button>
        <a href="/admin/" class="button button-plain">一覧へ戻る</a>
    </div>
</form>
<?php
render_footer(true);
