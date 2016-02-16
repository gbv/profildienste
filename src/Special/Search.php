<?php

namespace Special;

use Middleware\AuthToken;
use Profildienst\DB;
use Profildienst\TitleList;

/**
 * Performs a search.
 *
 * Class Search
 * @package Special
 */
class Search {

    /**
     * @var TitleList  List of found titles
     */
    private $titlelist;
    /**
     * @var int total amount of titles
     */
    private $total;

    /**
     * @param $q string Search query
     * @param $num int Requested page
     * @param AuthToken $auth Token
     * @throws \Exception
     */
    public function __construct($q, $num, AuthToken $auth) {

        if ($q === '') {
            throw new \Exception('Bitte geben Sie eine Suchanfrage ein.');
        }

        // check if format is valid
        preg_match('/^(.*?){3,4}\s(.*)$/', $q, $matches);

        if (sizeof($matches) != 3) {
            throw new \Exception('Bitte geben Sie eine valide Suchanfrage ein. Sie können die Hilfe aufrufen um weitere Informationen zu erhalten.');
        }

        // build database query
        $type = strtolower($matches[1]);
        $regexObj = new \MongoRegex("/.*$matches[2].*/i");

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
                $query = array('$and' => array(array('$or' => array(array('028C.d' => $regexObj), array('028C.a' => $regexObj), array('021A.h' => $regexObj), array('021B.h' => $regexObj))), array('XX01' => $auth->getID())));
                break;
            default:
            case 'tit':
                $query = array('$and' => array(array('$or' => array(array('021A.a' => $regexObj), array('021B.a' => $regexObj), array('021A.d' => $regexObj), array('021B.d' => $regexObj), array('021A.l' => $regexObj), array('021B.l' => $regexObj))), array('XX01' => $auth->getID())));
                break;
        }

        // perform search
        $t = DB::getTitleList($query, $num, $auth);
        $this->titlelist = $t['titlelist'];
        $this->total = $t['total'];
    }

    /**
     * Getter for titles
     *
     * @return TitleList Found titles
     */
    public function getTitles() {
        return $this->titlelist;
    }

    /**
     * Getter for the total amount of titles
     *
     * @return int total amount of titles found
     */
    public function getTotalCount() {
        return $this->total;
    }
}
?>
