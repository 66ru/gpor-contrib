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

}
