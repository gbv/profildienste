<?php

namespace Routes;


use Responses\BasicResponse;
use Exceptions\UserErrorException;
use Interop\Container\ContainerInterface;

class SearchRoute extends ViewRoute {

    private $searchFactory;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        $this->searchFactory = $ci->get('searchFactory');
    }

    public function getSearchOptions($request, $response, $args) {

        $config = $this->ci->get('config');

        $searchable_fields = [];
        foreach ($config->getSearchableFields() as $val => $name) {
            $searchable_fields[] = array(
                'name' => $name,
                'value' => $val
            );
        }

        $search_modes = [];
        foreach ($config->getSearchModes() as $val => $name) {
            $search_modes[] = array(
                'name' => $name,
                'value' => $val
            );
        }

        $data = [
            'searchable_fields' => $searchable_fields,
            'search_modes' => $search_modes
        ];

        return self::generateJSONResponse(new BasicResponse($data), $response);

    }

    public function searchTitles($request, $response, $args) {

        // validate arguments
        $page = self::validatePage($args);

        $queryType = $args['queryType'];
        if (empty($queryType) || !in_array($queryType, ['advanced', 'keyword'])) {
            throw new UserErrorException('Invalid or empty query type');
        }

        $query = $args['query'];

        if (empty($query)) {
            throw new UserErrorException('Empty queries are not allowed');
        }


        if ($queryType === 'advanced') {
            try {
                $query = json_decode($query, true);
            } catch (\Exception $e) {
                throw new UserErrorException('Invalid query');
            }
        }

        $search = $this->searchFactory->createSearch($query, $queryType);

        $titles = $search->getTitles($page);

        return self::titlePageResponse($titles, $page, $search->getTotalCount(), $response, $search->getSearchInformation());
    }

}