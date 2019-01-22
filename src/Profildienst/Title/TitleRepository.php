<?php

namespace Profildienst\Title;

/**
 * Class TitleRepository
 * @package Profildienst\Title
 */
class TitleRepository {

    private $gateway;

    /**
     * @var TitleFactory
     */
    private $titleFactory;

    public function __construct(TitleGateway $gateway, TitleFactory $titleFactory) {
        $this->gateway = $gateway;
        $this->titleFactory = $titleFactory;
    }

    public function getAllTitlesByStatus($status) {
        $titleData = $this->gateway->getAllTitlesByStatus($status);
        return $this->titleFactory->createTitleList($titleData);
    }

    public function getTitlesByStatus($status, $page, $dateSorted = false, $offset) {
        $titleData = $this->gateway->getTitlesByStatus($status, $page, $dateSorted, $offset);
        return $this->titleFactory->createTitleList($titleData);
    }

    public function getTitleCountWithStatus($status) {
        return $this->gateway->getTitleCountWithStatus($status);
    }

    public function findTitlesById($ids) {
        $titleData = $this->gateway->getTitlesById($ids);
        return $this->titleFactory->createTitleList($titleData);
    }

    public function changeStatusOfTitles($ids, $newStatus) {
        return $this->gateway->updateTitlesWithIds($ids, $newStatus);
    }

    public function changeStatusOfView($oldStatus, $newStatus) {
        return $this->gateway->updateTitlesWithStatus($oldStatus, $newStatus);
    }

    public function changeOrderInformationOfTitles($ids, $orderInformation) {
        return $this->gateway->updateTitlesOrderInformation($ids, $orderInformation);
    }

    public function deleteRejectedTitles() {
        return $this->gateway->deleteTitlesWithStatus('rejected');
    }

}