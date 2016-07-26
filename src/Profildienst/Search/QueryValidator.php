<?php

/**
 * @package Search
 */
namespace Profildienst\Search;

use Config\Configuration;


/**
 * Validates a search query and determines its correct type.
 * A correct type is either 'keyword', 'simple' or 'advanced'.
 * For keyword and simple searches, the queries have to be strings,
 * for advanced searches the query has to be an array of criteras.
 *
 * Class SearchQuery
 */
class QueryValidator {

    /**
     * The search query
     *
     * @var array
     */
    private $search;

    /**
     * SearchQuery constructor. Validates a query and determines
     * its correct type.
     *
     * @param string|array $query The query
     * @param null|string $type Supposed type of the query. If none is provided, 'keyword' is assumed.
     * @param Configuration $config
     * @throws InvalidQueryException
     */
    public function __construct($query, $type = 'keyword', Configuration $config) {

        // check if we got a query
        if (empty($query)) {
            throw new InvalidQueryException('Keine Suchanfrage eingegeben!');
        }

        // check for valid types
        if (!($type === 'keyword' || $type === 'simple' || $type === 'advanced')) {
            throw new InvalidQueryException('Ungültiger Query-Typ');
        }

        // check if the query has the correct datatype for the provided type
        if ($type === 'simple' || $type === 'keyword') {
            if (!is_string($query)) {
                throw new InvalidQueryException('Ungültiges Query für den gewählten Typ');
            }
        } else {
            if (!is_array($query)) {
                throw new InvalidQueryException('Ungültiges Query für den gewählten Typ');
            }
        }

        // additional checks for advanced searches
        if ($type === 'advanced') {

            $this->search = new MultipleFieldSearch();

            // check all criteria for validity
            for ($i = 0; $i < count($query); $i++) {

                $criterium = $query[$i];

                if (!is_array($criterium) || empty($criterium['field']) || empty($criterium['mode'] || !isset($criterium['value']))) {
                    throw new InvalidQueryException('Fehlende Informationen in einem Suchkriterium');
                }

                if (!in_array($criterium['mode'], array_keys($config->getSearchModes()))) {
                    throw new InvalidQueryException('Ungültiger Suchmodus');
                }

                // remove empty criteria
                if (empty($criterium['value'])) {
                    array_splice($query, $i, 1);
                    $i--;
                }
            }

            //check if we have enough criteria left after removing the empty ones
            if (count($query) === 0) {
                throw new InvalidQueryException('Bitte geben Sie mindestens ein Suchkriterium an');
            }

            // create a FieldSearch for each remaining criterium
            $criteria = [];
            foreach ($query as $criterium) {
                $crit = new FieldSearch();
                $crit->setField($criterium['field']);
                $crit->setMode($criterium['mode']);
                $crit->setSearchterm($criterium['value']);

                $criteria[] = $crit;
            }

            $this->search->setCriteria($criteria);

        } else {

            $this->search = new KeywordSearch();
            $this->search->setSearchterm($query);
            $this->search->setMode('contains');
            if (preg_match('/([a-z]{3}) (.*)/', $query, $matches) && in_array($matches[1], array_keys($config->getSearchableFields()))) {
                $this->search = new FieldSearch();
                $this->search->setField($matches[1]);
                $this->search->setSearchterm($matches[2]);
                $this->search->setMode('contains');
            }

            if (preg_match('/\"(.*?)\"/', $this->search->getSearchterm(), $matches)) {
                $this->search->setSearchterm($matches[1]);
                $this->search->setMode('is');
            }

        }

    }

    /**
     * @return SearchQuery
     */
    public function getSearch() {
        return $this->search;
    }
}