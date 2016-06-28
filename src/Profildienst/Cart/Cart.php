<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 18.06.16
 * Time: 17:35
 */

namespace Profildienst\Cart;


use Profildienst\Title\Title;
use Profildienst\Title\TitleRepository;

class Cart {

    private $titleRepository;

    private $totalPrice;
    private $priceKnown;
    private $priceEstimated;

    public function __construct(TitleRepository $titleRepository){
        $this->titleRepository = $titleRepository;
    }

    public function getAllTitles(){
        $titles = $this->titleRepository->getTitlesByStatus('cart');
    }

    public function getTitles($page){
        return $this->titleRepository->getTitlesByView('cart', $page);
    }

    public function addTitle(Title $title){

    }
    public function removeTitle(Title $title){

    }

    public function getCount(){
        return $this->titleRepository->getTitleCountInView('cart');
    }

    public function getPrice() {
        // TODO
        return $this->totalPrice;
    }

    public function getPriceKnown() {
        // TODO
        return $this->priceKnown;
    }

    public function getPriceEstimated() {
        // TODO
        return $this->priceEstimated;
    }

}