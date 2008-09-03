<?php
/**
 * Class for rendering maemo calendar panels shelf accordion leaf
 *
 * @package org.maemo.calendarpanel 
 * @author Jerry Jalava, http://protoblogr.net
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link http://www.microformats.org/wiki/hcalendar hCalendar microformat
 */

/**
 * @package org.maemo.calendarpanel 
 */
class org_maemo_calendarpanel_shelf_leaf extends org_maemo_calendarpanel_leaf
{   
    var $_shelf_items = array();
    
    /**
     * Initializes the class
     *
     */
    function org_maemo_calendarpanel_shelf_leaf()
    {
        parent::__construct();
        
        $this->name = 'shelf';
        $this->title = $this->_l10n->get($this->name);

        $this->_shelf_items = org_maemo_calendarpanel_shelf_leaf::fetch_shelf_items();
    }
    
    function generate_content()
    {
        $html = "";
        
        $html .= $this->_render_menu();
        $html .= $this->_render_shelf_contents();
        
        return $html;
    }
    
    function regenerate_list($shelf_items=false)
    {
        $items = org_maemo_calendarpanel_shelf_leaf::fetch_shelf_items();
        
        if (is_array($shelf_items))
        {
            $items = $shelf_items;
        }
        
        $return = "jQuery('#shelf-item-list').html('');\n";
        foreach ($items as $k => $item)
        {
            $return .= org_maemo_calendarpanel_shelf_leaf::render_item(&$item);
        }
        $return .= "jQuery('#shelf-item-list').Highlight(800, '#ff9');\n";
        
        return $return;
    }

    function _render_menu()
    {
        $html = "";
        
        $html .= "<div class=\"accordion-leaf-menu\">\n";
        $html .= "   <ul class=\"leaf-menu\">\n";

        $html .= "      <li><a href=\"#\" onclick=\"empty_shelf(); return false;\" title=\"Empty shelf\"><img src=\"" . MIDCOM_STATIC_URL . "/org.maemo.calendarpanel/images/icons/edit-clear.png\" alt=\"Empty\" /></a></a></li>\n";
        //$html .= "      <li><a href=\"#\" onclick=\"delete_shelf_contents(); return false;\" title=\"Delete shelf contents\"><img src=\"" . MIDCOM_STATIC_URL . "/org.maemo.calendarpanel/images/icons/trash.png\" alt=\"Trash\" /></a></li>\n";
        
        $html .= "   </ul>\n";
        $html .= "</div>\n";
        
        return $html;
    }
    
    function _render_shelf_contents()
    {
        $html = "\n";

        $html .= "<script type=\"text/javascript\">\n";
        
        $html .= "var shelf_item_tpl = function() {
            return [
                'li', { id: 'shelf-list-item-'+this.guid, onclick: this.action, style: 'background-color: #'+this.color+';' }, [
                    'span', { className: 'title' }, this.title
                ]
            ];
        };
        \n";
        $html .= "</script>\n";
        
        $html .= "<ul id=\"shelf-item-list\">\n";
        $html .= "</ul>\n";
        
        $html .= "<script type=\"text/javascript\">\n";
        $html .= "jQuery('#shelf-item-list').html('');\n";
        foreach ($this->_shelf_items as $k => $item)
        {
            $html .= org_maemo_calendarpanel_shelf_leaf::render_item(&$item);
        }
        $html .= "modify_foreground_color('#shelf-item-list li');\n";
        $html .= "</script>\n";        
        
        return $html;
    }
    
    function render_item(&$item)
    {
        $js = '';
        
        $title = urldecode($item->data->title);
        $item_data = "{ guid: '{$item->guid}',
                        title: \"{$title}\",
                        color: '{$item->data->color}',                        
                        action: function(){activate_shelf_item('{$item->guid}');} }";
        $js .= "jQuery('#shelf-item-list').tplAppend({$item_data}, shelf_item_tpl);\n";
        
        return $js;
    }
    
    function fetch_shelf_items()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $items = array();
        
        $session =& new midcom_service_session('org.maemo.calendarpanel');
        if ($session->exists('shelf_contents'))
        {
            $items = json_decode($session->get('shelf_contents'));
        }
        else
        {
            $session->set('shelf_contents',json_encode($items));
        }
        unset($session);
        
        debug_print_r('items',$items);
        
        debug_pop();        
        return $items;
    }
}

?>