<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 03.05.16
 * Time: 14:50
 */

namespace Responses;


class BasicResponse extends APIResponse{

  public function __construct($data){
    $this->data = $data;
  }

  public function getData() {
    return $this->data;
  }
}