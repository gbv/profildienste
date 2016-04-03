<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 02.04.16
 * Time: 13:26
 */

namespace Search;


class MultipleFieldSearch extends SearchQuery{

  private $criteria;

  public function __construct(){
    parent::__construct('advanced');
  }

  public function setCriteria($criteria){
    $this->criteria = $criteria;
  }

  public function getCriteria(){
    return $this->criteria;
  }

  /**
   * @return QueryBuilder Query ready to be used for the database
   */
  public function getDatabaseQuery() {

    // collect all critera referencing the same field
    $criteria = [];
    foreach ($this->criteria as $searchCriterion) {
      if (isset($criteria[$searchCriterion->getField()])) {
        $criteria[$searchCriterion->getField()][] = $searchCriterion;
      } else {
        $criteria[$searchCriterion->getField()] = [$searchCriterion];
      }
    }

    //build query

    foreach ($criteria as $searchkey => $searchCriteria) {
      if(count($searchCriteria) > 1){
        $subquery = new QueryBuilder();
        foreach($searchCriteria as $searchCriterion){
          $subquery->insertRaw($searchCriterion->getDatabaseQuery()->getQuery());
        }
        $subquery->joinWithOr();
        $this->dbquery->insertRaw($subquery->getQuery());
      }else{
        $this->dbquery->insertRaw($searchCriteria[0]->getDatabaseQuery()->getQuery());
      }
    }

    return $this->dbquery;
  }

  /**
   * @return array Returns a representation of the search critera as a plain array.
   */
  public function getSearchAsArray() {
    $r = [];
    foreach($this->criteria as $searchCriterion){
      $r[] = $searchCriterion->getSearchAsArray();
    }
    return $r;
  }
}