<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 30.06.16
 * Time: 11:38
 */

namespace Routes;


use Exceptions\UserException;
use Responses\ActionResponse;

class RejectRoute extends ViewRoute {

    use ActionHandler;

    public function getRejectedView($request, $response, $args) {
        $page = self::validatePage($args);
        return $this->makeTitleResponse('rejected', $page, $response);
    }

    public function addRejectedTitles($request, $response, $args) {

        $affected = $this->handleStatusChange($request, 'rejected', function ($oldState) {
            return $oldState !== 'cart' && $oldState !== 'done' && $oldState !== 'pending';
        });
        if (is_null($affected)) {
            throw new UserException('Failed to update rejected titles.');
        }

        return self::generateJSONResponse(new ActionResponse($affected, 'rejected'), $response);

    }

    public function removeRejectedTitles($request, $response, $args) {

        $affected = $this->handleStatusChange($request, 'normal', function ($oldState){
            return $oldState === 'rejected';
        });
        if (is_null($affected)) {
            throw new UserException('Failed to remove rejected titles.');
        }

        return self::generateJSONResponse(new ActionResponse($affected, 'overview'), $response);

    }

}