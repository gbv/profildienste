<?php

namespace Auth;

/**
 * Wrapper for the Auth Class.
 *
 * Class Login
 * @package Auth
 */
class Login {

    /**
     * @var bool Indicates whether the user is logged in
     */
    public $login = false;
    /**
     * @var string Name of the user
     */
    public $name = '';
    /**
     * @var string Library of the user
     */
    public $bib = '';
    /**
     * @var string Users librarys ISIL
     */
    public $isil = '';

    /**
     * Initiates and performs the authentication
     *
     * @param $user Username
     * @param $pwd Password
     */
    public function doLogin($user, $pwd) {

        $a = new Auth();
        $a->auth_user($user, $pwd);

        if ($a->isLoggedIn()) {
            $this->name = $a->getName();
            $this->bib = $a->getBib();
            $this->isil = $a->getISIL();
            $this->login = true;
        }
    }
}

?>