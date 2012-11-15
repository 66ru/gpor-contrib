<?php
date_default_timezone_set('Asia/Yekaterinburg');
/**
 * Файл для запуска в фоновом режиме консольных cron-команд
 *
 * @author Степанов Алексей
 * @since 28.01.2012
 */
require('yiiCronCommon.php');

$directory =  realpath( dirname(__FILE__) );
$runFile = Yii::app()->params['phpPath'] . ' ' . $directory . '/runner.php ';

$services = Yii::app()->cron->getCommands();

foreach ( $services as $name => $options )
{
    echo $runFile . $name . ' >> ' . $directory . '/cron.log &'."\n";
    passthru($runFile . $name . ' >> ' . $directory . '/cron.log &');
}
?>