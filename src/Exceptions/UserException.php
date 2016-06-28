<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 29.05.16
 * Time: 18:07
 */

namespace Exceptions;


class UserException extends BaseException{

    public function getModule() {
        return 'User';
    }
}