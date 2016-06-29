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

    public function __construct(TitleGateway $gateway, TitleFactory $titleFactory) {
        $this->gateway = $gateway;
        $this->titleFactory = $titleFactory;
    }

    public function getAllTitlesByStatus($status) {
        $titleData = $this->gateway->getAllTitlesByStatus($status);
        return $this->titleFactory->createTitleList($titleData);
    }

    public function getTitlesByStatus($status, $page) {
        $titleData = $this->gateway->getTitlesByStatus($status, $page);
        return $this->titleFactory->createTitleList($titleData);
    }

    public function getTitleCountWithStatus($status) {
        return $this->gateway->getTitleCountWithStatus($status);
    }

    public function findTitleById($id) {

    }

}