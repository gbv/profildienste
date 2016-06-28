<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 19.05.16
 * Time: 14:45
 */

namespace Profildienst\Watchlist;

class Watchlist {

    private $id;
    private $name;
    private $default;
    private $gateway;

    /**
     * Watchlist constructor.
     * @param $id
     * @param $name
     * @param $default
     * @param WatchlistGateway $gateway
     */
    public function __construct($id, $name, $default, WatchlistGateway $gateway) {
        $this->id = $id;
        $this->name = $name;
        $this->default = $default;

        $this->gateway = $gateway;
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
    public function isDefaultWatchlist(){
        return $this->default;
    }

    /**
     * Number of titles in the watchlist
     *
     * @return int
     */
    public function getTitleCount(){
        return $this->gateway->getWatchlistTitleCount($this->id);
    }

    // get titles view
    public function getTitleView($page){
        return $this->gateway->getWatchlistTitles($this->id, $page);
    }

    // TODO: add title

    // TODO: remove title
    
}