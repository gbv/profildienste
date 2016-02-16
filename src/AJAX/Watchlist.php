<?php

namespace AJAX;

use Middleware\AuthToken;
use Profildienst\DB;

/**
 * Adds a title to a watchlist
 *
 * Class Watchlist
 * @package AJAX
 */
class Watchlist extends AJAXResponse {

    /**
     * Adds a title to a watchlist
     *
     * @param $id string ID of the title
     * @param $wl_id int ID of the watchlist
     * @param AuthToken $auth Token
     */
    public function __construct($id, $wl_id, AuthToken $auth) {

        $this->resp['content'] = NULL;
        $this->resp['id'] = NULL;
        $this->resp['wl'] = NULL;

        if ($id === '' || $wl_id === '') {
            $this->error('Unvollständige Daten');
            return;
        }

        $this->resp['id'] = $id;
        $this->resp['wl'] = $wl_id;

        $watchlists = DB::getUserData('watchlist', $auth);

        if (is_null($watchlists)) {
            $this->error('Es konnten keine Merklisten für einen Benutzer mit dieser ID gefunden werden.');
            return;
        }

        if (is_null($watchlists[$wl_id])) {
            $this->error('Keine Merkliste mit dieser ID gefunden.');
            return;
        }

        $title = DB::getTitleByID($id);
        if($title->getUser() !== $auth->getID()){
            $this->error('Sie haben keine Berechtigung diesen Titel zu bearbeiten.');
            return;
        }

        if($title->isInWatchlist()){
            $this->error('Dieser Titel befindet sich bereits in der Merkliste.');
            return;
        }

        DB::upd(array('_id' => $id), array('$set' => array('watchlist' => $wl_id)), 'titles');
        $this->resp['content'] = DB::getWatchlistSize($wl_id, $auth);
        $this->resp['success'] = true;
    }
}

?>
