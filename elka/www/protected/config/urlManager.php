<?php 
return array(
	'urlFormat'=>'path',
	'showScriptName'=>false,
	'rules'=>array(
        '/' => 'site/index',
		'/<_a:([a-zA-Z0-9_]+)>' => 'site/<_a>',
	),
);
?>