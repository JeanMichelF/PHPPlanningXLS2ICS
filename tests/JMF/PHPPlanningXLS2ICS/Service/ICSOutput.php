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
     * @tags active
     */
    public function testExportSampleICSFile()
    {
        //création de l'objet à tester
        $planning = new \JMF\PHPPlanningXLS2ICS\Data\PersonnalPlanning();
        $planning->name = "Planning 115 Céline";
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
            ->contains("BEGIN:VCALENDAR
            PRODID:-//Google Inc//Google Calendar 70.9054//EN
            VERSION:2.0
            CALSCALE:GREGORIAN
            METHOD:PUBLISH
            X-WR-CALNAME:Planning 115 Céline
            X-WR-TIMEZONE:Europe/Paris
            X-WR-CALDESC:
            BEGIN:VEVENT
            DTSTART;VALUE=DATE:20130308
            DTEND;VALUE=DATE:20130309
            DTSTAMP:20130311T184907Z
            UID:ia4pj7t0joo9bvrkfhnb9b2m2g@google.com
            CREATED:20130311T184520Z
            DESCRIPTION:
            LAST-MODIFIED:20130311T184520Z
            LOCATION:
            SEQUENCE:0
            STATUS:CONFIRMED
            SUMMARY:RH
            TRANSP:TRANSPARENT
            END:VEVENT
            END:VCALENDAR");
    }
}
