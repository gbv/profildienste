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
use Responses\ErrorResponse;
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
}