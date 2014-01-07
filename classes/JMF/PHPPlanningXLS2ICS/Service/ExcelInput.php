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
    // Due to merging cells, we have to put a great number here...
    // @todo handle this with X empty cells in a row ?
    const MAX_NUMBER_OF_WORKERS = 40;
    const FIRST_ROW_OF_WORKER = 2;
    const FIRST_COLUMN_OF_DAYS = 1;
    const FIRST_ROW_OF_DAYS = 3;
    const NUMBERS_OF_DAYS_IN_A_WEEK = 7;
    const COLUMN_OF_WEEK = 0;
    const ROW_OF_WEEK = 1;
    // We have to make one...
    const BASE_YEAR = 2013;
    const MAX_YEARS_BEFORE_USING_THE_SAME_CALENDAR_IS_VALID = 28;
    const COLOR_HOTELS = "FF0000";
    const COLOR_DETACHES = "B81A9A";
    // Because one color for one thing is too mainstream
    const COLOR_DETACHES2 = "D410C6";
    const COLOR_PROGDIS = "00B050";
    const COLOR_HOTELHIVER = "FFC000";
    const NON_WORKER_TEXT = "Référent";


    /** @var null|PHPExcel */
    private $objPHPExcel = null;
    /** @var ILoggingService */
    private $loggingService;

    /** @var int $currentYear */
    private $currentYear = null;

    private $months = array(
        "JANVIER",
        "FEVRIER",
        "MARS",
        "AVRIL",
        "MAI",
        "JUIN",
        "JUILLET",
        "AOUT",
        "SEPTEMBRE",
        "OCTOBRE",
        "NOVEMBRE",
        "DECEMBRE"
    );

    /** @var  Array of merged cells */
    private $listOfMergedCells;

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
            $sheetTitleValue = strtoupper($this->wd_remove_accents(trim($sheet->getTitle())));
            // Initialization of the current year (maybe not BASE_YEAR ?)
            if (null == $this->currentYear) {
                $firstDayCell = $sheet->getCellByColumnAndRow(
                    self::COLUMN_OF_NAMES + self::NUMBERS_OF_DAYS_IN_A_WEEK,
                    self::FIRST_ROW_OF_WORKER + 1
                );
                $firstDayCellValue = trim($firstDayCell->getValue());
                $monthOfTheLastDayOfTheFirstWeek = $this->getMonthOfTheLastDayOfWeek($sheetTitleValue);
                if (null == $monthOfTheLastDayOfTheFirstWeek) {
                    $cell = $sheet->getCellByColumnAndRow(self::COLUMN_OF_WEEK, self::ROW_OF_WEEK);
                    $cellWeekValue = strtoupper($this->wd_remove_accents(trim($cell->getValue())));
                    $monthOfTheLastDayOfTheFirstWeek = $this->getMonthOfTheLastDayOfWeek($cellWeekValue);
                }
                $this->setCurrentYear($firstDayCellValue, $monthOfTheLastDayOfTheFirstWeek);
            }
            $daysOfTheSheet = $this->getDaysOfWeek($sheetTitleValue);
            if (empty($daysOfTheSheet)) {
                $cell = $sheet->getCellByColumnAndRow(self::COLUMN_OF_WEEK, self::ROW_OF_WEEK);
                $cellWeekValue = strtoupper($this->wd_remove_accents(trim($cell->getValue())));
                $daysOfTheSheet = $this->getDaysOfWeek($cellWeekValue);
            }
            if (empty($daysOfTheSheet)) {
                $this->loggingService->add(
                    ILoggingService::ERROR,
                    "Impossible de trouver la semaine correspondant à la feuille " . $sheetTitleValue
                );
            } else {
                $this->listOfMergedCells = $sheet->getMergeCells();
                for ($rowOfWorker = self::FIRST_ROW_OF_WORKER; $rowOfWorker < self::MAX_NUMBER_OF_WORKERS; $rowOfWorker++) {
                    $cell = $sheet->getCellByColumnAndRow(self::COLUMN_OF_NAMES, $rowOfWorker);
                    // If the cell is merged, we have to keep the previous name...
                    $tmpCellValue = trim($cell->getValue());
                    if (!$this->isCellMerged($cell) || ($this->isCellMerged($cell) && !empty($tmpCellValue))) {
                        $cellNameValue = $tmpCellValue;
                    }
                    if (!empty($cellNameValue) && ($cellNameValue != $sheetTitleValue) && strpos($cellNameValue, self::NON_WORKER_TEXT) === false) {
                        $personnalPlanning = new PersonnalPlanning();
                        $personnalPlanning->name = $this->sanitizeName($cellNameValue);
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
            // If the cell is merged, we don't treat the empty things...
            // Otherwise, it's a bug in the Excel planning, so it should return a warning
            if (!$this->isCellMerged($cellDay) || ($this->isCellMerged($cellDay) && !empty($dayValue))) {
                $color = $sheet->getStyleByColumnAndRow($activeColumn, $rowOfWorker)->getFill()->getStartColor()->getRGB();
                $dayData = $this->handleDayType($dayValue, $color, $daysOfTheSheet[$day], $name);
                if (!is_null($dayData)) {
                    $listOfDayData[] = $dayData;
                }
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
            case "RJF":
                $dayData = $this->setNotWorkingDay(TypeOfDay::RJF, $day);
                break;
            default:
                $matches = explode("-", $dayValue);
                if (count($matches) > 1 && strlen($dayValue) > 1) {
                    if (strpos($matches[0], ' ')) {
                        $startTime = strtoupper(substr($matches[0],0,strpos($matches[0], ' ')));
                    } else {
                        $startTime = strtoupper($matches[0]);
                    }
                    if (strpos($matches[1], ' ')) {
                        $finishTime = strtoupper(substr($matches[1],0,strpos($matches[1], ' ')));
                    } else {
                        $finishTime = strtoupper($matches[1]);
                    }
                    $dayData = $this->setWorkingDay($day, $startTime, $finishTime, $color, $name);
                } else {
                    if (!empty($dayValue)) {
                        $dayData = $this->setSpecificDay(TypeOfDay::SPECIFIC_DAY, $day, $dayValue);
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
        $dayData->specificDay = "";
        return $dayData;
    }

    /**
     * @param $typeOfDay    int         Day to update
     * @param $day          \DateTime   Day of the sheet
     * @param $dayValue     string      Value of the string in the Excel Cell
     * @return DayData
     */
    private function setSpecificDay($typeOfDay, $day, $dayValue)
    {
        $dayData = self::setNotWorkingDay($typeOfDay, $day);
        $dayData->specificDay = $dayValue;
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
            // Si l'heure renseignée n'a pas de minutes, il faut les ajouter...
            if (substr($startTime, strlen($startTime)-1) != "0") {
                $startTime .= "00";
            }
            if (substr($finishTime, strlen($finishTime)-1) != "0") {
                $finishTime .= "00";
            }
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
            $dayData->startingHour = $start;
            $dayData->finishingHour = $end;
        }
        /** @todo handle an array of colors (maybe) */
        if (self::COLOR_HOTELS == $color) {
            $dayData->isHotels = true;
        }
        if (self::COLOR_DETACHES == $color || self::COLOR_DETACHES2 == $color) {
            $dayData->isDetaches = true;
        }
        if (self::COLOR_PROGDIS == $color) {
            $dayData->isProGDis = true;
        }
        if (self::COLOR_HOTELHIVER == $color) {
            $dayData->isHotelsHiver = true;
        }
        $dayData->specificDay = "";
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
            // Sometimes year is in the cell value, sometimes not...
            if (is_numeric($month)) {
                $month = $matches[count($matches) - 2];
                $day = $matches[count($matches) - 3];
            }
            // Just to be sure the day is in fact a day not some letters...
            if (is_numeric($day)) {
                $reverseMonths = array_flip($this->months);
                if (array_key_exists($month, $reverseMonths)) {
                    $monthOfLastDayOfWeek = $reverseMonths[$month];
                    if ($monthOfLastDayOfWeek == 0 && $day < self::NUMBERS_OF_DAYS_IN_A_WEEK) {
                        $this->currentYear++;
                    }
                    $referenceDay =
                        new \DateTime(
                            $this->currentYear . '-' . ($monthOfLastDayOfWeek + 1) . '-' . $day
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

    /**
     * @param $cellWeekValue    string
     * @return array
     */
    private function getMonthOfTheLastDayOfWeek($cellWeekValue)
    {
        $monthOfLastDayOfWeek = null;
        $matches = explode(" ", $cellWeekValue);
        if (count($matches) > 2) {
            $month = $matches[count($matches) - 1];
            // Sometimes year is in the cell value, sometimes not...
            if (is_numeric($month)) {
                $month = $matches[count($matches) - 2];
            }
            $reverseMonths = array_flip($this->months);
            if (array_key_exists($month, $reverseMonths)) {
                $monthOfLastDayOfWeek = $reverseMonths[$month];
            }
        }
        return $monthOfLastDayOfWeek;
    }

    /**
     * We have to find which year it is from the name of the day and the month
     * We know that BASE_YEAR is 2013 AND that every 28 years, the calendar is being the same again
     * cf. http://progkor.inf.elte.hu/200405.1/calender_faq.htm
     * @param $lastDateOfTheWeek
     * @param $monthOfTheLastDayOfTheFirstWeek
     */
    private function setCurrentYear($lastDateOfTheWeek, $monthOfTheLastDayOfTheFirstWeek)
    {
        $days = array('dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi');
        try {
            $matches = explode(" ", trim($lastDateOfTheWeek));
            if (count($matches) == 2) {
                $dayReference = $matches[count($matches) - 1];
                $dayNameReference = $matches[count($matches) - 2];
                $i = 0;
                do {
                    $testYear = self::BASE_YEAR + $i;
                    $testedDate = new \DateTime(
                        $testYear . '-' . ($monthOfTheLastDayOfTheFirstWeek + 1) . '-' . $dayReference
                        , new \DateTimeZone('Europe/Paris')
                    );
                    $dayNumber = $testedDate->format("w");
                    $i++;
                } while (
                    (strcasecmp($dayNameReference, $days[$dayNumber]) !== 0)
                        &&
                    ($i < self::MAX_YEARS_BEFORE_USING_THE_SAME_CALENDAR_IS_VALID)
                );
                // Good ! We've got the year of the last day of the week.
                // Now let's have the year of the FIRST day of the week.
                $referenceDay =
                    new \DateTime(
                        $testYear . '-' . ($monthOfTheLastDayOfTheFirstWeek + 1) . '-' . $dayReference
                        , new \DateTimeZone('Europe/Paris')
                    );
                $date = clone $referenceDay;
                $date->sub(new \DateInterval('P' . (self::NUMBERS_OF_DAYS_IN_A_WEEK - 1) . 'D'));
                // And... we're done !
                $this->currentYear = $date->format('Y');
            } else {
                throw new \Exception("'". $lastDateOfTheWeek . "' n'a pas le format attendu");
            }
        } catch (\Exception $e) {
            $this->loggingService->add(
                ILoggingService::INFO,
                "Impossible de trouver l'année de référence à partir de " .
                $lastDateOfTheWeek .
                " " .
                $monthOfTheLastDayOfTheFirstWeek .
                " " .
                $e->getMessage()
            );
            $this->currentYear = self::BASE_YEAR;
        }
    }

    /**
     * @param $name string
     * @return string
     */
    private function sanitizeName($name) {
        $name = filter_var($name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
        $name = preg_replace("/[,]+/", "", $name);
        return $name;
    }


    /**
     * @param string $str
     * @param string $charset
     * @return string
     */
    private function wd_remove_accents($str, $charset='utf-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        return $str;
    }

    /**
     * Return true is the cell is merged in this worksheet
     * @param \PHPExcel_Cell $cell
     * @return bool
     */
    private function isCellMerged(\PHPExcel_Cell $cell) {
        $bool = false;
        foreach ($this->listOfMergedCells as $cells) {
            if ($cell->isInRange($cells)) {
                $bool = true;
                break;
            }
        }
        return $bool;
    }
}
