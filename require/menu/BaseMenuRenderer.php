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
 * The class generate and render the boostrap menu
 *
 * @category Cat
 * @package  MyPackage
 * @author   Cédric <cedric@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://factorfx.com
 *
 */
abstract class BaseMenuRenderer implements MenuRendererInterface
{
    private $_active_link;
    
    private $_parent_elem_clickable;
    
    public function __construct()
    {
    	$this->_active_link = null;
    	$this->_parent_elem_clickable = false;
    }

    /**
     * @see MenuRendererInterface::getActiveLink()
     */
    public function getActiveLink()
    {
        return $this->_active_link;
    }

    /**
     * @see MenuRendererInterface::setActiveLink()
     */
    public function setActiveLink($_active_link)
    {
        $this->_active_link = $_active_link;
    }
    
    /**
     * @see MenuRendererInterface::isParentElemClickable()
     */
    public function isParentElemClickable()
    {
        return $this->_parent_elem_clickable;
    }

    /**
     * @see MenuRendererInterface::setParentElemClickable()
     */
    public function setParentElemClickable($_parent_elem_clickable)
    {
        $this->_parent_elem_clickable = $_parent_elem_clickable;
    }

    /**
     * Convert array tag attributes to string
     * 
     * @param array $attr Array of attribute
     * 
     * @return string Convertion of attribute array in string
     */
    protected function attrToString(array $attr)
    {
        $html = '';
        foreach ($attr as $name => $value) {
            if (is_array($value)) {
                $val = implode(' ', $value);
            } else {
                $val = $value;
            }
            $html .= "$name='" . $val . "' ";
        }
        return $html;
    }
}
