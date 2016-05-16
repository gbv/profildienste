<?php
/**
 * Performs a basic authentication against the CBS
 */

/**
 * @package Auth
 */
namespace Auth;

use Config\Config;
use Config\Configuration;
use Exceptions\AuthException;
use Profildienst\DB;

/**
 * Performs a basic authentication against the CBS
 *
 * Class Auth
 */
class Auth {

  /**
   * @var string Name of the User
   */
  private $name = '';
  /**
   * @var string The Library the user belongs to
   */
  private $library = '';
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
   */
  public function __construct(string $user, string $pwd, Configuration $config) {

    if (empty($user) || empty($pwd)) {
      throw new AuthException('Benutzername und Passwort dürfen nicht leer sein');
    }

    $fp = fsockopen($config->getAuthServerHost(), $config->getAuthServerPort(), $errno, $errstr, 30);
    if (!$fp) {
      throw new AuthException('Aktuell ist leider keine Authentifizierung möglich. Bitte versuchen Sie es zu einem späteren Zeitpunkt erneut.');
    }

    $out = sprintf("GET /XML/PAR?P3C=US&P3Command=LOG+%s+%s HTTP/1.0\r\n", $user, $pwd);
    $out .= "Connection: Close\r\n\r\n";
    fwrite($fp, $out);
    while (!feof($fp)) {

      $line = fgets($fp, 1024);

      // Check for a failed login
      if (preg_match('/^<PICA:P3K ID=\"\/V\">REJECT<\/PICA:P3K>$/', $line)) {
        throw new AuthException('Login fehlgeschlagen, bitte überprüfen Sie Nutzername und Passwort.');
      }
      // Check for a successful login
      if (preg_match('/^<PICA:P3K ID=\"(LS|UB)\">(.*?) <([^>]+)><\/PICA:P3K>$/', $line, $matches)) {
        $this->library = $matches[2];
        $this->isil = $matches[3];
      }

      if (preg_match('/^<PICA:P3K ID=\"UM\">(.*?)<\/PICA:P3K>$/', $line, $matches)) {
        $this->name = preg_replace("/<(.*?)>/", '', $matches[0]);
      }
    }

    fclose($fp);

    // TODO: Rework once the database has refactored
    $data = DB::get(array('_id' => $user), 'users', array('_id' => 1), true);
    if(is_null($data)){
      throw new AuthException('Leider sind Sie nicht für den Profildienst freigeschaltet.');
    }
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
  public function getLibrary() {
    return $this->library;
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