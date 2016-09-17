<?php

namespace Config;

use Exceptions\UserErrorException;
use Profildienst\Library\Library;

/**
 * Main configuration. The configuration is read from a JSON file stored under
 * the specified path.
 *
 * Class Configuration
 * @package Config
 */
class Configuration {

    /**
     * Directory where the configuration file is located.
     * This dir has to be writeable by the php user if a default config file
     * should be created (in case there is none).
     *
     * @var string
     */
    private static $CONFIG_DIR = 'config/';

    /**
     * Name of the config file
     *
     * @var string
     */
    private static $CONFIG_FILE_NAME = 'config.json';

    /**
     * This is the default configuration, written if there is no configuration
     * file present. To ensure backwards compability with older versions, the
     * config from the configuration file and this default config are merged.
     * This leads to filling possibly missing fields in the actual config with default
     * values.
     *
     * @var array
     */
    private static $DEFAULT_CONFIGURATION = [
        'cbs' => [
            'host' => 'cbs-3.gbv.de',
            'port' => 3790
        ],
        'database' => [
            'host' => 'localhost',
            'port' => '27017',
            'database' => 'pd'
        ],
        'auth' => [
            'secret_key' => '',
            'encryption_algorithm' => 'HS256',
            'token' => [
                'expiration' => (60 * 60 * 24),
                'issuer' => ''
            ]
        ],
        'motd' => [
            'message' => ''
        ],
        'export' => [
            'host' => 'cbs4.gbv.de',
            'user' => 'cbs_ifd',
            'basedir' => '/export/home/cbs_ifd/andreas/profildienst/'
        ],
        'general' => [
            'pagesize' => 10,
            'production' => true,
            'maintenance' => false
        ],
        'search' => [
            'modes' => [
                'contains' => 'ENTHÄLT',
                'is' => 'ENTHÄLT GENAU'
            ],
            'fields' => [
                'tit' => 'Titel',
                'per' => 'Person',
                'ver' => 'Verlag',
                'dnb' => 'DNB-Nummer',
                'isb' => 'ISBN',
                'sgr' => 'DNB-Sachgruppe',
                'erj' => 'Erscheinungsjahr',
                'wvn' => 'Wochenlieferungsnr.',
                'mak' => 'Materialcode'
            ]
        ],

        'sorting' => [
            'sort' => [
                'erj' => 'Erscheinungsjahr',
                'wvn' => 'WV-Nummer',
                'tit' => 'Titel',
                'sgr' => 'Sachgruppe',
                'dbn' => 'DNB-Nummer',
                'per' => 'Person'
            ],
            'order' => [
                'desc' => 'Absteigend',
                'asc' => 'Aufsteigend'
            ]
        ],

        'libraries' => [

            'DE-601' => [
                'name' => 'VZG Göttingen',
                'ELN' => '1999',
                'ILN' => '1',
                'opac' => '',
                'export' => [
                    'advanced' => false,
                    'exportDir' => 'test'
                ]
            ]

        ]

    ];

    private $config;
    private $libraries;

    public function __construct() {

        $config_file_path = ConfigUtilities::addTrailingSlash(self::$CONFIG_DIR) . self::$CONFIG_FILE_NAME;

        //check for configuration file
        if (!file_exists($config_file_path)) {

            if (!is_writeable(self::$CONFIG_DIR)) {
                throw new UserErrorException('Das Konfigurationsverzeichnis ist nicht beschreibbar. Bitte Rechte überprüfen!');
            }

            $msg = 'Keine Konfigurationsdatei für den Profildienst gefunden. ';
            if (file_put_contents($config_file_path, json_encode(self::$DEFAULT_CONFIGURATION, JSON_PRETTY_PRINT)) !== false) {
                $msg .= 'Eine neue Konfigurationsdatei wurde erstellt, diese Datei muss noch anpasst werden.';
            } else {
                $msg .= 'Es konnte zudem keine Konfigurationsdatei angelegt werden.';
            }

            throw new UserErrorException($msg);
        }

        $config = file_get_contents($config_file_path);

        if ($config === false) {
            throw new UserErrorException('Die Konfigurationsdatei konnte nicht gelesen werden.');
        }

        $config = json_decode($config, true);
        if (is_null($config)) {
            throw new UserErrorException('Die Konfigurationsdatei ist kein valides JSON.');
        }

        $this->config = ConfigUtilities::array_merge_recursive_distinct(self::$DEFAULT_CONFIGURATION, $config);

        // check the config entries which most likely has been modified by the user
        try {
            ConfigUtilities::checkField($this->config, 'auth', 'secret_key');
            ConfigUtilities::checkField($this->config, 'auth', 'token');
            ConfigUtilities::checkField($this->config['auth']['token'], 'issuer');
            ConfigUtilities::checkField($this->config, 'libraries');
        } catch (UserErrorException $e) {
            throw new UserErrorException('Unvollständige Konfiguration: ' . $e->getMessage());
        }

        // generate list of all libraries
        $this->libraries = [];
        foreach ($this->config['libraries'] as $isil => $libraryData) {
            $libraryData['isil'] = $isil;
            $library = new Library($libraryData);
            $this->libraries[$library->getISIL()] = $library;
        }
    }

    /**
     * Get the list of all participating libraries
     *
     * @return array List of all participating libraries
     */
    public function getLibraries() {
        return array_values($this->libraries);
    }

    /**
     * Returns the Library object associated with the $isil.
     *
     * @param $isil
     * @return Library|null
     */
    public function getLibrary($isil) {
        if (isset($this->libraries[$isil])) {
            return $this->libraries[$isil];
        } else {
            return null;
        }
    }

    public function getSortOptions() {
        return $this->config['sorting']['sort'];
    }

    public function getOrderOptions() {
        return $this->config['sorting']['order'];
    }

    public function getSecretKey() {
        return $this->config['auth']['secret_key'];
    }

    public function getTokenCryptAlgorithm() {
        return $this->config['auth']['encryption_algorithm'];
    }

    public function getAuthServerHost() {
        return $this->config['cbs']['host'];
    }

    public function getAuthServerPort() {
        return $this->config['cbs']['port'];
    }

    public function getTokenIssuer() {
        return $this->config['auth']['token']['issuer'];
    }

    public function getTokenExpTime() {
        return $this->config['auth']['token']['expiration'];
    }

    public function getSearchableFields() {
        return $this->config['search']['fields'];
    }

    public function getSearchModes() {
        return $this->config['search']['modes'];
    }

    public function getMOTD() {
        return $this->config['motd']['message'];
    }

    public function getDatabaseHost() {
        return $this->config['database']['host'];
    }

    public function getDatabasePort() {
        return $this->config['database']['port'];
    }

    public function getDatabaseName() {
        return $this->config['database']['database'];
    }

    public function getPagesize() {
        return $this->config['general']['pagesize'];
    }

    public function getExportHost() {
        return $this->config['export']['host'];
    }

    public function getExportUser() {
        return $this->config['export']['user'];
    }

    public function getExportBasedir() {
        return $this->config['export']['basedir'];
    }
}