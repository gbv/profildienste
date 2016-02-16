<?php

namespace AJAX;

use Middleware\AuthToken;
use AJAX\Changers\CollectionStatusChanger;

/**
 * Removes a title from the rejected list.
 *
 * Class RemoveReject
 * @package AJAX
 */
class RemoveReject extends AJAXResponse {

    /**
     * Removes a title from the rejected list.
     *
     * @param $id string ID of the title
     * @param AuthToken $auth Token
     */
    public function __construct($ids, $view, AuthToken $auth) {

        $this->resp['id'] = array();

        try{
            CollectionStatusChanger::handleCollection($ids, $view, 'normal', $auth);
        }catch(\Exception $e){
            $this->error($e->getMessage());
            return;
        }

        $this->resp['success'] = true;
    }
}

?>
