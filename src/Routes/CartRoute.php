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

class CartRoute extends ViewRoute{

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

    ///** TODO */
// * Cart
// */
//$app->group('/cart', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $app->post('/remove', function () use ($app, $auth) {
//
//    $id = $app->request()->post('id');
//    $view = $app->request()->post('view');
//
//    $m = new \AJAX\RemoveCart($id, $view, $auth);
//    printResponse($m->getResponse());
//  });
//
//
//  $app->post('/add', function () use ($app, $auth) {
//
//    $id = $app->request()->post('id');
//    $view = $app->request()->post('view');
//
//    $m = new \AJAX\Cart($id, $view, $auth);
//    printResponse($m->getResponse());
//  });
//
//});

///** TODO */
// * Order
// */
//$app->post('/order', $authenticate($app, $auth), function () use ($app, $auth) {
//  $m = new \Special\Order($auth);
//  printResponse($m->getResponse());
//});
//

}