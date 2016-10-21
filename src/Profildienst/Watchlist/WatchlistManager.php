<?php

namespace Profildienst\Watchlist;


use Exceptions\UserErrorException;
use Profildienst\Title\Title;
use Profildienst\Title\TitleFactory;
use Profildienst\Title\TitleRepository;

class WatchlistManager {

    private $watchlists = [];

    private $gateway;
    private $titleRepository;

    public function __construct(WatchlistGateway $gateway, TitleRepository $titleRepository) {
        $this->gateway = $gateway;
        $this->titleRepository = $titleRepository;
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
            throw new UserErrorException('Failed to create a new watchlist');
        }

        return $this->getWatchlist($id);
    }

    public function deleteWatchlist(Watchlist $watchlist) {

        if (!$this->gateway->removeAllTitlesFromWatchlist($watchlist->getId())) {
            throw new UserErrorException('Failed to remove all titles from watchlist.');
        }

        if (!$this->gateway->deleteWatchlist($watchlist->getId())) {
            throw new UserErrorException('Failed to delete watchlist.');
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
            throw new UserErrorException('The watchlist with this id >'.$id.'< does not exist.');
        }

        return new Watchlist($id, $data['name'], $data['default'], $this->gateway, $this->titleRepository);
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
            throw new UserErrorException('Failed to rename watchlist');
        }
    }

    public function setDefaultWatchlist(Watchlist $watchlist){

        if (!$this->gateway->updateDefaultWatchlist($watchlist->getId())){
            throw new UserErrorException('Failed to update default watchlist');
        }
    }
}