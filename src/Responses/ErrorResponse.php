<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 03.05.16
 * Time: 13:55
 */

namespace Responses;


use Responses\APIResponse;

class ErrorResponse extends APIResponse{

  private $errMsg;

  public function __construct(string $reason, $httpReturnCode = 400){
    $this->errMsg = $reason;
    $this->httpReturnCode = $httpReturnCode;
  }

  public function getData() {
    return $this->errMsg;
  }

  public function getHTTPReturnCode() {
    return $this->httpReturnCode;
 }
}