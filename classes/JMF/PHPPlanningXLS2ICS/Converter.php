<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jean-Mi
 * Date: 04/03/13
 * Time: 19:29
 * To change this template use File | Settings | File Templates.
 */
namespace JMF\PHPPlanningXLS2ICS;

use JMF\PHPPlanningXLS2ICS\Service\IInputService;
use JMF\PHPPlanningXLS2ICS\Service\ExcelInput;
use JMF\PHPPlanningXLS2ICS\Service\IOutputService;
use JMF\PHPPlanningXLS2ICS\Service\ICSOutput;
use JMF\PHPPlanningXLS2ICS\Service\ILoggingService;
use JMF\PHPPlanningXLS2ICS\Service\ArrayLogging;

/** PHPPlanningXLS2ICS root directory */
if (!defined('PHPPlanningXLS2ICS')) {
    define('PHPPlanningXLS2ICS', dirname(__FILE__) . '/../../../');
    require(PHPPlanningXLS2ICS . 'SplClassLoader.php');
    $loader = new \SplClassLoader('JMF', PHPPlanningXLS2ICS.'/classes');
    $loader->register();
}

class Converter
{
    /** @var IInputService */
    private $inputService;
    /** @var IOutputService */
    private $outputService;
    /** @var ILoggingService */
    private $loggingService;

    /**
     * @param null $inputService
     * @param null $outputService
     * @param null $loggingService
     */
    function __construct($inputService = null, $outputService = null, $loggingService = null)
    {
        if (is_null($loggingService)) {
            $this->loggingService = ArrayLogging::getInstance();
        } else {
            $this->loggingService = $loggingService;
        }
        if (is_null($inputService)) {
            $this->inputService = new ExcelInput($this->loggingService);
        } else {
            $this->inputService = $inputService;
        }
        if (is_null($outputService)) {
            $this->outputService = new ICSOutput($this->loggingService);
        } else {
            $this->outputService = $outputService;
        }
    }

    /**
     * @param string    $pathInput
     * @param string    $pathOutput
     * @return array
     */
    public function convertFile($pathInput, $pathOutput = "") {
        $generatedFiles = array();

        $this->loggingService->add(
            ILoggingService::INFO,
            "Début du traitement"
        );

        if (!$this->createPath($pathOutput)) {
            $this->loggingService->add(
                ILoggingService::ERROR,
                "Erreur : impossible de créer le répertoire" . $pathOutput
            );
        } else {
            $this->inputService->openFile($pathInput);
            $dataExtracted = $this->inputService->extractData();
            $this->inputService->closeFile();

            if (
                !is_null($dataExtracted->listOfPersonnalPlanning)
                    &&
                count($dataExtracted->listOfPersonnalPlanning) > 0
            ) {
                foreach ($dataExtracted->listOfPersonnalPlanning as $personnalPlanning) {
                    $generatedFiles[] = $this->outputService->exportPersonnalPlanning($personnalPlanning, $pathOutput);
                }
            }

            $this->loggingService->add(
                ILoggingService::INFO,
                "Fin du traitement"
            );
        }
        return $generatedFiles;
    }

    /**
     * @return string
     */
    public function showLogs()
    {
        return $this->loggingService->displayLog();
    }


    /**
     * @param $path
     * @return bool
     */
    private function createPath(&$path)
    {
        if ((strrpos($path, '/', -1) + 1) != strlen($path)) {
            $path .= '/';
        }
        return is_dir($path) || mkdir($path, 0755, true);
    }
}
