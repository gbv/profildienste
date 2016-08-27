<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 31.05.16
 * Time: 19:44
 */

namespace Routes;


use Exceptions\UserException;
use Interop\Container\ContainerInterface;
use Responses\BasicResponse;

class UserRoute extends Route {

    private $user;
    private $cart;
    private $config;
    private $userController;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);

        $this->user = $this->ci->get('user');
        $this->cart = $this->ci->get('cart');
        $this->config = $this->ci->get('config');
        $this->userController = $this->ci->get('userController');
    }

    public function getUserInformation($request, $response, $args) {

        $defaults = $this->user->getDefaults();

        $data = [
            'name' => $this->user->getName(),
            'motd' => $this->config->getMOTD(),
            'defaults' => [
                'supplier' => '0000', //value, TODO
                'budget' => $defaults['budget'],
                'ssgnr' => $defaults['ssgnr'],
                'selcode' => $defaults['selcode']
            ],
            'budgets' => $this->user->getBudgets(),
            'suppliers' => $this->user->getSuppliers()
        ];

        return self::generateJSONResponse(new BasicResponse($data), $response);
    }

    public function getSettings($request, $response, $args) {

        $data = [
            'settings' => $this->user->getSettings()
        ];

        return self::generateJSONResponse(new BasicResponse($data), $response);
    }

    public function changeSetting($request, $response, $args) {

        // validate parameters
        $parameters = $request->getParsedBody();
        $type = $parameters['type'];
        $value = $parameters['value'];


        if (empty($type) || !in_array($type, ['order', 'sortby'])) {
            throw new UserException('Unknown or empty setting');
        }

        if ($type === 'order' && !in_array($value, array_keys($this->config->getOrderOptions()))) {
            throw new UserException('Unknown order option');
        }

        if ($type === 'sortby' && !in_array($value, array_keys($this->config->getSortOptions()))) {
            throw new UserException('Unknown sort option');
        }

        // update settings
        if ($type === 'order') {
            $this->user->setOrderSetting($value);
        } else if ($type === 'sortby') {
            $this->user->setSortSetting($value);
        }

        // persist changes
        $this->userController->persist($this->user);

        // response
        $data = [
            'type' => $type,
            'value' => $value
        ];

        return self::generateJSONResponse(new BasicResponse($data), $response);

    }
}