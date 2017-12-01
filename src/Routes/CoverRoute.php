<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 01.12.17
 * Time: 13:10
 */

namespace Routes;


use Exceptions\UserErrorException;
use Interop\Container\ContainerInterface;
use Profildienst\Cover\CoverController;
use Slim\Http\Request;
use Slim\Http\Response;

class CoverRoute {

    /**
     * @var CoverController
     */
    private $coverController;

    public function __construct(ContainerInterface $ci) {
        $this->coverController = $ci->get('coverController');
    }

    public function getCover(Request $request, Response $response, $args){

        $isbn = $args['isbn'];

        $size = isset($args['size']) && $args['size'] === 'large' ? 'l' : 's';

        if (empty($isbn)) {
            throw new UserErrorException('No ISBN given');
        }

        $cover = $this->coverController->getCover($isbn, $size);

        if ($cover) {
            $response = $response->withHeader('Content-Type', $cover['mime']);
            return $response->withBody($cover['cover']);
        } else {
            return $response->withStatus(404);
        }

    }

}