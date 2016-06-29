<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 04.06.16
 * Time: 14:02
 */

namespace Profildienst\Title;


interface TitleGateway {

    public function getTitleById($titleId);
    public function getTitlesByStatus($status, $page);
    public function getAllTitlesByStatus($status);
    public function getTitleCountWithStatus($status);
    public function deleteTitle($id);

}