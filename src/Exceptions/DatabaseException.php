<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 29.05.16
 * Time: 17:03
 */

namespace Exceptions;


class DatabaseException extends BaseException{

    public function getModule() {
        return 'Database';
    }
}