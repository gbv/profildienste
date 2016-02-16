<?php

namespace Content;

use Middleware\AuthToken;
use Profildienst\DB;

/**
 * Loads title from the Main view.
 *
 * Class Main
 * @package Content
 */
class Main extends Content {

    /**
     * Loads title from the Main view.
     *
     * @param $num int Page number
     * @param AuthToken $auth Token
     */
    public function __construct($num, AuthToken $auth) {

        $query = array('$and' => array(array('user' => $auth->getID()), array('status' => 'normal')));

        $t = DB::getTitleList($query, $num, $auth);
        $this->titlelist = $t['titlelist'];
        $this->total = $t['total'];
    }
}

?>
