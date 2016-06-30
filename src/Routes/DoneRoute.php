<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 30.06.16
 * Time: 14:36
 */

namespace Routes;


class DoneRoute extends ViewRoute{

    public function getDoneView($request, $response, $args){
        $page = self::validatePage($args);
        return $this->makeTitleResponse('done', $page, $response);
    }

}