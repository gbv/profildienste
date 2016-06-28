<?php
/**
 * Deletes all rejected titles from the view or the database.
 */

/**
 * @package AJAX
 */
namespace AJAX;

use Middleware\AuthToken;
use Profildienst\DB;

/**
 * Deletes all rejected titles from the view or the database.
 *
 * Class Delete
 */
class Delete extends AJAXResponse {

  /**
   * Delete constructor.
   * Deletes all rejected titles
   *
   * @param $auth AuthToken Auth token
   */
  public function __construct($auth) {

    $this->resp = array('success' => false, 'id' => NULL, 'errormsg' => '', 'msg' => '');

    $query = array('$and' => array(array('user' => $auth->getID()), array('status' => 'rejected')));
    $t = DB::getTitleList($query, NULL, $auth);
    $rejected = $t['titlelist']->getTitles();

    $rejectCount = 0;

    foreach ($rejected as $title) {

      if ($title->getUser() !== $auth->getID()) {
        $this->error('Sie haben keine Berechtigung diesen Titel zu bearbeiten.');
        return;
      }

      $del = DB::deleteTitle($title->getDirectly('_id'), $auth);
      if ($del !== TRUE) {
        $this->error($del);
        return;
      }

      $rejectCount++;

    }

    $this->resp['success'] = true;
    $this->resp['msg'] = 'Sie haben ' . $rejectCount . ' Titel aus Ihrer Ansicht gelöscht.';
  }
}

?>
