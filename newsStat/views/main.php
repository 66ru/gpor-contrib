<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">

<head>
	<title>Статистика новостей он-лайн</title>

	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<script src="js/jquery-1.4.2.js"  type="text/javascript"></script>
	<script src="js/jquery.newsStat.js"  type="text/javascript"></script>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
</head>

<body id="body">
<style type="text/css">
	* {line-height: 1.4em; font-family:	'Arial'; font-size:	12px;}
	body {width: 1920px;}
	.statConainer {width: 640px; float: left; position: relative;}

	.statConainer-graph {height: 450px; width: 640px;}

	.statConainer-current {margin: 0 auto; font-size: 12px; height: 80px;}
	.statConainer-current-pre {float: left; padding-right: 10px;}
	.statConainer-current-post {float: left; width: 300px;}
	.statConainer-today {font-size: 32px;}
	.statConainer-yesterday {font-size: 24px;}


	.statConainer-top-header {font-size: 32px;}
	.statConainer-top-list li {margin-top: 0.846em;padding-left: 20px;position: relative;}
	.statConainer-top-list-today {width: 50px; flost: left;}
	.statConainer-top-list-all {width: 50px; flost: left;}

	#feed {width: 640px; float: left; position: relative;}
	.feed-list-header {font-size: 32px;}
	.feed-list li {margin-top: 0.846em;padding-left: 20px;position: relative;}
	.feed-list-itemSource {width: 100px; flost: left;}

	.clear {clear: both;}

	a {color: #037DD3;text-decoration: underline;}
	ol, ul {list-style: none outside none;}
</style>

<script type="text/javascript">
	google.load('visualization', '1.0', {'packages':['corechart']});
    //google.setOnLoadCallback(initGraphs);
</script>
	<div id="head">
		<h1></h1>
	</div>
	
	<div id="viewsStat" class="statConainer">
		<div class="statConainer-current">
			<div class="statConainer-current-pre">Просмотров:</div>
			<div class="statConainer-current-post">
				<span class="statConainer-today"><?php echo $data['todayViews']; ?></span> сегодня<br/>
				<span class="statConainer-yesterday"><?php echo $data['yesterdayViews']; ?></span> вчера
			</div>
		</div>

		<div class="statConainer-graph" id="viewsStatGraph">
		</div>
		
		<div class="statConainer-top">
			<h2 class="statConainer-top-header">Самые просматриваемые</h2>
			<ul class="statConainer-top-list">
				<?php 
				if ($data['viewsTop'])
				{
					foreach ($data['viewsTop'] as $news)
					{
						?>
						<li>
							<div class="statConainer-top-list-today"><?php echo $news['views']; ?></div>
							<div class="statConainer-top-list-all"><?php echo $news['viewsTotal']; ?></div>
							<a href="<?php echo $news['link']; ?>"><?php echo $news['title']; ?></a>
						</li>
						<?php
					}
				}
				?>
			</ul>
		</div>

	</div>
	
	<div id="commentsStat" class="statConainer statConainer_comments">
		<div class="statConainer-current">
			<div class="statConainer-current-pre">Комментариев:</div>
			<div class="statConainer-current-post">
				<span class="statConainer-today"><?php echo $data['todayComments']; ?></span> сегодня<br/>
				<span class="statConainer-yesterday"><?php echo $data['yesterdayComments']; ?></span> вчера
			</div>
		</div>

		<div class="statConainer-graph" id="commentsStatGraph">
		</div>
		
		<div class="statConainer-top">
			<h2 class="statConainer-top-header">Самые комментируемые</h2>
			<ul class="statConainer-top-list">
				<?php 
				if ($data['commentsTop'])
				{
					foreach ($data['commentsTop'] as $news)
					{
						?>
						<li>
							<div class="statConainer-top-list-today"><?php echo $news['comments']; ?></div>
							<div class="statConainer-top-list-all"><?php echo $news['commentsTotal']; ?></div>
							<a href="<?php echo $news['link']; ?>"><?php echo $news['title']; ?></a>
						</li>
						<?php
					}
				}
				?>
			</ul>
		</div>
	</div>
	
	<div id="feed">
		<h2 class="statConainer-top-header">Последние новости</h2>
		<ul class="feed-list">
				<?php 
				if ($data['feed'])
				{
					foreach ($data['feed'] as $news)
					{
						?>
						<li>
							<div class="feed-list-itemSource"></div>
							<a href="<?php echo $news['link']; ?>"><?php echo $news['title']; ?></a>
						</li>
						<?php
					}
				}
				?>
		</ul>
	</div>

<script type="text/javascript">
$(document).ready(function(){
	$("#body").news_stat(
		{
			'sections' : [],
			'statData' : <? echo json_encode($data); ?>,
			'feedData' : <? echo json_encode($data['feed']); ?>,
			'currentSectionId' : <? echo $currentSectionId; ?>,
			'currentSectionName' : "<? echo $currentSectionName; ?>"
		}
	);
});

function initGraphs ()
{
	
}
</script>

</body>
</html>