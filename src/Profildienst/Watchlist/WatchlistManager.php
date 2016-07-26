<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 21.06.16
 * Time: 14:24
 */

namespace Profildienst\Watchlist;


use Exceptions\UserException;
use Profildienst\Title\TitleFactory;

class WatchlistManager {

    private $watchlists = [];

    private $gateway;
    private $titleFactory;

    public function __construct(WatchlistGateway $gateway) {
        $this->gateway = $gateway;
    }

    public function setTitleFactory(TitleFactory $titleFactory) {
        $this->titleFactory = $titleFactory;
    }

    public function getWatchlist($id) {
        return $this->watchlists[$id] ?? $this->createWatchlist($id);
    }

    public function addWatchlist($name) {

        // generate id
        $id = null;
        do {
            $id = uniqid();
        } while ($this->watchlistExists($id));

        $watchlistData = [
            'id' => $id,
            'name' => $name,
            'default' => false
        ];

        if (!$this->gateway->createWatchlist($watchlistData)) {
            throw new UserException('Failed to create a new watchlist');
        }

        return $this->getWatchlist($id);
    }

    public function deleteWatchlist(Watchlist $watchlist) {

        if (!$this->gateway->removeAllTitlesFromWatchlist($watchlist->getId())) {
            throw new UserException('Failed to remove all titles from watchlist.');
        }

        if (!$this->gateway->deleteWatchlist($watchlist->getId())) {
            throw new UserException('Failed to delete watchlist.');
        }

        // removed cache watchlist
        if (isset($this->watchlists[$watchlist->getId()])) {
            unset($this->watchlists[$watchlist->getId()]);
        }

    }

    public function getWatchlists() {
        $allWatchlists = $this->gateway->getWatchlists();

        $watchlists = [];
        foreach ($allWatchlists as $wlData) {

            $data = [
                'name' => $wlData['name'],
                'default' => $wlData['default']
            ];

            $watchlists[] = $this->watchlists[$wlData['id']] ?? $this->createWatchlist($wlData['id'], $data);
        }

        return $watchlists;
    }

    private function createWatchlist($id, $data = null) {

        if (is_null($data)) {
            $data = $this->gateway->getWatchlistData($id);
        }

        if (is_null($data)) {
            throw new UserException('The watchlist with this id does not exist.');
        }

        return new Watchlist($id, $data['name'], $data['default'], $this->gateway, $this->titleFactory);
    }

    private function watchlistExists($id) {
        return !is_null($this->gateway->getWatchlistData($id));
    }

    public function changeWatchlistOrder(array $order) {

        $newWatchlistOrder = [];

        foreach ($order as $watchlistId) {
            $watchlist = $this->getWatchlist($watchlistId);
            $newWatchlistOrder[] = [
                'id' => $watchlist->getId(),
                'name' => $watchlist->getName(),
                'default' => $watchlist->isDefaultWatchlist()
            ];
        }

        $this->gateway->updateWatchlists($newWatchlistOrder);
    }

    public function renameWatchlist(Watchlist $watchlist, $name){
        if (!$this->gateway->renameWatchlist($watchlist->getId(), $name)){
            throw new UserException('Failed to rename watchlist');
        }

        // TODO: changed cached watchlist
    }

    public function setDefaultWatchlist(Watchlist $watchlist){

        if (!$this->gateway->updateDefaultWatchlist($watchlist->getId())){
            throw new UserException('Failed to update default watchlist');
        }

        // TODO: changed cached watchlist
    }
}