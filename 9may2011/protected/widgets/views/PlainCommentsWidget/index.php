<ul id="plainCommentsWidget-comments">
<? foreach($comments as $comment) { ?>
	<li commentId="<?=$comment->id?>">
		<a href="<?=$comment->user->profileLink?>"><?=CHtml::image($comment->user->image, CHtml::encode($comment->user->name))?></a>
		<a href="<?=$comment->user->profileLink?>"><?=CHtml::encode($comment->user->name)?></a> :
		<?=$comment->date?><br>
		<?=CHtml::encode($comment->text)?>
	</li>
<? } ?>
</ul>
<? if ($moreComments) { ?>
	<a id="plainCommentsWidget-loadMore" href="<?=CHtml::normalizeUrl(array('/site/loadComments'))?>">Загрузить больше!</a>
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