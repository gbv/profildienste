<?php

namespace Routes;


use Responses\BasicResponse;
use Responses\ActionResponse;
use Exceptions\UserErrorException;
use Interop\Container\ContainerInterface;

class WatchlistRoute extends ViewRoute {

    private $watchlistManager;

    use ActionHandler;

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

        $data['watchlists'] = array_map(function ($watchlist) {
            return [
                'id' => $watchlist->getId(),
                'name' => $watchlist->getName(),
                'count' => $watchlist->getTitleCount(),
                'default' => $watchlist->isDefaultWatchlist()
            ];
        }, $watchlists);

        return self::generateJSONResponse(new BasicResponse($data), $response);
    }

    public function addTitlesToWatchlist($request, $response, $args) {

        $newWatchlist = $this->watchlistManager->getWatchlist($args['id']);

        $affected = $this->handleStatusChange($request, 'watchlist/'.$newWatchlist->getId(), function ($oldState) {
            return in_array($oldState, ['normal', 'cart', 'rejected']);
        });

        if (is_null($affected)) {
            throw new UserErrorException('Failed to add titles to watchlist.');
        }

        return self::generateJSONResponse(new ActionResponse($affected, 'watchlist', ['id' => $newWatchlist, 'name' => $newWatchlist->getName()]), $response);
    }

    public function removeTitlesFromWatchlist($request, $response, $args) {

        $oldWatchlist = $this->watchlistManager->getWatchlist($args['id']);

        $affected = $this->handleStatusChange($request, 'normal', function ($oldState) {
            return $oldState === 'watchlist';
        });

        if (is_null($affected)) {
            throw new UserErrorException('Failed to remove titles from watchlist.');
        }

        return self::generateJSONResponse(new ActionResponse($affected, ''), $response);
    }

    public function addWatchlist($request, $response, $args) {

        $parameters = $request->getParsedBody();
        $name = $parameters['name'];

        if (empty($name)) {
            throw new UserErrorException('The watchlist name must not be empty');
        }

        $newWatchlist = $this->watchlistManager->addWatchlist($name);

        $data = [
            'id' => $newWatchlist->getId(),
            'name' => $newWatchlist->getName(),
            'count' => $newWatchlist->getTitleCount(),
            'default' => $newWatchlist->isDefaultWatchlist()
        ];

        return self::generateJSONResponse(new BasicResponse($data), $response);
    }

    public function deleteWatchlist($request, $response, $args) {

        $id = $args['id'];

        if (empty($id)) {
            throw new UserErrorException('The watchlist id must not be empty');
        }

        $watchlist = $this->watchlistManager->getWatchlist($id);

        $this->watchlistManager->deleteWatchlist($watchlist);

        return self::generateJSONResponse(new BasicResponse([]), $response);
    }

    /**
     * Handles a change in the watchlist order
     *
     * @param $request
     * @param $response
     * @param $args
     * @return \Slim\Http\Response
     * @throws UserErrorException
     */
    public function changeWatchlistOrder($request, $response, $args) {
        $parameters = $request->getParsedBody();
        $order = $parameters['order'];

        $watchlists = $this->watchlistManager->getWatchlists();

        if (empty($order) || !is_array($order) || count($order) !== count($watchlists)) {
            throw new UserErrorException('Illegal order options (watchlists missing or empty)');
        }

        $this->watchlistManager->changeWatchlistOrder($order);

        return self::generateJSONResponse(new BasicResponse([]), $response);
    }

    /**
     * Handles the renaming of a watchlist
     *
     * @param $request
     * @param $response
     * @param $args
     * @return \Slim\Http\Response
     * @throws UserErrorException
     */
    public function renameWatchlist($request, $response, $args){

        $id = $args['id'];

        if (empty($id)) {
            throw new UserErrorException('The watchlist id must not be empty');
        }

        $parameters = $request->getParsedBody();
        $name = $parameters['name'];

        if (empty($name)) {
            throw new UserErrorException('The new watchlist name must not be empty');
        }

        $watchlist = $this->watchlistManager->getWatchlist($id);

        $this->watchlistManager->renameWatchlist($watchlist, $name);

        return self::generateJSONResponse(new BasicResponse([]), $response);
    }

    /**
     * Handles a change of the default watchlist.
     *
     * @param $request
     * @param $response
     * @param $args
     * @return \Slim\Http\Response
     * @throws UserErrorException
     */
    public function changeDefaultWatchlist($request, $response, $args){

        $parameters = $request->getParsedBody();
        $id = $parameters['id'];

        if (empty($id)) {
            throw new UserErrorException('The new watchlist name must not be empty');
        }

        $watchlist = $this->watchlistManager->getWatchlist($id);

        $this->watchlistManager->setDefaultWatchlist($watchlist);

        return self::generateJSONResponse(new BasicResponse([]), $response);
    }
}