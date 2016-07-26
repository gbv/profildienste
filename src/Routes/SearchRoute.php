<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 31.05.16
 * Time: 19:16
 */

namespace Routes;


use ErrorException;
use Exception;
use Exceptions\UserException;
use Interop\Container\ContainerInterface;
use Responses\BasicResponse;

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
        if (empty($queryType) || !in_array($queryType, ['advanced', 'keyword'])){
            throw new UserException('Invalid or empty query type');
        }

        $query = $args['query'];

        if (empty($query)){
            throw new UserException('Empty queries are not allowed');
        }


        if($queryType === 'advanced') {
            try {
                $query = json_decode($query, true);
            } catch (Exception $e) {
                throw new UserException('Invalid query');
            } catch (ErrorException $e) {
                throw new UserException('Invalid query');
            }
        }

        $search = $this->searchFactory->createSearch($query, $queryType);

        $titles = $search->getTitles($page);

        return self::titlePageResponse($titles, $page, $search->getTotalCount(), $response, $search->getSearchInformation());
    }


// TODO
//  $app->get('/search/:query/:queryType/page/:num', function ($query, $queryType = 'keyword', $num = 0) use ($app, $auth) {
//    try {
//
//      if($queryType === 'advanced'){
//        $query = json_decode($query, true);
//      }
//
//      $m = new \Search\Search($query, $queryType, $num, $auth);
//      printTitles($m->getTitles(), $m->getTotalCount(), $m->getSearchInformation());
//    } catch (\Exception $e) {
//      printResponse(NULL, true, $e->getMessage());
//    }
//
//  });
//

//});
//

}