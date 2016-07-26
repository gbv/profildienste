<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 31.03.16
 * Time: 18:14
 */

namespace Profildienst\Search;

class QueryBuilder {

    private $n = [];

    public function searchTitleField($value) {
        $this->n[] = ['$or' => [
            ['021A.a' => $value],
            ['021B.a' => $value],
            ['021A.d' => $value],
            ['021B.d' => $value],
            ['021A.l' => $value],
            ['021B.l' => $value]
        ]];
        return $this;
    }

    public function searchPersonField($value) {
        $this->n[] = ['$or' => [
            ['028C.d' => $value],
            ['028C.a' => $value],
            ['021A.h' => $value],
            ['021B.h' => $value]
        ]];
        return $this;
    }

    public function searchVerlagField($value) {
        $this->n[] = ['033A.n' => $value];
        return $this;
    }

    public function searchDNBNrField($value) {
        $this->n[] = ['006L.0' => $value];
        return $this;
    }

    public function searchDNBSachgruppeField($value) {
        $this->n[] = ['045G.a' => $value];
        return $this;
    }

    public function searchISBNField($value) {
        $this->n[] = ['$or' => [
            ['004A.0' => $value],
            ['004A.A' => $value]
        ]];
        return $this;
    }

    public function searchErscheinungsjahrField($value) {
        $this->n[] = ['011@.a' => $value];
        return $this;
    }

    public function searchWVNField($value) {
        $this->n[] = ['status' => $value];
        return $this;
    }

    public function searchMAKField($value) {
        $this->n[] = ['002@' => $value];
        return $this;
    }

    public function restrictToUser($id) {
        $this->n[] = ['user' => $id];
        return $this;
    }

    public function searchTitlesWithStatus($status) {
        $this->n[] = ['status' => $status];
        return $this;
    }

    public function joinWithAnd() {
        $this->n = [['$and' => $this->n]];
        return $this;
    }

    public function joinWithOr() {
        $this->n = [['$or' => $this->n]];
        return $this;
    }

    public function insertRaw($query) {
        $this->n[] = $query;
    }

    public function getQuery() {

        if (count($this->n) > 1) {
            throw new InvalidQueryException('The query is not in its final form');
        }

        return $this->n[0];
    }


}