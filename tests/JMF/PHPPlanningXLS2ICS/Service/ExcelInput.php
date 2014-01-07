<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 04/03/13
 * Time: 19:28
 * To change this template use File | Settings | File Templates.
 */

namespace tests\JMF\PHPPlanningXLS2ICS\Service;

//Inclusion de atoum
require_once __DIR__ . '/../../../atoum/mageekguy.atoum.phar';

use \mageekguy\atoum;
use \JMF\PHPPlanningXLS2ICS\Service;
use \JMF\PHPPlanningXLS2ICS\Data;

//Class loader
require_once __DIR__ . '/../../../../classes/JMF/PHPPlanningXLS2ICS/Converter.php';

/**
 * @namespace tests\
 */
class ExcelInput extends atoum\test
{

    public $testFile = '/../../../fixtures/test.xls';

    public $testMergedFile = '/../../../fixtures/testMerged.xls';

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

        $this
            ->string(\JMF\PHPPlanningXLS2ICS\Service\ArrayLogging::getInstance()->displayLog())
            ->contains("error")
            ->contains("Impossible d'ouvrir le fichier");
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
     *
     */
    public function testGetPersonnalPlannings() {
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
                ->isEqualTo(28);
        }

        $planningJulie = $dataLoaded->listOfPersonnalPlanning["Julie"];
        $mondayJulie = $planningJulie->listOfDayData[0];
        $this
            ->integer($mondayJulie->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::RH);
        $this
            ->boolean($mondayJulie->isHotels)
            ->isFalse();
        $this
            ->boolean($mondayJulie->isDetaches)
            ->isFalse();
        $this
            ->boolean($mondayJulie->isAllDayLong)
            ->isTrue();
        $this
            ->string($mondayJulie->specificDay)
            ->isEmpty();
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
            ->boolean($tuesdayJulie->isHotels)
            ->isTrue();
        $this
            ->boolean($tuesdayJulie->isDetaches)
            ->isFalse();
        $this
            ->string($tuesdayJulie->specificDay)
            ->isEmpty();
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
            ->boolean($wednesdayJulie->isHotels)
            ->isFalse();
        $this
            ->boolean($wednesdayJulie->isDetaches)
            ->isFalse();
        $this
            ->string($wednesdayJulie->specificDay)
            ->isEmpty();
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
            ->boolean($thursdayJulie->isHotels)
            ->isFalse();
        $this
            ->boolean($thursdayJulie->isDetaches)
            ->isFalse();
        $this
            ->string($thursdayJulie->specificDay)
            ->isEmpty();
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
            ->boolean($fridayJulie->isHotels)
            ->isFalse();
        $this
            ->boolean($fridayJulie->isDetaches)
            ->isFalse();
        $this
            ->string($fridayJulie->specificDay)
            ->isEmpty();
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
            ->boolean($saturdayJulie->isHotels)
            ->isFalse();
        $this
            ->boolean($saturdayJulie->isDetaches)
            ->isFalse();
        $this
            ->string($saturdayJulie->specificDay)
            ->isEmpty();
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
            ->boolean($sundayJulie->isHotels)
            ->isFalse();
        $this
            ->boolean($sundayJulie->isDetaches)
            ->isFalse();
        $this
            ->string($sundayJulie->specificDay)
            ->isEmpty();
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
            ->boolean($nextTuesdayJulie->isHotels)
            ->isFalse();
        $this
            ->boolean($nextTuesdayJulie->isDetaches)
            ->isFalse();
        $this
            ->string($nextTuesdayJulie->specificDay)
            ->isEmpty();
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
            ->boolean($fridayAnne->isHotels)
            ->isFalse();
        $this
            ->boolean($fridayAnne->isDetaches)
            ->isFalse();
        $this
            ->string($fridayAnne->specificDay)
            ->isEmpty();
        $this
            ->dateTime($fridayAnne->startingHour)
            ->hasDateAndTime('2013', '03', '08', '00', '00', '00');
        $this
            ->dateTime($fridayAnne->finishingHour)
            ->hasDateAndTime('2013', '03', '08', '00', '00', '00');
        $planningOlivier = $dataLoaded->listOfPersonnalPlanning["Olivier"];
        $mondayOlivier = $planningOlivier->listOfDayData[0];
        $this
            ->integer($mondayOlivier->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::WORK);
        $this
            ->boolean($mondayOlivier->isAllDayLong)
            ->isFalse();
        $this
            ->boolean($mondayOlivier->isHotels)
            ->isTrue();
        $this
            ->boolean($mondayOlivier->isDetaches)
            ->isFalse();
        $this
            ->string($mondayOlivier->specificDay)
            ->isEmpty();
        $this
            ->dateTime($mondayOlivier->startingHour)
            ->hasDateAndTime('2013', '03', '04', '09', '00', '00');
        $this
            ->dateTime($mondayOlivier->finishingHour)
            ->hasDateAndTime('2013', '03', '04', '17', '00', '00');
        $tuesdayOlivier = $planningOlivier->listOfDayData[1];
        $this
            ->integer($tuesdayOlivier->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::WORK);
        $this
            ->boolean($tuesdayOlivier->isAllDayLong)
            ->isFalse();
        $this
            ->boolean($tuesdayOlivier->isHotels)
            ->isFalse();
        $this
            ->boolean($tuesdayOlivier->isDetaches)
            ->isTrue();
        $this
            ->string($tuesdayOlivier->specificDay)
            ->isEmpty();
        $this
            ->dateTime($tuesdayOlivier->startingHour)
            ->hasDateAndTime('2013', '03', '05', '09', '00', '00');
        $this
            ->dateTime($tuesdayOlivier->finishingHour)
            ->hasDateAndTime('2013', '03', '05', '16', '00', '00');
        $wednesdayOlivier = $planningOlivier->listOfDayData[2];
        $this
            ->integer($wednesdayOlivier->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::WORK);
        $this
            ->boolean($wednesdayOlivier->isAllDayLong)
            ->isFalse();
        $this
            ->boolean($wednesdayOlivier->isHotels)
            ->isFalse();
        $this
            ->boolean($wednesdayOlivier->isDetaches)
            ->isTrue();
        $this
            ->string($wednesdayOlivier->specificDay)
            ->isEmpty();
        $this
            ->dateTime($wednesdayOlivier->startingHour)
            ->hasDateAndTime('2013', '03', '06', '09', '00', '00');
        $this
            ->dateTime($wednesdayOlivier->finishingHour)
            ->hasDateAndTime('2013', '03', '06', '16', '00', '00');
        $thursdayOlivier = $planningOlivier->listOfDayData[3];
        $this
            ->integer($thursdayOlivier->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::SPECIFIC_DAY);
        $this
            ->boolean($thursdayOlivier->isAllDayLong)
            ->isTrue();
        $this
            ->boolean($thursdayOlivier->isHotels)
            ->isFalse();
        $this
            ->boolean($thursdayOlivier->isDetaches)
            ->isFalse();
        $this
            ->string($thursdayOlivier->specificDay)
            ->contains("récup TEST");
        $this
            ->dateTime($thursdayOlivier->startingHour)
            ->hasDateAndTime('2013', '03', '07', '00', '00', '00');
        $this
            ->dateTime($thursdayOlivier->finishingHour)
            ->hasDateAndTime('2013', '03', '07', '00', '00', '00');
        $nextWednesdayOlivier = $planningOlivier->listOfDayData[9];
        $this
            ->integer($nextWednesdayOlivier->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::FERIE);
        $this
            ->boolean($nextWednesdayOlivier->isAllDayLong)
            ->isTrue();
        $this
            ->boolean($nextWednesdayOlivier->isHotels)
            ->isFalse();
        $this
            ->boolean($nextWednesdayOlivier->isDetaches)
            ->isFalse();
        $this
            ->string($nextWednesdayOlivier->specificDay)
            ->isEmpty();
        $this
            ->dateTime($nextWednesdayOlivier->startingHour)
            ->hasDateAndTime('2013', '05', '01', '00', '00', '00');
        $this
            ->dateTime($nextWednesdayOlivier->finishingHour)
            ->hasDateAndTime('2013', '05', '01', '00', '00', '00');
        $nextThursdayOlivier = $planningOlivier->listOfDayData[10];
        $this
            ->integer($nextThursdayOlivier->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::SPECIFIC_DAY);
        $this
            ->boolean($nextThursdayOlivier->isAllDayLong)
            ->isTrue();
        $this
            ->boolean($nextThursdayOlivier->isHotels)
            ->isFalse();
        $this
            ->boolean($nextThursdayOlivier->isDetaches)
            ->isFalse();
        $this
            ->string($nextThursdayOlivier->specificDay)
            ->contains("arrêt maladie");
        $this
            ->dateTime($nextThursdayOlivier->startingHour)
            ->hasDateAndTime('2013', '05', '02', '00', '00', '00');
        $this
            ->dateTime($nextThursdayOlivier->finishingHour)
            ->hasDateAndTime('2013', '05', '02', '00', '00', '00');
        $firstFriday2014Olivier = $planningOlivier->listOfDayData[25];
        $this
            ->integer($firstFriday2014Olivier->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::WORK);
        $this
            ->boolean($firstFriday2014Olivier->isAllDayLong)
            ->isFalse();
        $this
            ->boolean($firstFriday2014Olivier->isHotels)
            ->isFalse();
        $this
            ->boolean($firstFriday2014Olivier->isDetaches)
            ->isFalse();
        $this
            ->dateTime($firstFriday2014Olivier->startingHour)
            ->hasDateAndTime('2014', '01', '03', '13', '00', '00');
        $this
            ->dateTime($firstFriday2014Olivier->finishingHour)
            ->hasDateAndTime('2014', '01', '03', '20', '00', '00');
    }

    /**
     *
     */
    public function testMergedGetPersonnalPlannings() {
        //création de l'objet à tester
        $excelInputTest = new \JMF\PHPPlanningXLS2ICS\Service\ExcelInput();

        $excelInputTest->openFile(__DIR__ . $this->testMergedFile);

        $dataLoaded = $excelInputTest->extractData();

        $this
            ->object($dataLoaded)
            ->isInstanceOf('\JMF\PHPPlanningXLS2ICS\Data\Planning');

        $this
            ->integer(count($dataLoaded->listOfPersonnalPlanning))
            ->isEqualTo(10);

        $totalEntries = 0;
        foreach ($dataLoaded->listOfPersonnalPlanning as $personnalPlanning) {
            $totalEntries += count($personnalPlanning->listOfDayData);
        }

        $this
            ->integer($totalEntries)
            ->isEqualTo(92);

        $planningOlivier = $dataLoaded->listOfPersonnalPlanning["Olivier"];
        $mondayOlivier = $planningOlivier->listOfDayData[0];
        $this
            ->integer($mondayOlivier->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::WORK);
        $this
            ->boolean($mondayOlivier->isHotels)
            ->isFalse();
        $this
            ->boolean($mondayOlivier->isDetaches)
            ->isFalse();
        $this
            ->boolean($mondayOlivier->isAllDayLong)
            ->isFalse();
        $this
            ->string($mondayOlivier->specificDay)
            ->isEmpty();
        $this
            ->dateTime($mondayOlivier->startingHour)
            ->hasDateAndTime('2014', '02', '03', '13', '00', '00');
        $this
            ->dateTime($mondayOlivier->finishingHour)
            ->hasDateAndTime('2014', '02', '03', '20', '00', '00');
        $tuesdayOlivier1 = $planningOlivier->listOfDayData[1];
        $this
            ->integer($tuesdayOlivier1->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::WORK);
        $this
            ->boolean($tuesdayOlivier1->isAllDayLong)
            ->isFalse();
        $this
            ->boolean($tuesdayOlivier1->isHotels)
            ->isFalse();
        $this
            ->boolean($tuesdayOlivier1->isHotelsHiver)
            ->isTrue();
        $this
            ->boolean($tuesdayOlivier1->isDetaches)
            ->isFalse();
        $this
            ->string($tuesdayOlivier1->specificDay)
            ->isEmpty();
        $this
            ->dateTime($tuesdayOlivier1->startingHour)
            ->hasDateAndTime('2014', '02', '04', '13', '00', '00');
        $this
            ->dateTime($tuesdayOlivier1->finishingHour)
            ->hasDateAndTime('2014', '02', '04', '15', '00', '00');
        $tuesdayOlivier2 = $planningOlivier->listOfDayData[7];
        $this
            ->integer($tuesdayOlivier2->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::WORK);
        $this
            ->boolean($tuesdayOlivier2->isAllDayLong)
            ->isFalse();
        $this
            ->boolean($tuesdayOlivier2->isHotels)
            ->isFalse();
        $this
            ->boolean($tuesdayOlivier2->isHotelsHiver)
            ->isFalse();
        $this
            ->boolean($tuesdayOlivier2->isDetaches)
            ->isFalse();
        $this
            ->string($tuesdayOlivier2->specificDay)
            ->isEmpty();
        $this
            ->dateTime($tuesdayOlivier2->startingHour)
            ->hasDateAndTime('2014', '02', '04', '15', '00', '00');
        $this
            ->dateTime($tuesdayOlivier2->finishingHour)
            ->hasDateAndTime('2014', '02', '04', '20', '00', '00');
        $planningXavier = $dataLoaded->listOfPersonnalPlanning["Xavier"];
        $mondayXavier1 = $planningXavier->listOfDayData[0];
        $this
            ->integer($mondayXavier1->typeOfDay)
            ->isEqualTo(\JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay::WORK);
        $this
            ->boolean($mondayXavier1->isAllDayLong)
            ->isFalse();
        $this
            ->boolean($mondayXavier1->isHotels)
            ->isFalse();
        $this
            ->boolean($mondayXavier1->isHotelsHiver)
            ->isFalse();
        $this
            ->boolean($mondayXavier1->isDetaches)
            ->isFalse();
        $this
            ->string($mondayXavier1->specificDay)
            ->isEmpty();
        $this
            ->boolean($mondayXavier1->isProGDis)
            ->isTrue();
        $this
            ->dateTime($mondayXavier1->startingHour)
            ->hasDateAndTime('2014', '02', '03', '10', '30', '00');
        $this
            ->dateTime($mondayXavier1->finishingHour)
            ->hasDateAndTime('2014', '02', '03', '16', '00', '00');
    }
}
