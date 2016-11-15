<?php

use Profildienst\Search\QueryBuilder;

class QueryBuilderTest extends PHPUnit_Framework_TestCase {

  public function testQuery() {
    $q = new QueryBuilder();
    $q->searchTitleField('Op1')
      ->searchTitleField('Op2')
      ->joinWithOr();
    var_dump(json_encode($q->getQuery()));
  }
}
