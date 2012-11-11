<?php

/**
 * Empty controller singleton
 * Для использования функций базового контроллера вне его
 *
 * @author Vasiliy Aksyonov <outring@gmail.com>
 */
class EmptyController
{
    protected static $_instance = null;

    public static function getInstance()
    {
        if (null === self::$_instance)
        {
            self::$_instance = new Controller(null, Yii::app());
        }

        return self::$_instance;
    }
}