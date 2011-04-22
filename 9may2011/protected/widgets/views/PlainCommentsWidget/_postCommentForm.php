<?=CHtml::form(array('/comments/post'), 'post', array('id'=>'newComment-form'))?>
	<?=CHtml::textArea('newComment')?><br>
	<?=CHtml::submitButton('Отправить')?>
<?=CHtml::endForm()?>
<script type="text/javascript">
	var $form = $('#newComment-form');

	$form.bind('submit', function () {
		var newCommentText = $('#newComment').val();
		if (!newCommentText) {
			alert('Вы ничего не ввели');
			return false;
		}

		$.post($form.attr('action'), {
			'newComment' : newCommentText
		}, function (data) {
			$form.hide(600, function() {
				$(data).hide().prependTo('#plainCommentsWidget-comments').show(300);
			});
		});

		return false;
	})
</script>
