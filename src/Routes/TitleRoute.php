<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 04.06.16
 * Time: 13:44
 */

namespace Routes;


use Exceptions\UserException;
use Interop\Container\ContainerInterface;
use Profildienst\GetView;
use Responses\ActionResponse;

class TitleRoute extends Route {

    use ActionHandler;

    private $titleRepository;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        $this->titleRepository = $this->ci->get('titleRepository');
    }


    public function saveTitleInformation($request, $response, $args) {

        $affected = $this->validateAffectedTitles($request);

        // currenty saving information is only supported for a single title
        if (!is_array($affected) || count($affected) > 1) {
            throw new UserException('This operation is currently not implemented for views or multiple titles.');
        }

        // validate values to update
        $parameters = $request->getParsedBody();
        $save = $parameters['save'];

        if (!is_array($save) || count($save) < 1) {
            throw new UserException('Illegal format for save parameters');
        }

        $updatedOrderInformation = [];
        foreach ($save as $orderInfo) {
            $type = $orderInfo['type'] ?? null;
            $value = $orderInfo['value'] ?? null;

            if (is_null($type) || is_null($value) || !in_array($type, ['budget', 'lieft', 'selcode', 'ssgnr', 'comment'])) {
                throw new UserException('Save parameter must have a valid type and value');
            }

            $updatedOrderInformation[$type] = $value;
        }


        if (!$this->titleRepository->changeOrderInformationOfTitles($affected, $updatedOrderInformation)) {
            throw new UserException('Failed to save the updated order information');
        }


        return self::generateJSONResponse(new ActionResponse($affected, 'save', $updatedOrderInformation), $response);
    }

    public function delete($request, $response, $args) {
///**
// * Delete titles
// */
//$app->post('/delete', $authenticate($app, $auth), function () use ($app, $auth) {
//  $m = new \AJAX\Delete($auth);
//  printResponse($m->getResponse());
//});
//
    }

    public function titleInfo($request, $response, $args) {
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

    public function getOPACLink($request, $response, $args) {
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