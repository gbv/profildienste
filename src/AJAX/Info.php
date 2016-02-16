<?php

namespace AJAX;

use Middleware\AuthToken;
use Profildienst\DB;

/**
 * Loads additional information for a given title
 *
 * Class Info
 * @package AJAX
 */
class Info extends AJAXResponse {

    /**
     * Loads additional information for the title with the given id.
     *
     * @param $id string ID of the title
     * @param AuthToken $auth Token
     */
    public function __construct($id, AuthToken $auth) {

        $this->resp['content'] = NULL;
        $this->resp['id'] = NULL;
        $this->resp['type'] = NULL;

        if ($id === '') {
            $this->error('Unvollständige Daten');
            return;
        }

        $this->resp['id'] = $id;

        if (!$title = DB::getTitleByID($id)) {
            $this->error('Kein Eintrag unter der ID gefunden');
        }

        $url = $title->get('addr_erg_ang_url');
        $mime = $title->get('addr_erg_ang_mime');

        if ($mime === 'text/html') {

            $f = file_get_contents($url);
            preg_match('/<body>(.*?)<\/body>/si', $f, $matches);
            $this->resp['content'] = $matches[1];
            $this->resp['type'] = 'html';
            $this->resp['success'] = true;

        } else {
            $this->resp['content'] = $url;
            $this->resp['type'] = 'other';
            $this->resp['success'] = true;
        }
    }
}

?>
