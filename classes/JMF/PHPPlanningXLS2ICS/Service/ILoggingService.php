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
    /**
     * @param $level
     * @param $message
     */
    public function add($level, $message);

    /**
     * @return string
     */
    public function displayLog();
}
