<?php
/**
 *  Definition of our custom error handlers
 */


use Routes\Route;
use Nette\Mail\Message;
use Config\Configuration;
use Responses\ErrorResponse;
use Nette\Mail\SendmailMailer;
use Middleware\JSONPMiddleware;
use Exceptions\UserErrorException;
use Interop\Container\ContainerInterface;
use Exceptions\CustomMailMessageException;

$errorHandler = function ($container) {
    return function ($request, $response, $exception) use ($container) {

        if ($exception instanceof UserErrorException) {

            if ($exception->shouldCauseMail()) {
                if ($exception instanceof CustomMailMessageException) {
                    sendErrorMail($exception, $container, $exception->getMailText());
                } else {
                    sendErrorMail($exception, $container);
                }
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
 * @param string $err The error message
 * @param ContainerInterface $container The DI container
 * @param string $additionalInfo Additional information about the error
 */
function sendErrorMail($err, ContainerInterface $container, $additionalInfo = '') {

    $user = '---';
    try {
        $user = $container->get('user')->getId();
    } catch (Exception $e) {}

    // construct body
    $body = sprintf("%s\n---\nAdditional Info: %s\n---\n\nUser: %s\n Date: %s", $err, $additionalInfo ,$user, date('d.m.Y - H:i:s'));

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

