<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 04.06.16
 * Time: 13:44
 */

namespace Routes;


use Interop\Container\ContainerInterface;
use Profildienst\GetView;
use Responses\TitlelistResponse;

class TitleRoute extends Route{

    private $titleRepository;
    private $config;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        $this->titleRepository = $this->ci->get('titleRepository');
        $this->config = $this->ci->get('config');
    }


    public function getMainView($request, $response, $args){
        $page = self::validatePage($args);
        return $this->makeTitleResponse('normal', $page, $response);
    }
/*
    public function getPendingView($request, $response, $args){
        $resp = $this->makeTitleResponse('pending', self::validatePage($args));
        return self::generateJSONResponse($resp, $response);
    }

    public function getDoneView($request, $response, $args){
        $resp = $this->makeTitleResponse('done', self::validatePage($args));
        return self::generateJSONResponse($resp, $response);
    }

    public function getRejectedView($request, $response, $args){
        $resp = $this->makeTitleResponse('rejected', self::validatePage($args));
        return self::generateJSONResponse($resp, $response);
    }*/

    private function makeTitleResponse($view, $page, $response){
        $titles = $this->titleRepository->getTitlesByView($view, $page);
        $totalCount = 0;

        return self::titlePageResponse($titles, $page, $totalCount, $response);
    }

    public function saveTitleInformation($request, $response, $args){
//
//
///**
// * Save additional informations for titles
// */
//$app->post('/save', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $id = $app->request()->post('id');
//  $type = $app->request()->post('type');
//  $content = $app->request()->post('content');
//
//  $m = new \AJAX\Save($id, $type, $content, $auth);
//  printResponse($m->getResponse());
//
//});
//
    }

    public function delete($request, $response, $args){
///**
// * Delete titles
// */
//$app->post('/delete', $authenticate($app, $auth), function () use ($app, $auth) {
//  $m = new \AJAX\Delete($auth);
//  printResponse($m->getResponse());
//});
//
    }

    public function titleInfo($request, $response, $args){
///**
// * Verlagsmeldung
// */
//$app->post('/info', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $id = $app->request()->post('id');
//
//  $m = new \AJAX\Info($id, $auth);
//  printResponse($m->getResponse());
//});

    }

    public function getOPACLink($request, $response, $args){
//
///**
// * OPAC Abfrage
// */
//$app->post('/opac', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $titel = $app->request()->post('titel');
//  $verfasser = $app->request()->post('verfasser');
//
//  $query = $titel . ' ' . $verfasser;
//
//  $isil = \Profildienst\DB::getUserData('isil', $auth);
//
//  $opac_url = Config::$bibliotheken[$isil]['opac'];
//
//  $url = preg_replace('/%SEARCH_TERM%/', urlencode($query), $opac_url);
//
//  printResponse(array('data' => array('url' => $url)));
//
//});
//
    }

}