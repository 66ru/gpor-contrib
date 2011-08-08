<?php

/**
 * Classe pour gérer les traitements en csv
 */
abstract class CsvParser {

  /**
   * Список колонок
   */
  protected $_columns = array();

  /**
   * Игнорировать или нет первую строчку, например когда там идут названия колонок
   */
  protected $_ignoreFirstLine = true;

  /**
   * Сепаратор колонок
   */
  protected $_separator = ';';

  protected $_line_number = 0;

  public function parse($filename) {
    if(!file_exists($filename)) {
      throw new Exception('File not found: '.$filename);
    }

    $this->preParse();

    $f = fopen($filename,'rb');
    if($f == null){
      throw new Exception('Cound not open '.$filename.' for read');
    }
    
    $this->_line_number = 0;    
    if($this->_ignoreFirstLine) {
      fgets($f,1024);
      $this->_line_number++;
    }


    while($line = fgets($f,1024)) {
      $line = $this->clean($line);
      $line = str_getcsv($line,$this->_separator);
      if(count($line) != count($this->_columns)) continue;
      $line = array_combine($this->_columns,$line);
      array_walk($line,array($this,'clean'));
      $this->_line_number++;
      $this->parseLine((object)$line);
    }

    $this->postParse();
  }

  /**
   * Méthode qui est executer pour chaque ligne du fichier csv
   * @param $line stdClass La ligne a traiter
   * @param $i Le numéro de la ligne
   */
  abstract public function parseLine(stdClass $line);

  /**
   * Méthode executer avant que le fichier ne soit parser
   */
  public function preParse() {

  }

  /**
   * Méthode executer après que tous le fichier ai été parsé
   */
  public function postParse() {

  }

  /**
   * Méthode pour nettoyer le fichier csv
   */
  public function clean($value) {
    return trim(stripslashes($value));
  }
}

if(!function_exists('str_getcsv')){
  function str_getcsv($input, $delimiter = ',', $enclosure = '"', $escape = '\\'){
    $tmp = tmpfile();
    fwrite($tmp, $input);
    rewind($tmp);
    $data = fgetcsv($tmp, null, $delimiter, $enclosure);
    fclose($tmp);
    return $data;
  }
}