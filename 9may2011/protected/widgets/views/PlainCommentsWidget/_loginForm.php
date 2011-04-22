<?=CHtml::form('http://www.66.ru/login/', 'post', array('id'=>'login-form'))?>
	<?=CHtml::label('Ваш логин:', CHtml::getIdByName('LoginForm[login]'))?> <?=CHtml::textField('LoginForm[login]')?><br>
	<?=CHtml::label('Ваш пароль:', CHtml::getIdByName('LoginForm[password]'))?> <?=CHtml::passwordField('LoginForm[password]')?><br>
	<?=CHtml::submitButton('Отправить')?>
<?=CHtml::endForm()?>