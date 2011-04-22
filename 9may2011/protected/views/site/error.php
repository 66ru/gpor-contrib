<?
	$this->pageTitle=Yii::app()->name;
?>

<h2>Error <?=$code?></h2>

<div class="error">
<?=CHtml::encode($message)?>
</div>