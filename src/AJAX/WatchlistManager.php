<?php

namespace AJAX;

use Middleware\AuthToken;
use Profildienst\DB;

/**
 * Manages watchlists, i.e. adds, deletes, renames watchlists and changes the order of
 * watchlists.
 *
 * Class WatchlistManager
 * @package AJAX
 */
class WatchlistManager extends AJAXResponse {

    /**
     * @param $id string ID of the watchlist
     * @param $type string Type of the operatio
     * @param $content string New value
     * @param AuthToken $auth Token
     */
    public function __construct($id, $type, $content, AuthToken $auth) {

        if ($content == '') {
            $content = NULL;
        }

        $this->resp['type'] = NULL;
        $this->resp['id'] = NULL;

        if (($id == '' && $type != 'add-wl' && $type != 'change-order') || $type == '') {
            $this->error('Unvollständige Daten');
            return;
        }

        $this->resp['id'] = $id;
        $this->resp['type'] = $type;

        $watchlists = DB::getUserData('watchlist', $auth);
        $wl_def = DB::getUserData('wl_default', $auth);
        $wl_order = DB::getUserData('wl_order', $auth);

        if (!isset($watchlists[$id]) && !($type == 'add-wl' || $type == 'change-order')) {
            $this->error('Eine Merkliste unter der angegebenen ID konnte nicht gefunden werden!');
        }

        switch ($type) {
            case 'upd-name':
                if (is_null($content)) {
                    $this->error('Unvollständige Informationen');
                }
                if (isset($watchlists[$id]['name'])) {
                    $watchlists[$id]['name'] = $content;
                } else {
                    $this->error('Eine Merkliste unter der angegebenen ID konnte nicht gefunden werden!');
                }
                break;
            case 'add-wl':
                if (is_null($content)) {
                    $this->error('Unvollständige Informationen');
                }
                $i = max(array_keys($watchlists)) + 1;
                $nl = array('id' => $i, 'name' => $content);
                $watchlists[$i] = $nl;
                array_push($wl_order, strval($i));
                $this->resp['id'] = $i;
                break;
            case 'def':
                $wl_def = $id;
                break;
            case 'remove':
                unset($watchlists[$id]);
                $occ = array_search($id, $wl_order);
                $f = array_slice($wl_order, 0, $occ);
                $s = array_slice($wl_order, ($occ + 1), count($wl_order));
                $wl_order = array_merge($f, $s);

                $query = array('$and' => array(array('user' => $auth->getID()), array('watchlist' => $id)));
                $t = DB::getTitleList($query, NULL, $auth);

                if(!is_null($t['titlelist'])) {

                    $titles = $t['titlelist']->getTitles();

                    foreach ($titles as $title) {

                        if ($title->getUser() !== $auth->getID()) {
                            $this->error('Sie haben keine Berechtigung diesen Titel zu bearbeiten.');
                            return;
                        }

                        if (!$title->isInWatchlist()) {
                            $this->error('Dieser Titel befindet sich nicht in der Merkliste.');
                            return;
                        }

                        DB::upd(array('_id' => $title->getDirectly('_id')), array('$set' => array('watchlist' => null)), 'titles');

                    }
                }

                break;
            case 'change-order':
                if (!is_null($content)) {
                    $order = json_decode($content);
                    $wl_order = $order;
                } else {
                    $this->error('Unvollständige Informationen');
                }
                break;
        }


        DB::upd(array('_id' => $auth->getID()), array('$set' => array('watchlist' => $watchlists, 'wl_default' => $wl_def, 'wl_order' => $wl_order)), 'users');
        $this->resp['success'] = true;
    }
}

?>
