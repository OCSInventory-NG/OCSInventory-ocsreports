<?php

/**
 * MenuElem class file
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
 * MenuElem class
 *
 * The class generate one menu element
 *
 * @category Cat
 * @package  MyPackage
 * @author   Cédric <cedric@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://factorfx.com
 *      
 */
class MenuElem extends Menu
{
    
    private $_label;

    private $_url;

    /**
     * The constructor
     * 
     * @param string $label     Label
     * @param string $url       Url
     * @param array  $_children Children
     * @param number $_priority The priority of the MenuElem
     */
    public function __construct($label, $url , array $_children = array(), $_priority = 0)
    {
        $this->_label = $label;
        $this->_url = $url;
        
        parent::__construct($_children, $_priority);
    }
    
    /**
     * Get the MenuElem label
     * 
     * @return string $this->label 
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * Set the MenuElem label
     * 
     * @param string $_label MenuElem label
     * 
     * @return MenuElem
     */
    public function setLabel($_label)
    {
        $this->_label = $_label;
        return $this;
    }

    /**
     * Get MenuElem url
     * 
     * @return string $this->_url
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * Set MenuElem url
     * 
     * @param string $_url MenuElem url
     * 
     * @return MenuElem
     */
    public function setUrl($_url)
    {
        $this->_url = $_url;
        return $this;
    }
}