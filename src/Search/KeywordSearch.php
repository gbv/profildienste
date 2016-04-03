<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 02.04.16
 * Time: 13:39
 */

namespace Search;

class KeywordSearch extends SearchQuery{

  private $mode;
  private $searchterm;


  public function __construct($type = 'keyword'){
    parent::__construct($type);
  }

  public function setMode($mode){
    $this->mode = $mode;
  }

  public function getMode(){
    return $this->mode;
  }

  public function setSearchterm($searchterm){
    $this->searchterm = $searchterm;
  }

  public function getSearchterm(){
    return $this->searchterm;
  }

  /**
   * @return QueryBuilder Query ready to be used for the database
   */
  public function getDatabaseQuery() {

    $searchterm = $this->handleSearchterm($this->searchterm, $this->mode);

    $this->dbquery
      ->searchTitleField($searchterm)
      ->searchPersonField($searchterm)
      ->searchVerlagField($searchterm)
      ->searchDNBNrField($searchterm)
      ->searchISBNField($searchterm)
      ->searchDNBSachgruppeField($searchterm)
      ->searchErscheinungsjahrField($searchterm)
      ->searchWVNField($searchterm)
      ->searchMAKField($searchterm)
      ->joinWithOr();

      return $this->dbquery;
  }

  /**
   * @return array Returns a representation of the search critera as a plain array.
   */
  public function getSearchAsArray() {
    return array(
      'mode' => $this->mode,
      'field' => $this->searchterm
    );
  }
}