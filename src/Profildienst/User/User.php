<?php

namespace Profildienst\User;

use Profildienst\Library\Library;

/**
 * Represents the user in the whole application.
 *
 * Class User
 * @package Profildienst
 */
class User {

    private $name;
    private $id;
    private $settings;
    private $defaults;
    private $isil;
    private $budgets;
    private $suppliers;

    private $libraryController;

    /**
     * User constructor.
     * @param $id
     * @param $name
     * @param $settings
     * @param $defaults
     * @param $isil
     * @param $budgets
     * @param $suppliers
     * @param $libraryController
     */
    public function __construct($id, $name, $settings, $defaults, $isil, $budgets, $suppliers, $libraryController) {
        $this->name = $name;
        $this->id = $id;
        $this->settings = $settings;
        $this->defaults = $defaults;
        $this->isil = $isil;

        $this->budgets = [];
        foreach ($budgets as $budget) {
            $this->budgets[$budget['value']] = [
                'name' => $budget['name'],
                'value' => $budget['value']
            ];
        }

        $this->suppliers = [];
        foreach ($suppliers as $supplier) {
            $this->suppliers[$supplier['value']] = [
                'name' => $supplier['name'],
                'value' => $supplier['value']
            ];
        }

        $this->libraryController = $libraryController;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }


    /**
     * @return mixed
     */
    public function getSettings() {
        return $this->settings;
    }

    /**
     * @param mixed $settings
     */
    public function setSettings($settings) {
        $this->settings = $settings;
    }


    public function getDefaults() {
        return $this->defaults;
    }

    /**
     * @return mixed
     */
    public function getIsil() {
        return $this->isil;
    }

    /**
     * @return Library
     */
    public function getLibrary() {
        return $this->libraryController->getLibrary($this);
    }

    /**
     * @return mixed
     */
    public function getBudgets() {
        return array_values($this->budgets);
    }

    public function getBudget($value) {
        return $this->budgets[$value] ?? ['name' => 'NOTFOUND', 'value' => $value];
    }

    public function getSuppliers() {
        return array_values($this->suppliers);
    }

    public function getSupplier($value) {
        return $this->suppliers[$value] ?? ['name' => 'NOTFOUND', 'value' => $value];
    }

    public function setOrderSetting($order) {
        $this->settings['order'] = $order;
    }

    public function setSortSetting($sortby) {
        $this->settings['sortby'] = $sortby;
    }

}