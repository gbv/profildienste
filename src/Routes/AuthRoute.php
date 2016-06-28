<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 31.05.16
 * Time: 19:08
 */

namespace Routes;

use Exceptions\AuthException;
use Firebase\JWT\JWT;
use Responses\BasicResponse;

class AuthRoute extends Route{

    public function performAuthentication($request, $response, $args){

        $credentials = $request->getParsedBody();
        $config = $this->ci->get('config');
        $auth = $this->ci->get('auth');

        // have we got a username and a password?
        if (empty($credentials['user']) || empty($credentials['pass'])) {
            throw new AuthException('Bitte geben Sie einen Benutzername und ein Passwort ein.');
        }

        // Perform authentication. If the authentication fails, an exception will be thrown and
        // therefore the rest of this function will not be executed.
        $auth->authenticate($credentials['user'], $credentials['pass']);

        // construct token
        $token = [
            'iss' => $config->getTokenIssuer(),
            'aud' => $auth->getName(),
            'sub' => $auth->getName(),
            'pd_id' => $credentials['user'],
            'iat' => time(),
            'exp' => time() + $config->getTokenExpTime()
        ];

        $jwt = JWT::encode($token, $config->getSecretKey(), $config->getTokenCryptAlgorithm());
        self::generateJSONResponse(new BasicResponse($jwt), $response);
    }
}