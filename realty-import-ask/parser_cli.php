<?php
spl_autoload_register('autoload');
function autoload($class_name) {
	include './lib/'.$class_name . '.class.php';
}
$parser = new Parser();
$parser->configure(array('id','rooms', 'square', 'price', 'floor'), true, ';');
$parser->parse();
$parser->prepareAnnonceListAndImport();