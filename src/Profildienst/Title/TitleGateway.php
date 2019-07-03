<?php

namespace Profildienst\Title;


interface TitleGateway {

    public function getTitlesById(array $titleIds);
    public function getTitlesByStatus($status, $page, $dateSorted, $offset);
    public function getAllTitlesByStatus($status);
    public function getTitleCountWithStatus($status);
    public function deleteTitle($id);
    public function deleteTitlesWithStatus($status);
    public function updateTitlesWithStatus($oldStatus, $newStatus);
    public function updateTitlesWithIds(array $ids, $newStatus);
    public function updateTitleUser($id, $newUser);
    public function updateTitlesOrderInformation(array $ids, $orderInformation);
    /* TODO: Currently unsupported
    public function updateViewOrderInformation(array $ids, $orderInformation); */
}