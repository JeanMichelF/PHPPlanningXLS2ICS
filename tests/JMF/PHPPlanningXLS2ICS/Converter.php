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
require_once __DIR__."/../../../SplClassLoader.php";

$loader = new \SplClassLoader('JMF', __DIR__.'/../../../classes');
$loader->register();

/**
 * @namespace tests\
 */
class Converter extends atoum\test
{

    public function testToto() {
        //création de l'objet à tester
        $helloToTest = new PHPPlanningXLS2ICS\Converter();

        $this
            //le retour de la méthode doit être un entier
            ->integer($helloToTest->toto())
            //la valeur doit être égale à 2 !
            ->isEqualTo(2);
    }

}
