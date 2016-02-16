<?php

namespace Content;

use Middleware\AuthToken;
use Profildienst\DB;

/**
 * Loads titles from the cart
 *
 * Class Cart
 * @package Content
 */
class Cart extends Content {


    /**
     * Loads titles from the cart
     *
     * @param $num int Page number
     * @param $auth AuthToken Token
     */
    public function __construct($num, AuthToken $auth) {

        $query = array('$and' => array(array('user' => $auth->getID()), array('status' => 'cart')));

        $t = DB::getTitleList($query, $num, $auth);
        $this->titlelist = $t['titlelist'];
        $this->total = $t['total'];
    }
}


?>