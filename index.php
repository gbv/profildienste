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
use Profildienst\Search\MongoSearchGateway;
use Profildienst\Search\SearchFactory;
use Profildienst\Title\MongoTitleGateway;
use Profildienst\Title\TitleFactory;
use Profildienst\Title\TitleRepository;
use Profildienst\User\UserController;
use Profildienst\Watchlist\MongoWatchlistGateway;
use Profildienst\Watchlist\WatchlistManager;
use Responses\ErrorResponse;
use Profildienst\User\MongoUserGateway;
use Routes\Route;

require 'vendor/autoload.php';

$slimConfiguration = [
    'settings' => [
        'displayErrorDetails' => true,
    ]
];

$container = new \Slim\Container($slimConfiguration);
/*
$errorHandler = function ($container) {
    return function ($request, $response, $exception) use ($container) {

        if ($exception instanceof BaseException) {

            $errResp = new ErrorResponse($exception->getModule() . ' error: ' . $exception->getMessage());
            return JSONPMiddleware::handleJSONPResponse($request, Route::generateJSONResponse($errResp, $response));

        } else {

            // mail

            $errResp = new ErrorResponse('An internal error occured');
            return JSONPMiddleware::handleJSONPResponse($request, Route::generateJSONResponse($errResp, $response));
        }

    };
};

$container['errorHandler'] = $errorHandler;
$container['phpErrorHandler'] = $errorHandler;*/

$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $response->withStatus(404);
    };
};


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
    return new TitleFactory($container['watchlistManager']);
};

$container['watchlistManager'] = function ($container) {
    $gateway = new MongoWatchlistGateway($container['connectionFactory']->getConnection(), $container['user'], $container['config']);
    return new WatchlistManager($gateway);
};

$container['dataGateway'] = function ($container) {
    return new MongoDataGateway($container['connectionFactory']->getConnection());
};

$container['searchFactory'] = function ($container){
    return new SearchFactory($container['config'], $container['searchGateway'], $container['titleFactory']);
};

$container['searchGateway'] = function ($container){
  return new MongoSearchGateway($container['connectionFactory']->getConnection(), $container['user'], $container['config']);
};

$app = new \Slim\App($container);

$app->add(new JSONPMiddleware());
$auth = new AuthMiddleware($container);

$app->post('/auth', '\Routes\AuthRoute:performAuthentication');
$app->get('/libraries', '\Routes\LibraryRoute:getLibraries');

$app->group('/user', function () {
    $this->get('[/]', '\Routes\UserRoute:getUserInformation');
    $this->get('/settings', '\Routes\UserRoute:getSettings');
    $this->post('/settings', '\Routes\UserRoute:changeSetting');

})->add($auth);

$app->get('/settings', '\Routes\SettingsRoute:getSettings')->add($auth);

$app->group('/cart', function () {
    $this->get('[/page/{page}]', '\Routes\CartRoute:getCartView');
    $this->get('/info', '\Routes\CartRoute:getCartInformation');
    $this->post('/add', '\Routes\CartRoute:addTitlesToCart');
    $this->post('/remove', '\Routes\CartRoute:removeTitlesFromCart');
    $this->get('/orderlist', '\Routes\CartRoute:getOrderlist');
    // order
})->add($auth);

$app->group('/watchlist', function () {
    $this->get('/list', '\Routes\WatchlistRoute:getWatchlists');
    $this->get('/{id}[/page/{page}]', '\Routes\WatchlistRoute:getWatchlistView');
    $this->post('/{id}/add', '\Routes\WatchlistRoute:addTitlesToWatchlist');
    $this->post('/{id}/remove', '\Routes\WatchlistRoute:removeTitlesFromWatchlist');
    $this->delete('/{id}', '\Routes\WatchlistRoute:deleteWatchlist');
    $this->put('/new', '\Routes\WatchlistRoute:addWatchlist');
    $this->patch('/order', '\Routes\WatchlistRoute:changeWatchlistOrder');
    $this->post('/{id}/rename', '\Routes\WatchlistRoute:renameWatchlist');
    $this->post('/default', '\Routes\WatchlistRoute:changeDefaultWatchlist');
})->add($auth);

$app->group('/search', function () {
    $this->get('/{query}/{queryType}[/page/{page}]', '\Routes\SearchRoute:searchTitles');
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
    $this->get('/{id}/info', '\Routes\TitleRoute:titleInfo');
    $this->get('/{id}/opac', '\Routes\TitleRoute:getOPACLink');
    $this->post('/save', '\Routes\TitleRoute:saveTitleInformation');
    //$this->delete('/delete', '\Routes\TitleRoute:delete');
})->add($auth);

$app->run();

