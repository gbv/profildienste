<?php

namespace Profildienst\User;


interface UserGateway{

    public function findByID($id);
    public function updateUserData($id, $data);
}
