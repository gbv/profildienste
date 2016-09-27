<?php

namespace Exceptions;

/**
 * Class UserErrorException
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