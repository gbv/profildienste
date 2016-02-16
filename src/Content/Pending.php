<?php

namespace Content;

use Middleware\AuthToken;
use Profildienst\DB;

/**
 * Loads titles for the pending view.
 *
 * Class Pending
 * @package Content
 */
class Pending extends Content {

    /**
     * Loads titles for the pending view.
     *
     * @param $num int Page number
     * @param AuthToken $auth Token
     */
    public function __construct($num, AuthToken $auth) {

        $query = array('$and' => array(array('user' => $auth->getID()), array('status' => 'pending')));

        $t = DB::getTitleList($query, $num, $auth, array('lastStatusChange' => -1));
        $this->titlelist = $t['titlelist'];
        $this->total = $t['total'];

    }

}

?>
