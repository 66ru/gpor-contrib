<?php 
if ($data && $data['all'])
{
	$tabs = array ();
	if ($data['all'])
		$tabs[] = array('caption' => 'Все', 'prefix' => 'all');
	if ($data['sell'])
		$tabs[] = array('caption' => 'Продам', 'prefix' => 'sell');
	if ($data['serv'])
		$tabs[] = array('caption' => 'Услуги', 'prefix' => 'serv');
	if ($data['buy'])
		$tabs[] = array('caption' => 'Куплю', 'prefix' => 'buy');
	?>
<div class="b-advertisement-caption context">
	<h2 class="b-advertisement-caption__title">Объявления о <a href="<?php echo $urls['zdorovie']; ?>">здоровье</a>, <a href="<?php echo $urls['krasota']; ?>">красоте</a></h2>
	<a href="<?php echo $urls['announceAdd']; ?>" class="b-advertisement-caption__link-edit">Подать объявление</a>
</div>
<div class="b-advertisement-listing">
	<ul class="b-advertisement-listing__tabs context">
		<li class="b-advertisement-listing__tabs__first">
			<span class="tab-menu-new">новые:</span>
		</li>
	<?
	foreach ($tabs as $k => $row)
	{
		if ($row['prefix'] == 'all')
		{
		?>
		<li class="rc5 rc-bl rc-br selected" id="healthDoska_<?php echo $row['prefix']; ?>">
			<a href="#" onclick="return false;"><?php echo $row['caption']?></a>
		</li>
		<?php
		}
		else
		{
		?>
		<li class="rc5" id="healthDoska_<?php echo $row['prefix']; ?>">
			<a href="#" onclick="return false;"><?php echo $row['caption']?></a>
		</li>
		<?php
		}
	}
	?>
	</ul>
	
	<?php
	foreach ($data as $k => $rows)
	{
		$active = $k == 'all' ? true : false;
		
		?>
		<div id="healthDoska_<?php echo $k; ?>_table" style="<?php if(!$active){echo 'display: none;';} ?>">
			<table class="b-advertisement-listing__items"><tbody>
		<?php
		foreach ($rows as $row)
		{
			?>
			<tr>
				<td class="date"><?php echo healthAnnounceDate($row->updated); ?></td>
				<td class="descr"><a href="<?php echo $row->url; ?>"><?php echo $row->title; ?></a></td>
			</tr>
			<?php	
		}
		?>
			</tbody></table>
		</div>
		<?php
	}
}
?>
</div>
<script type="text/javascript">
	$(".b-advertisement-listing__tabs").find("li").click(function(){
		if ($(this).hasClass('b-advertisement-listing__tabs__first'))
		{
			return false;
		}
		else if ($(this).hasClass('selected'))
		{
			return false;
		}
		else
		{
			$selected = $(".b-advertisement-listing__tabs").find("li.selected");
			if ($selected.eq(0))
			{
				$selected.removeClass('selected');
				$selected.removeClass('rc-bl');
				$selected.removeClass('rc-br');
				id = $selected.attr('id');
				$('#'+id+'_table').hide();
			}
			
			$(this).addClass('selected rc-bl rc-br');
			id = $(this).attr('id');
			$('#'+id+'_table').show();
		}
	});
</script>