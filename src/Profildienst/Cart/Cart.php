<?php

namespace Profildienst\Cart;

use Profildienst\Title\Title;
use Profildienst\Common\DataGateway;
use Profildienst\Title\TitleRepository;

class Cart {

    private $titleRepository;
    private $dataGateway;

    private $totalPrice;
    private $priceKnown;
    private $priceEstimated;

    public function __construct(TitleRepository $titleRepository, DataGateway $dataGateway) {
        $this->titleRepository = $titleRepository;
        $this->dataGateway = $dataGateway;

        $this->totalPrice = 0;
        $this->priceKnown = 0;
        $this->priceEstimated = 0;

        $titles = $this->titleRepository->getAllTitlesByStatus('cart');
        $mean = $dataGateway->getMean();
        foreach ($titles as $title) {
            $price = $title->getEURPrice();
            if (is_null($price)) {
                $price = $mean;
                $this->priceEstimated++;
            } else {
                $this->priceKnown++;
            }

            $this->totalPrice += $price;
        }
    }


    /**
     * Returns all titles in the cart (if page is null) or
     * a page of titles from the cart.
     *
     * @param int|null $page
     * @return array Titles
     */
    public function getTitles($page = null) {
        return is_null($page)
            ? $this->titleRepository->getAllTitlesByStatus('cart')
            : $this->titleRepository->getTitlesByStatus('cart', $page);
    }

    public function addTitle(Title $title) {
        // hier auch beachten, dass price geupdated werden muss
    }

    public function removeTitle(Title $title) {
        // hier auch beachten, dass price geupdated werden muss
    }

    public function getCount() {
        return $this->titleRepository->getTitleCountWithStatus('cart');
    }

    public function getPrice() {
        return $this->totalPrice;
    }

    public function getPriceKnown() {
        return $this->priceKnown;
    }

    public function getPriceEstimated() {
        return $this->priceEstimated;
    }

}