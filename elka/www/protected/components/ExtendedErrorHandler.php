<?php

/**
 * @author vv
 * @version $Id$
 * 
 */
class ExtendedErrorHandler extends CErrorHandler
{
	public $customErrorLayout;
	public $customPageTitle = null;
	
	static $ERROR_CODE_2_TITLE = array(
		404 => 'Страница не найдена',
		500 => 'Ошибка на сервере',
		403 => 'Доступ запрещен'
	);

	/**
	 * Renders the view.
	 * @param string the view name (file name without extension).
	 * See {@link getViewFile} for how a view file is located given its name.
	 * @param array data to be passed to the view
	 */
	protected function render($view,$data)
	{
		if($view==='error' && $this->errorAction!==null)
			Yii::app()->runController($this->errorAction);
		else
		{
            // additional information to be passed to view
            if(isset(Yii::app()->controller))
            {
                $data['controller'] = Yii::app()->controller->id;
				
				if (isset(Yii::app()->controller->action))
                	$data['action'] = Yii::app()->controller->action->id;
                if(isset(Yii::app()->controller->module))
                    $data['module'] = Yii::app()->controller->module->id;
            }
			$data['version']=$this->getVersionInfo();
			$data['time']=time();
			$data['admin']=$this->adminInfo;

			$emptyController = EmptyController::getInstance();
			if (!empty($this->customPageTitle)) {
				$emptyController->pageTitle = $this->customPageTitle;
			}
			else {
				if (isset(self::$ERROR_CODE_2_TITLE[$data['code']]))
					$emptyController->pageTitle = self::$ERROR_CODE_2_TITLE[$data['code']];
			}
			$output = $emptyController->renderFile($this->getViewFile($view,$data['code']), $data, true);

			if (!empty($this->customErrorLayout)) {
				$emptyController->layout = $this->customErrorLayout;
			}

			if(($layoutFile=$emptyController->getLayoutFile($emptyController->layout))!==false)
			{
				$output=$emptyController->renderFile($layoutFile,array('content'=>$output),true);
			}

			$output=$emptyController->processOutput($output);
            //var_dump($data);

			echo $output;

			//include($this->getViewFile($view,$data['code']));
		}
	}

	/**
	 * Looks for the view under the specified directory.
	 * @param string the directory containing the views
	 * @param string view name (either 'exception' or 'error')
	 * @param integer HTTP status code
	 * @param string the language that the view file is in
	 * @return string view file path
	 */
	protected function getViewFileInternal($viewPath,$view,$code,$srcLanguage=null)
	{
		$app=Yii::app();
		$viewRenderer = $app->getViewRenderer();
		$viewExtension = isset($viewRenderer->fileExtension) ? $viewRenderer->fileExtension : '.php';

		if($view==='error')
		{
			$viewFile=$app->findLocalizedFile($viewPath.DIRECTORY_SEPARATOR."error{$code}{$viewExtension}",$srcLanguage);
			if(!is_file($viewFile))
				$viewFile=$app->findLocalizedFile($viewPath.DIRECTORY_SEPARATOR."error{$viewExtension}",$srcLanguage);
		}
		else
			$viewFile=$app->findLocalizedFile($viewPath.DIRECTORY_SEPARATOR."exception{$viewExtension}",$srcLanguage);
		if (!empty($viewFile)) {
			return $viewFile;
		}

		return parent::getViewFileInternal($viewPath,$view,$code,$srcLanguage);
	}

	protected function handleException($exception)
	{
		parent::handleException($exception);

		$this->reportException($exception);
	}

	protected function handleError($event)
	{
		parent::handleError($event);

		$this->reportError($event);
	}

	protected function reportError($event)
	{
		$code = $event->code;
		$message = $event->message;
		$file = $event->file;
		$line = $event->line;
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

		$subject = Yii::app()->name.": PHP Error $code";
		$fields = array(
			'message' => "PHP Error[$code]: $message<br/>".nl2br($traceString),
		);

		$app = Yii::app();
		if($app instanceof CWebApplication)
		{
			if (Yii::app()->controller)
				$html = Yii::app()->controller->renderPartial('application.views.mail.error', $fields, true);
			else
				$html = $fields['message'];
		}
		else
			$html = Yii::app()->renderPartial('application.views.mail.error', $fields, true);
		
		$message = array (
			'html' => $html,
			'text' => '',
			'subject' => $subject,
		);
		
		return MailHelper::sendMailToAdmin($message);

	}
	
	protected function reportException($exception)
	{
		$message = '<h1>'.get_class($exception)."</h1>\n";
		$message .=	'<p>'.$exception->getMessage().' ('.$exception->getFile().':'.$exception->getLine().')</p>';
		$message .=	'<pre>'.$exception->getTraceAsString().'</pre>';
		
		$subject = Yii::app()->name.": ".get_class($exception)." exception: ".$exception->getMessage();
		$fields = array(
			'message' => $message,
		);

		$app = Yii::app();
		if($app instanceof CWebApplication)
		{
			if (Yii::app()->controller)
				$html = Yii::app()->controller->renderPartial('application.views.mail.error', $fields, true);
			else
				$html = $message;
		}
		else
			$html = Yii::app()->renderPartial('application.views.mail.error', $fields, true);
		
		$message = array (
			'html' => $html,
			'text' => '',
			'subject' => $subject,
		);
		
		return MailHelper::sendMailToAdmin($message);

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