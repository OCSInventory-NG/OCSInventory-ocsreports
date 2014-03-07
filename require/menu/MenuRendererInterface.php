<?php
/**
 * Menu interface file
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
 * Menu interface
 *
 * @category Cat
 * @package  MyPackage
 * @author   Cédric <cedric@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://factorfx.com
 *
 */
interface MenuRendererInterface
{
    /**
     * Render the Menu to html
     * 
     * @param Menu $menu The Menu to render
     * 
     * @return string The generated html
     */
    public function render(Menu $menu);
    
    /**
     * Render a MenuElem to html
     *
     * @param MenuElem $menu_elem The MenuElem to render
     *
     * @return string The generated html
     */
    public function renderElem(MenuElem $menu_elem);
    
    /**
     * Get the active link
     * 
     * @return string The active link
     */
    public function getActiveLink();
    
    /**
     * Set the active link
     * 
     * @param string $active_link
     */
    public function setActiveLink($active_link);
    
    /**
     * Get if an element containing other elements should be clickable
     * 
     * @return boolean
     */
    public function isParentElemClickable();
    
    /**
     * Set to true if an element containing other elements should be clickable, false otherwise
     * 
     * @param boolean $parent_elem_clickable
     */
    public function setParentElemClickable($parent_elem_clickable);
}