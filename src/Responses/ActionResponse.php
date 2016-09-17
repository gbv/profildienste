<?php

namespace Responses;


class ActionResponse extends APIResponse{

    public function __construct($affected, $newState, $additionalInfo = null){

        $this->data['affected'] = $affected;
        $this->data['newState'] = $newState;

        if (!is_null($additionalInfo)){
            $this->data['additionalInfo'] = $additionalInfo;
        }
    }

    public function getData() {
        return $this->data;
    }
}