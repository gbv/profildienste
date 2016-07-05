<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 07.06.16
 * Time: 14:36
 */

namespace Profildienst\Title;


use Profildienst\Watchlist\WatchlistManager;

class TitleFactory {

    private $watchlistManager;

    public function __construct(WatchlistManager $watchlistManager){
        $this->watchlistManager = $watchlistManager;
        $this->watchlistManager->setTitleFactory($this);
    }

    public function createTitle($titleData){
        return new Title($titleData, $this->watchlistManager);
    }

    public function createTitleList(array $titleListData){
        $titles = [];
        foreach($titleListData as $titleData){
            $titles[] = self::createTitle($titleData);
        }
        return $titles;
    }
}