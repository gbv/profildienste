<?php
/**
 * Performs a search
 */

/**
 * @package Search
 */
namespace Profildienst\Search;

use Config\Configuration;
use Profildienst\Title\TitleFactory;

/**
 * Performs a search.
 *
 * Class Search
 */
class Search {

    private $gateway;
    private $titleFactory;

    private $search;
    private $dbquery;

    /**
     * Performs a search
     *
     * @param $q string|array Search query
     * @param $queryType string Type of the search query(keyword or advanced)
     * @param SearchGateway $gateway
     * @param Configuration $config
     * @param TitleFactory $titleFactory
     * @internal param int $num Requested page
     * @internal param AuthToken $auth Token
     */
    public function __construct($q, $queryType, SearchGateway $gateway, Configuration $config, TitleFactory $titleFactory) {

        $query = new QueryValidator($q, $queryType, $config);
        $this->search = $query->getSearch();

        $this->dbquery = $this->search->getDatabaseQuery();
        $this->dbquery->searchTitlesWithStatus('normal')
            ->joinWithAnd();

        $this->gateway = $gateway;
        $this->titleFactory = $titleFactory;
    }

    /**
     * Getter for titles
     *
     * @param int $page
     * @return array Page of matching titles
     */
    public function getTitles($page = 0) {

        $titles = $this->gateway->getTitles($this->dbquery->getQuery(), $page);
        return $this->titleFactory->createTitleList($titles);
    }

    /**
     * Getter for the total amount of titles
     *
     * @return int total amount of titles found
     */
    public function getTotalCount() {
        return $this->gateway->getMatchingTitleCount($this->dbquery->getQuery());
    }

    public function getSearchInformation() {
        return array(
            'type' => $this->search->getType(),
            'criteria' => $this->search->getSearchAsArray()
        );
    }


}
