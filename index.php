<?php

use Auth\Auth;
use Config\Configuration;
use Exceptions\BaseException;
use Middleware\AuthMiddleware;
use Middleware\JSONPMiddleware;
use Profildienst\Cart\CartController;
use Profildienst\Cart\Cart;
use Profildienst\Cart\MongoCartGateway;
use Profildienst\ConnectionFactory;
use Profildienst\Library\LibraryController;
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
    return new Cart($container['titleRepository']);
};

$container['titleRepository'] = function ($container) {
    $gateway = new MongoTitleGateway($container['connectionFactory']->getConnection());
    return new TitleRepository($gateway, $container['titleFactory'] ,$container['user'], $container['config']);
};

$container['titleFactory'] = function ($container){
    return new TitleFactory();
};

$container['watchlistManager'] = function ($container){
    $gateway = new MongoWatchlistGateway($container['connectionFactory']->getConnection(), $container['user'], $container['config']);
    return new WatchlistManager($gateway, $container['user']);
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

//
///**
// * Watchlists
// */
//$app->group('/watchlist', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $app->post('/remove', function () use ($app, $auth) {
//
//    $id = $app->request()->post('id');
//    $wl = $app->request()->post('wl');
//
//    $m = new \AJAX\RemoveWatchlist($id, $wl, $auth);
//    printResponse($m->getResponse());
//  });
//
//  $app->post('/add', function () use ($app, $auth) {
//
//    $id = $app->request()->post('id');
//    $wl = $app->request()->post('wl');
//
//    $m = new \AJAX\Watchlist($id, $wl, $auth);
//    printResponse($m->getResponse());
//  });
//
//  $app->post('/manage', function () use ($app, $auth) {
//
//    $id = $app->request()->post('id');
//    $type = $app->request()->post('type');
//    $content = $app->request()->post('content');
//
//    $m = new \AJAX\WatchlistManager($id, $type, $content, $auth);
//    printResponse($m->getResponse());
//  });
//
//});
//
///**
// * Cart
// */
//$app->group('/cart', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $app->post('/remove', function () use ($app, $auth) {
//
//    $id = $app->request()->post('id');
//    $view = $app->request()->post('view');
//
//    $m = new \AJAX\RemoveCart($id, $view, $auth);
//    printResponse($m->getResponse());
//  });
//
//
//  $app->post('/add', function () use ($app, $auth) {
//
//    $id = $app->request()->post('id');
//    $view = $app->request()->post('view');
//
//    $m = new \AJAX\Cart($id, $view, $auth);
//    printResponse($m->getResponse());
//  });
//
//});
//
///**
// * Reject
// */
//$app->group('/reject', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $app->post('/remove', function () use ($app, $auth) {
//    $id = $app->request()->post('id');
//    $view = $app->request()->post('view');
//
//    $m = new \AJAX\RemoveReject($id, $view, $auth);
//    printResponse($m->getResponse());
//  });
//
//
//  $app->post('/add', function () use ($app, $auth) {
//    $id = $app->request()->post('id');
//    $view = $app->request()->post('view');
//
//    $m = new \AJAX\Reject($id, $view, $auth);
//    printResponse($m->getResponse());
//  });
//
//});
//

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

///**
// * Order
// */
//$app->post('/order', $authenticate($app, $auth), function () use ($app, $auth) {
//  $m = new \Special\Order($auth);
//  printResponse($m->getResponse());
//});
//

$app->group('/cart', function () {
    $this->get('/titles[/page/{page}]', '\Routes\CartRoute:getCartView');
    $this->get('/info', '\Routes\CartRoute:getCartInformation');
    // add
    // remove
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
    $this->get('[/page/{page}]', '\Routes\TitleRoute:getMainView');
})->add($auth);

$app->group('/title', function () {
    $this->post('/info', '\Routes\TitleRoute:titleInfo');
    $this->post('/opac', '\Routes\TitleRoute:getOPACLink');
})->add($auth);
/*
$this->get('/pending[/page/{page}]', '\Routes\ViewRoute:getPendingView');
$this->get('/done[/page/{page}]', '\Routes\ViewRoute:getDoneView');
$this->get('/rejected[/page/{page}]', '\Routes\ViewRoute:getRejectedView');
$this->post('/save', '\Routes\TitleRoute:saveTitleInformation');
$this->post('/delete', '\Routes\TitleRoute:delete');
*/

//  $app->get('/search/:query/:queryType/page/:num', function ($query, $queryType = 'keyword', $num = 0) use ($app, $auth) {
//    try {
//
//      if($queryType === 'advanced'){
//        $query = json_decode($query, true);
//      }
//
//      $m = new \Search\Search($query, $queryType, $num, $auth);
//      printTitles($m->getTitles(), $m->getTotalCount(), $m->getSearchInformation());
//    } catch (\Exception $e) {
//      printResponse(NULL, true, $e->getMessage());
//    }
//
//  });
//

//});
//
$app->run();

