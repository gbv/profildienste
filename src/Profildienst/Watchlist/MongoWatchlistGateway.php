<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 28.06.16
 * Time: 14:56
 */

namespace Profildienst\Watchlist;


use Config\Configuration;
use MongoDB\Database;
use Profildienst\Common\MongoOptionHelper;
use Profildienst\User\User;

class MongoWatchlistGateway implements WatchlistGateway{

    use MongoOptionHelper;

    private $users;
    private $titles;

    private $user;
    private $config;

    public function __construct(Database $db, User $user, Configuration $config){

        $this->users = $db->selectCollection('users');
        $this->titles = $db->selectCollection('titles');

        $this->user = $user;
        $this->config = $config;

    }

    public function getWatchlistData($watchlistId) {
        $query = ['$and' => [['_id' => $this->user->getId()], ['watchlists.id' => $watchlistId]]];
        $wlData = $this->users->findOne($query, ['projection' => ['watchlists' => true]]);

        return $wlData['watchlists'][0] ?? null;
    }

    public function getWatchlistTitleCount($watchlistId) {
        $query = ['$and' => [['user' => $this->user->getId()], ['watchlist' => $watchlistId]]];
        return $this->titles->count($query);
    }

    public function getWatchlistTitles($watchlistId, $page) {
        $query = ['$and' => [['user' => $this->user->getId()], ['watchlist' => $watchlistId]]];
        $options = self::sortedPageOptions($this->config, $this->user, $page);

        $cursor = $this->titles->find($query, $options);

        $titles = [];
        foreach ($cursor as $titleData) {
            $titles[] = $titleData;
        }

        return $titles;
    }

    public function getWatchlists() {
        $wlData = $this->users->findOne(['_id' => $this->user->getId()], ['projection' => ['watchlists' => true]]);
        return $wlData['watchlists'];
    }
}