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

class DayData
{
    /** @var int */
    public $typeOfDay = 0;
    /** @var DateTime */
    public $startingHour;
    /** @var DateTime */
    public $finishingHour;
    /** @var bool */
    public $isAllDayLong = false;
    /** @var bool */
    public $isHotels = false;
    /** @var bool */
    public $isDetaches = false;
    /** @var bool */
    public $isProGDis = false;
    /** @var bool */
    public $isHotelsHiver = false;
    /** @var bool */
    public $isPlaquette = false;
    /** @var string */
    public $specificDay = "";
}
