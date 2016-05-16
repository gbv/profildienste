<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 15.05.16
 * Time: 14:28
 */

namespace Exceptions;


class AuthException extends BaseException{

  public function getModule() {
    return 'Authentication';
  }
}