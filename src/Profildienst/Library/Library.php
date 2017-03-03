<?php

namespace Profildienst\Library;

use Config\ConfigUtilities;
use Exceptions\UserErrorException;

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

        } catch (UserErrorException $e) {
            $errMsg = $e->getMessage();
            if (!empty($this->libraryData['name'])) {
                $errMsg .= ' for library ' . $this->libraryData['name'];
            } else {
                $errMsg .= ' for unknown library';
            }
            throw new \Exception($errMsg);
        }
    }

    public function getName() {
        return $this->libraryData['name'];
    }

    public function getISIL() {
        return $this->libraryData['isil'];
    }

    public function getILN() {
        return $this->libraryData['ILN'];
    }

    public function getELN() {
        return $this->libraryData['ELN'];
    }

    public function usesAdvancedExport() {
        return $this->libraryData['export']['advanced'];
    }

    public function getExportDir() {

        if ($this->libraryData['export']['advanced']) {
            throw new \Exception('Method ineligible for chosen export method');
        }

        return $this->libraryData['export']['exportDir'];
    }

    public function getOPACURL() {
        return $this->libraryData['opac'];
    }

    public function isHidden() {
        return $this->libraryData['hidden'] ?? false;
    }
}