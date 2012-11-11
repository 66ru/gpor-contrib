    <h2>Error <?php echo $code; ?></h2>
    <p>
	<?php echo nl2br(CHtml::encode($message)); ?>
	</p>
	<p>
	The above error occurred when the Web server was processing your request.
	</p>
	<p>
	If you think this is a server error, please contact
    <?php echo CHtml::mailto(Yii::app()->params['senderEmail']); ?>.
	</p>
    <p>
    <?php echo CHtml::link('Return to homepage',Yii::app()->homeUrl); ?>
    </p>