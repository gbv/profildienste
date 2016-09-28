<?php

namespace Profildienst\Watchlist;

use Profildienst\Title\TitleFactory;
use Profildienst\Title\TitleRepository;

class Watchlist {

    private $id;
    private $name;
    private $default;

    private $gateway;
    private $titleRepository;

    /**
     * Watchlist constructor.
     * @param $id
     * @param $name
     * @param $default
     * @param WatchlistGateway $gateway
     * @param TitleRepository $titleRepository
     */
    public function __construct($id, $name, $default, WatchlistGateway $gateway, TitleRepository $titleRepository) {
        $this->id = $id;
        $this->name = $name;
        $this->default = $default;

        $this->gateway = $gateway;
        $this->titleRepository = $titleRepository;
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
        return $this->titleRepository->getTitleCountWithStatus('watchlist/'.$this->getId());
    }

    public function getTitleView($page) {
        return $this->titleRepository->getTitlesByStatus('watchlist/'.$this->getId(), $page);
    }

    public function addTitles(array $titles) {

        $ids = array_map(function ($title){
            return $title->getId();
        }, $titles);

        return $this->titleRepository->changeStatusOfTitles($ids, 'watchlist/'.$this->id);
    }

    public function addTitlesFromView($view) {
        // TODO
    }

    public function removeTitles(array $titles) {

        $ids = array_map(function ($title){
            return $title->getId();
        }, $titles);

        return $this->titleRepository->changeStatusOfTitles($ids, 'normal');
    }

    public function removeTitlesFromView($view) {
        // TODO
    }

}