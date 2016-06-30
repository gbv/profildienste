<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 04.06.16
 * Time: 14:13
 */

namespace Profildienst\Title;


use Config\Configuration;
use Exceptions\UserException;
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

    public function findTitlesById($ids) {
        $titleData = $this->gateway->getTitlesById($ids);
        return $this->titleFactory->createTitleList($titleData);
    }

    public function changeStatusOfTitles($ids, $newStatus){

        $titles = $this->findTitlesById($ids);

        foreach($titles as $title){
            if(!$this->allowReject($title->getStatus()) || $title->isInWatchlist()){
                throw new UserException('This selection of titles can not be rejected');
            }
        }
        
        if (!$this->gateway->updateTitlesWithIds($ids, $newStatus)){
            throw new UserException('Nothing to change or updating failed.');
        }
    }

    private function allowReject($oldState){
        return $oldState !== 'cart' && $oldState !== 'done' && $oldState !== 'pending';
    }



}