<?php

namespace Responses;


abstract class APIResponse {

  protected $data;

  protected $httpReturnCode;

  public abstract function getData();

  public function getHTTPReturnCode() {
    return 200;
  }

}