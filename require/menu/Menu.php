<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

/**
 * Menu class
 *
 * The class generate the menu
 *
 */
class Menu {
    private $_children;
    private $_priority;

    /**
     * Constructor
     *
     * @param array  $_children An array of MenuElem
     * @param number $_priority The priority of this element to sort
     */
    public function __construct($_children = array(), $_priority = 0) {
        $this->_children = $_children;
        $this->_priority = $_priority;
    }

    /**
     * Sort the Menu
     *
     * @return number
     */
    public function sortMenu() {
        foreach ($this->getChildren() as $menu) {
            if ($menu->hasChildren()) {
                $menu->sortMenu();
            }
        }

        uasort($this->_children, function($a, $b) {
            if ($a->getPriority() == $b->getPriority()) {
                return 0;
            }
            return ($a->getPriority() < $b->getPriority()) ? -1 : 1;
        });
    }

    /**
     * Get the MenuElem children
     *
     * @return Ambigous <array, MenuElem>
     */
    public function getChildren() {
        return $this->_children;
    }

    /**
     * Set the MenuElem children
     *
     * @param MenuElem $_children Children for this MenuElem
     *
     * @return MenuElem
     */
    public function setChildren(array $_children) {
        $this->_children = $_children;
        return $this;
    }

    /**
     * Get the MenuElem by an index
     *
     * @param string $index The index of the MenuElem
     *
     * @return array An array of the childrens
     */
    public function getElem($index) {
        return $this->_children[$index];
    }

    /**
     * Check if this MenuElem has childrens
     *
     * @return boolean
     */
    public function hasChildren() {
        return !empty($this->_children);
    }

    /**
     * Get the priority of this MenuElem
     *
     * @return number Priority
     */
    public function getPriority() {
        return $this->_priority;
    }

    /**
     * Set the priority of this MenuElem
     *
     * @param number $_priority The priority of this MenuElem
     *
     * @return MenuElem
     */
    public function setPriority($_priority) {
        $this->_priority = $_priority;
        return $this;
    }

    /**
     * Find MenuElem by its index
     *
     * @param string $elem_index The index we searching for
     *
     * @return <string, MenuElem> The MenuElem if function find it
     */
    public function findElemByIndex($elem_index) {
        foreach ($this->getChildren() as $index => $menu) {
            if ($index == $elem_index) {
                return $menu;
            } else {
                $res = $menu->findElemByIndex($elem_index);
                if ($res) {
                    return $res;
                }
            }
        }
    }

    /**
     * Delete a MenuElem
     *
     * @param string $elem_index The index of MenuElem to delete
     *
     * @return Menu
     */
    public function delElem($elem_index) {
        unset($this->_children[$elem_index]);
        return $this;
    }

    /**
     * Replace the MenuElem by this pass in parameter if it exists
     *
     * @param string   $elem_index The index of MenuElem to replace
     * @param MenuElem $menuElem   The new MenuElem
     *
     * @return Menu
     */
    public function replaceElem($elem_index, MenuElem $menuElem) {
        if (isset($this->_children[$elem_index])) {
            $this->_children[$elem_index] = $menuElem;
        }
        return $this;
    }

    /**
     * Add a MenuElem
     *
     * @param string   $index    Index name for the MenuElem we want to add
     * @param MenuElem $menuElem MenuEleme to add
     *
     * @return MenuElem Return the current MenuElem
     */
    public function addElem($index, MenuElem $menuElem) {
        $this->_children[$index] = $menuElem;
        return $this;
    }

}