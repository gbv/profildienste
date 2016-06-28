<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 05.06.16
 * Time: 02:12
 */

namespace Routes;


use Interop\Container\ContainerInterface;
use Responses\BasicResponse;

class CartRoute extends Route{

    private $cart;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        $this->cart = $this->ci->get('cart');
    }

    public function getCartView($request, $response, $args){
        $page = self::validatePage($args);
        $titles = $this->cart->getTitles($page);
        $totalCount = $this->cart->getCount();

        return self::titlePageResponse($titles, $page, $totalCount, $response);
    }

    public function getCartInformation($request, $response, $args){

        $data = [
            'count' => $this->cart->getCount(),
            'price' => [
                'total' => $this->cart->getPrice(),
                'known' => $this->cart->getPriceKnown(),
                'estimated' => $this->cart->getPriceEstimated()
            ]
        ];

        return self::generateJSONResponse(new BasicResponse($data), $response);
    }

}