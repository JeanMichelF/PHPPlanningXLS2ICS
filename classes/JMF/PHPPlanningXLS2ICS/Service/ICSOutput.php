<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 04/03/13
 * Time: 22:59
 * To change this template use File | Settings | File Templates.
 */
namespace JMF\PHPPlanningXLS2ICS\Service;

use \JMF\PHPPlanningXLS2ICS\Data\PersonnalPlanning;

class ICSOutput implements IOutputService
{

    /**
     * @param PersonnalPlanning $planning
     * @return string
     */
    public function exportPersonnalPlanning(PersonnalPlanning $planning)
    {
        return 'planning' . $planning->name . '.ics';
    }

    private function writeFileHeader() {

    }

    private function writeFileFooter() {

    }

    private function writeEvent() {

    }
}
