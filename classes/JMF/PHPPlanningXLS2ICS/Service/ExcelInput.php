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
use \JMF\PHPPlanningXLS2ICS\Data\DayData;
use \JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay;

/** Include PHPExcel */
require_once __DIR__."/../../../../lib/PHPExcel/Classes/PHPExcel.php";

class ExcelInput implements IInputService
{
    // @todo handle those datas with config in constructor
    const COLUMN_OF_NAMES = 0;
    const MAX_NUMBER_OF_WORKERS = 20;
    const FIRST_ROW_OF_WORKER = 2;
    const FIRST_COLUMN_OF_DAYS = 1;
    const FIRST_ROW_OF_DAYS = 3;
    const NUMBERS_OF_DAYS_IN_A_WEEK = 7;
    const COLUMN_OF_WEEK = 0;
    const ROW_OF_WEEK = 1;
    const CURRENT_YEAR = 2013;
    const COLOR_HOTELS = "FF0000";
    const COLOR_DETACHES = "B81A9A";


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
            $sheetTitleValue = $sheet->getTitle();
            $daysOfTheSheet = $this->getDaysOfWeek($sheetTitleValue);
            if (empty($daysOfTheSheet)) {
                $cell = $sheet->getCellByColumnAndRow(self::COLUMN_OF_WEEK, self::ROW_OF_WEEK);
                $cellWeekValue = $cell->getValue();
                $daysOfTheSheet = $this->getDaysOfWeek($cellWeekValue);
            }
            if (empty($daysOfTheSheet)) {
                /** @todo handle an error there */
            } else {
                for ($rowOfWorker = self::FIRST_ROW_OF_WORKER; $rowOfWorker < self::MAX_NUMBER_OF_WORKERS; $rowOfWorker++) {
                    $cell = $sheet->getCellByColumnAndRow(self::COLUMN_OF_NAMES, $rowOfWorker);
                    $cellNameValue = $cell->getValue();
                    if (!is_null($cellNameValue)) {
                        $personnalPlanning = new PersonnalPlanning();
                        $personnalPlanning->name = $cellNameValue;
                        $personnalPlanning->listOfDayData = $this->extractDaysOfSheetData(
                            $sheet,
                            $rowOfWorker,
                            $daysOfTheSheet
                        );

                        $this->smartAddPersonnalPlanningIntoData($personnalPlanning, $data);
                    }
                }
            }
        }

        //print_r($data);

        return $data;
    }

    /**
     * @param $sheet            \PHPExcel_Worksheet
     * @param $rowOfWorker      int
     * @param $daysOfTheSheet   array
     * @return array
     */
    private function extractDaysOfSheetData($sheet, $rowOfWorker, $daysOfTheSheet)
    {
        $listOfDayData = array();
        for ($day = 0; $day < self::NUMBERS_OF_DAYS_IN_A_WEEK; $day++) {
            $activeColumn = self::COLUMN_OF_NAMES + $day + 1;
            $cellDay = $sheet->getCellByColumnAndRow($activeColumn, $rowOfWorker);
            $dayValue = $cellDay->getValue();
            $color = $sheet->getStyleByColumnAndRow($activeColumn, $rowOfWorker)->getFill()->getStartColor()->getRGB();
            $dayData = $this->handleDayType($dayValue, $color, $daysOfTheSheet[$day]);
            $listOfDayData[] = $dayData;
        }
        return $listOfDayData;
    }

    /**
     * @param $dayValue string      Written in input
     * @param $color    string      Color of the cell
     * @param $day      \DateTime   Day of the sheet
     * @return \JMF\PHPPlanningXLS2ICS\Data\DayData
     */
    private function handleDayType($dayValue, $color, $day)
    {
        $dayData = new DayData();
        switch ($dayValue) {
            case "RH":
                $dayData = $this->setNotWorkingDay(TypeOfDay::RH, $day);
                break;
            case "CT":
                $dayData = $this->setNotWorkingDay(TypeOfDay::CT, $day);
                break;
            case "RTT":
                $dayData = $this->setNotWorkingDay(TypeOfDay::RTT, $day);
                break;
            case "FERIE":
                $dayData = $this->setNotWorkingDay(TypeOfDay::FERIE, $day);
                break;
            /** Could happen... maybe */
            case "CA":
                $dayData = $this->setNotWorkingDay(TypeOfDay::CA, $day);
                break;
            case "CP":
                $dayData = $this->setNotWorkingDay(TypeOfDay::CP, $day);
                break;
            case "RH":
                $dayData = $this->setNotWorkingDay(TypeOfDay::RH, $day);
                break;
            case "RJF":
                $dayData = $this->setNotWorkingDay(TypeOfDay::RJF, $day);
                break;
            default:
                $matches = explode("-", $dayValue);
                if (count($matches) > 1) {
                    $startTime = strtoupper($matches[0]);
                    $finishTime = strtoupper($matches[1]);
                    $dayData = $this->setWorkingDay($day, $startTime, $finishTime, $color);
                } else {
                    // @todo log the error : find a way to get day & worker name
                }
                break;
        }
        return $dayData;
    }

    /**
     * @param $typeOfDay    int         Day to update
     * @param $day          \DateTime   Day of the sheet
     * @return DayData
     */
    private function setNotWorkingDay($typeOfDay, $day)
    {
        $dayData = new DayData();
        $dayData->typeOfDay = $typeOfDay;
        $dayData->isAllDayLong = true;
        $dayData->startingHour = $day;
        $dayData->finishingHour = $day;
        $dayData->isHotels = false;
        $dayData->isDetaches = false;
        return $dayData;
    }

    /**
     * @param $day          \DateTime   Day of the sheet
     * @param $startTime    string      Starting hour
     * @param $finishTime   string      Ending hour
     * @param $color        string      Color of the cell
     * @return DayData
     */
    private function setWorkingDay($day, $startTime, $finishTime, $color)
    {
        $dayData = new DayData();
        $dayData->typeOfDay = TypeOfDay::WORK;
        $dayData->isAllDayLong = false;
        $start = clone $day;
        $end = clone $day;
        $dayData->startingHour = $start->add(new \DateInterval('PT' . $startTime . 'M'));
        $dayData->finishingHour = $end->add(new \DateInterval('PT' . $finishTime . 'M'));
        /** @todo handle an array of colors (maybe) */
        if (self::COLOR_HOTELS == $color) {
            $dayData->isHotels = true;
        }
        if (self::COLOR_DETACHES == $color) {
            $dayData->isDetaches = true;
        }
        return $dayData;
    }
    /**
     * @param $cellWeekValue    string
     * @return array
     */
    private function getDaysOfWeek($cellWeekValue)
    {
        $daysOfTheSheet = array();

        $matches = explode(" ", $cellWeekValue);
        if (count($matches) > 2) {
            $month = $matches[count($matches) - 1];
            $day = $matches[count($matches) - 2];
            $reverseMonths = array_flip($this->months);
            $monthOfLastDayOfWeek = $reverseMonths[$month];
            /** @todo find a better way to handle year */
            $referenceDay =
                new \DateTime(
                    self::CURRENT_YEAR . '-' . ($monthOfLastDayOfWeek + 1) . '-' . $day
                    , new \DateTimeZone('Europe/Paris')
                );
            for ($i = 0; $i < self::NUMBERS_OF_DAYS_IN_A_WEEK; $i++) {
                $date = clone $referenceDay;
                $toSubstract = self::NUMBERS_OF_DAYS_IN_A_WEEK - ($i + 1);
                $date->sub(new \DateInterval('P' . $toSubstract . 'D'));
                $daysOfTheSheet[$i] = $date;
            }
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
