<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 01.04.16
 * Time: 12:29
 */

namespace Profildienst\Search;


use MongoDB\BSON\Regex;

abstract class SearchQuery {

  protected $dbquery;
  private $type;

  public function __construct($type) {
    $this->type = $type;
    $this->dbquery = new QueryBuilder();
  }

  public function getType() {
    return $this->type;
  }

  /**
   * @return QueryBuilder Query ready to be used for the database
   */
  public abstract function getDatabaseQuery();

  /**
   * @return array Returns a representation of the search critera as a plain array.
   */
  public abstract function getSearchAsArray();


  protected function handleSearchterm($searchterm, $mode) {

    if ($mode === 'contains') {
      return new Regex("^.*$searchterm.*$", 'i');
    } else if ($mode === 'is') {
      return $searchterm;
    }

    return null;
  }

}