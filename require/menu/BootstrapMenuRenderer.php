<?php
/**
 * Boostrap Menu class file
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
 * Boostrap Menu class
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

class BootstrapMenuRenderer extends BaseMenuRenderer
{
	private $urls;
	
	public function __construct($urls) {
		parent::__construct();
		
		$this->urls = $urls;
	}

	
    /**
     * @see MenuRendererInterface::render()
     */
    public function render(Menu $menu)
    {
        $html = '<div class="navbar navbar-default"><ul class="nav navbar-nav">';
        
        foreach ($menu->getChildren() as $menu_elem) {
            $html .= $this->renderElem($menu_elem);
        }
        
        $html .= '</ul></div>';
        
        return $html;
    }
    
    /**
     * Render a MenuElem with html tag
     * 
     * @param MenuElem $menu_elem The MenuElem to convert
     * @param integer  $level The level
     * 
     * @return string The html tag code
     */
    public function renderElem(MenuElem $menu_elem, $level = 0)
    {
        $caret = '';
        $attr_li = $attr_a = array();
        $href = "?".PAG_INDEX."=".$this->urls->getUrl($menu_elem->getUrl());
        
        if ($menu_elem->hasChildren()) {
            if ($level > 0) {
                $attr_li['class'][] = 'dropdown-submenu';
                if (!$this->isParentElemClickable()) {
                    $href = "#";
                    $attr_a['class'][] = 'dropdown-toggle';
                } else {
                    $attr_a['class'][] = 'dropdown-submenu-toggle';
                }
            } else {
                $attr_li['class'][] = 'dropdown';
                $caret = "<b class='caret'></b>";
            }
            $attr_a['data-toggle'][] = 'dropdown';  
        }

        if ($this->getActiveLink() == $href) {
            $attr_li['class'][] = 'active';
        } elseif ($menu_elem->getLabel() == 'divider' && $menu_elem->getUrl() == 'divider') {
            $attr_li['class'] = 'divider';
        }
        
        $attr_string_li = $this->attrToString($attr_li);
        $attr_string_a = $this->attrToString($attr_a);
        
        $label = $menu_elem->getLabel();
        if (preg_match('/^g\(\d+\)$/', $label)) {
        	$label = find_lbl($label);
        }
        
        $html = "<li $attr_string_li>";
        $html .= "<a href='$href' $attr_string_a>".$label." $caret</a>";

        if ($menu_elem->hasChildren()) {
        	$html .= '<ul class="dropdown-menu">';
        	
        	foreach ($menu_elem->getChildren() as $child_elem) {
        		$html .= $this->renderElem($child_elem, $level + 1);
        	}

        	$html .= '</ul>';
        }
        
        $html .= '</li>';
   
        return $html;
    }
}