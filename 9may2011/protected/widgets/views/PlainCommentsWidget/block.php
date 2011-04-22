<? foreach($comments as $comment) { ?><? include('_comment.php') ?><? } ?>

<? if (!$moreComments) { ?>
<script type="text/javascript">
	$('#plainCommentsWidget-loadMore').hide();
</script>
<? } ?>