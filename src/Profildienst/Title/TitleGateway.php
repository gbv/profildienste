<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 04.06.16
 * Time: 14:02
 */

namespace Profildienst\Title;


interface TitleGateway {

    public function getTitleById($userId, $titleId);
    public function getTitlesByStatus($userId, $status, $limit, $skip);
    public function getTitleCountWithStatus($userId, $status);
    public function deleteTitle($userId, $id);

}