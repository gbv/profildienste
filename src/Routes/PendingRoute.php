<?php

namespace Routes;


class PendingRoute extends ViewRoute{

    public function getPendingView($request, $response, $args){
        $page = self::validatePage($args);
        $offset = self::validateOffset($args);
        return $this->makeTitleResponse('pending', $page, $response, true, $offset);
    }

}