<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 05.06.16
 * Time: 02:12
 */

namespace Routes;


use Profildienst\Cart\Cart;
use Profildienst\User\User;
use Responses\BasicResponse;
use Responses\ActionResponse;
use Exceptions\UserErrorException;
use Profildienst\Cart\OrderController;
use Interop\Container\ContainerInterface;

class CartRoute extends ViewRoute {

    use ActionHandler;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var OrderController
     */
    private $orderController;

    /**
     * @var User
     */
    private $user;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        $this->cart = $this->ci->get('cart');
        $this->orderController = $this->ci->get('orderController');
        $this->user = $this->ci->get('user');
    }

    public function getCartView($request, $response, $args) {
        $page = self::validatePage($args);
        $offset = self::validateOffset($args);
        $titles = $this->cart->getTitles($page, $offset);
        $totalCount = $this->cart->getCount();

        return self::titlePageResponse($titles, $page, $totalCount, $response, null, $offset);
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

        $affected = $this->handleStatusChange($request, 'cart', function ($oldState) {
            return in_array($oldState, ['normal', 'watchlist']);
        });

        if (is_null($affected)) {
            throw new UserErrorException('Failed to update titles in cart.');
        }

        return self::generateJSONResponse(new ActionResponse($affected, 'cart'), $response);
    }

    public function removeTitlesFromCart($request, $response, $args) {

        $affected = $this->handleStatusChange($request, 'normal', function ($oldState) {
            return $oldState === 'cart';
        });

        if (is_null($affected)) {
            throw new UserErrorException('Failed to remove titles from cart.');
        }

        return self::generateJSONResponse(new ActionResponse($affected, 'overview'), $response);
    }

    /**
     * @param $request
     * @param $response
     * @param $args
     * @return \Slim\Http\Response
     */
    public function getOrderlist($request, $response, $args) {

        $data = [];

        foreach ($this->cart->getTitles() as $title) {
            $data[] = [
                'title' => $title->getTitle(),
                'uebergeordnete_gesamtheit' => $title->getUebergeordneteGesamtheit(),
                'supplier' => $this->orderInfoResolve('supplier', $title->getSupplier()),
                'budget' => $this->orderInfoResolve('budget', $title->getBudget()),
                'ssgnr' => $this->orderInfoResolve('ssgnr', $title->getSSGNr()),
                'selcode' => $this->orderInfoResolve('selcode', $title->getSelcode()),
                'comment' => $title->getComment(),
                'gvkt' => $title->getGVKInfo()
            ];
        }

        return self::generateJSONResponse(new BasicResponse($data), $response);
    }

    private function orderInfoResolve($orderInfo, $titlesInfo) {
        $setByUser = true;
        $info = empty($titlesInfo)
            ? $this->user->getDefaults()[$orderInfo]
            : $titlesInfo;

        // display the name of the supplier and the budget instead of the key
        if ($orderInfo === 'supplier') {
            $info = $this->user->getSupplier($info)['name'];
        }

        if ($orderInfo === 'budget') {
            $info = $this->user->getBudget($info)['name'];
        }

        return ['value' => $info, 'setByUser' => !empty($titlesInfo)];

    }

    public function order() {
        $this->ci->get('orderController')->order($this->cart);
    }
}
