<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 01.05.16
 * Time: 18:58
 */

namespace Exceptions;


abstract class BaseException extends \Exception {

  public abstract function getModule();

}