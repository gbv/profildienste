<?php

namespace Middleware;

use Firebase\JWT\JWT;
use Interop\Container\ContainerInterface;

/**
 * Class AuthMiddleware
 *
 * Authentication middleware. This middleware checks if the user is authenticated
 * and registers the user to the DI container (upon successful authentication).
 * Authentication is performed by passing a JWT in the 'Authorization'-Header of
 * the request.
 *
 * @package Middleware
 */
class AuthMiddleware {

    private $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function __invoke($request, $response, $next) {

        $authHeader = $request->getHeader('Authorization');

        if (empty($authHeader)) {
            return $response->withStatus(401);
        }

        $tok = explode(' ', $authHeader[0]);


        if (count($tok) === 2 && $tok[0] === 'Bearer') {

            try {

                $decoded = JWT::decode($tok[1], $this->ci['config']->getSecretKey(),
                    [$this->ci['config']->getTokenCryptAlgorithm()]);

                // initialize the user object
                $token = (array)$decoded;
                $user = $this->ci['userController']->findById($token['pd_id']);
                $user->setName($token['sub']);
                $this->ci['user'] = function ($container) use ($user) {
                    return $user;
                };

            } catch (\Exception $e) {
                return $response->withStatus(401);
            }
        } else {
            return $response->withStatus(401);
        }

        $response = $next($request, $response);
        return $response;
    }

}