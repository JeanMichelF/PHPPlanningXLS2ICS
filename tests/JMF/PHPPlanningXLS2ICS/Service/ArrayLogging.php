<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 11/03/13
 * Time: 20:26
 * To change this template use File | Settings | File Templates.
 */
namespace tests\JMF\PHPPlanningXLS2ICS\Service;

//Inclusion de atoum
require_once __DIR__ . '/../../../atoum/mageekguy.atoum.phar';

use \mageekguy\atoum;
use \JMF\PHPPlanningXLS2ICS\Service;

//Class loader
require_once __DIR__."/../../../../SplClassLoader.php";

$loader = new \SplClassLoader('JMF', __DIR__.'/../../../../classes');
$loader->register();

/**
 * @namespace tests\
 */
class ArrayLogging extends atoum\test
{
    /**
     *
     */
    public function testLog() {
        \JMF\PHPPlanningXLS2ICS\Service\ArrayLogging::getInstance()->add("debug", "test un");
        \JMF\PHPPlanningXLS2ICS\Service\ArrayLogging::getInstance()->add("error", "test deux");

        $this
            ->string(\JMF\PHPPlanningXLS2ICS\Service\ArrayLogging::getInstance()->displayLog())
            ->contains("debug")
            ->contains("error")
            ->contains("test un")
            ->contains("test deux");
    }
}
