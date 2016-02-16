<?php

namespace Auth;

/**
 * Checks if the user is registered in the database.
 *
 * Class LoginChecker
 * @package Auth
 */
class LoginChecker {

    /**
     * Checks if the user is registered in the database.
     *
     * @param $id string ID of the user
     * @return bool true if the user has an entry in the database
     */
    public static function check($id) {

        $data = \Profildienst\DB::get(array('_id' => $id), 'users', array('_id' => 1), true);

        return !is_null($data);
    }
}

?>