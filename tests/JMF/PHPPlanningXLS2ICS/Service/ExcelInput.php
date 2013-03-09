<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 04/03/13
 * Time: 19:28
 * To change this template use File | Settings | File Templates.
 */

namespace tests\JMF\PHPPlanningXLS2ICS\Service;

//Inclusion de la classe à tester
require_once __DIR__ . '/../../../../classes/JMF/PHPPlanningXLS2ICS/Service/ExcelInput.php';

//Inclusion de atoum dans toutes les classes de tests
require_once __DIR__ . '/../../../atoum/mageekguy.atoum.phar';

use \mageekguy\atoum;
use \JMF\PHPPlanningXLS2ICS\Service;

//Class loader
require_once __DIR__."/../../../../classes/SplClassLoader.php";

$loader = new \SplClassLoader('JMF\PHPPlanningXLS2ICS', '');
$loader->register();

/**
 * @namespace tests\
 */
class ExcelInput extends atoum\test
{

    public $testFile = '/../../../fixtures/test.xls';

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

    public function testOpenFileOK() {
        //création de l'objet à tester
        $excelInputTest = new \JMF\PHPPlanningXLS2ICS\Service\ExcelInput();


        $excelInputTest->openFile(__DIR__ . $this->testFile);

        $this
            ->object($excelInputTest->getFile())
            ->isInstanceOf('\PHPExcel');
    }

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
}
