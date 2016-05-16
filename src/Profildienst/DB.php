<?php
/**
 * * Connection to the Database
 */

/**
 * @package Profildienst
 */
namespace Profildienst;
use Config\Config;
use Middleware\AuthToken;
use MongoDB\Client;

/**
 * Connection to the Database (Singleton)
 *
 * Class DB
 */
class DB {

  /**
   * @var MongoDB Client instance
   */
  private static $m;

  /**
   * @var MongoDB database instance
   */
  private static $db;

  /**
   * @var array options
   */
  private static $opt = array(
    'safe' => true,
    'fsync' => true,
    'timeout' => 10000
  );

  /**
   * DB constructor. Since it is designed
   * as a singleton, we prevent direct instantiation
   */
  private function __construct() {
  }

  /**
   * Prevent cloning as well
   */
  private function __clone() {
  }

  /**
   * Initializes the Database connection.
   * @throws \Exception
   */
  private static function init_db() {
    // create a new instance if we can't use a previous one
    if (!isset(self::$m)) {
      self::$m = new Client();
      self::$db = self::$m->selectDatabase('pd');
      if (!isset(self::$m)) {
        throw new \Exception('Connection failed');
      }
    }
  }

  /**
   * Retrieves user data
   * @param $v string Desired aspect of user data
   * @param AuthToken $auth Token
   * @return array|null
   */
  public static function getUserData($v, AuthToken $auth) {
    if (!$c = DB::get(array('_id' => $auth->getID()), 'users', array($v => 1), true)) {
      die('Kein Benutzer unter der ID gefunden.');
    }
    return isset($c[$v]) ? $c[$v] : NULL;
  }

  /**
   * Gets a list of Titles according to the provided query.
   *
   * @param $query array The query
   * @param $skip int Amount of titles which should be skipped
   * @param AuthToken $auth Token
   * @param $sortFields
   * @return array
   * @throws \Exception
   */
  public static function getTitleList($query, $skip, AuthToken $auth, $sortFields = null) {
    self::init_db();
    $collection = self::$db->selectCollection('titles');
    $r = array();
    $options = [];

    if (!is_null($skip)) {
      $lm = Config::$pagesize;
      $options['skip'] = $lm * $skip;
      $options['limit'] = $lm;
    }

    if (!is_null($sortFields)) {
      $options['sort'] = $sortFields;
    } else {
      /*
       * TODO: Check if this is not a config option
       */
      $sortby = array('erj' => '011@.a', 'wvn' => '006U.0', 'tit' => '021A.a', 'sgr' => '045G.a', 'dbn' => '006L.0', 'per' => '028A.a');
      $settings = self::getUserData('settings', $auth);
      if ($settings['order'] == 'asc') {
        $o = 1;
      } else {
        $o = -1;
      }
      $options['sort'] = array($sortby[$settings['sortby']] => $o);
    }

    $cursor = $collection->find($query, $options);

    $cnt = $collection->count($query);

    foreach ($cursor as $doc) {
      $t = new Title($doc);
      $id = $t->getDirectly('_id');
      $r[$id] = $t;
    }

    $ret = array('titlelist' => NULL, 'total' => $cnt);

    if (count($r) > 0) {
      $ret['titlelist'] = new TitleList($r, $auth);
    }

    return $ret;
  }

  /**
   * Gets a single title which matches the query.
   * @param $query array query
   * @return null|Title
   * @throws \Exception
   */
  public static function getTitle($query) {
    self::init_db();
    $collection = self::$db->selectCollection('titles');
    $c = $collection->findOne($query);
    return $c ? new Title($c) : $c;
  }

  /**
   * Gets a title by its ID.
   *
   * @param $id string The ID
   * @return null|Title
   * @throws \Exception
   */
  public static function getTitleByID($id) {
    self::init_db();
    $collection = self::$db->selectCollection('titles');
    $query = array('_id' => $id);
    $c = $collection->findOne($query);
    return $c ? new Title($c) : $c;
  }

  /**
   * Check if titles matching the query exist in a collection.
   *
   * @param $query array Query
   * @param $coll string Name of collection
   * @return bool true if there exists a title
   * @throws \Exception
   */
  public static function exists($query, $coll) {
    self::init_db();
    $collection = self::$db->selectCollection($coll);
    $c = $collection->findOne($query);
    return $c ? true : false;
  }

  /**
   * Inserts data into the collection
   *
   * @param $data mixed Data
   * @param $coll string Name of collection
   * @throws \Exception
   */
  public static function ins($data, $coll) {
    self::init_db();
    $collection = self::$db->selectCollection($coll);
    try {
      $collection->insert($data, self::$opt);
    } catch (\Exception $mce) {
      die('Error: ' . $mce);
    }
  }

  /**
   * Gets data from the database
   *
   * @param $query array Query
   * @param $coll string Name of the collection
   * @param array $fields
   * @param bool $findone
   * @param array sortFields
   * @return array
   * @throws \Exception
   */
  public static function get($query, $coll, $fields = array(), $findone = false, $sortFields = null) {
    self::init_db();
    $collection = self::$db->selectCollection($coll);
    if ($findone) {
      $c = $collection->findOne($query, $fields);
      return $c;
    } else {
      $c = $collection->find($query, $fields);
      if (!is_null($sortFields)) {
        $c->sort($sortFields);
      }
      $r = array();
      foreach ($c as $doc) {
        array_push($r, $doc);
      }
      return $r;
    }
  }

  /**
   * Update data in the database
   *
   * @param $cond array Condition
   * @param $data mixed Data
   * @param $coll string Name of collection
   * @param $opt Options
   * @throws \Exception
   */
  public static function upd($cond, $data, $coll, $opt = null) {
    self::init_db();
    $collection = self::$db->selectCollection($coll);
    try {
      $options = is_null($opt) ? self::$opt : $opt;
      return $collection->updateMany($cond, $data, $options);
    } catch (\MongoCursorException $mce) {
      die('Error: ' . $mce);
    }
  }

  /**
   * Delete Title with the ID $id
   *
   * @param $id Title to delete
   * @param AuthToken $auth AuthToken
   * @throws \Exception
   */
  public static function deleteTitle($id, AuthToken $auth) {
    self::init_db();
    $titles = self::$db->selectCollection('titles');
    try {
      $titles->deleteOne(array('_id' => $id));
    } catch (\Exception $e) {
      return $e->getMessage();
    }
    return TRUE;
  }

  /**
   * Get the size of the watchlist with ID $id
   *
   * @param $id ID of the watchlist
   * @param AuthToken $auth
   * @return int Number of titles in the watchlist
   * @throws \Exception
   */
  public static function getWatchlistSize($id, AuthToken $auth) {
    self::init_db();
    $titles = self::$db->selectCollection('titles');
    return $titles->count(array('$and' => array(array('user' => $auth->getID()), array('watchlist' => strval($id)))));
  }

  /**
   * Get the size of the cart
   *
   * @param AuthToken $auth
   * @return int Number of titles in the cart
   * @throws \Exception
   */
  public static function getCartSize(AuthToken $auth) {
    self::init_db();
    $titles = self::$db->selectCollection('titles');
    return $titles->count(array('$and' => array(array('user' => $auth->getID()), array('status' => 'cart'))));
  }
}

?>
