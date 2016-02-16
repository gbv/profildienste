<?php

namespace AJAX;

use AJAX\Changers\CollectionStatusChanger;
use Middleware\AuthToken;

/**
 * Rejects one or multiple titles.
 *
 * Class Reject
 * @package AJAX
 */
class Reject extends AJAXResponse {

    /**
     * Rejects the titles with the given IDs.
     *
     * @param array $ids The IDs
     * @param AuthToken $auth Token
     */
    public function __construct($ids, $view, AuthToken $auth) {

        $this->resp['ids'] = array();

        try{
            CollectionStatusChanger::handleCollection($ids, $view, 'rejected', $auth);
        }catch(\Exception $e){
            $this->error($e->getMessage());
            return;
        }

        $this->resp['success'] = true;
    }
}

?>
