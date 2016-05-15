<?php
/**
 * Interface for all responses to user initiated actions
 */

/**
 * @package AJAX
 */
namespace AJAX;

/**
 * An interface for all AJAX responses. Technically speaking, all responses
 * from the API are AJAX responses in the current version. Before version 1.0.0,
 * the app was a PHP app instead of an Angular app and so the only AJAX responses were actions.
 * Nowadays, this interface is for all actions the user can initiate, so the name Actions would
 * probably suit better.
 *
 * @TODO: Rename to Actions.
 *
 * Interface AJAX
 */
interface AJAX {

  /**
   * Return the Response as an array. This array will be then transformed
   * into valid JSON.
   *
   * @return array Response of the action
   */
  public function getResponse();

}

?>