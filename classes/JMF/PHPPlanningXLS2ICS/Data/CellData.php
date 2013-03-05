<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 04/03/13
 * Time: 19:32
 * To change this template use File | Settings | File Templates.
 */
namespace JMF\PHPPlanningXLS2ICS\Data;

use DateTime;

class CellData
{
    /** @var int */
    public $typeOfDay;
    /** @var DateTime */
    public $startingHour;
    /** @var DateTime */
    public $finishingHour;
    /** @var bool */
    public $isAllDayLong;
}
