<?php
/**
 *  * Loads titles which are marked as done.
 */

/**
 * @package Content
 */
namespace Content;

use Middleware\AuthToken;
use Profildienst\DB;

/**
 * Loads titles which are marked as done.
 *
 * Class Done
 */
class Done extends Content {


  /**
   * Loads titles which are marked as done.
   *
   * @param $num int Page Number
   * @param AuthToken $auth Token
   */
  public function __construct($num, AuthToken $auth) {

    $query = array('$and' => [array('user' => $auth->getID()), array('status' => 'done')]);

    $t = DB::getTitleList($query, $num, $auth);
    $this->titlelist = $t['titlelist'];
    $this->total = $t['total'];

  }
}

?>

