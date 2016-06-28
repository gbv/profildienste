<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 28.04.16
 * Time: 14:47
 */

namespace Responses;


abstract class APIResponse {

  protected $data;

  protected $httpReturnCode;

  public abstract function getData();

  public function getHTTPReturnCode() {
    return 200;
  }

}