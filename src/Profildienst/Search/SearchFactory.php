<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 27.07.16
 * Time: 00:31
 */

namespace Profildienst\Search;


use Config\Configuration;
use Profildienst\Title\TitleFactory;

class SearchFactory {

    private $config;
    private $gateway;
    private $user;
    private $titleFactory;

    public function __construct(Configuration $config, SearchGateway $gateway, TitleFactory $titleFactory){
        $this->config = $config;
        $this->gateway = $gateway;
        $this->titleFactory = $titleFactory;
    }

    public function createSearch($query, $queryType){
        return new Search($query, $queryType, $this->gateway, $this->config, $this->titleFactory);
    }

}