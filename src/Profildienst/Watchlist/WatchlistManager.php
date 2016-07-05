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

    public function setTitleFactory(TitleFactory $titleFactory){
        $this->titleFactory = $titleFactory;
    }

    public function getWatchlist($id) {
        return $this->watchlists[$id] ?? $this->createWatchlist($id);
    }

    public function addWatchlist() {
        // returns id and/or additional info
    }

    public function removeWatchlist() {

    }

    public function getWatchlists() {
        $allWatchlists = $this->gateway->getWatchlists();

        $watchlists = [];
        foreach ($allWatchlists as $wlData) {

            $data = [
                'name' => $wlData['name'],
                'default' => $wlData['default']
            ];

            $watchlists[] = $this->getWatchlist($wlData['id'], $data);
        }

        return $watchlists;
    }

    private function createWatchlist($id, $data = null) {

        if (is_null($data)) {
            $data = $this->gateway->getWatchlistData($id);
        }

        if(is_null($data)){
            throw new UserException('The watchlist with this id does not exist.');
        }

        return new Watchlist($id, $data['name'], $data['default'], $this->gateway, $this->titleFactory);
    }

}