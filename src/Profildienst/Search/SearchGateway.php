<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 27.07.16
 * Time: 00:32
 */

namespace Profildienst\Search;


interface SearchGateway {

    public function getTitles($query, $page);

    public function getMatchingTitleCount($query);

}