<?php

namespace Exceptions;

/**
 * Class UserErrorException
 *
 * An exception intended to be shown to the user. A raised exception
 * of this type either means that the user did something wrong or that
 * something happened the user should be made aware of.
 *
 * @package Exceptions
 */
class UserErrorException extends \Exception {

    private $causeMail = false;

    public function __construct(string $message, $causeMail = false) {
        parent::__construct($message);
        $this->causeMail = $causeMail;
    }

    public function shouldCauseMail() {
        return $this->causeMail;
    }

}