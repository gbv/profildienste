<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 31.05.16
 * Time: 19:44
 */

namespace Routes;


use Interop\Container\ContainerInterface;
use Responses\BasicResponse;

class UserRoute extends Route {

    private $user;
    private $cart;
    private $config;

    public function __construct(ContainerInterface $ci){
        parent::__construct($ci);

        $this->user = $this->ci->get('user');
        $this->cart = $this->ci->get('cart');
        $this->config = $this->ci->get('config');
    }

    public function getUserInformation($request, $response, $args) {
        
        $defaults = $this->user->getDefaults();

        $data = [
            'name' => $this->user->getName(),
            'motd' => $this->config->getMOTD(),
            'defaults' => [
                'lft'     => $defaults['lieft'],
                'budget'  => $defaults['budget'],
                'ssgnr'   => $defaults['ssgnr'],
                'selcode' => $defaults['selcode']
            ],
            'budgets' => $this->user->getBudgets()
        ];

        return $this->generateJSONResponse(new BasicResponse($data), $response);
    }
    
    public function getSettings($request, $response, $args){

        $data = [
            'settings' => $this->user->getSettings()
        ];

        return self::generateJSONResponse(new BasicResponse($data), $response);
    }

    public function getOrderlist($request, $response, $args){
        throw new \RuntimeException('Not implemented yet!');
//        try {
//      $m = new \Special\Orderlist($auth);
//
//      printResponse(array('data' => array('orderlist' => $m->getOrderlist())));
//    } catch (\Exception $e) {
//      printResponse(NULL, true, $e->getMessage());
//    }
        // TODO
    }
}