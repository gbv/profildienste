<?php
/**
 * Abstract class for all AJAX reponses
 *
 * @TODO: Rename to ActionResponse
 */

/**
 * @package AJAX
 */
namespace AJAX;

/**
 * All classes which will be called using an AJAX request have to implement
 * this interface.
 */
abstract class AJAXResponse implements AJAX {

  /**
   * @var array Required fields for all responses
   */
  protected $resp = array('success' => false, 'errormsg' => '');


  /**
   * Returns the response as an array which will be passed to the caller.
   *
   * @return array
   */
  public function getResponse() {
    return $this->resp;
  }

  /**
   * Indicates in the response that an error occured.
   *
   * @param $msg string error message
   */
  protected function error($msg) {
    $this->resp['success'] = false;
    $this->resp['errormsg'] = $msg;
  }


}

?>