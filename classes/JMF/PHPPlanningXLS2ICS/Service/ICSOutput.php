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
        $filename = 'planning' . $this->wd_remove_accents($planning->name) . '.ics';
        $file = new \SplFileObject($filename, 'w');
        return $filename;
    }

    private function writeFileHeader()
    {

    }

    private function writeFileFooter()
    {

    }

    private function writeEvent()
    {

    }

    /**
     * @param string $str
     * @param string $charset
     * @return string
     */
    public function wd_remove_accents($str, $charset='utf-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        return $str;
    }
}
