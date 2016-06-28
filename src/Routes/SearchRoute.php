<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 31.05.16
 * Time: 19:16
 */

namespace Routes;


use Responses\BasicResponse;

class SearchRoute extends Route{

    public function getSearchOptions($request, $response, $args){

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

}