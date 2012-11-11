<?php

/**
 * @author Stepanoff Alex
 */
class ExtendedConsoleApplication extends CConsoleApplication
{
	public function getController()
	{
		return null;
	}

    public function displayError($code,$message,$file,$line)
	{
        $trace=debug_backtrace();
		$traceString='';
		foreach($trace as $i=>$t)
		{
			if(!isset($t['file']))
				$t['file']='unknown';
			if(!isset($t['line']))
				$t['line']=0;
			if(!isset($t['function']))
				$t['function']='unknown';
			$traceString.="#$i {$t['file']}({$t['line']}): ";
			if(isset($t['object']) && is_object($t['object']))
				$traceString.=get_class($t['object']).'->';
			$traceString.="{$t['function']}(";
			if (!empty($t['args'])) {
				foreach ($t['args'] as &$arg) {
					$arg = self::parseArg($arg);
				}
				$args = implode(', ', $t['args']);
				$traceString.= $args;
			}
			$traceString.= ")\n";
		}

		echo "PHP Error[$code]: $message\n";
		echo "in file $file at line $line\n";
        echo $traceString;
	}
	
	public function renderPartial ($alias, $____data, $____return = false)
	{
		$fileName = Yii::getPathOfAlias($alias);
		if ($fileName)
		{
			$template = $fileName.'.php';
			if (!file_exists($template))
				throw new CException(Yii::t('yii','Alias "{alias}" is invalid. Make sure it points to an existing directory or file.',
					array('{alias}'=>$alias)));
			foreach ($____data as $k=>$v)
			{
				$$k = $v;
				unset ($____data);
				if ($____return)
					ob_start();
				include ($template);
				if ($____return)
				{
					$res = ob_get_clean();
					return $res;
				}
			}
		}
	}
	
	private static function parseArg($arg) {
		if (is_object($arg))
			$arg = get_class($arg)." Object";
		else if (is_array($arg))
			$arg = CVarDumper::dumpAsString($arg, 3);
		elseif (is_resource($arg))
			$arg = 'resource';

		if (strlen($arg) > 1000)
			$arg = substr($arg, 0, 1000)."...";

		return $arg;
	}

	
}