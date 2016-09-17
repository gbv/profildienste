<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 28.05.16
 * Time: 19:58
 */

namespace Profildienst\User;


use Exceptions\UserErrorException;
use Profildienst\Library\LibraryController;

class UserController {

    private $gateway;
    private $libraryController;

    public function __construct(UserGateway $gateway, LibraryController $libraryController) {
        $this->gateway = $gateway;
        $this->libraryController = $libraryController;
    }

    public function findByID($id) {
        $data = $this->gateway->findByID($id);

        if (is_null($data)) {
            return null;
        }

        return new User($id, null, $data['settings'], $data['defaults'], $data['isil'], $data['budgets'], $data['suppliers'], $this->libraryController);
    }

    public function userExists($id) {
        return !is_null($this->findByID($id));
    }
    
    public function persist(User $user){
        
        $userData = [
            'settings' => $user->getSettings()
        ];

        if (!$this->gateway->updateUserData($user->getId(), $userData)){
            throw new UserErrorException('Failed to update user data');
        }
    }
}