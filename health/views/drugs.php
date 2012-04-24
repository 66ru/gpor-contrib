<?php 
if ($rubrics)
{
	?>
	<h3 class="b-zdorovje-rubricator__title">Каталог лекарств</h3>
		<ul class="b-zdorovje-rubricator__list">
	<?
	foreach ($rubrics as $rubric)
	{
		?> 
		<li><a href="<?php echo $rubric['link']; ?>"><?php echo $rubric['name']; ?></a>&nbsp;(<?php echo $rubric['count']; ?>)</li>
		<?php 
	}
	?>
	</ul>
	<?php 
}
?>