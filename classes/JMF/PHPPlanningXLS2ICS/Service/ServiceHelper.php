<?php
/**
 * Created by PhpStorm.
 * User: Jean-Mi
 * Date: 07/01/14
 * Time: 19:24
 */

namespace JMF\PHPPlanningXLS2ICS\Service;


class ServiceHelper {

    /**
     * Remove every accent
     * @param string $str
     * @param string $charset
     * @return string
     */
    public static function wd_remove_accents($str, $charset='utf-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        return $str;
    }
} 