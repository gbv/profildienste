<?php

use Auth\Auth;
use Config\Configuration;
use Exceptions\BaseException;
use Middleware\AuthMiddleware;
use Middleware\JSONPMiddleware;

use Profildienst\Cart\Cart;
use Profildienst\Common\ConnectionFactory;
use Profildienst\Common\MongoDataGateway;
use Profildienst\Library\LibraryController;
use Profildienst\Title\MongoTitleGateway;
use Profildienst\Title\TitleFactory;
use Profildienst\Title\TitleRepository;
use Profildienst\User\UserController;
use Profildienst\Watchlist\MongoWatchlistGateway;
use Profildienst\Watchlist\WatchlistManager;
use Responses\ErrorResponse;
use Profildienst\User\MongoUserGateway;

require 'vendor/autoload.php';

set_error_handler(function ($errno, $errstr) {
    throw new Exception($errstr, $errno);
}, E_ALL);


$slimConfiguration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$container = new \Slim\Container($slimConfiguration);

$container['config'] = function ($container) {
    return new Configuration();
};

$container['connectionFactory'] = function ($container) {
    return new ConnectionFactory($container['config']);
};

$container['auth'] = function ($container) {
    return new Auth($container['config'], $container['userController']);
};

$container['userController'] = function ($container) {
    $gateway = new MongoUserGateway($container['connectionFactory']->getConnection());
    return new UserController($gateway);
};

$container['libraryController'] = function ($container) {
    return new LibraryController($container['config']);
};

$container['user'] = function ($container) {
    throw new RuntimeException('Method is not available in a non-user context');
};

$container['cart'] = function ($container) {
    return new Cart($container['titleRepository'], $container['dataGateway']);
};

$container['titleRepository'] = function ($container) {
    $gateway = new MongoTitleGateway($container['connectionFactory']->getConnection(), $container['config'], $container['user']);
    return new TitleRepository($gateway, $container['titleFactory']);
};

$container['titleFactory'] = function ($container) {
    return new TitleFactory();
};

$container['watchlistManager'] = function ($container) {
    $gateway = new MongoWatchlistGateway($container['connectionFactory']->getConnection(), $container['user'], $container['config']);
    return new WatchlistManager($gateway, $container['user']);
};

$container['dataGateway'] = function ($container) {
    return new MongoDataGateway($container['connectionFactory']->getConnection());
};

$app = new \Slim\App($container);
$app->add(new JSONPMiddleware());

$auth = new AuthMiddleware($container);
/*
$container['errorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {

        if ($exception instanceof BaseException) {

            $errResp = new ErrorResponse($exception->getModule() . ' error: ' . $exception->getMessage());
            return JSONPMiddleware::handleJSONPResponse($request, Route::generateJSONResponse($errResp, $response));

        } else {

            // mail

            $errResp = new ErrorResponse('An internal error occured:' . $exception->getMessage());
            return JSONPMiddleware::handleJSONPResponse($request, Route::generateJSONResponse($errResp, $response));
        }

    };
};*/

$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $response->withStatus(404);
    };
};

$app->post('/auth', '\Routes\AuthRoute:performAuthentication');
$app->get('/libraries', '\Routes\LibraryRoute:getLibraries');

/**
 * User related information
 */
$app->group('/user', function () {
    $this->get('[/]', '\Routes\UserRoute:getUserInformation');
    $this->get('/settings', '\Routes\UserRoute:getSettings');

})->add($auth);

/**
 * Settings
 */
$app->get('/settings', '\Routes\SettingsRoute:getSettings')->add($auth);
$app->post('/settings', '\Routes\SettingsRoute:changeSetting')->add($auth);

$app->group('/cart', function () {
    $this->get('[/page/{page}]', '\Routes\CartRoute:getCartView');
    $this->get('/info', '\Routes\CartRoute:getCartInformation');
    $this->post('/add', '\Routes\CartRoute:addTitlesToCart');
    $this->post('/remove', '\Routes\CartRoute:removeTitlesFromCart');
    // orderlist
    // order
})->add($auth);

$app->group('/watchlist', function () {
    $this->get('/list', '\Routes\WatchlistRoute:getWatchlists');
    $this->get('/{id}[/page/{page}]', '\Routes\WatchlistRoute:getWatchlistView');
    // add
    // remove
    // manage
})->add($auth);

$app->group('/search', function () {
    // search
    $this->get('/options', '\Routes\SearchRoute:getSearchOptions');
})->add($auth);


$app->group('/overview', function () {
    $this->get('[/page/{page}]', '\Routes\OverviewRoute:getMainView');
})->add($auth);

$app->group('/rejected', function () {
    $this->get('[/page/{page}]', '\Routes\RejectRoute:getRejectedView');
    $this->post('/add', '\Routes\RejectRoute:addRejectedTitles');
    $this->post('/remove', '\Routes\RejectRoute:removeRejectedTitles');
})->add($auth);

$app->group('/pending', function () {
    $this->get('[/page/{page}]', '\Routes\PendingRoute:getPendingView');
})->add($auth);

$app->group('/done', function () {
    $this->get('[/page/{page}]', '\Routes\DoneRoute:getDoneView');
})->add($auth);

$app->group('/titles', function () {
    /*$this->get('/title/{id}/info', '\Routes\TitleRoute:titleInfo');
    $this->get('/opac', '\Routes\TitleRoute:getOPACLink');
    $this->post('/save', '\Routes\TitleRoute:saveTitleInformation');
    $this->delete('/delete', '\Routes\TitleRoute:delete');*/
})->add($auth);

$app->run();

