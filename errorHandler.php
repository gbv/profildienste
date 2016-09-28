<?php

use Config\Configuration;
use Interop\Container\ContainerInterface;
use Responses\ErrorResponse;
use Middleware\JSONPMiddleware;
use Exceptions\UserErrorException;
use Routes\Route;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

$errorHandler = function ($container) {
    return function ($request, $response, $exception) use ($container) {

        if ($exception instanceof UserErrorException) {

            if ($exception->shouldCauseMail()) {
                sendErrorMail($exception, $container);
            }

            $errResp = new ErrorResponse($exception->getMessage());
            return JSONPMiddleware::handleJSONPResponse($request, Route::generateJSONResponse($errResp, $response));

        } else {

            sendErrorMail($exception, $container);

            $errResp = new ErrorResponse('Leider ist ein interner Fehler aufgetreten. Eine anonymisierte Fehlermeldung wurde erstellt und wird schnellstmöglichst bearbeitet.', 500);
            return JSONPMiddleware::handleJSONPResponse($request, Route::generateJSONResponse($errResp, $response));
        }

    };
};

/**
 * @param Exception $ex
 * @param Configuration $config
 */
function sendErrorMail($err, ContainerInterface $container) {

    $user = '---';
    try {
        $user = $container->get('user')->getId();
    } catch (Exception $e) {}

    // construct body
    $body = sprintf("%s\n\nUser: %s\n Date: %s", $err, $user, date('d.m.Y - H:i:s'));

    $mail = new Message;

    foreach ($container->get('config')->getLogMailAddresses() as $address) {
        $mail->addTo($address);
    }

    $mail->setFrom('Profildienst Error <noreply@online-profildienst.gbv.de>')
        ->setSubject('Fehler beim Profildienst aufgetreten')
        ->setBody($body);

    $mailer = new SendmailMailer;
    try {
        $mailer->send($mail);
    } catch (Exception $e) {}
}

function initErrorHandling(ContainerInterface $container) {
    global $errorHandler;
    $container['errorHandler'] = $errorHandler;
    $container['phpErrorHandler'] = $errorHandler;

    $container['notFoundHandler'] = function ($container) {
        return function ($request, $response) use ($container) {
            return $response->withStatus(404);
        };
    };
}

