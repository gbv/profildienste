<?php
/**
 * Saves comments and the additional information Lieferant, Budget, SSG-Nr and Selektionscode to the database. A save is
 * triggered every time the user changes the field contents.
 */

/**
 * @package AJAX
 */
namespace AJAX;

use Profildienst\DB;

/**
 * This class is responsible for storing user-changeable information such as the title comment persistently in the
 * database.
 *
 * Class Save
 * @package AJAX
 */
class Save extends AJAXResponse{

    /**
     * Save constructor.
     *
     * @param $id title ID of the respective title
     * @param $type type of information to save (lieft, budget, ssgnr, selcode or comment)
     * @param $val value to store
     * @param $auth the auth token
     */
    public function __construct($id, $type, $val, $auth){

        $this->resp['type'] = NULL;
        $this->resp['id'] = NULL;

        if (empty($id)|| empty($type) || ($type !== 'lieft' && $type !== 'budget' && $type !== 'ssgnr' && $type !== 'selcode' && $type !== 'comment')) {
            $this->error('Unvollständige Daten');
            return;
        }

        $this->resp['id'] = $id;
        $this->resp['type'] = $type;

        $title = DB::getTitleByID($id);
        if(is_null($title)){
            $this->error('Es existiert kein Titel mit dieser ID.');
            return;
        }

        if($title->getUser() !== $auth->getID()){
            $this->error('Sie haben keine Berechtigung diesen Titel zu bearbeiten.');
            return;
        }

        if($val === ''){
            $val = null;
        }

        DB::upd(array('_id' => $id), array('$set' => array($type => $val)), 'titles');
        $this->resp['success'] = true;
    }

}