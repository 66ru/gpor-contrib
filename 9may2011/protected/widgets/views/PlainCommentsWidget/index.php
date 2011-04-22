<? if (!$user->isGuest) { ?>
	<? include('_postCommentForm.php') ?>
<? } else { ?>
	<? include('_loginForm.php') ?>
<? } ?>
<ul id="plainCommentsWidget-comments">
<? foreach($comments as $comment) { ?>
	<? $this->render('_comment', array('comment'=>$comment)) ?>
<? } ?>
</ul>
<? if ($moreComments) { ?>
	<a id="plainCommentsWidget-loadMore" href="<?=CHtml::normalizeUrl(array('/comments/load'))?>">Загрузить больше!</a>
<? } ?>

<script type="text/javascript">
	$('#plainCommentsWidget-loadMore').bind('click', function () {
		$.get($(this).attr('href'), {
			'lastId' : $('#plainCommentsWidget-comments li:last').attr('commentId')
		}, function (data) {
			$('#plainCommentsWidget-comments').append(data);
		});

		return false;
	});
</script>