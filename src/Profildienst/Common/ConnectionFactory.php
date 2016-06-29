<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 29.05.16
 * Time: 14:34
 */

namespace Profildienst\Common;


use Config\Configuration;
use Exceptions\DatabaseException;
use MongoDB\Client;

class ConnectionFactory{

    private $config;

    public function __construct(Configuration $config){
        $this->config = $config;
    }

    public function getConnection(){
        try{
            $client = new Client('mongodb://'.$this->config->getDatabaseHost().':'.$this->config->getDatabasePort());
        } catch(\Exception $e){
            throw new DatabaseException('Cannot connect to database');
        }
        
        return $client->selectDatabase($this->config->getDatabaseName());
    }

}