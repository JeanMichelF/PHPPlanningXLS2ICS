<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 04/03/13
 * Time: 19:28
 * To change this template use File | Settings | File Templates.
 */

namespace tests\JMF\PHPPlanningXLS2ICS\Service;

//Inclusion de atoum dans toutes les classes de tests
require_once __DIR__ . '/../../../atoum/mageekguy.atoum.phar';

use \mageekguy\atoum;
use \JMF\PHPPlanningXLS2ICS\Service;
use \JMF\PHPPlanningXLS2ICS\Data;

//Class loader
require_once __DIR__."/../../../../SplClassLoader.php";

$loader = new \SplClassLoader('JMF', __DIR__.'/../../../../classes');
$loader->register();


/**
 * @namespace tests\
 */
class ExcelInput extends atoum\test
{

    public $testFile = '/../../../fixtures/test.xls';

    /**
     *
     */
    public function testOpenFileKO() {
        //création de l'objet à tester
        $excelInputTest = new \JMF\PHPPlanningXLS2ICS\Service\ExcelInput();

        $this
            ->exception(
            function() use($excelInputTest) {
                // ce code lève une exception: throw new \Exception;
                $excelInputTest->openFile('badfile');
            }
        );

        $this
            ->variable($excelInputTest->getFile())
            ->isNull();
    }

    /**
     *
     */
    public function testOpenFileOK() {
        //création de l'objet à tester
        $excelInputTest = new \JMF\PHPPlanningXLS2ICS\Service\ExcelInput();


        $excelInputTest->openFile(__DIR__ . $this->testFile);

        $this
            ->object($excelInputTest->getFile())
            ->isInstanceOf('\PHPExcel');
    }

    /**
     *
     */
    public function testDoubleOpenFile() {
        //création de l'objet à tester
        $excelInputTest = new \JMF\PHPPlanningXLS2ICS\Service\ExcelInput();

        $excelInputTest->openFile(__DIR__ . $this->testFile);

        $this
            ->exception(
            function() use($excelInputTest) {
                // ce code lève une exception: throw new \Exception;
                $excelInputTest->openFile('badfile');
            }
        );

        $this
            ->object($excelInputTest->getFile())
            ->isInstanceOf('\PHPExcel');
    }

    /**
     *
     */
    public function testCloseFile() {
        //création de l'objet à tester
        $excelInputTest = new \JMF\PHPPlanningXLS2ICS\Service\ExcelInput();

        $excelInputTest->openFile(__DIR__ . $this->testFile);

        $this
            ->object($excelInputTest->getFile())
            ->isInstanceOf('\PHPExcel');

        $excelInputTest->closeFile();

        $this
            ->variable($excelInputTest->getFile())
            ->isNull();
    }

    /**
     * @tags active
     */
    public function testGetNumberOfPersonnalPlannings() {
        //création de l'objet à tester
        $excelInputTest = new \JMF\PHPPlanningXLS2ICS\Service\ExcelInput();

        $excelInputTest->openFile(__DIR__ . $this->testFile);

        $dataLoaded = $excelInputTest->extractData();

        $this
            ->object($dataLoaded)
            ->isInstanceOf('\JMF\PHPPlanningXLS2ICS\Data\Planning');

        $this
            ->integer(count($dataLoaded->listOfPersonnalPlanning))
            ->isEqualTo(9);

        foreach ($dataLoaded->listOfPersonnalPlanning as $personnalPlanning) {
            $this
                ->integer(count($personnalPlanning->listOfDayData))
                ->isEqualTo(21);
        }

        $planningJulie = $dataLoaded->listOfPersonnalPlanning["Julie"];
        $mondayJulie = $planningJulie->listOfDayData[0];
        $this
            ->integer($mondayJulie->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::RH);
        $this
            ->boolean($mondayJulie->isAllDayLong)
            ->isTrue();
        $this
            ->dateTime($mondayJulie->startingHour)
            ->hasDateAndTime('2013', '03', '04', '00', '00', '00');
        $this
            ->dateTime($mondayJulie->finishingHour)
            ->hasDateAndTime('2013', '03', '04', '00', '00', '00');
        $tuesdayJulie = $planningJulie->listOfDayData[1];
        $this
            ->integer($tuesdayJulie->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::WORK);
        $this
            ->boolean($tuesdayJulie->isAllDayLong)
            ->isFalse();
        $this
            ->dateTime($tuesdayJulie->startingHour)
            ->hasDateAndTime('2013', '03', '05', '10', '00', '00');
        $this
            ->dateTime($tuesdayJulie->finishingHour)
            ->hasDateAndTime('2013', '03', '05', '19', '00', '00');
        $wednesdayJulie = $planningJulie->listOfDayData[2];
        $this
            ->integer($wednesdayJulie->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::CT);
        $this
            ->boolean($wednesdayJulie->isAllDayLong)
            ->isTrue();
        $this
            ->dateTime($wednesdayJulie->startingHour)
            ->hasDateAndTime('2013', '03', '06', '00', '00', '00');
        $this
            ->dateTime($wednesdayJulie->finishingHour)
            ->hasDateAndTime('2013', '03', '06', '00', '00', '00');
        $thursdayJulie = $planningJulie->listOfDayData[3];
        $this
            ->integer($thursdayJulie->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::CT);
        $this
            ->boolean($thursdayJulie->isAllDayLong)
            ->isTrue();
        $this
            ->dateTime($thursdayJulie->startingHour)
            ->hasDateAndTime('2013', '03', '07', '00', '00', '00');
        $this
            ->dateTime($thursdayJulie->finishingHour)
            ->hasDateAndTime('2013', '03', '07', '00', '00', '00');
        $fridayJulie = $planningJulie->listOfDayData[4];
        $this
            ->integer($fridayJulie->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::CT);
        $this
            ->boolean($fridayJulie->isAllDayLong)
            ->isTrue();
        $this
            ->dateTime($fridayJulie->startingHour)
            ->hasDateAndTime('2013', '03', '08', '00', '00', '00');
        $this
            ->dateTime($fridayJulie->finishingHour)
            ->hasDateAndTime('2013', '03', '08', '00', '00', '00');
        $saturdayJulie = $planningJulie->listOfDayData[5];
        $this
            ->integer($saturdayJulie->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::RH);
        $this
            ->boolean($saturdayJulie->isAllDayLong)
            ->isTrue();
        $this
            ->dateTime($saturdayJulie->startingHour)
            ->hasDateAndTime('2013', '03', '09', '00', '00', '00');
        $this
            ->dateTime($saturdayJulie->finishingHour)
            ->hasDateAndTime('2013', '03', '09', '00', '00', '00');
        $sundayJulie = $planningJulie->listOfDayData[6];
        $this
            ->integer($sundayJulie->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::RH);
        $this
            ->boolean($sundayJulie->isAllDayLong)
            ->isTrue();
        $this
            ->dateTime($sundayJulie->startingHour)
            ->hasDate('2013', '03', '10', '00', '00', '00');
        $this
            ->dateTime($sundayJulie->finishingHour)
            ->hasDate('2013', '03', '10', '00', '00', '00');
        $nextTuesdayJulie = $planningJulie->listOfDayData[8];
        $this
            ->integer($nextTuesdayJulie->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::WORK);
        $this
            ->boolean($nextTuesdayJulie->isAllDayLong)
            ->isFalse();
        $this
            ->dateTime($nextTuesdayJulie->startingHour)
            ->hasDateAndTime('2013', '04', '30', '16', '30', '00');
        $this
            ->dateTime($nextTuesdayJulie->finishingHour)
            ->hasDateAndTime('2013', '04', '30', '23', '00', '00');
        $planningAnne = $dataLoaded->listOfPersonnalPlanning["Anne"];
        $fridayAnne = $planningAnne->listOfDayData[4];
        $this
            ->integer($fridayAnne->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::RTT);
        $this
            ->boolean($fridayAnne->isAllDayLong)
            ->isTrue();
        $this
            ->dateTime($fridayAnne->startingHour)
            ->hasDateAndTime('2013', '03', '08', '00', '00', '00');
        $this
            ->dateTime($fridayAnne->finishingHour)
            ->hasDateAndTime('2013', '03', '08', '00', '00', '00');
        $planningOlivier = $dataLoaded->listOfPersonnalPlanning["Olivier"];
        $wednesdayOlivier = $planningOlivier->listOfDayData[9];
        $this
            ->integer($wednesdayOlivier->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::FERIE);
        $this
            ->boolean($wednesdayOlivier->isAllDayLong)
            ->isTrue();
        $this
            ->dateTime($wednesdayOlivier->startingHour)
            ->hasDateAndTime('2013', '05', '01', '00', '00', '00');
        $this
            ->dateTime($wednesdayOlivier->finishingHour)
            ->hasDateAndTime('2013', '05', '01', '00', '00', '00');
    }
}
