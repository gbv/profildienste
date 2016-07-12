<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 03.05.16
 * Time: 16:09
 */

namespace Profildienst\Library;


use Config\ConfigUtilities;
use Exceptions\ConfigurationException;

class Library {

    private $libraryData;

    public function __construct($libraryData) {

        $this->libraryData = $libraryData;

        try {

            ConfigUtilities::checkField($this->libraryData, 'name');
            ConfigUtilities::checkField($this->libraryData, 'isil');
            ConfigUtilities::checkField($this->libraryData, 'ILN');
            ConfigUtilities::checkField($this->libraryData, 'ELN');
            ConfigUtilities::checkField($this->libraryData, 'export');
            ConfigUtilities::checkField($this->libraryData, 'export', 'advanced', true);

            if (!$this->libraryData['export']['advanced']) {
                ConfigUtilities::checkField($this->libraryData, 'export', 'exportDir');
            }

        } catch (ConfigurationException $e) {
            $errMsg = $e->getMessage();
            if (!empty($this->libraryData['name'])) {
                $errMsg .= ' for library ' . $this->libraryData['name'];
            } else {
                $errMsg .= ' for unknown library';
            }
            throw new ConfigurationException($errMsg);
        }
    }

    public function getName() {
        return $this->libraryData['name'];
    }

    public function getISIL() {
        return $this->libraryData['isil'];
    }

    public function getILN() {
        return $this->libraryData['iln'];
    }

    public function getELN() {
        return $this->libraryData['eln'];
    }

    public function usesAdvancedExport() {
        return $this->libraryData['export']['advancedExport'];
    }

    public function getExportDir() {

        if ($this->libraryData['export']['advancedExport']) {
            throw new ConfigurationException('Method ineligible for chosen export method');
        }

        return $this->libraryData['export']['exportDir'];
    }

    public function getOPACURL() {
        return $this->libraryData['opac'];
    }
}