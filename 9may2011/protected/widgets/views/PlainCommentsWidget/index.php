<div class="list-greet">
	<h3>Поздравления от посетителей</h3>

	<div class="leave-greeting-block">
		<i class="lt"></i><i class="lb"></i><i class="rt"></i><i class="rb"></i>
		<table class="leave-greeting">
			<tr>
				<td class="leave-greeting__link"><a id="linkOpenFormGreeting" href="#">Оставить свое поздравление</a>
				</td>
				<td class="leave-greeting__stat">на сайте уже <b id="commentsCount"><?=$commentsCount?></b>
					поздравлен<span id="pluralForm"><?=StringHelper::plural($commentsCount,'ие','ия','ий')?></span></td>
			</tr>
			<? if (!$user->isGuest) { ?>
				<? include('_postCommentForm.php') ?>
			<? } else { ?>
				<? include('_loginForm.php') ?>
			<? } ?>
		</table>
	</div>

	<div id="plainCommentsWidget-comments">
	<? foreach($comments as $comment) { ?>
		<? include('_comment.php') ?>
		<? #$this->render('_comment', array('comment'=>$comment)) ?>
	<? } ?>
	</div>
	<? if ($moreComments) { ?>
	<div class="leave-greeting-block-bottom">
		<i class="lt"></i><i class="lb"></i><i class="rt"></i><i class="rb"></i>
		<table class="leave-greeting leave-greeting-last">
			<tr>
				<td class="leave-greeting__link leave-greeting__link-other">
					<a id="plainCommentsWidget-loadMore" href="<?=CHtml::normalizeUrl(array('/comments/load'))?>">Показать еще</a>
				</td>
			</tr>
		</table>
	</div>
	<? } ?>

	<script type="text/javascript">
		$('#plainCommentsWidget-loadMore').bind('click', function () {
			$.get($(this).attr('href'), {
				'lastId' : $('#plainCommentsWidget-comments>div:last').attr('commentId')
			}, function (data) {
				$('#plainCommentsWidget-comments').append(data);
			});

			return false;
		});

		$('#linkOpenFormGreeting').bind('click', function () {
			$('#expandForm').show();

			return false;
		})

		$('#plainCommentsWidget-comments').delegate('a.deletecomment','click', function() {
			$.get($(this).attr('href'), function (data) {
				if (!data.errCode) {
					$('#plainCommentsWidget-comments div[commentId='+data.commentId+']').hide(300);
				} else {
					alert(data.errMsg);
				}
			}, 'json');

			return false;
		});
	</script>
</div>