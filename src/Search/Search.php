<?php
/**
 * Performs a search
 */

/**
 * @package Search
 */
namespace Search;

use Middleware\AuthToken;
use Profildienst\DB;
use Profildienst\TitleList;

/**
 * Performs a search.
 *
 * Class Search
 */
class Search {

  /**
   * @var TitleList  List of found titles
   */
  private $titlelist;
  /**
   * @var int total amount of titles
   */
  private $total;

  /**
   * Performs a search
   *
   * @param $q string|array Search query
   * @param $queryType Type of the search query(keyword or advanced)
   * @param $num int Requested page
   * @param AuthToken $auth Token
   * @throws \Exception
   */
  public function __construct($q, $queryType, $num, AuthToken $auth) {

    $query = new QueryValidator($q, $queryType);
    $search = $query->getSearch();

    $dbquery = $search->getDatabaseQuery();
    $dbquery
      ->searchTitlesWithStatus('normal')
      ->restrictToUser($auth->getID())
      ->joinWithAnd();
 
    $t = DB::getTitleList($dbquery->getQuery(), $num, $auth);
    $this->titlelist = $t['titlelist'];
    $this->total = $t['total'];
  }

  /**
   * Getter for titles
   *
   * @return TitleList Found titles
   */
  public function getTitles() {
    return $this->titlelist;
  }

  /**
   * Getter for the total amount of titles
   *
   * @return int total amount of titles found
   */
  public function getTotalCount() {
    return $this->total;
  }


}

?>
