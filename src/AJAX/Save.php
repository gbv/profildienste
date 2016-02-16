<?php

namespace AJAX;

use Profildienst\DB;

class Save extends AJAXResponse{

    public function __construct($id, $type, $val, $auth){

        $this->resp['type'] = NULL;
        $this->resp['id'] = NULL;

        if (empty($id) || empty($type) || ($type !== 'lieft' && $type !== 'budget' && $type !== 'ssgnr' && $type !== 'selcode' && $type !== 'comment')) {
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