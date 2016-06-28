<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 04.06.16
 * Time: 14:13
 */

namespace Profildienst\Title;


use Config\Configuration;
use Profildienst\User\User;

class TitleRepository {

    private $gateway;
    private $titleFactory;
    private $user;
    private $config;

    public function __construct(TitleGateway $gateway, TitleFactory $titleFactory, User $user, Configuration $config) {
        $this->gateway = $gateway;
        $this->user = $user;
        $this->config = $config;
        $this->titleFactory = $titleFactory;
    }

//    public function getTitlesByStatus($status) {
//        $titleData = $this->gateway->getTitlesByStatus(
//            $this->user->getId(),
//            $status,
//            null,
//            null
//        );
//
//        return $this->titleFactory->createTitleList($titleData);
//    }

    public function getTitlesByView($view, $page = 0) {
        $titleData = $this->gateway->getTitlesByStatus(
            $this->user->getId(),
            $view,
            $this->config->getPagesize(),
            $this->config->getPagesize() * $page
        );

        return $this->titleFactory->createTitleList($titleData);
    }

    public function getTitleCountInView($view) {
        return $this->gateway->getTitleCountWithStatus($this->user->getId(), $view);
    }

    public function findTitleById($id) {

    }


}