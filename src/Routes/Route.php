<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 31.05.16
 * Time: 19:02
 */

namespace Routes;


use Interop\Container\ContainerInterface;
use Responses\APIResponse;
use Responses\TitlelistResponse;
use Slim\Http\Response;

abstract class Route {

    protected $ci;

    public function __construct(ContainerInterface $ci){
        $this->ci = $ci;
    }

    public static function generateJSONResponse(APIResponse $response, Response $out) {

        $resp = [];

        $status = $response->getHTTPReturnCode();
        if ($response instanceof ErrorResponse) {
            $resp['error'] = $response->getData();
        } else {
            $resp['data'] = $response->getData();
        }

        return $out->withJson($resp, $status);

    }

    protected static function validatePage($args){
        $page = ($args['page'] ?? 0);

        if (empty($page) || !filter_var($page, FILTER_VALIDATE_INT) || $page < 0){
            $page = 0;
        }

        return $page;
    }

    public function titlePageResponse($titles, $page, $totalCount, $response){

        $more = ($this->ci->get('config')->getPagesize() * ($page+1) < $totalCount);

        return self::generateJSONResponse(new TitlelistResponse($titles, $totalCount, $more), $response);
    }

}