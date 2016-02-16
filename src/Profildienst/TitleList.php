<?php

namespace Profildienst;

use Middleware\AuthToken;

/**
 * Class TitleList
 * @package Profildienst
 */
class TitleList {

    /**
     * @var array Titles
     */
    private $titles;

    /**
     * @param array $titles Array of titles
     * @param AuthToken $auth Token
     */
    public function __construct(array $titles, AuthToken $auth) {

        $this->titles = $titles;

        $data = DB::get(array('_id' => $auth->getID()), 'users', array(), true);
        $watchlists = $data['watchlist'];

        foreach ($this->titles as $title) {

            if($title->isInWatchlist()){
                $title->setWlName($watchlists[$title->getWlID()]['name']);
            }
        }
    }

    /**
     * Getter for the titles
     *
     * @return Title[] Titles
     */
    public function getTitles() {
        return $this->titles;
    }

    /**
     * Number of titles in the list
     *
     * @return int number of titles
     */
    public function getCount() {
        return count($this->titles);
    }
}
?>
