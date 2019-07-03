<?php

namespace Profildienst\User;

use MongoDB\Database;

class MongoUserGateway implements UserGateway {

    private $users;

    public function __construct(Database $db) {
        $this->users = $db->selectCollection('users');
    }

    public function findByID($id) {
        return $this->users->findOne(['_id' => $id]);
    }
    
    public function findColleagues($id, $isil) {
        
        $query = ['isil' => $isil, '_id' => ['$ne' => $id]];
        $options = [
            
            'sort' => ['_id' => 1],
            'projection' => ['_id' => 1]
            
        ];
        
        $result = $this->users->find($query, $options);
                
        return $result->toArray();
        
    }

    public function updateUserData($id, $data) {
        $result = $this->users->updateOne(['_id' => $id], ['$set' => $data]);
        return $result->isAcknowledged();
    }
}
