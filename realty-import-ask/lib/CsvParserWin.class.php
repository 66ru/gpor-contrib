<?php

require dirname(__FILE__).'/CsvParser.class.php';

/**
 * Classer pour parser des fichier csv qui sont au format windows-1252
 */
abstract class CsvParserWin extends CsvParser{
	
	public function clean($value){
		return stripslashes(trim(iconv('Windows-1252', 'UTF-8//TRANSLIT//IGNORE',$value)));
	}
}