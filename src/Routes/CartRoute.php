<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 05.06.16
 * Time: 02:12
 */

namespace Routes;


use Exceptions\UserException;
use Interop\Container\ContainerInterface;
use Responses\ActionResponse;
use Responses\BasicResponse;

class CartRoute extends ViewRoute {

    use ActionHandler;

    private $cart;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        $this->cart = $this->ci->get('cart');
    }

    public function getCartView($request, $response, $args) {
        $page = self::validatePage($args);
        $titles = $this->cart->getTitles($page);
        $totalCount = $this->cart->getCount();

        return self::titlePageResponse($titles, $page, $totalCount, $response);
    }

    public function getCartInformation($request, $response, $args) {

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

    public function addTitlesToCart($request, $response, $args) {

        $affected = $this->handleStatusChange($request, 'cart', function ($oldStatus) {
            return $oldStatus !== 'done' && $oldStatus !== 'pending' && $oldStatus !== 'rejected';
        });

        if (is_null($affected)) {
            throw new UserException('Failed to update titles in cart.');
        }

        return self::generateJSONResponse(new ActionResponse($affected, 'cart'), $response);
    }

    public function removeTitlesFromCart($request, $response, $args) {

        $affected = $this->handleStatusChange($request, 'normal', function ($oldStatus) {
            return true;
        });

        if (is_null($affected)) {
            throw new UserException('Failed to remove titles from cart.');
        }

        return self::generateJSONResponse(new ActionResponse($affected, 'overview'), $response);
    }

    public function getOrderlist($request, $response, $args) {

        $data = [];

        foreach ($this->cart->getTitles() as $title) {
            $data[] = array(
                'title' => $title->getTitle(),
                'lieft' => $title->getSupplier(),
                'budget' => $title->getBudget(),
                'ssgnr' => $title->getSSGNr(),
                'selcode' => $title->getSelcode(),
                'comment' => $title->getComment(),
                'supplier' => $title->getSupplier(),
                'gvkt' => $title->getGVKInfo()
            );
        }

        return self::generateJSONResponse(new BasicResponse($data), $response);
    }

///** TODO */
// * Order
// */
//$app->post('/order', $authenticate($app, $auth), function () use ($app, $auth) {
//  $m = new \Special\Order($auth);
//  printResponse($m->getResponse());
//});
//

}