<?php
 
class Widget extends CWidget {

	private static $_viewPaths;

	public function getViewPath()
	{
		$className=get_class($this);
		if(isset(self::$_viewPaths[$className]))
			return self::$_viewPaths[$className];
		else
		{
			$class=new ReflectionClass($className);
			return self::$_viewPaths[$className]=dirname($class->getFileName()).'/views/'.$className;
		}
	}

	public function getViewFile($viewName) {

		// позволяет обращатся к общим view через слэш // вначале
		if($viewName[0]==='/' && strncmp($viewName,'//',2)===0)
		{
			if(($renderer=Yii::app()->getViewRenderer())!==null)
				$extension=$renderer->fileExtension;
			else
				$extension='.php';

			$viewFile=Yii::app()->getViewPath().$viewName;

			if(is_file($viewFile.$extension))
				return Yii::app()->findLocalizedFile($viewFile.$extension);
			else if($extension!=='.php' && is_file($viewFile.'.php'))
				return Yii::app()->findLocalizedFile($viewFile.'.php');
			else
				return false;
		}

		return parent::getViewFile($viewName);
	}
}
