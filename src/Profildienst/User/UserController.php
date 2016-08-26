<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 28.05.16
 * Time: 19:58
 */

namespace Profildienst\User;


use Exceptions\UserException;

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

        return new User($id, null, $data['settings'], $data['defaults'], $data['isil'], $data['budgets'], $data['suppliers']);
    }

    public function userExists($id) {
        return !is_null($this->findByID($id));
    }
    
    public function persist(User $user){
        
        $userData = [
            'settings' => $user->getSettings()
        ];

        if (!$this->gateway->updateUserData($user->getId(), $userData)){
            throw new UserException('Failed to update user data');
        }
    }
}