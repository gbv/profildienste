<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 15.05.16
 * Time: 17:05
 */

namespace Profildienst;

use Exception;

/**
 * Represents the user in the whole application. It can only be used in a context
 * where the user has to be logged in, otherwise an exception will be raised when
 * trying to get user information. This class is implemented as a singleton in order
 * to make the information easily accessible throughout the whole app. Initialisation
 * is performed by the auth middleware with the infos from the token.
 *
 * Class User
 * @package Profildienst
 */
class User {

  /**
   * @var 
   */
  private static $instance;

  private $name;
  private $id;

  /**
   * Returns the instance of this class
   *
   * @return User
   */
  public static function getInstance(){
    if (is_null(self::$instance)){
      self::$instance = new User();
    }

    return self::$instance;
  }

  /**
   * Initializes the user information. This function can only be called once,
   * subsequent calls will result in a runtime exception.
   *
   * @param $name real name of the user
   * @param $id ID of the user
   * @throws Exception
   */
  public function initialize($name, $id){
    if (empty($this->name) && empty($this->id)){
      $this->name = $name;
      $this->id = $id;
    } else {
      throw new Exception('User reinitialisation is not allowed');
    }
  }

  /**
   * User constructor.
   */
  private function __construct(){}

  /**
   * Returns the ID of the user
   *
   * @return string
   * @throws Exception
   */
  public function getID(){
    return self::checkIfSetAndReturn($this->id);
  }

  /**
   * Returns the name of the user
   *
   * @return string
   * @throws Exception
   */
  public function getName(){
    return self::checkIfSetAndReturn($this->name);
  }

  /**
   * Checks if field $field is set and returns it, otherwise throws an exception.
   *
   * @param $field
   * @return mixed
   * @throws Exception
   */
  private static function checkIfSetAndReturn($field){
    if (empty($field)){
      throw new Exception('Operation not allowed in a non-user context');
    }

    return $field;
  }


}