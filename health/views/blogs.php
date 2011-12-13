<?php 
if ($blogs)
{
	?>
	<h3 class="b-blogs-list__title"><!-- a href=""><ins class="b-blogs-list__ico"></ins -->В блогах<!-- /a --></h3>
	<?php
	foreach ($blogs as $blog)
	{
		?>
			<dl class="b-blogs-list__item">
				<dt><a class="js-user js_user-m-off" href="<?php echo $blog['user_url']; ?>"><?php echo $blog['username']; ?></a></dt>
				<dd>
					<a href="<?php echo $blog['blog_url']; ?>"><?php echo $blog['title']; ?></a>
					<?php 
					if ($blog['count'])
					{
						?>
						<sup><?php echo $blog['count']; ?></sup>
						<?php 
					}
					?>
				</dd>
			</dl>
		<?php
	}
}
?>