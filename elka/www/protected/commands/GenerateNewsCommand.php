<?php

Yii::import('application.extensions.croncommand.*');

class CronFixCommand extends CConsoleCommand
{
    /**
     * Запуск консольной команды
     * @param mixed $args Не используется
     */
	public function run($args)
	{
        $commands = array_keys( CronCommand::getCronCommands() );
        $commands = array_unique($commands);

        $criteria = new CDbCriteria();
        $criteria->condition    = '`finishTime` IS NULL';
        $criteria->order        = 'launchTime';
        $running = Yii::app()->db->commandBuilder->createFindCommand('cron', $criteria)->queryAll();

        foreach($running as $item)
        {
            $found = false;
            unset($output);
            unset($result);
            exec('ps ax | grep '.$item['command'],$output,$result);
            foreach($output as $line)
            {
                if(strpos($line,'runner.php')!==false)
                {
                    $found=true;
                    break;
                }
            }
            unset($output);
            unset($result);
            if(!$found)
            {
                $criteria = new CDbCriteria();
                $criteria->addCondition('command=:command');
                $criteria->params = array(':command'=>$item['command']);
                Yii::app()->db->commandBuilder->createDeleteCommand('cron', $criteria)->execute();
            }
        }


    }
}
?>
