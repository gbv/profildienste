<?php

namespace Middleware;


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