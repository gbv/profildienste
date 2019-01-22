<?php

namespace Routes;

use Responses\TitlelistResponse;
use Interop\Container\ContainerInterface;

abstract class ViewRoute extends Route{

    protected $titleRepository;
    protected $config;

    public function __construct(ContainerInterface $ci){
        parent::__construct($ci);
        $this->ci = $ci;
        $this->titleRepository = $this->ci->get('titleRepository');
        $this->config = $this->ci->get('config');
    }

    protected static function validatePage($args){
        $page = ($args['page'] ?? 0);

        if (empty($page) || !filter_var($page, FILTER_VALIDATE_INT) || $page < 0){
            $page = 0;
        }

        return $page;
    }
    
    protected static function validateOffset($args){
        
        $offset = ($args['offset'] ?? 0);
        
        if (empty($offset) || !filter_var($offset, FILTER_VALIDATE_INT) || $offset > 0){
            $offset = 0;
        }
        
        return $offset;
    }

    protected function titlePageResponse($titles, $page, $totalCount, $response, $additionalInformation = null, $offset = 0){
        
        $more = ($this->ci->get('config')->getPagesize() * ($page+1) < $totalCount - $offset);

        return self::generateJSONResponse(new TitlelistResponse($titles, $totalCount, $more, $additionalInformation), $response);
    }

    protected function makeTitleResponse($view, $page, $response, $dateSorted = false, $offset = 0){
        $titles = $this->titleRepository->getTitlesByStatus($view, $page, $dateSorted, $offset);
        $totalCount = $this->titleRepository->getTitleCountWithStatus($view);

        return self::titlePageResponse($titles, $page, $totalCount, $response, null, $offset);
    }

}