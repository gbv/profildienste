<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 31.05.16
 * Time: 19:20
 */

namespace Routes;


use Responses\BasicResponse;

class SettingsRoute extends Route {

    public function getSettings($request, $response, $args) {

        $config = $this->ci->get('config');

        $data = [
            'searchable_fields' => $this->nameValueList($config->getSearchableFields()),
            'search_modes' => $this->nameValueList($config->getSearchModes()),
            'sortby' => $this->nameValueList($config->getSortOptions()),
            'order' => $this->nameValueList($config->getOrderOptions())
        ];

        return self::generateJSONResponse(new BasicResponse($data), $response);
    }

    private function nameValueList($list) {
        $nvlist = [];
        foreach ($list as $val => $name) {
            $nvlist[] = array(
                'name' => $name,
                'value' => $val
            );
        }
        return $nvlist;
    }

    public function changeSetting($request, $response, $args) {
        ///**
// * Settings
// */
//$app->post('/settings', $authenticate($app, $auth), function () use ($app, $auth) {
//
//  $type = $app->request()->post('type');
//  $value = $app->request()->post('value');
//
//  $m = new \AJAX\ChangeSetting($type, $value, $auth);
//  printResponse($m->getResponse());
//});
//
//
    }

}