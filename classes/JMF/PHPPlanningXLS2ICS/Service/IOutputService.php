<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 12/03/13
 * Time: 19:34
 * To change this template use File | Settings | File Templates.
 */
namespace JMF\PHPPlanningXLS2ICS\Service;

use \JMF\PHPPlanningXLS2ICS\Data\PersonnalPlanning;

interface IOutputService
{
    /**
     * @param PersonnalPlanning $planning
     * @return string                       Path of the generated file
     */
    public function exportPersonnalPlanning(PersonnalPlanning $planning);
}
