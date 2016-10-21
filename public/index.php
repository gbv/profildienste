<?php

require '../vendor/autoload.php';

require '../bootstrap/init.php';
require '../bootstrap/errorHandler.php';
require '../bootstrap/routes.php';

use Middleware\AuthMiddleware;
use Middleware\JSONPMiddleware;
use Middleware\MaintenanceMiddleware;
use Middleware\PublicPathMiddleware;

$slimConfiguration = [
    'settings' => [
        'displayErrorDetails' => false,
    ]
];

/**
 * Initialise the DI container and error handling
 */
$container = new \Slim\Container($slimConfiguration);
initErrorHandling($container);
initContainer($container);

/**
 * Create the app and init define all available routes
 */
$app = new \Slim\App($container);

$app->add(new MaintenanceMiddleware($container));
$app->add(new PublicPathMiddleware());
$app->add(new JSONPMiddleware());
$auth = new AuthMiddleware($container);

initRoutes($app);

$app->run();


