<?php

namespace Profildienst;
use Config\Config;
use Middleware\AuthToken;
use MongoClient;
use MongoCollection;
use MongoDB;

/**
 * Connection to the Database (Singleton)
 *
 * Class DB
 * @package Profildienst
 */
class DB {

    /**
     * @var MongoClient database instance
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

    // prevent direct instantiation
    private function __construct() {
    }

    // prevent cloning
    private function __clone() {
    }

    /**
     * Initializes the Database connection.
     * @throws \Exception
     */
    private static function init_db() {
        // create a new instance if we can't use a previous one
        if (!isset(self::$m)) {
            self::$m = new MongoClient();
            self::$db = self::$m->selectDB('pd');
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
     * @return array
     * @throws \Exception
     */
    public static function getTitleList($query, $skip, AuthToken $auth, $sortFields = null) {
        self::init_db();
        $collection = new \MongoCollection(self::$db, 'titles');
        $r = array();
        $cursor = $collection->find($query);
        if(!is_null($sortFields)){
            $cursor->sort($sortFields);
        }
        $cnt = $cursor->count();
        $settings = self::getUserData('settings', $auth);
        if ($settings['order'] == 'asc') {
            $o = 1;
        } else {
            $o = -1;
        }

        if (!is_null($skip)) {
            $lm = Config::$pagesize;
            $cursor = $cursor->skip($lm * $skip);
            $cursor = $cursor->limit($lm);
        }

        // only sort the regular way if sortFields has no value
        if(is_null($sortFields)){
            $sortby = array('erj' => '011@.a', 'wvn' => '006U.0', 'tit' => '021A.a', 'sgr' => '045G.a', 'dbn' => '006L.0', 'per' => '028A.a');
            $cursor = $cursor->sort(array($sortby[$settings['sortby']] => $o));
        }
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
        $collection = new \MongoCollection(self::$db, 'titles');
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
        $collection = new \MongoCollection(self::$db, 'titles');
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
        $collection = new \MongoCollection(self::$db, $coll);
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
        $collection = new \MongoCollection(self::$db, $coll);
        try {
            $collection->insert($data, self::$opt);
        } catch (\MongoCursorException $mce) {
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
     * @return array
     * @throws \Exception
     */
    public static function get($query, $coll, $fields = array(), $findone = false, $sortFields = null) {
        self::init_db();
        $collection = new \MongoCollection(self::$db, $coll);
        if ($findone) {
            $c = $collection->findOne($query, $fields);
            return $c;
        } else {
            $c = $collection->find($query, $fields);
            if(!is_null($sortFields)){
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
     * @throws \Exception
     */
    public static function upd($cond, $data, $coll, $opt = null) {
        self::init_db();
        $collection = new \MongoCollection(self::$db, $coll);
        try {
            $options = is_null($opt) ? self::$opt : $opt;
            return $collection->update($cond, $data, $options);
        } catch (\MongoCursorException $mce) {
            die('Error: ' . $mce);
        }
    }

    /**
     * @param $id Title to delete
     * @param AuthToken $auth AuthToken
     * @throws \Exception
     */
    public static function deleteTitle($id, AuthToken $auth){
        self::init_db();
        $titles = new \MongoCollection(self::$db, 'titles');
        try{
            $titles->remove(array('_id' => $id));
        }catch(\Exception $e) {
            return $e->getMessage();
        }
        return TRUE;
    }

    /**
     * @param $id Id of the watchlist
     * @param AuthToken $auth
     * @return int Number of titles in the watchlist
     * @throws \Exception
     */
    public static function getWatchlistSize($id, AuthToken $auth){
        self::init_db();
        $titles = new \MongoCollection(self::$db, 'titles');
        return $titles->find(array('$and' => array(array('user' => $auth->getID()), array('watchlist' => strval($id)))))->count();
    }

    /**
     * @param AuthToken $auth
     * @return int Number of titles in the cart
     * @throws \Exception
     */
    public static function getCartSize(AuthToken $auth){
        self::init_db();
        $titles = new \MongoCollection(self::$db, 'titles');
        return $titles->find(array('$and' => array(array('user' => $auth->getID()), array('status' => 'cart'))))->count();
    }
}

?>
