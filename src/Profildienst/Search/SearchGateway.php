<?php

namespace Profildienst\Search;


interface SearchGateway {

    public function getTitles($query, $page);

    public function getMatchingTitleCount($query);

}