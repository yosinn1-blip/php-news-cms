// 管理画面の小さな動き（jQuery）
$(function () {
  // 削除の前に確認する
  $('.js-delete-form').on('submit', function (event) {
    var title = $(this).find('button').data('title');
    if (!window.confirm('「' + title + '」を削除します。よろしいですか？')) {
      event.preventDefault();
    }
  });

  // タイトルの文字数を表示する
  var $target = $('.js-count-target');
  var $counter = $('.js-counter');
  function updateCounter() {
    var max = Number($counter.data('max'));
    var length = Array.from($target.val() || '').length; // 絵文字も1文字として数える
    $counter.text(length + ' / ' + max + '文字').toggleClass('is-over', length > max);
  }
  if ($target.length && $counter.length) {
    $target.on('input', updateCounter);
    updateCounter();
  }

  // 「公開する」にチェックしたときだけ公開日の欄を出す
  var $toggle = $('.js-publish-toggle');
  var $date = $('.js-published-at');
  function updateDateField() {
    $date.toggle($toggle.is(':checked'));
  }
  if ($toggle.length) {
    $toggle.on('change', updateDateField);
    updateDateField();
  }
});
