<?php

namespace Profildienst\Watchlist;


use MongoDB\Database;
use Config\Configuration;
use Profildienst\User\User;
use MongoDB\BSON\UTCDatetime;

class MongoWatchlistGateway implements WatchlistGateway{

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

    public function getWatchlists() {
        $wlData = $this->users->findOne(['_id' => $this->user->getId()], ['projection' => ['watchlists' => true]]);
        return $wlData['watchlists'];
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
        $criterion = ['$and' => [['_id' => $this->user->getId()], ['watchlists.id' => $watchlistId]]];

        $update = ['$set' => [
            'watchlists.$.name' => $name
        ]];

        $result = $this->users->updateOne($criterion, $update);

        return $result->isAcknowledged();
    }

    public function updateDefaultWatchlist($watchlistId) {

        // first unset the old default watchlist

        $criterion = ['$and' => [['_id' => $this->user->getId()], ['watchlists.default' => true]]];

        $update = ['$set' => [
            'watchlists.$.default' => false
        ]];

        $result = $this->users->updateOne($criterion, $update);

        if (!$result->isAcknowledged()){
            return false;
        }

        $criterion = ['$and' => [['_id' => $this->user->getId()], ['watchlists.id' => $watchlistId]]];

        $update = ['$set' => [
            'watchlists.$.default' => true
        ]];

        $result = $this->users->updateOne($criterion, $update);

        return $result->isAcknowledged();


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