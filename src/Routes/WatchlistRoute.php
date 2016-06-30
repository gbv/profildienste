<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 05.06.16
 * Time: 22:51
 */

namespace Routes;


use Interop\Container\ContainerInterface;
use Responses\BasicResponse;
use Responses\TitlelistResponse;

class WatchlistRoute extends ViewRoute {

    private $watchlistManager;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        $this->watchlistManager = $this->ci->get('watchlistManager');
    }

    public function getWatchlistView($request, $response, $args) {

        $page = self::validatePage($args);
        $wlId = $args['id'];

        $watchlist = $this->watchlistManager->getWatchlist($wlId);
        
        $titles = $watchlist->getTitleView($page);
        
        return self::titlePageResponse($titles, $page, $watchlist->getTitleCount(), $response);
    }

    public function getWatchlists($request, $response, $args) {

        $watchlists = $this->watchlistManager->getWatchlists();

        $data = [];
        foreach ($watchlists as $watchlist) {
            $data[] = [
                'id' => $watchlist->getId(),
                'name' => $watchlist->getName(),
                'count' => $watchlist->getTitleCount(),
                'default' => $watchlist->isDefaultWatchlist()
            ];
        }

        return self::generateJSONResponse(new BasicResponse($data), $response);
    }


//
///** TODO */
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


}