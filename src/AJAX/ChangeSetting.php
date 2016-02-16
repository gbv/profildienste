<?php

namespace AJAX;

use Middleware\AuthToken;
use Profildienst\DB;

/**
 * Changes a setting (order or sorting criterion)
 *
 * Class ChangeSetting
 * @package AJAX
 */
class ChangeSetting extends AJAXResponse {


    /**
     * @param $type string type of the setting which should be changed
     * @param $value string the new value
     * @param AuthToken $auth Token
     */
    public function __construct($type, $value, AuthToken $auth) {

        $this->resp['type'] = NULL;
        $this->resp['value'] = NULL;

        //check if we got all the data we need
        if ($type === '' || $value === '') {
            $this->error('Unvollständige Daten');
            return;
        }

        // check if user exists
        if (!$c = DB::get(array('_id' => $auth->getID()), 'users', array('settings' => 1, '_id' => 0), true)) {
            $this->error('Kein Benutzer unter der ID gefunden.');
        }

        // update settings
        $settings = $c['settings'];

        if (!in_array($type, array_keys($settings))) {
            $this->error('Diese Einstellung existiert nicht!');
        } else {
            $settings[$type] = $value;
            DB::upd(array('_id' => $auth->getID()), array('$set' => array('settings' => $settings)), 'users');
            $this->resp['success'] = true;
            $this->resp['type'] = $type;
            $this->resp['value'] = $value;
        }
    }
}

?>
