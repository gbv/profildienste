<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 04.06.16
 * Time: 17:51
 */

namespace Responses;


use Exceptions\DatabaseException;
use Profildienst\Title\Title;

class TitlelistResponse extends APIResponse{

    public function __construct(array $titles, int $totalCount, bool $more){

        $titlesOut = [];
        foreach($titles as $title){
            if ($title instanceof Title) {
                $titlesOut[] = $title->toJson();
            } else {
                throw new DatabaseException('Only Title objects can be used in a TitleList response');
            }
        }

        $this->data['titles'] = $titlesOut;
        $this->data['total'] = $totalCount;
        $this->data['more'] = $more;
    }

    public function getData() {
        return $this->data;
    }
}