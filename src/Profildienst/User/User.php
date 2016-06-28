<?php

namespace Profildienst\User;

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

    /**
     * User constructor.
     * @param $name
     * @param $id
     * @param $settings
     * @param $defaults
     * @param $isil
     * @param $budgets
     */
    public function __construct($id, $name, $settings, $defaults, $isil, $budgets) {
        $this->name = $name;
        $this->id = $id;
        $this->settings = $settings;
        $this->defaults = $defaults;
        $this->isil = $isil;
        $this->budgets = $budgets;
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
     * @return mixed
     */
    public function getBudgets() {
        return $this->budgets;
    }

}