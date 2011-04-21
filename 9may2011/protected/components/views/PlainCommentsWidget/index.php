<ul>
<? foreach($comments as $comment) { ?>
	<li>
		<a href="<?=$comment->user->profileLink?>"><?=CHtml::image($comment->user->image, $comment->user->name)?></a>
		<a href="<?=$comment->user->profileLink?>"><?=$comment->user->name?></a> :
		<?=$comment->date?><br>
		<?=CHtml::encode($comment->text)?>
	</li>
<? } ?>
</ul>