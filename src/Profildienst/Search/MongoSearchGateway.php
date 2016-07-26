<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 27.07.16
 * Time: 01:03
 */

namespace Profildienst\Search;


use Config\Configuration;
use MongoDB\Database;
use Profildienst\Common\MongoOptionHelper;
use Profildienst\User\User;

class MongoSearchGateway implements SearchGateway {

    use MongoOptionHelper;

    private $titles;

    private $user;
    private $config;

    public function __construct(Database $db, User $user, Configuration $config) {

        $this->titles = $db->selectCollection('titles');

        $this->user = $user;
        $this->config = $config;
    }

    public function getTitles($query, $page) {
        $options = self::sortedPageOptions($this->config, $this->user, $page);

        $query = ['$and' => [['user' => $this->user->getId()], $query]];
        $cursor = $this->titles->find($query, $options);

        $titles = [];
        foreach ($cursor as $titleData) {
            $titles[] = $titleData;
        }

        return $titles;
    }

    public function getMatchingTitleCount($query) {
        $query = ['$and' => [['user' => $this->user->getId()], $query]];
        return $this->titles->count($query);
    }
}