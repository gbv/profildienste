<?php

namespace Content;

use Profildienst\TitleList;

/**
 * All Classes which are used by the pageloader have to implement
 * this interface.
 *
 * Class Content
 * @package Content
 */
abstract class Content {

    /**
     * @var TitleList List of Titles
     */
    protected $titlelist;

    /**
     * @var int total amount of titles
     */
    protected $total;

    /**
     * Getter for the titles
     *
     * @return TitleList
     */
    public function getTitles() {
        return $this->titlelist;
    }

    /**
     * Getter for the total amount
     *
     * @return int
     */
    public function getTotalCount() {
        return $this->total;
    }

}


?>