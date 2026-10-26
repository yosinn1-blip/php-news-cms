<?php
declare(strict_types=1);

/*
 * 入力チェック（validate_news）と表示用の関数のテスト。データベースは使わない。
 * 実行: php tests/validate_test.php
 */

require_once __DIR__ . '/../src/functions.php';

$failures = 0;
$count = 0;

function check(string $name, bool $ok): void
{
    global $failures, $count;
    $count++;
    if ($ok) {
        echo "OK  {$name}\n";
    } else {
        $failures++;
        echo "NG  {$name}\n";
    }
}

$today = '2026-10-26';

// 正しい入力
[$data, $errors] = validate_news(['title' => ' お知らせ ', 'body' => '本文', 'is_published' => '1', 'published_at' => ''], $today);
check('正しい入力はエラーなし', $errors === []);
check('前後の空白を取る', $data['title'] === 'お知らせ');
check('公開で日付が空なら今日になる', $data['published_at'] === $today);
check('公開フラグは1', $data['is_published'] === 1);

// 下書き
[$data, $errors] = validate_news(['title' => 'a', 'body' => 'b', 'published_at' => ''], $today);
check('下書きで日付が空ならnullのまま', $data['published_at'] === null && $data['is_published'] === 0);

// 必須チェック
[, $errors] = validate_news(['title' => '   ', 'body' => ''], $today);
check('タイトルが空ならエラー', isset($errors['title']));
check('本文が空ならエラー', isset($errors['body']));

// 文字数（日本語も1文字として数える）
[, $errors] = validate_news(['title' => str_repeat('あ', 100), 'body' => 'b'], $today);
check('タイトル100文字はOK', !isset($errors['title']));
[, $errors] = validate_news(['title' => str_repeat('あ', 101), 'body' => 'b'], $today);
check('タイトル101文字はエラー', isset($errors['title']));

// 日付
[, $errors] = validate_news(['title' => 'a', 'body' => 'b', 'published_at' => '2026-02-30'], $today);
check('存在しない日付はエラー', isset($errors['published_at']));
[, $errors] = validate_news(['title' => 'a', 'body' => 'b', 'published_at' => '2026/10/26'], $today);
check('形が違う日付はエラー', isset($errors['published_at']));
check('正しい日付はOK', is_valid_date('2026-10-26'));

// エスケープ
check('h() は <script> を無害な文字にする', h('<script>alert("x")</script>') === '&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;');
check("h() はシングルクォートも変換する", h("'") === '&#039;');

echo "\n{$count}件中 " . ($count - $failures) . "件OK\n";
exit($failures === 0 ? 0 : 1);
