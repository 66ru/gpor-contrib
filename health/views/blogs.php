<?php 
if ($blogs)
{
	?>
<div class="b-blogs-list rc3">
	<div class="b-blogs-list_pad">
	<h3 class="b-blogs-list__title">В блогах</h3>
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
						<ins class="inline-block g-social-count"><?php echo $blog['count']; ?></ins>
						<?php 
					}
					?>
				</dd>
			</dl>
		<?php
	}
	?>
	</div>
</div>
	<?php
}
?>