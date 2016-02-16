<?php

namespace Content;

use Middleware\AuthToken;
use Profildienst\DB;

/**
 * Loads rejected titles.
 *
 * Class Rejected
 * @package Content
 */
class Rejected extends Content {

    /**
     * Loads rejected titles.
     *
     * @param $num int page number
     * @param $auth AuthToken Token
     */
    public function __construct($num, AuthToken $auth) {

        $query = array('$and' => array(array('user' => $auth->getID()), array('status' => 'rejected')));

        $t = DB::getTitleList($query, $num, $auth);
        $this->titlelist = $t['titlelist'];
        $this->total = $t['total'];
    }

}

?>
