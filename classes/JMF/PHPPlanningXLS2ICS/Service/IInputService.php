<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 09/03/13
 * Time: 15:52
 * To change this template use File | Settings | File Templates.
 */
namespace JMF\PHPPlanningXLS2ICS\Service;

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
     * @return mixed
     */
    public function extractData();
}
