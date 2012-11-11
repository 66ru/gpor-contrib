    <h2>Доступ запрещен</h2>
    <p>
    У вас недостаточно прав для доступа в данную зону
    </p>
    <p>
    If you think this is a server error, please contact
    <?php echo CHtml::mailto(Yii::app()->params['adminEmail']); ?>.
    </p>
    <p>
    <?php echo CHtml::link('Return to homepage',Yii::app()->homeUrl); ?>
    </p>

    <p>
    <?php echo CHtml::link('Регистрация', array('/user/user/register')); ?>
    </p>
