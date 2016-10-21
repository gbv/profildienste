<?php

class QueryValidatorTest extends PHPUnit_Framework_TestCase {

  public function testExceptionIfQueryEmpty() {
    $this->expectException(\Search\InvalidQueryException::class);
    new Search\QueryValidator('');
  }

  public function testInvalidQueryType() {
    $this->expectException(\Search\InvalidQueryException::class);
    new Search\QueryValidator('nonsense', 3);
  }

  public function testExceptionIfInvalidQueryTypeArrayKeyword() {
    $this->expectException(\Search\InvalidQueryException::class);
    new Search\QueryValidator([]);
  }

  public function testExceptionIfInvalidQueryTypeArraySimple() {
    $this->expectException(\Search\InvalidQueryException::class);
    new Search\QueryValidator([], 'simple');
  }

  public function testExceptionIfInvalidQueryTypeStringAdvanced() {
    $this->expectException(\Search\InvalidQueryException::class);
    new Search\QueryValidator('test', 'advanced');
  }

  public function testAcceptedQueryTypes() {
    new \Search\QueryValidator('test');
    new \Search\QueryValidator('tit test', 'simple');
    new \Search\QueryValidator([array(
      'field' => 'tit',
      'mode' => 'is',
      'value' => 'test'
    )], 'advanced');
  }

  public function testMalformedCriterium() {
    $this->expectException(\Search\InvalidQueryException::class);
    new \Search\QueryValidator([array(
      'apples' => 'banana'
    )], 'advanced');
  }

  public function testRemovalOfEmptyCritera() {

    $query = [
      array(
        'field' => 'tit',
        'mode' => 'is',
        'value' => 'test'),
      array(
        'field' => 'per',
        'mode' => 'is',
        'value' => ''),
    ];

    $sq = new \Search\QueryValidator($query, 'advanced');
    $this->assertEquals(count($sq->getSearch()), count($query) - 1);

    $query[0]['value'] = '';
    $this->expectException(\Search\InvalidQueryException::class);
    new \Search\QueryValidator($query, 'advanced');
  }

  public function testInvalidMode() {

    new \Search\QueryValidator([array(
      'field' => 'tit',
      'mode' => 'is',
      'value' => 'test'
    )], 'advanced');

    $this->expectException(\Search\InvalidQueryException::class);
    new \Search\QueryValidator([array(
      'field' => 'tit',
      'mode' => 'foo',
      'value' => 'test'
    )], 'advanced');

    $this->expectException(\Search\InvalidQueryException::class);
    new \Search\QueryValidator([array(
      'field' => 'foo',
      'mode' => 'bar',
      'value' => 'test'
    )], 'advanced');
  }

  public function testSimpleSearchRecognition() {
    $sq = new \Search\QueryValidator('tit test', 'keyword');
    $this->assertInstanceOf(\Search\FieldSearch::class, $sq->getSearch());

    $sq = new \Search\QueryValidator('xyz test', 'keyword');
    $this->assertInstanceOf(\Search\KeywordSearch::class, $sq->getSearch());

    $sq = new \Search\QueryValidator('test', 'simple');
    $this->assertInstanceOf(\Search\KeywordSearch::class, $sq->getSearch());
  }
}
