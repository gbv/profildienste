<?php

namespace Routes;


use Responses\BasicResponse;

class LibraryRoute extends Route{

    public function getLibraries($request, $response, $args) {
        $data = [];
        $libraries = array_filter($this->ci->get('libraryController')->getLibraries(), function ($library) {
            return !$library->isHidden();
        });

        foreach ($libraries as $library) {
            $data[] = [
                'isil' => $library->getISIL(),
                'name' => $library->getName()
            ];
        }

        return self::generateJSONResponse(new BasicResponse($data), $response);
    }

}