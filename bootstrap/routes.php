<?php
use Slim\App;

/**
 * Route Definition.
 * This function initializes all routes/endpoints of the API.
 * @param App $app
 */
function initRoutes(App $app) {
    global $auth;

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
        $this->post('/order', '\Routes\CartRoute:order');
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
        $this->get('/{id}/pica', '\Routes\TitleRoute:getTitlePicaData');
        $this->post('/save', '\Routes\TitleRoute:saveTitleInformation');
        $this->delete('/delete', '\Routes\TitleRoute:delete');
    })->add($auth);

    $app->get('/status', function (){});
}