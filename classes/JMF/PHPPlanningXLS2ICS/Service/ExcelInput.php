<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 04/03/13
 * Time: 22:58
 * To change this template use File | Settings | File Templates.
 */
namespace JMF\PHPPlanningXLS2ICS\Service;

use PHPExcel;
use PHPExcel_IOFactory;
use \JMF\PHPPlanningXLS2ICS\Data;

/** Include PHPExcel */
require_once __DIR__."/../../../../lib/PHPExcel/Classes/PHPExcel.php";

class ExcelInput implements IInputService
{
    /** @var null|PHPExcel */
    private $objPHPExcel = null;

    public function openFile($path = "") {
        if (is_null($this->objPHPExcel)) {
            $this->objPHPExcel = PHPExcel_IOFactory::load($path);
        } else {
            throw new \Exception("Currently handling one file, close it first");
        }
    }

    public function closeFile() {
        $this->objPHPExcel = null;
    }

    /**
     * @return null|\PHPExcel
     */
    public function getFile()
    {
        return $this->objPHPExcel;
    }

    /**
     * @return mixed
     */
    public function extractData()
    {
        return new Data\Planning();
    }
}
