<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/functions.php';

require_login();
require_post();   // 削除はリンク（GET）ではなく、フォーム（POST）からだけ受け付ける
verify_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id && find_news($id) !== null) {
    delete_news($id);
    flash('お知らせを削除しました。');
}
redirect('/admin/');
