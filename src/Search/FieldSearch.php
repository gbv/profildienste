<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 02.04.16
 * Time: 13:26
 */

namespace Search;

class FieldSearch extends KeywordSearch{

  private $field;

  public function __construct(){
    parent::__construct('simple');
  }

  public function setField($field){
    $this->field = $field;
  }

  public function getField(){
    return $this->field;
  }

  /**
   * @return QueryBuilder Query ready to be used for the database
   */
  public function getDatabaseQuery() {

    $searchterm = $this->handleSearchterm($this->getSearchterm(), $this->getMode());

    switch ($this->field) {
      case 'tit':
        $this->dbquery->searchTitleField($searchterm);
        break;
      case 'per':
        $this->dbquery->searchPersonField($searchterm);
        break;
      case 'ver':
        $this->dbquery->searchVerlagField($searchterm);
        break;
      case 'dnb':
        $this->dbquery->searchDNBNrField($searchterm);
        break;
      case 'isb':
        $this->dbquery->searchISBNField($searchterm);
        break;
      case 'sgr':
        $this->dbquery->searchDNBSachgruppeField($searchterm);
        break;
      case 'erj':
        $this->dbquery->searchErscheinungsjahrField($searchterm);
        break;
      case 'wvn':
        $this->dbquery->searchWVNField($searchterm);
        break;
      case 'mak':
        $this->dbquery->searchMAKField($searchterm);
        break;
    }

    return $this->dbquery;
  }
}