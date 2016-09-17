<?php

namespace Profildienst\Watchlist;

use Profildienst\Title\TitleFactory;

class Watchlist {

    private $id;
    private $name;
    private $default;

    private $gateway;
    private $titleFactory;

    /**
     * Watchlist constructor.
     * @param $id
     * @param $name
     * @param $default
     * @param WatchlistGateway $gateway
     * @param TitleFactory $titleFactory
     */
    public function __construct($id, $name, $default, WatchlistGateway $gateway, TitleFactory $titleFactory) {
        $this->id = $id;
        $this->name = $name;
        $this->default = $default;

        $this->gateway = $gateway;
        $this->titleFactory = $titleFactory;
    }

    /**
     * Returns the watchlist id
     *
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Returns the name of the watchlist
     *
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Returns true if the watchlist is the users
     * default watchlist.
     *
     * @return boolean
     */
    public function isDefaultWatchlist() {
        return $this->default;
    }

    /**
     * Number of titles in the watchlist
     *
     * @return int
     */
    public function getTitleCount() {
        return $this->gateway->getWatchlistTitleCount($this->id);
    }

    public function getTitleView($page) {
        $titleData = $this->gateway->getWatchlistTitles($this->id, $page);
        return $this->titleFactory->createTitleList($titleData);
    }


    public function addTitles(array $titles) {

        $ids = [];
        foreach ($titles as $title) {
            $ids[] = $title->getId();
        }

        return $this->gateway->updateTitlesWatchlist($ids, $this->id);
    }

    public function addTitlesFromView($view) {
        // TODO
    }

    public function removeTitles(array $titles) {

        $ids = [];
        foreach ($titles as $title) {
            $ids[] = $title->getId();
        }

        return $this->gateway->updateTitlesWatchlist($ids, null);
    }

    public function removeTitlesFromView($view) {
        // TODO
    }

}