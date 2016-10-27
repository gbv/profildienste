<?php

namespace Profildienst\Library;

use Config\Configuration;
use Profildienst\User\User;

class LibraryController {

    private $config;

    public function __construct(Configuration $config){
        $this->config = $config;
    }

    public function getLibrary(User $user){
        return $this->config->getLibrary($user->getIsil());
    }

    public function getLibraries(){
        return $this->config->getLibraries();
    }

}