<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 11/03/13
 * Time: 20:02
 * To change this template use File | Settings | File Templates.
 */
namespace JMF\PHPPlanningXLS2ICS\Service;

interface ILoggingService
{
    const ERROR = 40;
    const WARNING  = 30;
    const INFO = 20;
    const DEBUG = 10;

    const ERROR_STRING = "error";
    const WARNING_STRING  = "warning";
    const INFO_STRING = "info";
    const DEBUG_STRING = "debug";

    /**
     * @param $level
     * @param $message
     */
    public function add($level, $message);

    /**
     * @param null $level
     * @return mixed
     */
    public function displayLog($level = null);

    /**
     * @return mixed
     */
    public function pruneLog();
}
