<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 04.06.16
 * Time: 14:13
 */

namespace Profildienst\Title;

use MongoDB\Database;
use Profildienst\Title;

class MongoTitleGateway implements TitleGateway {

    private $titles;

    public function __construct(Database $db) {
        $this->titles = $db->selectCollection('titles');
    }

    public function getTitleById($userId, $titleId) {
        // TODO: Implement getTitleById() method.
    }

    public function getTitlesByStatus($userId, $status, $limit, $skip) { //TODO: page statt limit und skip
        $query = ['$and' => [['user' => $userId], ['status' => $status]]];

        // limit results if parameters are given
        $options = [];
        if (!is_null($limit)) {
            $options['limit'] = $limit;
        }

        if (!is_null($skip)) {
            $options['skip'] = $skip;
        }

        $cursor = $this->titles->find($query, $options);

        $titles = [];
        foreach ($cursor as $titleData) {
            $titles[] = $titleData;
        }

        return $titles;

    }

    public function deleteTitle($userId, $id) {
        // TODO: Implement deleteTitle() method.
    }

    public function getTitleCountWithStatus($userId, $status) {
        $query = ['$and' => [['user' => $userId], ['status' => $status]]];
        
        return $this->titles->count($query);
    }
}