<? foreach($comments as $comment) { ?><? $this->render('_comment', array('comment'=>$comment)) ?><? } ?>

<? if (!$moreComments) { ?>
<script type="text/javascript">
	$('#plainCommentsWidget-loadMore').hide();
</script>
<? } ?>