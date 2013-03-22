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
    /** @var ILoggingService */
    private $loggingService;

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
     * @param null $loggingService
     */
    function __construct($loggingService = null)
    {
        if (is_null($loggingService)) {
            $this->loggingService = ArrayLogging::getInstance();
        } else {
            $this->loggingService = $loggingService;
        }
    }

    /**
     * @param string $path
     * @return mixed|void
     * @throws \PHPExcel_Reader_Exception
     * @throws \Exception
     */
    public function openFile($path = "") {
        if (is_null($this->objPHPExcel)) {
            try {
                $this->objPHPExcel = PHPExcel_IOFactory::load($path);
            } catch(\PHPExcel_Reader_Exception $e) {
                $this->loggingService->add(
                    ILoggingService::ERROR,
                    "Impossible d'ouvrir le fichier " . $path . " : " . $e->getMessage()
                );
                throw $e;
            }
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
            $sheetTitleValue = trim($sheet->getTitle());
            $daysOfTheSheet = $this->getDaysOfWeek($sheetTitleValue);
            if (empty($daysOfTheSheet)) {
                $cell = $sheet->getCellByColumnAndRow(self::COLUMN_OF_WEEK, self::ROW_OF_WEEK);
                $cellWeekValue = trim($cell->getValue());
                $daysOfTheSheet = $this->getDaysOfWeek($cellWeekValue);
            }
            if (empty($daysOfTheSheet)) {
                $this->loggingService->add(
                    ILoggingService::ERROR,
                    "Impossible de trouver la semaine correspondant à la feuille " . $sheetTitleValue
                );
            } else {
                for ($rowOfWorker = self::FIRST_ROW_OF_WORKER; $rowOfWorker < self::MAX_NUMBER_OF_WORKERS; $rowOfWorker++) {
                    $cell = $sheet->getCellByColumnAndRow(self::COLUMN_OF_NAMES, $rowOfWorker);
                    $cellNameValue = trim($cell->getValue());
                    if (!empty($cellNameValue) && ($cellNameValue != $sheetTitleValue)) {
                        $personnalPlanning = new PersonnalPlanning();
                        $personnalPlanning->name = $cellNameValue;
                        $personnalPlanning->listOfDayData = $this->extractDaysOfSheetData(
                            $sheet,
                            $rowOfWorker,
                            $daysOfTheSheet,
                            $cellNameValue
                        );

                        $this->smartAddPersonnalPlanningIntoData($personnalPlanning, $data);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param $sheet            \PHPExcel_Worksheet
     * @param $rowOfWorker      int
     * @param $daysOfTheSheet   array
     * @param $name             string              Only for logging purpose
     * @return array
     */
    private function extractDaysOfSheetData($sheet, $rowOfWorker, $daysOfTheSheet, $name)
    {
        $listOfDayData = array();
        for ($day = 0; $day < self::NUMBERS_OF_DAYS_IN_A_WEEK; $day++) {
            $activeColumn = self::COLUMN_OF_NAMES + $day + 1;
            $cellDay = $sheet->getCellByColumnAndRow($activeColumn, $rowOfWorker);
            $dayValue = trim($cellDay->getValue());
            $color = $sheet->getStyleByColumnAndRow($activeColumn, $rowOfWorker)->getFill()->getStartColor()->getRGB();
            $dayData = $this->handleDayType($dayValue, $color, $daysOfTheSheet[$day], $name);
            if (!is_null($dayData)) {
                $listOfDayData[] = $dayData;
            }
        }
        return $listOfDayData;
    }

    /**
     * @param $dayValue string      Written in input
     * @param $color    string      Color of the cell
     * @param $day      \DateTime   Day of the sheet
     * @param $name     string      Only for logging purpose
     * @return \JMF\PHPPlanningXLS2ICS\Data\DayData
     */
    private function handleDayType($dayValue, $color, $day, $name)
    {
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
                    $dayData = $this->setWorkingDay($day, $startTime, $finishTime, $color, $name);
                } else {
                    $dayData = null;
                    $this->loggingService->add(
                        ILoggingService::WARNING,
                        "Impossible de trouver l'activité de " .
                        $name .
                        " pour le " .
                        $day->format("d/m/Y")
                    );
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
     * @param $name         string      Only for logging purpose
     * @return DayData
     */
    private function setWorkingDay($day, $startTime, $finishTime, $color, $name)
    {
        $dayData = new DayData();
        $dayData->typeOfDay = TypeOfDay::WORK;
        $dayData->isAllDayLong = false;
        $start = clone $day;
        $end = clone $day;
        try {
            $dayData->startingHour = $start->add(new \DateInterval('PT' . $startTime . 'M'));
            $dayData->finishingHour = $end->add(new \DateInterval('PT' . $finishTime . 'M'));
        } catch (\Exception $e) {
            $this->loggingService->add(
                ILoggingService::WARNING,
                "Impossible de trouver les heures correctes de travail de " .
                $name .
                " pour le " .
                $day->format("d/m/Y") .
                " : " .
                $e->getMessage()
            );
        }
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
            if (array_key_exists($month, $reverseMonths)) {
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
                    try {
                        $date->sub(new \DateInterval('P' . $toSubstract . 'D'));
                    } catch (\Exception $e) {
                        $this->loggingService->add(
                            ILoggingService::ERROR,
                            "Impossible de trouver le jour correspondant à " .
                            $date->format("d/m/Y") .
                            " - " .
                            self::NUMBERS_OF_DAYS_IN_A_WEEK - ($i + 1) .
                            " : " .
                            $e->getMessage()
                        );
                        return null;
                    }
                    $daysOfTheSheet[$i] = $date;
                }
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
