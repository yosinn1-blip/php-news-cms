<?php
declare(strict_types=1);

/*
 * お知らせ（news テーブル）の読み書き。
 * SQL に値を直接つなげず、必ずプレースホルダ（:id など）で渡す（SQLインジェクション対策）。
 */

const NEWS_TITLE_MAX = 100;
const NEWS_BODY_MAX = 5000;

/** 公開ページ用: 公開中で、公開日が今日以前のものだけ */
function find_published_news(): array
{
    $sql = 'SELECT id, title, body, published_at
              FROM news
             WHERE is_published = 1 AND published_at <= CURDATE()
             ORDER BY published_at DESC, id DESC';
    return db()->query($sql)->fetchAll();
}

function find_published_news_by_id(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, title, body, published_at
           FROM news
          WHERE id = :id AND is_published = 1 AND published_at <= CURDATE()'
    );
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

/** 管理画面用: 下書きも含めて全部 */
function find_all_news(): array
{
    $sql = 'SELECT id, title, is_published, published_at, updated_at
              FROM news
             ORDER BY updated_at DESC, id DESC';
    return db()->query($sql)->fetchAll();
}

function find_news(int $id): ?array
{
    $stmt = db()->prepare('SELECT id, title, body, is_published, published_at FROM news WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function create_news(array $data): int
{
    $stmt = db()->prepare(
        'INSERT INTO news (title, body, is_published, published_at)
         VALUES (:title, :body, :is_published, :published_at)'
    );
    $stmt->execute([
        ':title'        => $data['title'],
        ':body'         => $data['body'],
        ':is_published' => $data['is_published'],
        ':published_at' => $data['published_at'],
    ]);
    return (int) db()->lastInsertId();
}

function update_news(int $id, array $data): void
{
    $stmt = db()->prepare(
        'UPDATE news
            SET title = :title, body = :body, is_published = :is_published, published_at = :published_at
          WHERE id = :id'
    );
    $stmt->execute([
        ':id'           => $id,
        ':title'        => $data['title'],
        ':body'         => $data['body'],
        ':is_published' => $data['is_published'],
        ':published_at' => $data['published_at'],
    ]);
}

function delete_news(int $id): void
{
    $stmt = db()->prepare('DELETE FROM news WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

/**
 * フォームの入力をチェックして、保存できる形に整える。
 * 戻り値: [整えたデータ, エラーメッセージの配列]
 */
function validate_news(array $input, ?string $today = null): array
{
    $today ??= date('Y-m-d');
    $errors = [];

    $title = trim((string) ($input['title'] ?? ''));
    $body = trim((string) ($input['body'] ?? ''));
    $isPublished = !empty($input['is_published']) ? 1 : 0;
    $publishedAt = trim((string) ($input['published_at'] ?? ''));

    if ($title === '') {
        $errors['title'] = 'タイトルを入力してください。';
    } elseif (mb_strlen($title) > NEWS_TITLE_MAX) {
        $errors['title'] = 'タイトルは' . NEWS_TITLE_MAX . '文字以内にしてください。';
    }

    if ($body === '') {
        $errors['body'] = '本文を入力してください。';
    } elseif (mb_strlen($body) > NEWS_BODY_MAX) {
        $errors['body'] = '本文は' . NEWS_BODY_MAX . '文字以内にしてください。';
    }

    if ($publishedAt === '') {
        // 公開するのに日付が空なら今日にする。下書きなら空のまま。
        $publishedAt = $isPublished ? $today : null;
    } elseif (!is_valid_date($publishedAt)) {
        $errors['published_at'] = '公開日は「2026-10-26」の形で入力してください。';
    }

    $data = [
        'title'        => $title,
        'body'         => $body,
        'is_published' => $isPublished,
        'published_at' => $publishedAt,
    ];
    return [$data, $errors];
}

function is_valid_date(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}
