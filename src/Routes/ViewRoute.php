<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 30.06.16
 * Time: 11:45
 */

namespace Routes;


use Interop\Container\ContainerInterface;
use Responses\TitlelistResponse;

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

    protected function titlePageResponse($titles, $page, $totalCount, $response){

        $more = ($this->ci->get('config')->getPagesize() * ($page+1) < $totalCount);

        return self::generateJSONResponse(new TitlelistResponse($titles, $totalCount, $more), $response);
    }

    protected function makeTitleResponse($view, $page, $response){
        $titles = $this->titleRepository->getTitlesByStatus($view, $page);
        $totalCount = $this->titleRepository->getTitleCountWithStatus($view);

        return self::titlePageResponse($titles, $page, $totalCount, $response);
    }

}