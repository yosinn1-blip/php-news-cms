#!/usr/bin/env bash
# 画面を実際に動かすテスト（サーバーを起動した状態で実行する）
# 使い方: BASE_URL=http://127.0.0.1:8000 ADMIN_ID=admin ADMIN_PASS=... bash tests/http_test.sh
set -u

BASE_URL=${BASE_URL:-http://127.0.0.1:8000}
ADMIN_ID=${ADMIN_ID:?ADMIN_ID を指定してください}
ADMIN_PASS=${ADMIN_PASS:?ADMIN_PASS を指定してください}

JAR=$(mktemp)
OUT=$(mktemp)
trap 'rm -f "$JAR" "$OUT"' EXIT
FAIL=0
COUNT=0

check() { # check 名前 条件の終了コード
  COUNT=$((COUNT + 1))
  if [ "$2" -eq 0 ]; then echo "OK  $1"; else echo "NG  $1"; FAIL=$((FAIL + 1)); fi
}
status() { curl -s -o "$OUT" -w '%{http_code}' -b "$JAR" -c "$JAR" "$@"; }
token() { grep -o 'name="csrf_token" value="[^"]*"' "$OUT" | head -1 | sed 's/.*value="//; s/"$//'; }

# 1. 公開ページ
code=$(status "$BASE_URL/"); check "公開ページが開ける (200)" $([ "$code" = 200 ]; echo $?)
grep -q '新しい商品ページを公開しました' "$OUT"; check "公開中のお知らせが出る" $?
! grep -q '冬のキャンペーン' "$OUT"; check "下書きは公開ページに出ない" $?

# 2. ログインしていないと管理画面に入れない
code=$(status "$BASE_URL/admin/"); check "未ログインで管理画面はログインへ移動 (303)" $([ "$code" = 303 ]; echo $?)

# 3. ログイン
status "$BASE_URL/admin/login.php" >/dev/null; T=$(token)
code=$(status -X POST "$BASE_URL/admin/login.php" --data-urlencode "csrf_token=$T" --data-urlencode "login_id=$ADMIN_ID" --data-urlencode "password=wrong-password")
grep -q 'ログインIDまたはパスワードが違います' "$OUT"; check "パスワードが違うとログインできない" $?
T=$(token)
code=$(status -X POST "$BASE_URL/admin/login.php" --data-urlencode "csrf_token=$T" --data-urlencode "login_id=$ADMIN_ID" --data-urlencode "password=$ADMIN_PASS")
check "正しいパスワードでログインできる (303)" $([ "$code" = 303 ]; echo $?)
code=$(status "$BASE_URL/admin/"); check "ログイン後は管理画面が開ける (200)" $([ "$code" = 200 ]; echo $?)
grep -q '冬のキャンペーン' "$OUT"; check "管理画面には下書きも出る" $?

# 4. CSRFトークンなしの送信は断る
code=$(status -X POST "$BASE_URL/admin/edit.php" --data-urlencode "title=x" --data-urlencode "body=y")
check "トークンなしの保存は断る (400)" $([ "$code" = 400 ]; echo $?)

# 5. 新しく作る（タイトルにわざと <script> を入れて、エスケープされるか見る）
status "$BASE_URL/admin/edit.php" >/dev/null; T=$(token)
XSS_TITLE='テスト<script>alert(1)</script>'
code=$(status -X POST "$BASE_URL/admin/edit.php" --data-urlencode "csrf_token=$T" --data-urlencode "title=$XSS_TITLE" \
  --data-urlencode "body=テスト本文" --data-urlencode "is_published=1" --data-urlencode "published_at=")
check "お知らせを作成できる (303)" $([ "$code" = 303 ]; echo $?)
status "$BASE_URL/" >/dev/null
grep -q 'テスト&lt;script&gt;alert(1)&lt;/script&gt;' "$OUT"; check "作ったお知らせが公開ページに出る（<script>はエスケープ済み）" $?
! grep -q '<script>alert(1)</script>' "$OUT"; check "<script> がそのまま出ていない" $?

# 6. 入力エラー
status "$BASE_URL/admin/edit.php" >/dev/null; T=$(token)
code=$(status -X POST "$BASE_URL/admin/edit.php" --data-urlencode "csrf_token=$T" --data-urlencode "title=" --data-urlencode "body=")
grep -q 'タイトルを入力してください' "$OUT"; check "空のタイトルはエラーを表示" $?

# 7. 編集して下書きに戻す → 公開ページから消える
status "$BASE_URL/admin/" >/dev/null
ID=$(grep -A12 'テスト&lt;script&gt;' "$OUT" | grep -o 'edit.php?id=[0-9]*' | head -1 | sed 's/.*=//')
[ -n "$ID" ]; check "作ったお知らせが管理画面の一覧にある" $?
status "$BASE_URL/admin/edit.php?id=$ID" >/dev/null; T=$(token)
code=$(status -X POST "$BASE_URL/admin/edit.php?id=$ID" --data-urlencode "csrf_token=$T" --data-urlencode "title=テスト（編集後）" \
  --data-urlencode "body=編集しました" --data-urlencode "published_at=")
check "お知らせを編集できる (303)" $([ "$code" = 303 ]; echo $?)
status "$BASE_URL/" >/dev/null
! grep -q 'テスト（編集後）' "$OUT"; check "下書きに戻すと公開ページから消える" $?

# 8. 削除は GET では受け付けない、POST なら消える
code=$(status "$BASE_URL/admin/delete.php?id=$ID"); check "GETでの削除は断る (405)" $([ "$code" = 405 ]; echo $?)
status "$BASE_URL/admin/" >/dev/null; T=$(token)
code=$(status -X POST "$BASE_URL/admin/delete.php" --data-urlencode "csrf_token=$T" --data-urlencode "id=$ID")
check "POSTで削除できる (303)" $([ "$code" = 303 ]; echo $?)
status "$BASE_URL/admin/" >/dev/null
! grep -q 'テスト（編集後）' "$OUT"; check "削除したものは一覧から消える" $?

# 9. ログアウト
T=$(token)
code=$(status -X POST "$BASE_URL/admin/logout.php" --data-urlencode "csrf_token=$T")
code=$(status "$BASE_URL/admin/"); check "ログアウト後は管理画面に入れない (303)" $([ "$code" = 303 ]; echo $?)

echo
echo "${COUNT}件中 $((COUNT - FAIL))件OK"
[ "$FAIL" -eq 0 ]
