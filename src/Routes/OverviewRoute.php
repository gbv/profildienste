<?php

namespace Routes;


class OverviewRoute extends ViewRoute{

    public function getMainView($request, $response, $args){
        $page = self::validatePage($args);
        return $this->makeTitleResponse('normal', $page, $response);
    }

}