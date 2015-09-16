<?php
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
class MenuRenderer {
    private $active_link;
    private $parent_elem_clickable;
	
	public function __construct() {
    	$this->active_link = null;
    	$this->parent_elem_clickable = false;
    }
    
    public function render(Menu $menu) {
        $html = '<ul class="nav navbar-nav">';
        
        foreach ($menu->getChildren() as $menu_elem) {
            $html .= $this->renderElem($menu_elem);
        }
        
        $html .= '</ul>';
        
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
    public function renderElem(MenuElem $menu_elem, $level = 0) {
    	// Hook to check if the elem must be displayed or not
    	if (!$this->canSeeElem($menu_elem)) {
    		return '';
    	}
        
    	if ($this->isParentElemClickable() or !$menu_elem->hasChildren()) {
	        $href = $this->getUrl($menu_elem);
    	} else {
    		$href = "#";
    	}
    	
        $label = $this->getLabel($menu_elem);
        $attrs = $this->buildAttrs($menu_elem);
        
        $html = "<li ".$attrs['li'].">";
        $html .= "<a href='$href' ".$attrs['a'].">$label $caret</a>";
        
        if ($menu_elem->hasChildren()) {
        	$children_html = '';
        	foreach ($menu_elem->getChildren() as $child_elem) {
        		$children_html .= $this->renderElem($child_elem, $level + 1);
        	}

        	// Hide menu elem if none of its children could be displayed
        	if (empty($children_html)) {
        		return '';
        	}

        	$html .= '<ul class="dropdown-menu">';
        	$html .= $children_html;
        	$html .= '</ul>';
        }
        
        $html .= '</li>';
   
        return $html;
    }

    public function getActiveLink() {
        return $this->active_link;
    }

    public function setActiveLink($active_link) {
        $this->active_link = $active_link;
    }
    
    public function isParentElemClickable() {
        return $this->parent_elem_clickable;
    }

    public function setParentElemClickable($parent_elem_clickable) {
        $this->parent_elem_clickable = $parent_elem_clickable;
    }
    
    protected function canSeeElem(MenuElem $menu_elem) {
    	return true;
    }
    
    protected function getUrl(MenuElem $menu_elem) {
    	return $menu_elem->getUrl();
    }
    
    protected function getLabel(MenuElem $menu_elem) {
    	$label = $this->translateLabel($menu_elem->getLabel());
    	
    	if ($menu_elem->hasChildren() and $level == 0) {
    		$label .= ' <b class="caret"></b>';
    	}
    	
    	return $label;
    }
    
    protected function buildAttrs(MenuElem $menu_elem) {
        $attr_li = $attr_a = array();
        
        if ($menu_elem->hasChildren()) {
            if ($level > 0) {
                $attr_li['class'][] = 'dropdown-submenu';
                
                if (!$this->isParentElemClickable()) {
                    $attr_a['class'][] = 'dropdown-toggle';
                } else {
                    $attr_a['class'][] = 'dropdown-submenu-toggle';
                }
            } else {
                $attr_li['class'][] = 'dropdown';
            }
            $attr_a['data-toggle'][] = 'dropdown';  
        }

        if ($this->getActiveLink() and $this->getActiveLink() == $menu_elem->getUrl()) {
            $attr_li['class'][] = 'active';
        } else if ($menu_elem->getLabel() == 'divider' && $menu_elem->getUrl() == 'divider') {
            $attr_li['class'] = 'divider';
        }
        
        $attr_string_li = $this->attrToString($attr_li);
        $attr_string_a = $this->attrToString($attr_a);
        
        return array(
        	'li' => $attr_string_li,
        	'a' => $attr_string_a
        );
    }

    /**
     * Convert array tag attributes to string
     * 
     * @param array $attr Array of attribute
     * 
     * @return string Conversion of attribute array to string
     */
    protected function attrToString(array $attr) {
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
    
    protected function translateLabel($label) {
		global $l;
		
		if (substr($label,0,2) == 'g(')
			$label= ucfirst($l->g(substr(substr($label,2),0,-1)));
		
		return strip_tags_array($label);
    }
}
