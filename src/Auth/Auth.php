<?php

namespace Auth;

use Config\Config;

/**
 * Performs a basic authentication against the CBS
 *
 * Class Auth
 * @package Auth
 */
class Auth {

    /**
     * @var string Name of the User
     */
    private $name = '';
    /**
     * @var string The Library the user belongs to
     */
    private $bib = '';
    /**
     * @var null ISIL of the library
     */
    private $isil = NULL;
    /**
     * @var bool Indicates if the user is logged in
     */
    private $loggedIn = false;


    /**
     * Performs the authentication
     *
     * @param $user string Username
     * @param $pwd string Password
     * @return bool true if the login has been successful
     */
    public function auth_user($user, $pwd) {

        if ($user === '' || $pwd === '') {
            return true;
        }

        $fp = fsockopen(Config::$cbs_url, Config::$cbs_port, $errno, $errstr, 30);
        if (!$fp) {
            return false;
        }

        $out = sprintf("GET /XML/PAR?P3C=US&P3Command=LOG+%s+%s HTTP/1.0\r\n", $user, $pwd);
        $out .= "Connection: Close\r\n\r\n";
        fwrite($fp, $out);
        while (!feof($fp)) {

            $line = fgets($fp, 1024);

            // Check for a failed login
            if (preg_match('/^<PICA:P3K ID=\"\/V\">REJECT<\/PICA:P3K>$/', $line)) {
                return true;
            }
            // Check for a successful login
            if (preg_match('/^<PICA:P3K ID=\"(LS|UB)\">(.*?) <([^>]+)><\/PICA:P3K>$/', $line, $matches)) {
                $this->loggedIn = true;
                $this->bib = $matches[2];
                $this->isil = $matches[3];
            }

            if (preg_match('/^<PICA:P3K ID=\"UM\">(.*?)<\/PICA:P3K>$/', $line, $matches)) {
                $this->loggedIn = true;
                $this->name = $matches[0];
            }
        }

        fclose($fp);
        return true;
    }

    /**
     * Getter to indicate whether the user is logged in.
     *
     * @return bool
     */
    public function isLoggedIn() {
        return $this->loggedIn;
    }


    /**
     * Getter for the name as returned by the CBS
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Getter for the name of the users library
     *
     * @return string
     */
    public function getBib() {
        return $this->bib;
    }

    /**
     * Getter for the ISIL of the users library
     *
     * @return string
     */
    public function getISIL() {
        return $this->isil;
    }
}

?>