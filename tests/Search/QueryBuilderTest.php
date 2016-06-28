<?php

class QueryBuilderTest extends PHPUnit_Framework_TestCase {

  public function testQuery() {
    $q = new \Search\QueryBuilder();
    $q
      ->searchTitleField('Op1')
      ->searchTitleField('Op2')
      ->joinWithOr();
    var_dump(json_encode($q->getQuery()));
  }
}
