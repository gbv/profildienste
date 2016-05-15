<?php
/**
 * Removes a title from the rejected list.
 */

/**
 * @package AJAX
 */
namespace AJAX;

use AJAX\Changers\CollectionStatusChanger;
use Middleware\AuthToken;

/**
 * Removes a title from the rejected list.
 *
 * Class RemoveReject
 */
class RemoveReject extends AJAXResponse {

  /**
   * Removes a title from the rejected list.
   *
   * @param $id string ID of the title
   * @param $view
   * @param AuthToken $auth Token
   */
  public function __construct($ids, $view, AuthToken $auth) {

    $this->resp['id'] = array();

    try {
      CollectionStatusChanger::handleCollection($ids, $view, 'normal', $auth);
    } catch (\Exception $e) {
      $this->error($e->getMessage());
      return;
    }

    $this->resp['success'] = true;
  }
}

?>
