<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 31.03.16
 * Time: 18:14
 */

namespace Search;


class QueryBuilder {
  /*
    // build database query
  $type = strtolower($matches[1]);
  $regexObj = new \MongoDB\BSON\Regex("/.*$matches[2].*", 'i');
  
  switch ($type) {
  case 'mak':
  $query = array('$and' => array(array('002@' => $regexObj), array('XX01' => $auth->getID())));
  break;
  case 'dbn':
  $query = array('$and' => array(array('_id' => $regexObj), array('XX01' => $auth->getID())));
  break;
  case 'isb':
  $query = array('$and' => array(array('$or' => array(array('004A.0' => $regexObj), array('004A.A' => $regexObj))), array('XX01' => $auth->getID())));
  break;
  case 'wvn':
  $query = array('$and' => array(array('006U' => $regexObj), array('XX01' => $auth->getID())));
  break;
  case 'erj':
  $query = array('$and' => array(array('011@.a' => $regexObj), array('XX01' => $auth->getID())));
  break;
  case 'sgr':
  $query = array('$and' => array(array('045G.a' => $regexObj), array('XX01' => $auth->getID())));
  break;
  case 'ref':
  $query = array('$and' => array(array('$or' => array(array('XX00.e' => $regexObj), array('XX01' => $regexObj))), array('XX01' => $auth->getID())));
  break;
  case 'per':
  
  break;
  default:
  case 'tit':
  
  break;
  }*/

  private $n = [];

  public function searchTitleField($value) {
    $this->n[] = array('$or' => [
      array('021A.a' => $value),
      array('021B.a' => $value),
      array('021A.d' => $value),
      array('021B.d' => $value),
      array('021A.l' => $value),
      array('021B.l' => $value)
    ]);
    return $this;
  }

  public function searchPersonField($value) {
    $this->n[] = array('$or' => [
      array('028C.d' => $value),
      array('028C.a' => $value),
      array('021A.h' => $value),
      array('021B.h' => $value)
    ]);
    return $this;
  }

  public function searchVerlagField($value) {
    $this->n[] = array('033A.n' => $value);
    return $this;
  }

  public function searchDNBNrField($value) {
    $this->n[] = array('006L.0' => $value);
    return $this;
  }

  public function searchDNBSachgruppeField($value) {
    $this->n[] = array('045G.a' => $value);
    return $this;
  }

  public function searchISBNField($value) {
    $this->n[] = array('$or' => [
      array('004A.0' => $value),
      array('004A.A' => $value)
    ]);
    return $this;
  }

  public function searchErscheinungsjahrField($value) {
    $this->n[] = array('011@.a' => $value);
    return $this;
  }

  public function searchWVNField($value) {
    $this->n[] = array('status' => $value);
    return $this;
  }

  public function searchMAKField($value) {
    $this->n[] = array('002@' => $value);
    return $this;
  }

  public function restrictToUser($id) {
    $this->n[] = array('user' => $id);
    return $this;
  }

  public function searchTitlesWithStatus($status) {
    $this->n[] = array('status' => $status);
    return $this;
  }

  public function joinWithAnd() {
    $this->n = [array('$and' => $this->n)];
    return $this;
  }

  public function joinWithOr() {
    $this->n = [array('$or' => $this->n)];
    return $this;
  }

  public function insertRaw($query) {
    $this->n[] = $query;
  }

  public function getQuery() {

    if (count($this->n) > 1) {
      throw new InvalidQueryException('The query is not in its final form');
    }

    return $this->n[0];
  }


}