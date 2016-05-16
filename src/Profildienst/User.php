<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 15.05.16
 * Time: 17:05
 */

namespace Profildienst;


class User {

  private static $instance;

  public static function getInstance(){
    if (is_null(self::$instance)){
      self::$instance = new User();
    }

    return self::$instance;
  }

  public static function initialize(){

  }

  private function __construct(){

  }


}