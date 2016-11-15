<?php

namespace Exceptions;

/**
 * Class CustomMessageException
 *
 * This exception indicates that a severe error occured.
 * A mail will be sent in any case. In addition to the
 * usual UserErrorException this exception also allows a
 * custom text for the mail (e.g. further debug info).
 *
 * @package Exceptions
 */
class CustomMailMessageException extends UserErrorException {

    private $mailText;

    public function __construct($message, $mailText) {
        parent::__construct($message, true);
        $this->mailText = $mailText;
    }

    public function getMailText() {
        return $this->mailText;
    }

}