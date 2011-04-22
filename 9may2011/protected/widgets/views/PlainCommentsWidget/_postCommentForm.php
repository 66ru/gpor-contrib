<tr id="expandForm" style="display: none;">
	<td colspan="2">
		<?=CHtml::form(array('/comments/post'), 'post', array('id'=>'newComment-form'))?>
			<fieldset>
				<?=CHtml::textArea('newComment')?>
			</fieldset>
		<?=CHtml::submitButton('Отправить')?>
		<?=CHtml::endForm()?>
	</td>
</tr>
<script type="text/javascript">
	var $form = $('#newComment-form');

	function plural_str(i, str1, str2, str3) {
		function plural(a) {
			if (a % 10 == 1 && a % 100 != 11) return 0
			else if (a % 10 >= 2 && a % 10 <= 4 && ( a % 100 < 10 || a % 100 >= 20)) return 1
			else return 2;
		}

		switch (plural(i)) {
			case 0: return str1;
			case 1: return str2;
			default: return str3;
		}
	}
	$form.bind('submit', function () {
		var newCommentText = $('#newComment').val();
		if (!newCommentText) {
			alert('Вы ничего не ввели');
			return false;
		}

		$.post($form.attr('action'), {
			'newComment' : newCommentText
		}, function (data) {
			$('#expandForm').hide();
			$('#newComment').val('');
			var newCommentsCount = parseInt($('#commentsCount').text())+1;
			$('#commentsCount').text(newCommentsCount);
			$('#pluralForm').text(plural_str(newCommentsCount, 'ие','ия','ий'));
			$(data).hide().prependTo('#plainCommentsWidget-comments').show(300);
		});

		return false;
	})
</script>
