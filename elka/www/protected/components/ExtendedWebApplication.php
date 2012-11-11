<?php

/**
 * @author Stepanoff Alex
 * 
 * @version $Id$
 *
 */
class ExtendedWebApplication extends CWebApplication
{
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