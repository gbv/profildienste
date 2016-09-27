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

        $data['watchlists'] = [];
        foreach ($watchlists as $watchlist) {
            $data['watchlists'][] = [
                'id' => $watchlist->getId(),
                'name' => $watchlist->getName(),
                'count' => $watchlist->getTitleCount(),
                'default' => $watchlist->isDefaultWatchlist()
            ];
        }

        return self::generateJSONResponse(new BasicResponse($data), $response);
    }

    public function addTitlesToWatchlist($request, $response, $args) {

        $newWatchlist = $args['id'];

        if (is_null($newWatchlist)) {
            throw new UserErrorException('No watchlist id given!');
        }


        $affected = $this->handleWatchlistChange($request, null, $newWatchlist, function ($status, $inWatchlist) {
            return $status !== 'rejected' && $status !== 'done' && $status !== 'pending' && $inWatchlist === false;
        });

        if (is_null($affected)) {
            throw new UserErrorException('Failed to add titles to watchlist.');
        }

        $watchlist = $this->watchlistManager->getWatchlist($newWatchlist);

        return self::generateJSONResponse(new ActionResponse($affected, 'watchlist', ['id' => $newWatchlist, 'name' => $watchlist->getName()]), $response);
    }

    public function removeTitlesFromWatchlist($request, $response, $args) {

        $oldWatchlist = $args['id'];

        if (is_null($oldWatchlist)) {
            throw new UserErrorException('No watchlist id given!');
        }


        $affected = $this->handleWatchlistChange($request, $oldWatchlist, null, function ($status, $inWatchlist) {
            return $inWatchlist === true;
        });

        if (is_null($affected)) {
            throw new UserErrorException('Failed to remove titles from watchlist.');
        }

        return self::generateJSONResponse(new ActionResponse($affected, ''), $response);
    }


    private function handleWatchlistChange($request, $oldWatchlist, $newWatchlist, $allow) {

        if (is_null($oldWatchlist) && is_null($newWatchlist)) {
            throw new UserErrorException('At least the old watchlist or new watchlist have to be specified');
        }

        $affected = $this->validateAffectedTitles($request);

        if (is_array($affected)) {

            $titles = $this->titleRepository->findTitlesById($affected);

            if (count($titles) === 0) {
                throw new UserErrorException('No titles with the given IDs found!');
            }

            foreach ($titles as $title) {
                if (!$allow($title->getStatus(), $title->isInWatchlist())) {
                    throw new UserErrorException('This action is not allowed on the selection of titles!');
                }
            }

            if (!is_null($newWatchlist)) {
                $watchlist = $this->watchlistManager->getWatchlist($newWatchlist);
                return $watchlist->addTitles($titles) ? $affected : null;
            } else {
                $watchlist = $this->watchlistManager->getWatchlist($oldWatchlist);
                return $watchlist->removeTitles($titles) ? $affected : null;
            }

        } /*else {

            if ($affected === 'overview') {
                $affected = 'normal';
            }

            if (!$this->allow($affected, null)) {
                throw new UserException('This action is not allowed on the selection of titles!');
            }

            return $this->titleRepository->changeWatchlistOfView($affected, $newWatchlist) ? $affected : null;

        } TODO: implement watchlist view change*/
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