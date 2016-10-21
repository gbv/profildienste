<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 20.09.16
 * Time: 17:07
 */

namespace Middleware;


use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class MaintenanceMiddleware {

    private $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {

        if ($this->ci->get('config')->isInMaintenance()){
            return $response->withStatus(503);
        }

        $response = $next($request, $response);
        return $response;
    }
}