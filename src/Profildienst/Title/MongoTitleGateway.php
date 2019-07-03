<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 04.06.16
 * Time: 14:13
 */

namespace Profildienst\Title;

use MongoDB\Database;
use Config\Configuration;
use Profildienst\User\User;
use MongoDB\BSON\UTCDatetime;
use Profildienst\Common\MongoOptionHelper;

class MongoTitleGateway implements TitleGateway {

    use MongoOptionHelper;

    private $titles;

    private $config;
    private $user;

    public function __construct(Database $db, Configuration $config, User $user) {
        $this->titles = $db->selectCollection('titles');

        $this->config = $config;
        $this->user = $user;
    }

    public function getTitlesById(array $titleIds) {
        $query = ['$and' => [['user' => $this->user->getId()], ['_id' => ['$in' => $titleIds]]]];

        $cursor = $this->titles->find($query);

        $titles = [];
        foreach ($cursor as $titleData) {
            $titles[] = $titleData;
        }

        return $titles;
    }

    public function deleteTitle($id) {
        // TODO: Implement deleteTitle() method.
    }

    public function getTitleCountWithStatus($status) {
        $query = ['$and' => [['user' => $this->user->getId()], ['status' => $status]]];
        return $this->titles->count($query);
    }

    public function getTitlesByStatus($status, $page, $dateSorted, $offset) {
        $options = self::sortedPageOptions($this->config, $this->user, $page, $dateSorted, $offset);
        return $this->getTitles($status, $options);
    }

    public function getAllTitlesByStatus($status) {
        return $this->getTitles($status);
    }

    private function getTitles($status, $options = []){
        $query = ['$and' => [['user' => $this->user->getId()], ['status' => $status]]];
        $cursor = $this->titles->find($query, $options);

        $titles = [];
        foreach ($cursor as $titleData) {
            $titles[] = $titleData;
        }

        return $titles;
    }


    public function updateTitlesWithStatus($oldStatus, $newStatus) {
        $criterion = ['$and' => [['user' => $this->user->getId()], ['status' => $oldStatus]]];
        $update = ['$set' => [
            'status' => $newStatus,
            'lastStatusChange' => new UTCDateTime((time() * 1000))
        ]];
        
        $result = $this->titles->updateMany($criterion, $update);

        return $result->isAcknowledged();
    }

    public function updateTitlesWithIds(array $ids, $newStatus) {
        $criterion = ['$and' => [['user' => $this->user->getId()], ['_id' => ['$in' => $ids]]]];
        $update = ['$set' => [
            'status' => $newStatus,
            'lastStatusChange' => new UTCDateTime((time() * 1000))
        ]];

        $result = $this->titles->updateMany($criterion, $update);

        return $result->isAcknowledged();
    }
    
    public function updateTitleUser($id, $newUser){
        $criterion = ['$and' => [['user' => $this->user->getId()], ['_id' => $id]]];
        $update = ['$set' => [
            'user' => $newUser,
            'status' => 'normal',
            'lastStatusChange' => new UTCDateTime((time() * 1000))
        ]];
        
        $result = $this->titles->updateOne($criterion, $update);

        return $result->isAcknowledged();
    }

    public function updateTitlesOrderInformation(array $ids, $orderInformation) {

        $toUpdate = [];
        foreach (['budget', 'selcode', 'ssgnr', 'supplier', 'comment'] as $orderInfoField){
            if (isset($orderInformation[$orderInfoField])){
                $toUpdate[$orderInfoField] = $orderInformation[$orderInfoField];
            }
        }

        if(count($toUpdate) == 0){
            return false;
        }

        $criterion = ['$and' => [['user' => $this->user->getId()], ['_id' => ['$in' => $ids]]]];
        $update = ['$set' => $toUpdate];

        $result = $this->titles->updateMany($criterion, $update);

        return $result->isAcknowledged();
    }

    public function deleteTitlesWithStatus($status) {

        $criterion = ['$and' => [['user' => $this->user->getId()], ['status' => $status]]];
        $result = $this->titles->deleteMany($criterion);

        return $result->isAcknowledged();
    }
}