<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 31.05.16
 * Time: 18:59
 */

namespace Routes;


use Responses\BasicResponse;

class LibraryRoute extends Route{

    public function getLibraries($request, $response, $args) {
        $data = [];
        foreach ($this->ci->get('libraryController')->getLibraries() as $library) {
            $data[] = [
                'isil' => $library->getISIL(),
                'name' => $library->getName()
            ];
        }

        return self::generateJSONResponse(new BasicResponse($data), $response);
    }

}