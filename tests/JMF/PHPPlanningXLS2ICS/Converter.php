<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 04/03/13
 * Time: 19:28
 * To change this template use File | Settings | File Templates.
 */

namespace tests\JMF\PHPPlanningXLS2ICS;

//Inclusion de la classe à tester
require_once __DIR__ . '/../../../classes/JMF/PHPPlanningXLS2ICS/Converter.php';

//Inclusion de atoum dans toutes les classes de tests
require_once __DIR__ . '/../../atoum/mageekguy.atoum.phar';

use \mageekguy\atoum;
use \JMF\PHPPlanningXLS2ICS;

//Class loader
require_once __DIR__ . '/../../../classes/JMF/PHPPlanningXLS2ICS/Converter.php';
/**
 * @namespace tests\
 */
class Converter extends atoum\test
{
    public $testFile = '/../../fixtures/test.xls';

    /**
     *@tags active
     */
    public function testConverterOk() {
        //création de l'objet à tester
        $converterTest = new PHPPlanningXLS2ICS\Converter();

        $results = $converterTest->convertFile(__DIR__ . $this->testFile, __DIR__ . "../../../result/test");

        echo $converterTest->showLogs();
        $this->array($results)
            ->hasSize(9)
            ->contains('planningJulie.ics')
            ->contains('planningXavier.ics')
            ->contains('planningNoelie.ics')
            ->contains('planningOlivier.ics')
            ->contains('planningAline.ics')
            ->contains('planningAnne.ics')
            ->contains('planningClaire.ics')
            ->contains('planningCeline.ics')
            ->contains('planningMarie.ics');
    }

    /**
     *
     */
    public function testConverterKo() {
        //création de l'objet à tester
        $converterTest = new PHPPlanningXLS2ICS\Converter();

        $this
            ->exception(
            function() use($converterTest) {
                // ce code lève une exception: throw new \Exception;
                $converterTest->convertFile('badfile');
            }
        );

        echo $converterTest->showLogs();
    }
}
