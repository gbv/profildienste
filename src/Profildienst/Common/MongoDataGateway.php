<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 29.06.16
 * Time: 01:30
 */

namespace Profildienst\Common;

use MongoDB\Database;

class MongoDataGateway implements DataGateway{

    private $data;

    public function __construct(Database $db){
        $this->data = $db->selectCollection('data');
    }

    public function getMean() {
        $resp = $this->data->findOne(['_id' => 'mean']);
        return $resp['value'];
    }
}