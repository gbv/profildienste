<?php

use Responses\ErrorResponse;
use Middleware\JSONPMiddleware;
use Exceptions\UserErrorException;
use Routes\Route;

$errorHandler = function ($container) {
    return function ($request, $response, $exception) use ($container) {

        if ($exception instanceof UserErrorException) {

            $errResp = new ErrorResponse($exception->getMessage());
            return JSONPMiddleware::handleJSONPResponse($request, Route::generateJSONResponse($errResp, $response));

        } else {

            // mail .$exception->getMessage()

            $errResp = new ErrorResponse('Leider ist ein interner Fehler aufgetreten. Eine anonymisierte Fehlermeldung wurde erstellt und wird schnellstmöglichst bearbeitet.', 500);
            return JSONPMiddleware::handleJSONPResponse($request, Route::generateJSONResponse($errResp, $response));
        }

    };
};

function initErrorHandling(\Interop\Container\ContainerInterface $container) {
    global $errorHandler;
    $container['errorHandler'] = $errorHandler;
    $container['phpErrorHandler'] = $errorHandler;

    $container['notFoundHandler'] = function ($container) {
        return function ($request, $response) use ($container) {
            return $response->withStatus(404);
        };
    };
}

