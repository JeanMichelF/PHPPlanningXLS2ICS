<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 09/03/13
 * Time: 15:52
 * To change this template use File | Settings | File Templates.
 */
namespace JMF\PHPPlanningXLS2ICS\Service;

use \JMF\PHPPlanningXLS2ICS\Data\Planning;

interface IInputService
{
    /**
     * @param string $path
     * @return mixed
     */
    public function openFile($path = "");

    /**
     * @return mixed
     */
    public function closeFile();

    /**
     * @return Planning
     */
    public function extractData();
}
