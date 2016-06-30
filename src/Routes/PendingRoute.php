<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 30.06.16
 * Time: 14:36
 */

namespace Routes;


class PendingRoute extends ViewRoute{

    public function getPendingView($request, $response, $args){
        $page = self::validatePage($args);
        return $this->makeTitleResponse('pending', $page, $response);
    }

}