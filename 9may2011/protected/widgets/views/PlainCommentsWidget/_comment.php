<div class="js_comment  ie_layout" style="display: block;" commentId="<?=$comment->id?>">
	<div class="comment_head rc5">
		<div class="comment_head_avatar"><?=CHtml::image($comment->user->image, CHtml::encode($comment->user->name))?></div>
		<a href="<?=$comment->user->profileLink?>" class=" js_user"><?=CHtml::encode($comment->user->name)?></a>
		<i class="comment_head_date"><?=DateHelper::formatRusDate($comment->datetime)?></i>
		<? if ($user->isAdmin) { ?>
			<a href="<?=CHtml::normalizeUrl(array('/comments/delete','commentId'=>$comment->id))?>" title="Удалить комментарий" class="buttons_report_small comment_head_icon deletecomment">Удалить комментарий</a>
		<? } ?>
	</div>
	<div class="comment_content content">
		<?=CHtml::encode($comment->text)?>
	</div>
</div>
