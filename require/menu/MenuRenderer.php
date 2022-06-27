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
 * The class generate and render the boostrap menu
 *
 */
class MenuRenderer {
    private $active_link;
    private $parent_elem_clickable;
    private $extension_hooks;

    public function __construct() {
        $this->active_link = null;
        $this->parent_elem_clickable = false;
        $extMgr = new ExtensionManager();
        $this->extension_hooks = new ExtensionHook($extMgr->installedExtensionsList);
    }

    public function render(Menu $menu) {
        $html = '<ul class="nav navbar-nav">';

        foreach ($menu->getChildren() as $menu_elem) {
            if($this->extension_hooks->haveExtSubMenu($menu_elem->getUrl())){
                foreach ($this->extension_hooks->activatedExt as $extName){
                    if(isset($this->extension_hooks->subMenuExtensionsHooks[$menu_elem->getUrl()][$extName])){
                        for ($index = 0; $index < count($this->extension_hooks->subMenuExtensionsHooks[$menu_elem->getUrl()][$extName]); $index++) {
                            $extMenuElem = $this->extension_hooks->generateMenuRenderer(
                                $this->extension_hooks->subMenuExtensionsHooks[$menu_elem->getUrl()][$extName][$index],
                                true
                            );
                            $menu_elem->addElem($extMenuElem->getUrl(), $extMenuElem);
                        }
                    }
                }

            }
            $html .= $this->renderElem($menu_elem);
        }

        // If extension generate new menu / sub menus
        if($this->extension_hooks->needHookTrigger(ExtensionHook::MENU_HOOK)){
            foreach ($this->extension_hooks->menuExtensionsHooks as $menus_array) {
                for ($index = 0; $index < count($menus_array); $index++) {
                    $extension_menus_elem = $this->extension_hooks->generateMenuRenderer($menus_array[$index], false);
                    $html .= $this->renderElem($extension_menus_elem, 0, true);
                }
            }
        }

        return $html . '</ul>';
    }

    /**
     * Render a MenuElem with html tag
     *
     * @param MenuElem $menu_elem The MenuElem to convert
     * @param integer  $level The level
     * @param boolean  $extFrom Render from extension ?
     *
     * @return string The html tag code
     */
    public function renderElem(MenuElem $menu_elem, $level = 0, $extFrom = false) {

        // Add for extension manager
        if($extFrom){
            $this->addValueToProfileAndUrls($menu_elem->getUrl(), $this->extension_hooks->extDeclaredMenu[$menu_elem->getUrl()]);
        }

        // Hook to check if the elem must be displayed or not
        if (!$this->canSeeElem($menu_elem)) {
            return '';
        }

        if ($this->isParentElemClickable() || !$menu_elem->hasChildren()) {
            $href = $this->getUrl($menu_elem);
        } else {
            $href = "#";
        }

        $label = $this->getLabel($menu_elem);
        $attrs = $this->buildAttrs($menu_elem);

        $html = "<li " . $attrs['li'] . ">";
        $html .= "<a href='$href' " . $attrs['a'] . ">$label</a>";

        if ($menu_elem->hasChildren()) {
            $children_html = '';
            foreach ($menu_elem->getChildren() as $child_elem) {
                if($this->extension_hooks->isMenuFromExt($child_elem->getUrl())){
                    $children_html .= $this->renderElem($child_elem, $level + 1, true);
                }else{
                    $children_html .= $this->renderElem($child_elem, $level + 1);
                }
            }

            // Hide menu elem if none of its children could be displayed
            if (empty($children_html)) {
                return '';
            }

            $html .= '<ul class="dropdown-menu">';
            $html .= $children_html;
            $html .= '</ul>';
        }

        return $html . '</li>';
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
        //@TODO : buggy code
        return true;
    }

    protected function addValueToProfileAndUrls($value, $extMapName){
        //@TODO : management of multiple profile (hook.xml improvement)
        return true;
    }

    protected function getUrl(MenuElem $menu_elem) {
        return $menu_elem->getUrl();
    }

    protected function getLabel(MenuElem $menu_elem) {
        $label = $this->translateLabel($menu_elem->getLabel());

        if ($menu_elem->hasChildren() && isset($level) && $level == 0) {
            $label .= ' <b class="caret"></b>';
        }

        return $label;
    }

    protected function buildAttrs(MenuElem $menu_elem) {
        $attr_li = $attr_a = array();

        if ($menu_elem->hasChildren()) {
            //@TODO : buggy code
            if (isset($level) && $level > 0) {
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

        if ($this->getActiveLink() && $this->getActiveLink() == $menu_elem->getUrl()) {
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

        if (substr($label, 0, 2) == 'g(') {
            $label = ucfirst($l->g(substr(substr($label, 2), 0, -1)));
        }

        return strip_tags_array($label);
    }

}
