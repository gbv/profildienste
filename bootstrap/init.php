<?php

use Auth\Auth;
use Config\Configuration;
use Profildienst\Cart\Cart;
use Profildienst\Cover\CoverController;
use Profildienst\Title\TitleFactory;
use Profildienst\User\UserController;
use Profildienst\Cart\OrderController;
use Profildienst\Search\SearchFactory;
use Profildienst\Title\TitleRepository;
use Profildienst\User\MongoUserGateway;
use Profildienst\Title\MongoTitleGateway;
use Profildienst\Common\MongoDataGateway;
use Profildienst\Common\ConnectionFactory;
use Profildienst\Search\MongoSearchGateway;
use Profildienst\Library\LibraryController;
use Profildienst\Watchlist\WatchlistManager;
use Profildienst\Watchlist\MongoWatchlistGateway;

/**
 * Initializes the dependency injection (DI) container by registering all
 * necessary gateways, controllers, repositores and factories.
 *
 * @param \Interop\Container\ContainerInterface $container The DI container
 */
function initContainer(Interop\Container\ContainerInterface $container) {
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
        return new UserController($gateway, $container['libraryController']);
    };

    $container['libraryController'] = function ($container) {
        return new LibraryController($container['config']);
    };

    $container['user'] = function ($container) {
        throw new Exception('Method is not available in a non-user context');
    };

    $container['cart'] = function ($container) {
        return new Cart($container['titleRepository'], $container['dataGateway']);
    };

    $container['titleRepository'] = function ($container) {
        $gateway = new MongoTitleGateway($container['connectionFactory']->getConnection(), $container['config'], $container['user']);
        return new TitleRepository($gateway, $container['titleFactory']);
    };

    $container['titleFactory'] = function ($container) {
        return new TitleFactory($container);
    };

    $container['watchlistManager'] = function ($container) {
        $gateway = new MongoWatchlistGateway($container['connectionFactory']->getConnection(), $container['user'], $container['config']);
        return new WatchlistManager($gateway, $container['titleRepository']);
    };

    $container['dataGateway'] = function ($container) {
        return new MongoDataGateway($container['connectionFactory']->getConnection());
    };

    $container['searchFactory'] = function ($container) {
        return new SearchFactory($container['config'], $container['searchGateway'], $container['titleFactory']);
    };

    $container['searchGateway'] = function ($container) {
        return new MongoSearchGateway($container['connectionFactory']->getConnection(), $container['user'], $container['config']);
    };

    $container['orderController'] = function ($container) {
        return new OrderController($container['user'], $container['config'], $container['titleRepository']);
    };

    $container['coverController'] = function ($container) {
        return new CoverController($container['config']);
    };
}
