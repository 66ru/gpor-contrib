<li commentId="<?=$comment->id?>">
	<a href="<?=$comment->user->profileLink?>"><?=CHtml::image($comment->user->image, CHtml::encode($comment->user->name))?></a>
	<a href="<?=$comment->user->profileLink?>"><?=CHtml::encode($comment->user->name)?></a> :
	<?=DateHelper::formatRusDate($comment->datetime)?>
	<? if ($user->isAdmin) { ?>
		<a href="<?=CHtml::normalizeUrl(array('/admin/deletecomment'))?>">жалоба!</a>
	<? } ?>
	<br>
	<?=CHtml::encode($comment->text)?>
</li>