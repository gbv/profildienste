<?php

namespace Profildienst\User;


interface UserGateway{

    public function findByID($id);
    public function findColleagues($id, $isil);
    public function updateUserData($id, $data);
  
}