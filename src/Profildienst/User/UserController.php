<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 28.05.16
 * Time: 19:58
 */

namespace Profildienst\User;


class UserController {

    private $gateway;

    public function __construct(UserGateway $gateway) {
        $this->gateway = $gateway;
    }

    public function findByID($id) {
        $data = $this->gateway->findByID($id);

        if (is_null($data)) {
            return null;
        }

        return new User($id, null, $data['settings'], $data['defaults'], $data['isil'], $data['budgets']);
    }

    public function userExists($id) {
        return !is_null($this->findByID($id));
    }
}