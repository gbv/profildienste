<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 29.05.16
 * Time: 17:48
 */

namespace Profildienst\Library;


use Config\Configuration;
use Profildienst\User\User;

class LibraryController {

    private $config;

    public function __construct(Configuration $config){
        $this->config = $config;
    }

    public function getLibrary(User $user){
        return $this->config->getLibrary($user->getISIL());
    }

    public function getLibraries(){
        return $this->config->getLibraries();
    }

}