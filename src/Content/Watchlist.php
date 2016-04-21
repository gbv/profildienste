<?php
/**
 * Loads all titles from a specific watchlist
 */

/**
 * @package Content
 */
namespace Content;

use Middleware\AuthToken;
use Profildienst\DB;

/**
 * Loads titles in a watchlist.
 *
 * Class Watchlist
 */
class Watchlist extends Content {

  /**
   * Loads titles in a watchlist.
   *
   * @param $num int page number
   * @param $id int watchlist ID
   * @param AuthToken $auth Token
   */
  public function __construct($num, $id, AuthToken $auth) {

    $data = DB::get(array('_id' => $auth->getID()), 'users', array(), true);

    $watchlists = $data['watchlist'];

    if (isset($watchlists[$id])) {
      $query = array('$and' => [array('user' => $auth->getID()), array('watchlist' => $id)]);
      $t = DB::getTitleList($query, $num, $auth);
      $this->titlelist = $t['titlelist'];
      $this->total = $t['total'];

    }
  }

}

?>
