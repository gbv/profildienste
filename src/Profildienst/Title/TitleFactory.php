<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 07.06.16
 * Time: 14:36
 */

namespace Profildienst\Title;


class TitleFactory {

    public function createTitle($titleData){
        return new Title($titleData);
    }

    public function createTitleList(array $titleListData){
        $titles = [];
        foreach($titleListData as $titleData){
            $titles[] = self::createTitle($titleData);
        }
        return $titles;
    }
}