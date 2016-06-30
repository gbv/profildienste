<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 30.06.16
 * Time: 11:31
 */

namespace Routes;


class OverviewRoute extends ViewRoute{

    public function getMainView($request, $response, $args){
        $page = self::validatePage($args);
        return $this->makeTitleResponse('normal', $page, $response);
    }

}