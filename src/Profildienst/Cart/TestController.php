<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 26.08.16
 * Time: 17:49
 */

namespace Profildienst\Cart;


use Config\Configuration;
use Exceptions\UserException;
use Profildienst\Title\TitleRepository;
use Profildienst\User\User;

class TestController {

    public function __construct(User $user, Configuration $config, TitleRepository $titleRepository) {
        /*$this->user = $user;
        $this->config = $config;
        $this->titleRepository = $titleRepository;*/
    }

    public function test() {
        throw new UserException('Test!');
    }
}