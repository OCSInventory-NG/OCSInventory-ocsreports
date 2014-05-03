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
	private $profile;
	private $urls;
	
	public function __construct($profile, $urls) {
		parent::__construct();
		
		$this->profile = $profile;
		$this->urls = $urls;
	}

	
    /**
     * @see MenuRendererInterface::render()
     */
    public function render(Menu $menu)
    {
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
    public function renderElem(MenuElem $menu_elem, $level = 0)
    {
    	// Hide menu if the profile doesn't have the rights and the menu doesn't have children 
    	if ($this->profile and !$menu_elem->hasChildren() and !$this->profile->hasPage($menu_elem->getUrl())) {
    		return '';
    	}
    	
        $caret = '';
        $attr_li = $attr_a = array();
        
        if ($url = $this->urls->getUrl($menu_elem->getUrl())) {
	        $href = "?".PAG_INDEX."=".$url;
        } else {
        	$href = $menu_elem->getUrl();
        }
        
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
        
        $label = $this->translateLabel($menu_elem->getLabel());
        
        $html = "<li $attr_string_li>";
        $html .= "<a href='$href' $attr_string_a>".$label." $caret</a>";
        
        if ($menu_elem->hasChildren()) {
        	$html .= '<ul class="dropdown-menu">';
        	
        	$children_html = '';
        	foreach ($menu_elem->getChildren() as $child_elem) {
        		$children_html .= $this->renderElem($child_elem, $level + 1);
        	}

        	// Hide menu if the profile doesn't have the rights for any of its children
        	if ($this->profile and empty($children_html)) {
        		return '';
        	}

        	$html .= $children_html;
        	$html .= '</ul>';
        }
        
        $html .= '</li>';
   
        return $html;
    }
    
    private function translateLabel($label) {
		global $l;
		
		if (substr($label,0,2) == 'g(')
			$label= ucfirst($l->g(substr(substr($label,2),0,-1)));
		
		return strip_tags_array($label);
    }
}
