<?php

namespace Routes;


use Responses\ActionResponse;
use Exceptions\UserErrorException;

class RejectRoute extends ViewRoute {

    use ActionHandler;

    public function getRejectedView($request, $response, $args) {
        $page = self::validatePage($args);
        return $this->makeTitleResponse('rejected', $page, $response);
    }

    public function addRejectedTitles($request, $response, $args) {

        $affected = $this->handleStatusChange($request, 'rejected', function ($oldState) {
            return in_array($oldState, ['normal', 'watchlist']);
        });

        if (is_null($affected)) {
            throw new UserErrorException('Failed to update rejected titles.');
        }

        return self::generateJSONResponse(new ActionResponse($affected, 'rejected'), $response);

    }

    public function removeRejectedTitles($request, $response, $args) {

        $affected = $this->handleStatusChange($request, 'normal', function ($oldState){
            return $oldState === 'rejected';
        });

        if (is_null($affected)) {
            throw new UserErrorException('Failed to remove rejected titles.');
        }

        return self::generateJSONResponse(new ActionResponse($affected, 'overview'), $response);

    }

}