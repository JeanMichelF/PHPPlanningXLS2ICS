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
     * @param $path
     * @return array
     */
    public function convertFile($path) {
        $this->loggingService->add(
            "info",
            "Début du traitement"
        );
        $this->inputService->openFile($path);
        $dataExtracted = $this->inputService->extractData();
        $this->inputService->closeFile();

        $generatedFiles = array();
        if (
            !is_null($dataExtracted->listOfPersonnalPlanning)
                &&
            count($dataExtracted->listOfPersonnalPlanning) > 0
        ) {
            foreach ($dataExtracted->listOfPersonnalPlanning as $personnalPlanning) {
                $generatedFiles[] = $this->outputService->exportPersonnalPlanning($personnalPlanning);
            }
        }

        $this->loggingService->add(
            "info",
            "Fin du traitement"
        );

        return $generatedFiles;
    }

    /**
     * @return string
     */
    public function showLogs()
    {
        return $this->loggingService->displayLog();
    }

}
