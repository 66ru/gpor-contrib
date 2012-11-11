<?php

/**
 * Класс, содержащий статический метод рендеринга из простых php view-файлов,
 * создающий вывод текста наподобие того, как это работает в CController::render()
 *
 * @author vv
 * @since 22.01.2010
 */
class SimpleViewRenderer
{

	public static function renderView ($viewFileAlias, $data, $return=true)
	{
        $ctrl = EmptyController::getInstance();

		if ($return) {
			return $ctrl->renderPartial($viewFileAlias, $data, true);
		}

		$ctrl->render($viewFileAlias, $data, $return);
	}
}