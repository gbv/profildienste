<?php

namespace AJAX;

use Middleware\AuthToken;
use Profildienst\DB;
use AJAX\Changers\CollectionStatusChanger;

/**
 * Removes a title from the cart.
 *
 * Class RemoveCart
 * @package AJAX
 */
class RemoveCart extends AJAXResponse
{

    /**
     * Removes a title from the cart.
     *
     * @param $id
     * @param $view
     * @param AuthToken $auth Token
     */
    public function __construct($ids, $view, AuthToken $auth){

        $this->resp['content'] = NULL;
        $this->resp['ids'] = NULL;
        $this->resp['price'] = array();

        // check if we got all the data we need
        if ($view === '' && (is_null($ids) || !is_array($ids) || count($ids) === 0)) {
            $this->error('Unvollständige Daten');
            return;
        }

        if($view !== '' && $view === 'overview'){
            $view = 'normal';
        }

        // prepare part of response
        $this->resp['ids'] = $ids;

        $query = array(
            '$and' => array(
                array('user' => $auth->getID()),
                array('_id' => array('$in' => $ids))
            )
        );

        // change query if we want to update a whole view
        if($view !== ''){
            $query = array(
                '$and' => array(
                    array('user' => $auth->getID()),
                    array('status' => $view)
                )
            );
        }

        $t = DB::getTitleList($query, NULL, $auth);

        if($t['total'] === 0){
            $this->error('Keine passenden Titel gefunden!');
            return;
        }

        $titles = $t['titlelist']->getTitles();

        $p = DB::getUserData('price', $auth);
        $mean = DB::get(array('_id' => 'mean'), 'data', array(), true);

        foreach($titles as $tit){

            $pr = $tit->getEURPrice();

            if (is_null($pr)) {
                // price was unknown
                $p['est'] = $p['est'] - 1;
                $pr = $mean['value'];

            } else {
                // price was known
                $p['known'] = $p['known'] - 1;
            }

            $p['price'] = $p['price'] - $pr;
            if($p['price'] < 0){
                $p['price'] = 0;
            }
        }

        //update the total price for the whole cart
        DB::upd(array('_id' => $auth->getID()), array('$set' => array('price' => $p)), 'users');

        // insert updated price into response
        $p['price'] = number_format($p['price'], 2, '.', '');
        $this->resp['price'] = $p;

        if($view === ''){
            CollectionStatusChanger::changeStatusOfCollection($ids, 'normal', $auth);
        }else{
            CollectionStatusChanger::changeStatusOfView($view, 'normal', $auth);
        }

        $this->resp['content'] = DB::getCartSize($auth);
        $this->resp['success'] = true;

    }
}

?>
