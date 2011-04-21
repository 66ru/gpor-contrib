<ul>
<? foreach($comments as $comment) { ?>
	<li>
		<a href="<?=$comment->user->profileLink?>"><?=CHtml::image($comment->user->image, CHtml::encode($comment->user->name))?></a>
		<a href="<?=$comment->user->profileLink?>"><?=CHtml::encode($comment->user->name)?></a> :
		<?=$comment->date?><br>
		<?=CHtml::encode($comment->text)?>
	</li>
<? } ?>
</ul>