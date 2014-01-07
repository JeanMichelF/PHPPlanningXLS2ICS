<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 04/03/13
 * Time: 22:59
 * To change this template use File | Settings | File Templates.
 */
namespace JMF\PHPPlanningXLS2ICS\Service;

use JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay;
use \JMF\PHPPlanningXLS2ICS\Data\PersonnalPlanning;
use \JMF\PHPPlanningXLS2ICS\Data\DayData;

class ICSOutput implements IOutputService
{
    // @todo handle those datas with config in constructor
    const CALENDAR_NAME = "Planning 115";
    const CALENDAR_DESC_1 = "Planning de travail de";
    const CALENDAR_DESC_2 = "Attention, les horaires sont donnés à titre indicatif et peuvent ne pas être corrects. De même pour les indications sur HOTELS et DETACHES.";
    const CALENDAR_PRODID = "-//JMF//PHP Planning XSL2ICS 1.0//FR";
    const EVENT_SUMMARY_1 = "Travail";
    const EVENT_SUMMARY_2 = "RTT";
    const EVENT_SUMMARY_3 = "CP";
    const EVENT_SUMMARY_4 = "CT";
    const EVENT_SUMMARY_5 = "CA";
    const EVENT_SUMMARY_6 = "RJF";
    const EVENT_SUMMARY_7 = "RH";
    const EVENT_SUMMARY_8 = "Jour Férié : pas de travail";
    const EVENT_SUMMARY_HOTELS = " - HOTELS";
    const EVENT_SUMMARY_DETACHES = " - DETACHES";
    const EVENT_SUMMARY_PROGDIS = " - PROGDIS";
    const EVENT_SUMMARY_HOTELSHIVER = " - HOTELS HIVER";
    const EVENT_DESCRIPTION_1 = "Travail";
    const EVENT_DESCRIPTION_2 = "R.T.T.";
    const EVENT_DESCRIPTION_3 = "C.P.";
    const EVENT_DESCRIPTION_4 = "C.T.";
    const EVENT_DESCRIPTION_5 = "C.A.";
    const EVENT_DESCRIPTION_6 = "R.J.F.";
    const EVENT_DESCRIPTION_7 = "R.H.";
    const EVENT_DESCRIPTION_8 = "Jour Férié : pas de travail";
    const EVENT_DESCRIPTION_HOTELS = " : attention, journée HOTELS";
    const EVENT_DESCRIPTION_DETACHES = " : attention, journée DETACHES";

    /** @var ILoggingService */
    private $loggingService;

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
     * @param PersonnalPlanning $planning
     * @param string            $path
     * @return string
     */
    public function exportPersonnalPlanning(PersonnalPlanning $planning, $path = "")
    {
        $name = $planning->name;
        $filename = 'planning' . ServiceHelper::wd_remove_accents($name) . '.ics';
        $file = new \SplFileObject($path . $filename, 'w');
        $file->fwrite($this->writeFileHeader($name));
        foreach ($planning->listOfDayData as $dayData) {
            $file->fwrite($this->writeEvent($dayData));
        }
        $file->fwrite($this->writeFileFooter());
        return $filename;
    }

    /**
     * @param $name
     * @return string
     */
    private function writeFileHeader($name)
    {
        $header = "BEGIN:VCALENDAR" . PHP_EOL .
        "VERSION:2.0" . PHP_EOL .
        "PRODID:" . self::CALENDAR_PRODID . PHP_EOL .
        "CALSCALE:GREGORIAN" . PHP_EOL .
        "METHOD:PUBLISH" . PHP_EOL .
        "X-WR-CALNAME:" . self::CALENDAR_NAME . " "  . $name . PHP_EOL .
        "X-WR-TIMEZONE:Europe/Paris" . PHP_EOL .
        "TZID:Europe/Paris" . PHP_EOL .
        "X-WR-CALDESC:" . self::CALENDAR_DESC_1 . " " . $name . ". " . self::CALENDAR_DESC_2 . PHP_EOL;
        return $header;
    }

    /**
     * @return string
     */
    private function writeFileFooter()
    {
        return "END:VCALENDAR";
    }

    /**
     * @param DayData $dayData
     * @return string
     */
    private function writeEvent(DayData $dayData)
    {
        $dateTimeStart = clone $dayData->startingHour;
        $dateTimeFinish = clone $dayData->finishingHour;
        try {
            if ($dayData->isAllDayLong) {
                $dateTimeStart->setTimezone(new \DateTimeZone('Europe/Paris'));
                $dateTimeFinish->setTimezone(new \DateTimeZone('Europe/Paris'));
                $startDate = ';VALUE=DATE:' . $dateTimeStart->format("Ymd");
                $endDate = ';VALUE=DATE:' . $dateTimeFinish->add(new \DateInterval("P1D"))->format("Ymd");
                $eventTransp = "TRANSPARENT";
            } else {
                //$dateTimeStart->setTimezone(new \DateTimeZone('UTC'));
                //$dateTimeFinish->setTimezone(new \DateTimeZone('UTC'));
                $startDate = ';TZID=Europe/Paris:' . $dateTimeStart->format('Ymd'). 'T'. $dateTimeStart->format('His');
                $endDate = ';TZID=Europe/Paris:' . $dateTimeFinish->format('Ymd'). 'T'. $dateTimeFinish->format('His');
                $eventTransp = "OPAQUE";
            }
        } catch (\Exception $e) {
            $this->loggingService->add(
                ILoggingService::ERROR,
                "Erreur lors de l'export du " .
                $dateTimeStart->format("d/m/Y") .
                " au " .
                $dateTimeFinish->format("d/m/Y") .
                " (" .
                constant('self::EVENT_SUMMARY_' . $dayData->typeOfDay) .
                ") : " .
                $e->getMessage()
            );
            return "";
        }

        $event = "BEGIN:VEVENT" . PHP_EOL .
        "DTSTART" . $startDate . PHP_EOL .
        "DTEND" . $endDate . PHP_EOL .
        "DTSTAMP;TZID=Europe/Paris:" . gmdate('Ymd').'T'. gmdate('His') . PHP_EOL .
        "UID:" . md5(uniqid(mt_rand(), true)) . '@PHPPlanningXLS2ICS.fr' . PHP_EOL .
        "CREATED;TZID=Europe/Paris:" . gmdate('Ymd').'T'. gmdate('His') . PHP_EOL .
        "DESCRIPTION:" . (TypeOfDay::SPECIFIC_DAY == $dayData->typeOfDay ?
            $dayData->specificDay :
            constant('self::EVENT_DESCRIPTION_' . $dayData->typeOfDay)) .
            ($dayData->isHotels ? self::EVENT_DESCRIPTION_HOTELS : '') .
            ($dayData->isDetaches ? self::EVENT_DESCRIPTION_DETACHES : '') . PHP_EOL .
        "LAST-MODIFIED;TZID=Europe/Paris:" . gmdate('Ymd').'T'. gmdate('His') . PHP_EOL .
        "LOCATION:" . PHP_EOL .
        "SEQUENCE:0" . PHP_EOL .
        "STATUS:CONFIRMED" . PHP_EOL .
        "SUMMARY:" . (TypeOfDay::SPECIFIC_DAY == $dayData->typeOfDay ?
            $dayData->specificDay :
            constant('self::EVENT_SUMMARY_' . $dayData->typeOfDay)) .
            ($dayData->isHotels ? self::EVENT_SUMMARY_HOTELS : '') .
            ($dayData->isDetaches ? self::EVENT_SUMMARY_DETACHES : '') .
            ($dayData->isProGDis ? self::EVENT_SUMMARY_PROGDIS : '') .
            ($dayData->isHotelsHiver ? self::EVENT_SUMMARY_HOTELSHIVER : '') .PHP_EOL .
        "TRANSP:" . $eventTransp . PHP_EOL .
        "END:VEVENT" . PHP_EOL;

        return $event;
    }
}
