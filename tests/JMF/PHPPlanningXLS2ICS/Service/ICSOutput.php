<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 12/03/13
 * Time: 19:42
 * To change this template use File | Settings | File Templates.
 */

namespace tests\JMF\PHPPlanningXLS2ICS\Service;

//Inclusion de atoum
require_once __DIR__ . '/../../../atoum/mageekguy.atoum.phar';

use \mageekguy\atoum;
use SplFileObject;
use \JMF\PHPPlanningXLS2ICS\Service;

//Class loader
require_once __DIR__."/../../../../SplClassLoader.php";

$loader = new \SplClassLoader('JMF', __DIR__.'/../../../../classes');
$loader->register();


/**
 * @namespace tests\
 */
class ICSOutput extends atoum\test
{
    /**
     *
     */
    public function testExportSampleICSFileRH()
    {
        //création de l'objet à tester
        $planning = new \JMF\PHPPlanningXLS2ICS\Data\PersonnalPlanning();
        $planning->name = "Céline";
        $dayData = new \JMF\PHPPlanningXLS2ICS\Data\DayData();
        $dayData->typeOfDay = \JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::RH;
        $dayData->isAllDayLong = true;
        $dayData->isDetaches = false;
        $dayData->isHotels = false;
        $dayData->startingHour = new \DateTime("2013-03-08");
        $dayData->finishingHour = new \DateTime("2013-03-08");
        $planning->listOfDayData[] = $dayData;
        $ICSOutputTest = new \JMF\PHPPlanningXLS2ICS\Service\ICSOutput();

        /** @var $file SplFileObject */
        $filePath = $ICSOutputTest->exportPersonnalPlanning($planning);
        $result = "";
        $file = new SplFileObject($filePath);
        if ($file->getSize() > 0) {
            while (!$file->eof()) {
                $result .= $file->fgets();
            }
        }
        $this
            ->string($result)
            ->contains("BEGIN:VCALENDAR")
            ->contains("PRODID:" . \JMF\PHPPlanningXLS2ICS\Service\ICSOutput::CALENDAR_PRODID)
            ->contains("VERSION:2.0")
            ->contains("CALSCALE:GREGORIAN")
            ->contains("METHOD:PUBLISH")
            ->contains("X-WR-CALNAME:" . \JMF\PHPPlanningXLS2ICS\Service\ICSOutput::CALENDAR_NAME . " Céline")
            ->contains("X-WR-TIMEZONE:Europe/Paris")
            ->contains("X-WR-CALDESC:" . \JMF\PHPPlanningXLS2ICS\Service\ICSOutput::CALENDAR_DESC_1 . " Céline. " . \JMF\PHPPlanningXLS2ICS\Service\ICSOutput::CALENDAR_DESC_2)
        // Test RH
            ->contains("BEGIN:VEVENT")
            ->contains("DTSTART;VALUE=DATE:20130308")
            ->contains("DTEND;VALUE=DATE:20130309")
            ->contains("DTSTAMP:")
            ->contains("UID:")
            ->contains("CREATED:")
            ->contains("DESCRIPTION:")
            ->contains("LAST-MODIFIED:")
            ->contains("LOCATION:")
            ->contains("SEQUENCE:0")
            ->contains("STATUS:CONFIRMED")
            ->contains("SUMMARY:RH")
            ->contains("TRANSP:TRANSPARENT")
            ->contains("END:VEVENT")
            ->contains("END:VCALENDAR");
    }

    /**
     *
     */
    public function testExportSampleICSFileWork()
    {
        //création de l'objet à tester
        $planning = new \JMF\PHPPlanningXLS2ICS\Data\PersonnalPlanning();
        $planning->name = "Céline";
        $dayData = new \JMF\PHPPlanningXLS2ICS\Data\DayData();
        $dayData->typeOfDay = \JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::WORK;
        $dayData->isAllDayLong = false;
        $dayData->isDetaches = false;
        $dayData->isHotels = false;
        $dayData->startingHour = new \DateTime("2013-03-08 19:12:00", new \DateTimeZone('Europe/Paris'));
        $dayData->finishingHour = new \DateTime("2013-03-08 20:24:00", new \DateTimeZone('Europe/Paris'));
        $planning->listOfDayData[] = $dayData;
        $ICSOutputTest = new \JMF\PHPPlanningXLS2ICS\Service\ICSOutput();

        /** @var $file SplFileObject */
        $filePath = $ICSOutputTest->exportPersonnalPlanning($planning);
        $result = "";
        $file = new SplFileObject($filePath);
        if ($file->getSize() > 0) {
            while (!$file->eof()) {
                $result .= $file->fgets();
            }
        }
        $this
            ->string($result)
            ->contains("BEGIN:VCALENDAR")
            ->contains("PRODID:" . \JMF\PHPPlanningXLS2ICS\Service\ICSOutput::CALENDAR_PRODID)
            ->contains("VERSION:2.0")
            ->contains("CALSCALE:GREGORIAN")
            ->contains("METHOD:PUBLISH")
            ->contains("X-WR-CALNAME:" . \JMF\PHPPlanningXLS2ICS\Service\ICSOutput::CALENDAR_NAME . " Céline")
            ->contains("X-WR-TIMEZONE:Europe/Paris")
            ->contains("X-WR-CALDESC:" . \JMF\PHPPlanningXLS2ICS\Service\ICSOutput::CALENDAR_DESC_1 . " Céline. " . \JMF\PHPPlanningXLS2ICS\Service\ICSOutput::CALENDAR_DESC_2)
        // Test Work
            ->contains("BEGIN:VEVENT")
            ->contains("DTSTART:20130308T181200Z")  // 19h12 en heure française
            ->contains("DTEND:20130308T192400Z")    // 20h24 en heure française
            ->contains("DTSTAMP:")
            ->contains("UID:")
            ->contains("CREATED:")
            ->contains("DESCRIPTION:Travail")
            ->contains("LAST-MODIFIED:")
            ->contains("LOCATION:")
            ->contains("SEQUENCE:0")
            ->contains("STATUS:CONFIRMED")
            ->contains("SUMMARY:Travail")
            ->contains("TRANSP:OPAQUE")
            ->contains("END:VEVENT")
            ->contains("END:VCALENDAR");
    }

    /**
     *
     */
    public function testExportSampleICSFileWorkHotels()
    {
        //création de l'objet à tester
        $planning = new \JMF\PHPPlanningXLS2ICS\Data\PersonnalPlanning();
        $planning->name = "Céline";
        $dayData = new \JMF\PHPPlanningXLS2ICS\Data\DayData();
        $dayData->typeOfDay = \JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::WORK;
        $dayData->isAllDayLong = false;
        $dayData->isDetaches = false;
        $dayData->isHotels = true;
        $dayData->startingHour = new \DateTime("2013-03-08 19:12:00", new \DateTimeZone('Europe/Paris'));
        $dayData->finishingHour = new \DateTime("2013-03-08 20:24:00", new \DateTimeZone('Europe/Paris'));
        $planning->listOfDayData[] = $dayData;
        $ICSOutputTest = new \JMF\PHPPlanningXLS2ICS\Service\ICSOutput();

        /** @var $file SplFileObject */
        $filePath = $ICSOutputTest->exportPersonnalPlanning($planning);
        $result = "";
        $file = new SplFileObject($filePath);
        if ($file->getSize() > 0) {
            while (!$file->eof()) {
                $result .= $file->fgets();
            }
        }
        $this
            ->string($result)
            ->contains("BEGIN:VCALENDAR")
            ->contains("PRODID:" . \JMF\PHPPlanningXLS2ICS\Service\ICSOutput::CALENDAR_PRODID)
            ->contains("VERSION:2.0")
            ->contains("CALSCALE:GREGORIAN")
            ->contains("METHOD:PUBLISH")
            ->contains("X-WR-CALNAME:" . \JMF\PHPPlanningXLS2ICS\Service\ICSOutput::CALENDAR_NAME . " Céline")
            ->contains("X-WR-TIMEZONE:Europe/Paris")
            ->contains("X-WR-CALDESC:" . \JMF\PHPPlanningXLS2ICS\Service\ICSOutput::CALENDAR_DESC_1 . " Céline. " . \JMF\PHPPlanningXLS2ICS\Service\ICSOutput::CALENDAR_DESC_2)
        // Test Work
            ->contains("BEGIN:VEVENT")
            ->contains("DTSTART:20130308T181200Z")  // 19h12 en heure française
            ->contains("DTEND:20130308T192400Z")    // 20h24 en heure française
            ->contains("DTSTAMP:")
            ->contains("UID:")
            ->contains("CREATED:")
            ->contains("DESCRIPTION:Travail : attention, journée HOTELS")
            ->contains("LAST-MODIFIED:")
            ->contains("LOCATION:")
            ->contains("SEQUENCE:0")
            ->contains("STATUS:CONFIRMED")
            ->contains("SUMMARY:Travail - HOTELS")
            ->contains("TRANSP:OPAQUE")
            ->contains("END:VEVENT")
            ->contains("END:VCALENDAR");
    }
}
