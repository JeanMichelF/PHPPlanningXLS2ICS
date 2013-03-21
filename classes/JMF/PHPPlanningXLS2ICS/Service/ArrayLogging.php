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
        $logData = new \stdClass();
        $reflect = new \ReflectionClass($this);
        $constants = $reflect->getConstants();
        $constantName = "ERROR";
        foreach ($constants as $name => $value) {
            if ($value == $level)
            {
                $constantName = $name;
                break;
            }
        }
        $logData->level = $level;
        $logData->message =
            "[" .
            $timestamp->format("d/m/Y H:i:s") .
            "] - " .
            constant('self::' .
            $constantName .
            '_STRING') .
            " - " .
            $message;
        ArrayLogging::getInstance()->logData[] = $logData;

    }

    /**
     * @param null $level
     * @return mixed|string
     */
    public function displayLog($level = null)
    {
        $filteredArray = array();
        foreach ($this->logData as $logData) {
            if (is_null($level) || (!is_null($level) && $logData->level >= $level)) {
                $filteredArray[] = $logData->message;
            }
        }

        return implode(PHP_EOL, $filteredArray);
    }


    public function pruneLog()
    {
        ArrayLogging::getInstance()->logData = array();
    }
}
