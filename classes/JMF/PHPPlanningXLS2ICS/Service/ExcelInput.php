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
use \JMF\PHPPlanningXLS2ICS\Data\Planning;
use \JMF\PHPPlanningXLS2ICS\Data\PersonnalPlanning;

/** Include PHPExcel */
require_once __DIR__."/../../../../lib/PHPExcel/Classes/PHPExcel.php";

class ExcelInput implements IInputService
{
    const COLUMN_OF_NAMES = 0;
    const MAX_NUMBER_OF_WORKERS = 20;
    const FIRST_ROW_OF_WORKER = 2;
    const FIRST_COLUMN_OF_DAYS = 1;
    const FIRST_ROW_OF_DAYS = 3;
    const NUMBERS_OF_DAYS_IN_A_WEEK = 7;
    const COLUMN_OF_WEEK = 0;
    const ROW_OF_WEEK = 1;
    const CURRENT_YEAR = 2003;


    /** @var null|PHPExcel */
    private $objPHPExcel = null;

    private $months = array(
        "JANVIER",
        "FEVRIER",
        "MARS",
        "AVRIL",
        "MAI",
        "JUIN",
        "Juillet",
        "AOUT",
        "SEPTEMBRE",
        "OCTOBRE",
        "NOVEMBRE",
        "DECEMBRE"
    );


    /**
     * @param string $path
     * @return mixed|void
     * @throws \Exception
     */
    public function openFile($path = "") {
        if (is_null($this->objPHPExcel)) {
            $this->objPHPExcel = PHPExcel_IOFactory::load($path);
        } else {
            throw new \Exception("Currently handling one file, close it first");
        }
    }

    /**
     * @return mixed|void
     */
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
     * @return Planning
     */
    public function extractData()
    {
        $data = new Planning();

        /** @var \PHPExcel_Worksheet $sheet */
        foreach($this->objPHPExcel->getAllSheets() as $sheet) {
            /** @todo find a better way to grab days */
            $cell = $sheet->getCellByColumnAndRow(self::COLUMN_OF_WEEK, self::ROW_OF_WEEK);
            $cellWeekValue = $cell->getValue();
            $daysOfTheSheet = $this->getDaysOfWeek($cellWeekValue);

            for ($i = self::FIRST_ROW_OF_WORKER; $i < self::MAX_NUMBER_OF_WORKERS; $i++) {
                $cell = $sheet->getCellByColumnAndRow(self::COLUMN_OF_NAMES, $i);
                $cellNameValue = $cell->getValue();
                if (!is_null($cellNameValue)) {
                    $personnalPlanning = new PersonnalPlanning();
                    $personnalPlanning->name = $cellNameValue;
                    $personnalPlanning->listOfDayData = $daysOfTheSheet;
                    $this->smartAddPersonnalPlanningIntoData($personnalPlanning, $data);
                }
            }
        }

//        print_r($data);

        return $data;
    }

    private function getDaysOfWeek($cellWeekValue)
    {
        $matches = explode(" ", $cellWeekValue);

        $daysOfTheSheet = array();
        $month = $matches[count($matches) - 1];
        $day = $matches[count($matches) - 2];
        $reverseMonths = array_flip($this->months);
        $monthOfLastDayOfWeek = $reverseMonths[$month];
        /** @todo find a better way to handle year */
        $daysOfTheSheet[self::NUMBERS_OF_DAYS_IN_A_WEEK - 1] =
            mktime(0, 0, 0, $monthOfLastDayOfWeek + 1, $day, self::CURRENT_YEAR);
        for ($i = 0; $i < 6; $i++) {
            $daysOfTheSheet[$i] = strtotime(
                '-' . self::NUMBERS_OF_DAYS_IN_A_WEEK + ($i + 1) . ' day',
                $daysOfTheSheet[self::NUMBERS_OF_DAYS_IN_A_WEEK - 1]
            );
        }
        return $daysOfTheSheet;
    }

    /**
     * @param $personnalPlanning    PersonnalPlanning
     * @param $data                 Planning
     */
    private function smartAddPersonnalPlanningIntoData($personnalPlanning, $data)
    {
        $cellNameValue = $personnalPlanning->name;
        if (array_key_exists($cellNameValue, $data->listOfPersonnalPlanning)) {
            $data->listOfPersonnalPlanning[$cellNameValue]->listOfDayData = array_merge(
                $data->listOfPersonnalPlanning[$cellNameValue]->listOfDayData,
                $personnalPlanning->listOfDayData
            );
        } else {
            $data->listOfPersonnalPlanning[$cellNameValue] = $personnalPlanning;
        }
    }
}
