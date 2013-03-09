<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 04/03/13
 * Time: 19:29
 * To change this template use File | Settings | File Templates.
 */
namespace JMF\PHPPlanningXLS2ICS;

use JMF\PHPPlanningXLS2ICS\Data\DayData;
use JMF\PHPPlanningXLS2ICS\Constant\TypeOfDay;

class Converter
{
    public function toto() {
        $toto = new DayData();
        $toto->typeOfDay = TypeOfDay::RTT;

        return $toto->typeOfDay;
    }
}
