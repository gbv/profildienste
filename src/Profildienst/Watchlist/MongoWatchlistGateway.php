<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 28.06.16
 * Time: 14:56
 */

namespace Profildienst\Watchlist;


use Config\Configuration;
use MongoDB\BSON\UTCDatetime;
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

        if (is_null($wlData)){
            return null;
        }

        foreach ($wlData['watchlists'] as $watchlist){
            if ($watchlist['id'] === $watchlistId){
                return $watchlist;
            }
        }

        return null;
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

    public function updateTitlesWatchlist(array $ids, $watchlistId) {
        // TODO: check if watchlist is null before updating
        $criterion = ['$and' => [['user' => $this->user->getId()], ['_id' => ['$in' => $ids]]]];
        $update = ['$set' => [
            'watchlist' => $watchlistId,
            'lastStatusChange' => new UTCDateTime((time() * 1000))
        ]];

        $result = $this->titles->updateMany($criterion, $update);
        return $result->isAcknowledged();
    }

    public function updateViewsWatchlist($status, $watchlistId) {
        // TODO: check if watchlist is null before updating
        $criterion = ['$and' => [['user' => $this->user->getId()], ['status' => $status]]];
        $update = ['$set' => [
            'watchlist' => $watchlistId,
            'lastStatusChange' => new UTCDateTime((time() * 1000))
        ]];

        $result = $this->titles->updateMany($criterion, $update);
        return $result->isAcknowledged();
    }

    public function createWatchlist(array $watchlistData) {
        $watchlists = $this->getWatchlists();
        $watchlists[] = $watchlistData;

        $result = $this->users->updateOne(
            ['_id' => $this->user->getId()],
            ['$set' => ['watchlists' => $watchlists]]
        );
        return $result->isAcknowledged();

    }

    public function deleteWatchlist($watchlistId) {

        $watchlists = $this->getWatchlists();

        $updatedWatchlists = [];
        foreach ($watchlists as $watchlist){
            if ($watchlist['id'] === $watchlistId){
                continue;
            }
            $updatedWatchlists[] = $watchlist;
        }

        $result = $this->users->updateOne(
            ['_id' => $this->user->getId()],
            ['$set' => ['watchlists' => $updatedWatchlists]]
        );
        return $result->isAcknowledged();

    }

    public function renameWatchlist($watchlistId, $name) {
        // TODO: Implement renameWatchlist() method.
    }

    public function updateDefaultWatchlist($watchlistId) {
        // TODO: Implement updateDefaultWatchlist() method.
    }

    public function removeAllTitlesFromWatchlist($watchlistId) {
        $criterion = ['$and' => [['user' => $this->user->getId()], ['watchlist' => $watchlistId]]];

        $update = ['$set' => [
            'watchlist' => null,
            'lastStatusChange' => new UTCDateTime((time() * 1000))
        ]];

        $result = $this->titles->updateMany($criterion, $update);
        return $result->isAcknowledged();
    }

    public function updateWatchlists(array $watchlists) {

        $result = $this->users->updateOne(
            ['_id' => $this->user->getId()],
            ['$set' => ['watchlists' => $watchlists]]
        );
        
        return $result->isAcknowledged();
    }
}