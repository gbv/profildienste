<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 05.06.16
 * Time: 22:51
 */

namespace Routes;


use Exceptions\UserException;
use Interop\Container\ContainerInterface;
use Responses\ActionResponse;
use Responses\BasicResponse;

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

    public function addTitlesToWatchlist($request, $response, $args) {

        $newWatchlist = $args['id'];

        if (is_null($newWatchlist)) {
            throw new UserException('No watchlist id given!');
        }


        $affected = $this->handleWatchlistChange($request, null, $newWatchlist, function ($status, $inWatchlist) {
            return $status !== 'rejected' && $status !== 'done' && $status !== 'pending' && $inWatchlist === false;
        });

        if (is_null($affected)) {
            throw new UserException('Failed to add titles to watchlist.');
        }

        return self::generateJSONResponse(new ActionResponse($affected, 'watchlist', ['id' => $newWatchlist]), $response);
    }

    public function removeTitlesFromWatchlist($request, $response, $args) {

        $oldWatchlist = $args['id'];

        if (is_null($oldWatchlist)) {
            throw new UserException('No watchlist id given!');
        }


        $affected = $this->handleWatchlistChange($request, $oldWatchlist, null, function ($status, $inWatchlist) {
            return $inWatchlist === true;
        });

        if (is_null($affected)) {
            throw new UserException('Failed to remove titles from watchlist.');
        }

        return self::generateJSONResponse(new ActionResponse($affected, ''), $response);
    }


    private function handleWatchlistChange($request, $oldWatchlist, $newWatchlist, $allow) {

        if (is_null($oldWatchlist) && is_null($newWatchlist)) {
            throw new UserException('At least the old watchlist or new watchlist have to be specified');
        }

        $affected = $this->validateAffectedTitles($request);

        if (is_array($affected)) {

            $titles = $this->titleRepository->findTitlesById($affected);

            if (count($titles) === 0) {
                throw new UserException('No titles with the given IDs found!');
            }

            foreach ($titles as $title) {
                if (!$allow($title->getStatus(), $title->isInWatchlist())) {
                    throw new UserException('This action is not allowed on the selection of titles!');
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
}