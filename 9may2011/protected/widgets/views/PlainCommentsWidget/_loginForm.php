<tr id="expandForm" style="display: none;">
	<td colspan="2">
		<?=CHtml::form('http://www.66.ru/login/', 'post', array('id'=>'login-form'))?>
			<?=CHtml::label('Ваш логин:', CHtml::getIdByName('LoginForm[login]'))?> <?=CHtml::textField('LoginForm[login]')?><br>
			<?=CHtml::label('Ваш пароль:', CHtml::getIdByName('LoginForm[password]'))?> <?=CHtml::passwordField('LoginForm[password]')?><br>
		
			<?=CHtml::submitButton('Отправить')?>
		<?=CHtml::endForm()?>
	</td>
</tr>