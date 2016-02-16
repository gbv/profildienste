<?php

namespace Middleware;

use Config\Config;
use Slim\Middleware;

/**
 * Slim middleware which represents an auth token.
 *
 * Class AuthToken
 * @package Middleware
 */
class AuthToken extends Middleware {

    /**
     * @var bool Indicates whether the token is valid
     */
    private $valid = false;
    /**
     * @var string The name of the user as stored in the token
     */
    private $name = NULL;
    /**
     * @var string The id of the user as stored in the token
     */
    private $id = NULL;


    public function call() {

        $app = $this->app;

        if ($app->request->headers->get('Authorization')) {

            $tok = explode(' ', $app->request->headers->get('Authorization'));

            if (count($tok) === 2 && $tok[0] === 'Bearer') {
                try {

                    $decoded = \JWT::decode($tok[1], Config::$token_key);
                    $token = (array)$decoded;
                    $this->valid = true;
                    $this->name = $token['sub'];
                    $this->id = $token['pd_id'];

                } catch (\Exception $e) {
                    $this->valid = false;
                }
            }
        }

        $this->next->call();
    }

    /**
     * Getter for the name
     *
     * @return string The name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Getter for the ID
     *
     * @return string ID
     */
    public function getID() {
        return $this->id;
    }

    /**
     * Getter for valid property
     *
     * @return bool true if the token is valid
     */
    public function isValid() {
        return $this->valid;
    }
}
?>
