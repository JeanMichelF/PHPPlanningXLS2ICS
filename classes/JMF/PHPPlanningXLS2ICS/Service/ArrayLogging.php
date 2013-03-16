<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 11/03/13
 * Time: 20:06
 * To change this template use File | Settings | File Templates.
 */
namespace JMF\PHPPlanningXLS2ICS\Service;

class ArrayLogging implements ILoggingService
{

    private $logData = array();

    private static $_instance;

    /**
     * Empêche la création externe d'instances.
     */
    private function __construct() {}

    /**
     * Empêche la copie externe de l'instance.
     */
    private function __clone() {}

    /**
     * Renvoi de l'instance et initialisation si nécessaire.
     */
    public static function getInstance() {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @param $level
     * @param $message
     */
    public function add($level, $message)
    {
        $timestamp = new \DateTime();
        ArrayLogging::getInstance()->logData[] =
            "[" . $timestamp->format("d/m/Y H:i:s") . "] - " . $level . " - " . $message;
    }

    /**
     * @return string
     */
    public function displayLog()
    {
        return implode(PHP_EOL, $this->logData);
    }


    public function pruneLog()
    {
        ArrayLogging::getInstance()->logData = array();
    }
}
