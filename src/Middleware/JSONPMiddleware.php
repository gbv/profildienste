<?php

namespace Middleware;

/**
 * Class JSONPMiddleware
 *
 * Middleware handling JSONP requests. If a query parameter
 * named 'callback' is present, the JSON response will be wrapped
 * in a function named as specified in the callback parameter.
 *
 * @package Middleware
 */
class JSONPMiddleware {

    public function __invoke($request, $response, $next) {

        $response = $next($request, $response);
        return self::handleJSONPResponse($request, $response);
    }

    public static function handleJSONPResponse($request, $response) {

        $queryParams = $request->getQueryParams();
        if (!empty($queryParams['callback'])) {
            $response = $response->withHeader('Content-type', 'application/javascript');
            $resp_body = htmlspecialchars($queryParams['callback']) . '(' . $response->getBody() . ');';

            $body = $response->getBody();
            $body->rewind();
            $body->write($resp_body);
        }

        return $response;
    }

}