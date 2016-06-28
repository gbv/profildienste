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

    private $cartRepository;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        $this->cartRepository = $this->ci->get('cartRepository');
    }

    public function getCartView($request, $response, $args){
        $page = self::validatePage($args);
        $titles = $this->cartRepository->getTitles($page);
        $totalCount = $this->cartRepository->getCount();

        return self::titlePageResponse($titles, $page, $totalCount, $response);
    }

    public function getCartInformation($request, $response, $args){

        $data = [
            'count' => $this->cartRepository->getCount(),
            'price' => [
                'total' => $this->cartRepository->getPrice(),
                'known' => $this->cartRepository->getPriceKnown(),
                'estimated' => $this->cartRepository->getPriceEstimated()
            ]
        ];

        return self::generateJSONResponse(new BasicResponse($data), $response);
    }

}