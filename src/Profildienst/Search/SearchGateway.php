<?php

namespace Profildienst\Search;


interface SearchGateway {

    public function getTitles($query, $page, $offset);

    public function getMatchingTitleCount($query);

}