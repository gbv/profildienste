<?php

namespace Profildienst\Search;


use MongoDB\Database;
use Config\Configuration;
use Profildienst\User\User;
use Profildienst\Common\MongoOptionHelper;

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

    public function getTitles($query, $page, $offset) {
        $options = self::sortedPageOptions($this->config, $this->user, $page, false, $offset);

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