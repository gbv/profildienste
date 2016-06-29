<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 04.06.16
 * Time: 14:13
 */

namespace Profildienst\Title;

use Config\Configuration;
use MongoDB\Database;
use Profildienst\Common\MongoOptionHelper;
use Profildienst\Title;
use Profildienst\User\User;

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

    public function getTitleById($titleId) {
        // TODO: Implement getTitleById() method.
    }

    public function deleteTitle($id) {
        // TODO: Implement deleteTitle() method.
    }

    public function getTitleCountWithStatus($status) {
        $query = ['$and' => [['user' => $this->user->getId()], ['status' => $status]]];
        return $this->titles->count($query);
    }

    public function getTitlesByStatus($status, $page) {
        $options = self::sortedPageOptions($this->config, $this->user, $page);
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
}