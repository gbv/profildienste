<?php

namespace Profildienst\Title;

use Interop\Container\ContainerInterface;

class TitleFactory {

    /**
     * @var ContainerInterface
     */
    private $ci;

    public function __construct(ContainerInterface $ci){
        $this->ci = $ci;
    }

    public function createTitle($titleData){
        return new Title($titleData, $this->ci);
    }

    public function createTitleList(array $titleListData){
        $titles = [];
        foreach($titleListData as $titleData){
            $titles[] = self::createTitle($titleData);
        }
        return $titles;
    }
}