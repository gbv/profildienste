<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 15.05.16
 * Time: 16:35
 */

namespace Middleware;


use Config\Configuration;
use Firebase\JWT\JWT;

class AuthMiddleware {

  private $config;

  public function __construct(Configuration $config) {
    $this->config = $config;
  }

  public function __invoke($request, $response, $next) {

    $authHeader = $request->getHeader('Authorization');

    if (empty($authHeader)) {
      return $response->withStatus(401);
    }

    $tok = explode(' ', $authHeader[0]);


    if (count($tok) === 2 && $tok[0] === 'Bearer') {

      try {

        $decoded = JWT::decode($tok[1], $this->config->getSecretKey(), [$this->config->getTokenCryptAlgorithm()]);

        $token = (array) $decoded;
        $this->valid = true;
        $this->name = $token['sub'];
        $this->id = $token['pd_id'];

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