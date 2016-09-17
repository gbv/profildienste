<?php

namespace Profildienst\Title;

use Profildienst\User\User;
use Profildienst\Watchlist\WatchlistManager;

class TitleFactory {

    private $watchlistManager;

    /**
     * @var User
     */
    private $user;

    public function __construct(WatchlistManager $watchlistManager, User $user){
        $this->watchlistManager = $watchlistManager;
        $this->watchlistManager->setTitleFactory($this);
        $this->user = $user;
    }

    public function createTitle($titleData){
        return new Title($titleData, $this->watchlistManager, $this->user);
    }

    public function createTitleList(array $titleListData){
        $titles = [];
        foreach($titleListData as $titleData){
            $titles[] = self::createTitle($titleData);
        }
        return $titles;
    }
}