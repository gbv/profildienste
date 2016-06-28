<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 01.05.16
 * Time: 19:09
 */

namespace Exceptions;


class ConfigurationException extends BaseException {

  public function getModule() {
    return 'Configuration';
  }
}