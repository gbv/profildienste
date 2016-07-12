<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 28.05.16
 * Time: 19:58
 */

namespace Profildienst\User;


interface UserGateway{

    public function findByID($id);
    public function updateUserData($id, $data);
}