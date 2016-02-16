<?php

namespace Special;

use Middleware\AuthToken;
use Profildienst\DB;

/**
 * Creates a list with basic information of all orders.
 *
 * Class Orderlist
 * @package Special
 */
class Orderlist {

    /**
     * @var array List of all orders
     */
    private $orderlist = array();

    /**
     * Creates a new Orderlist.
     *
     * @param $auth
     * @throws \Exception
     */
    public function __construct(AuthToken $auth) {


        if (DB::getCartSize($auth) == 0) {
            throw new \Exception('Keine Titel im Warenkorb.');
        }

        $query = array('$and' => array(array('user' => $auth->getID()), array('status' => 'cart')));

        $t = DB::getTitleList($query, NULL, $auth);
        $titlelist = $t['titlelist'];

        foreach ($titlelist->getTitles() as $tit) {
            $this->orderlist[] = array(
                'lieft' => $tit->getLft(),
                'budget' => $tit->getBdg(),
                'ssgnr' => $tit->getSSGNr(),
                'selcode' => $tit->getSelcode(),
                'comment' => $tit->getComment(),
                'titel' => $tit->get('titel'),
                'gvkt' => $tit->get('gvkt_mak')
            );
        }
    }

    /**
     * Getter for the Orderlist
     *
     * @return array
     */
    public function getOrderlist() {
        return $this->orderlist;
    }
}

?>
