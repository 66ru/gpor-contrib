<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">

<head>
	<title>Статистика новостей он-лайн</title>

	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<script src="js/jquery-1.4.2.js"  type="text/javascript"></script>
	<script src="js/number_functions.js"  type="text/javascript"></script>
	<script src="js/jquery.newsStat.js"  type="text/javascript"></script>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
</head>

<body id="body">
<style type="text/css">
	* {line-height: 1.4em; font-family:	'Arial'; font-size:	12px; color: #646464;}
	html, body {width: 1920px; margin: 0; padding: 0;}
	
	#head {background-color: #779f1a; height: 40px; margin-bottom: 20px;}
	.pageTitle {display: block; text-align: center; height: 40px; line-height: 40px; color: #ffffff; font-size: 28px;}
	
	.statConainer {width: 640px; float: left; position: relative;}

	.statConainer-graph {height: 500px; width: 640px;}

	.statConainer-current {position: relative; height: 80px; width: 640px;}
	.statConainer-current-pre {position: absolute; width: 100px; padding-top: 12px; font-size: 18px; left: 120px; top: 0; }
	.statConainer-current-post {position: absolute; left: 240px; top: 0;}
	.statConainer-current-post__comments {left: 270px; top: 0;}
	.statConainer-today-container {height: 50px; }
	.statConainer-yesterday-container {height: 30px; }
	.statConainer-today-count {font-size: 32px; float: left; width: 120px;}
	.statConainer-yesterday-count {float: left; font-size: 24px; width: 120px;}
	.statConainer-today {float: left; width: 120px; font-size: 18px; padding-top: 12px;}
	.statConainer-yesterday {float: left; width: 120px; font-size: 18px; padding-top: 5px;}

	.statConainer-top-header {font-size: 24px; height: 38px; border-bottom: 2px solid #ffab00; margin-bottom: 30px; padding-left: 10px;}
	.statConainer-top {padding: 0 20px;}
	.statConainer-top-list-item {position: relative; width: 590px; overflow: hidden; height: 30px; padding-left: 10px;}
	.statConainer-top-list-today {width: 60px; position: absolute; left:10px; top: 0; color: #779f1a; font-size: 14px; font-weight: bold; height: 30px; line-height: 30px;}
	.statConainer-top-list-all {width: 50px; position: absolute; left:70px; top: 0; font-size: 14px; height: 30px; line-height: 30px; }
	.statConainer-top-list-link {position: absolute; width: 1000px; left:120px; top: 0; font-size: 14px; height: 30px; line-height: 30px;}

	#feed {width: 620px; float: left; position: relative; padding-right: 20px;}
	.statConainer-feed-header {font-size: 24px; margin-bottom: 20px; padding-left: 10px;}
	.feed-list-header {font-size: 32px;}
	.feed-list {}
	.feed-list-item {height: 50px; border-bottom: 1px solid #cccccc; margin-bottom: 5px; padding: 0 10px;}
	.feed-list-itemSource {width: 600px; font-size: 12px; white-space: nowrap; overflow: hidden; height: 30px; line-height: 30px;}
	.feed-list-itemSource a {color: #646464; text-decoration: none; }
	.feed-list-itemLink {display: block; width: 600px; overflow: hidden; font-size: 14px; white-space: nowrap; overflow: hidden; height: 20px; line-height: 20px;}
	.feed-list-itemDate {color: #779f1a; padding-right: 5px;}

	.clear {clear: both;}
	.newItem {display:none;}
	.recordCount {color: #52ad5a;}
	.lowCount {color: #dc3912;}
	.newestItemClass {background-color: #E6F6DA; border-bottom: 1px solid #CAE9B5;}
	
	#footer {margin-top: 20px; padding: 0 20px;}
	.footerText {border-top: 1px solid #cccccc; margin-top: 5px; padding: 0 10px;}
	.footerText p {font-size: 11px; margin: 0 10px;}

	a {color: #037DD3;text-decoration: none;}
	ol, ul {list-style: none outside none; margin: 0; padding: 0;}
	h1, h2 {padding: 0; margin: 0;}
	.odd {background-color: #F2F6E8;}
</style>

<script type="text/javascript">
	google.load('visualization', '1.0', {'packages':['corechart']});
    //google.setOnLoadCallback(initGraphs);
</script>
	<div id="head">
		<h1 class="pageTitle">Статистика новостей</h1>
	</div>
	
	<div id="viewsStat" class="statConainer">
		<div class="statConainer-current">
			<div class="statConainer-current-pre">Просмотров</div>
			<div class="statConainer-current-post">
				<div class="statConainer-today-container">
					<div class="statConainer-today-count">0</div><div class="statConainer-today">сегодня</div>
				</div>
				<div class="statConainer-yesterday-container">
					<div class="statConainer-yesterday-count">0</div><div class="statConainer-yesterday">вчера</div>
				</div>
			</div>
		</div>

		<div class="statConainer-graph" id="viewsStatGraph">
		</div>
		
		<div class="statConainer-top">
			<h2 class="statConainer-top-header">Самые просматриваемые</h2>
			<div class="statConainer-top-list">
			</div>
		</div>

	</div>
	
	<div id="commentsStat" class="statConainer statConainer_comments">
		<div class="statConainer-current">
			<div class="statConainer-current-pre">Комментариев</div>
			<div class="statConainer-current-post statConainer-current-post__comments">
				<div class="statConainer-today-container">
					<div class="statConainer-today-count">0</div><div class="statConainer-today">сегодня</div>
				</div>
				<div class="statConainer-yesterday-container">
					<div class="statConainer-yesterday-count">0</div><div class="statConainer-yesterday">вчера</div>
				</div>
			</div>
		</div>

		<div class="statConainer-graph" id="commentsStatGraph">
		</div>
		
		<div class="statConainer-top">
			<h2 class="statConainer-top-header">Самые комментируемые</h2>
			<div class="statConainer-top-list">
			</div>
		</div>
	</div>
	
	<div id="feed">
		<h2 class="statConainer-feed-header">Последние новости</h2>
		<ul class="feed-list">
		</ul>
	</div>
	
<div id="footer">
	<div class="footerText">
		<p><?php echo $config['footerText'];?></p>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function(){
	$("#body").news_stat(
		{
			'sections' : [],
			'statData' : <? echo json_encode($data); ?>,
			'feedData' : <? echo json_encode($data['feed']); ?>,
			'topItemHeight' : 30,
			'feedItemHeight' : 50,
			'currentSectionId' : <? echo $currentSectionId; ?>,
			'currentSectionName' : "<? echo $currentSectionName; ?>",
			'refreshStatInterval' : <? echo $config['refreshStatInterval']*1000; ?>,
			'refreshFeedInterval' : <? echo $config['refreshFeedInterval']*1000; ?>,
			'reloadPageInterval' : <?php echo $config['reloadPageInterval']*1000; ?>
		}
	);
});

function initGraphs ()
{
	
}
</script>

</body>
</html>