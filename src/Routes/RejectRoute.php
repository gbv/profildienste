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

    public function getRejectedView($request, $response, $args) {
        $page = self::validatePage($args);
        return $this->makeTitleResponse('rejected', $page, $response);
    }

    public function addRejectedTitles($request, $response, $args) {

        $parameters = $request->getParsedBody();

        $affected = $parameters['affected'];

        if (is_null($affected) || empty($affected)) {
            throw new UserException('At least one title must be affected by the change!');
        }

        if(is_array($affected)){
            $this->titleRepository->changeStatusOfTitles($affected, 'rejected');
        } else {
            // view betroffen TODO
        }
        
        return self::generateJSONResponse(new ActionResponse($affected, 'rejected'), $response);

    }

    public function removeRejectedTitles($request, $response, $args) {

        $parameters = $request->getParsedBody();

        $affected = $parameters['affected'];

        if (is_null($affected) || empty($affected)) {
            throw new UserException('At least one title must be affected by the change!');
        }

        if(is_array($affected)){
            $this->titleRepository->changeStatusOfTitles($affected, 'normal');
        } else {
            // view betroffen TODO
        }

        return self::generateJSONResponse(new ActionResponse($affected, 'overview'), $response);

    }


    //
///** TODO */
// * Reject
// */
//$app->group('/reject', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $app->post('/remove', function () use ($app, $auth) {
//    $id = $app->request()->post('id');
//    $view = $app->request()->post('view');
//
//    $m = new \AJAX\RemoveReject($id, $view, $auth);
//    printResponse($m->getResponse());
//  });
//
//
//  $app->post('/add', function () use ($app, $auth) {
//    $id = $app->request()->post('id');
//    $view = $app->request()->post('view');
//
//    $m = new \AJAX\Reject($id, $view, $auth);
//    printResponse($m->getResponse());
//  });
//
//});
//


}