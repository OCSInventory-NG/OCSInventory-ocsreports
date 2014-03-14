<?php

/**
 * Menu class file
 *
 * PHP version 5
 *
 * @category Cat
 * @package  MyPackage
 * @author   Cédric <cedric@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://factorfx.com
 *
 */

/**
 * Menu class
 * 
 * The class generate the menu
 * 
 * @category Cat
 * @package  MyPackage
 * @author   Cédric <cedric@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://factorfx.com
 *
 */
class Menu
{

    private $_children;
    
    private $_priority;

    /**
     * Constructor
     * 
     * @param array  $_children An array of MenuElem
     * @param number $_priority The priority of this elemment to sort
     */
    public function __construct(array $_children = array(), $_priority = 0)
    {
        $this->_children = $_children;
        $this->_priority = $_priority; 
    } 
    
    /**
     * Sort the Menu
     * 
     * @return number
     */
    public function sortMenu() 
    {
        foreach ($this->getChildren() as $index => $menu) {
            if ($menu->hasChildren()) {
                $menu->sortMenu();
            }
        }
        
        uasort(
            $this->_children,
      		function($a, $b) {
       			if ($a->getPriority() == $b->getPriority()) {
       				return 0;
       			}
       			return ($a->getPriority() < $b->getPriority()) ? -1 : 1;
       		}
       	);
    }
    
    /**
     * Get the MenuElem children
     * 
     * @return Ambigous <array, MenuElem>
     */
    public function getChildren()
    {
        return $this->_children;
    }
    
    /**
     * Set the MenuElem children
     *
     * @param MenuElem $_children Children for this MenuElem
     *
     * @return MenuElem
     */
    public function setChildren(array $_children)
    {
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
    public function getElem($index) 
    {
        return $this->_children[$index];
    }

    /**
     * Check if this MenuElem has childrens
     *
     * @return boolean
     */
    public function hasChildren()
    {
        return ! empty($this->_children);
    }
    

    /**
     * Get the priority of this MenuElem
     *
     * @return number Priority
     */
    public function getPriority()
    {
        return $this->_priority;
    }
    
    /**
     * Set the priority of this MenuElem
     *
     * @param number $_priority The priority of this MenuElem
     *
     * @return MenuElem
     */
    public function setPriority($_priority)
    {
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
    public function findElemByIndex($elem_index)
    {
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
    public function delElem($elem_index)
    {
        unset($this->_children[$elem_index]);
        return $this;
    }
    
    /**
     * Replace the MenuELem by this pass in paramter if exist
     * 
     * @param string   $elem_index The index of MenuElem to replace
     * @param MenuElem $menuElem   The new MenuElem
     * 
     * @return Menu
     */
    public function replaceElem($elem_index, MenuElem $menuElem) 
    {
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
    public function addElem($index, MenuElem $menuElem)
    {        
        $this->setChildren(
            array_merge(
                $this->getChildren(),
                array($index => $menuElem)
            )
        );
        return $this;
    }  
}