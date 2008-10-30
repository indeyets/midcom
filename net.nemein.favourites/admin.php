<?php
/**
 * @package net.nemein.favourites
 */

/**
 * Forum AIS interface class.
 *
 * @package net.nemein.favourites
 */
class net_nemein_favourites_admin
{
    var $_content_topic = null;

    function __construct($topic, $config)
    {
    }

    function _on_initialize()
    {
        $this->_content_topic = $this->_request_data['content_topic'];
        return true;
    }
    
    function get_data($objectType, $guid, $link_for_anonymous = true)
    {
        static $cache = array();
        
        if (isset($cache[$guid]))
        {
            return $cache[$guid];
        }
        
        $cache[$guid] = array
        (
            'favs'     => 0,
            'buries'   => 0,
            'has_faved' => false,
            'can_fav'  => true,
            'has_buried' => false,
            'can_bury' => true,
        );

        if (   !$_MIDCOM->auth->user
            && !$link_for_anonymous)
        {
            $cache[$guid]['can_fav'] = false;
            $cache[$guid]['can_bury'] = false;
        }

        $mc = net_nemein_favourites_favourite::new_collector('objectGuid', $guid);
        $mc->set_key_property('guid');
        $mc->add_value_property('bury');
        $mc->add_value_property('metadata.creator');
        $mc->execute();
        $favourites = $mc->list_keys();

        if (empty($favourites))
        {
            return $cache[$guid];
        }

        foreach ($favourites as $favourites_guid => $value)
        {
            $bury = $mc->get_subkey($favourites_guid, 'bury');
            if ($bury)
            {
                $cache[$guid]['buries']++;
            }
            else
            {
                $cache[$guid]['favs']++;
            }

            if ($_MIDCOM->auth->user)
            {
                $creator = $mc->get_subkey($favourites_guid, 'creator');
                if ($creator != $_MIDCOM->auth->user->guid)
                {
                    continue;
                }
                
                if ($bury)
                {
                    $cache[$guid]['can_bury'] = false;
                    $cache[$guid]['has_buried'] = true;
                }
                else
                {
                    $cache[$guid]['can_fav'] = false;
                    $cache[$guid]['has_faved'] = true;
                }
            }
        }
        
        return $cache[$guid];
    }

    function get_json_data($objectType, $guid, $url = '', $link_for_anonymous = true)
    {
        if (empty($url))
        {
            $node = midcom_helper_find_node_by_component('net.nemein.favourites');
            if (!empty($node))
            {
                $url = $node[MIDCOM_NAV_FULLURL];
            }
        }
        if (empty($url))
        {
            return '';
        }
        
        $data = net_nemein_favourites_admin::get_data($objectType, $guid, $link_for_anonymous);
        
        if ($data['can_fav'])
        {
            $data['fav_url'] = "{$url}json/fav/{$objectType}/{$guid}/";
        }
        
        if ($data['can_bury'])
        {
            $data['bury_url'] = "{$url}json/bury/{$objectType}/{$guid}/";
        }

        echo json_encode($data);
    }

    function get_add_link($objectType, $guid, $url = '', $link_for_anonymous = true)
    {
        if (   empty($objectType)
            || empty($guid))
        {
            return false;
        }

        if (empty($url))
        {
            $node = midcom_helper_find_node_by_component('net.nemein.favourites');
            if (!empty($node))
            {
                $url = $node[MIDCOM_NAV_FULLURL];
            }
        }

        if (empty($url))
        {
            return false;
        }

        $midcom_i18n =& $_MIDCOM->get_service('i18n');
        $l10n =& $midcom_i18n->get_l10n('net.nemein.favourites');

        $data = net_nemein_favourites_admin::get_data($objectType, $guid, $link_for_anonymous);

        $return_url = rawurlencode($_SERVER['REQUEST_URI']);
        $bury = array
        (
            'icon' => MIDCOM_STATIC_URL . '/net.nemein.favourites/not-buried.png',
            'title' => $l10n->get('bury'),
            'url' => "{$url}bury/{$objectType}/{$guid}/?return={$return_url}"
        );
        $fav = array
        (
            'icon' => MIDCOM_STATIC_URL . '/net.nemein.favourites/not-favorite.png',
            'title' => $l10n->get('add to favourites'),
            'url' => "{$url}create/{$objectType}/{$guid}/?return={$return_url}"
        );
        
        if (!$data['can_fav'])
        {
            $fav['url'] = null;
        }
        
        if (!$data['can_bury'])
        {
            $bury['url'] = null;
        }        

        if ($data['has_faved'])
        {
            // User has already favourited the item
            $fav['icon'] = MIDCOM_STATIC_URL . '/net.nemein.favourites/favorite.png';
            $fav['title'] = $l10n->get('favourite');
            $fav['url'] = null;
            $bury['url'] = null;
        }
        
        if ($data['has_buried'])
        {
            // User has already buried the item
            $bury['icon'] = MIDCOM_STATIC_URL . '/net.nemein.favourites/bury.png';
            $bury['title'] = $l10n->get('buried');
            $bury['url'] = null;
            $fav['url'] = null;
        }
        
        $fav_button  = "<span class=\"net_nemein_favourites\">";
        if ($fav['url'])
        {
            $fav_button .= "{$data['favs']} <a href=\"{$fav['url']}\" class=\"net_nemein_favourites_create\"><img src=\"{$fav['icon']}\" style=\"border: none;\" alt=\"{$fav['title']}\" title=\"{$fav['title']}\" /></a>";
        }
        else
        {
            $fav_button .= "{$data['favs']} <img src=\"{$fav['icon']}\" style=\"border: none;\" alt=\"{$fav['title']}\" title=\"{$fav['title']}\" />";
        }
        if ($bury['url'])
        {
            $fav_button .= "{$data['buries']} <a href=\"{$bury['url']}\" class=\"net_nemein_favourites_create\"><img src=\"{$bury['icon']}\" style=\"border: none;\" alt=\"{$bury['title']}\" title=\"{$bury['title']}\" /></a>";
        }
        else
        {
            $fav_button .= "{$data['buries']} <img src=\"{$bury['icon']}\" style=\"border: none;\" alt=\"{$bury['title']}\" title=\"{$bury['title']}\" />";
        }
        $fav_button .= "</span>";
        return $fav_button;
    }

    function render_add_link($objectType, $guid, $url = '', $link_for_anonymous = true)
    {
        echo net_nemein_favourites_admin::get_add_link($objectType, $guid, $url, $link_for_anonymous);
    }
    
    function render_ajax_add_link($objectType, $guid, $url = '', $link_for_anonymous = true, $force_url_prefix = null)
    {
        if (   empty($objectType)
            || empty($guid))
        {
            return false;
        }
        
        $url_prefix = '';
        if (! is_null($force_url_prefix))
        {
            $url_prefix = $force_url_prefix;
        }

        if (empty($url))
        {
            $node = midcom_helper_find_node_by_component('net.nemein.favourites');
            if (!empty($node))
            {
                $url = $node[MIDCOM_NAV_FULLURL];
            }
        }

        if (empty($url))
        {
            return false;
        }

        $midcom_i18n =& $_MIDCOM->get_service('i18n');
        $l10n =& $midcom_i18n->get_l10n('net.nemein.favourites');

        $data = net_nemein_favourites_admin::get_data($objectType, $guid, $link_for_anonymous);

        $bury = array
        (
            'icon' => MIDCOM_STATIC_URL . '/net.nemein.favourites/not-buried.png',
            'title' => $l10n->get('bury'),
            'url' => "{$url_prefix}?net_nemein_favourites_execute=bury&net_nemein_favourites_execute_for={$guid}&net_nemein_favourites_url={$url}/json/bury/{$objectType}/{$guid}/"
        );
        $fav = array
        (
            'icon' => MIDCOM_STATIC_URL . '/net.nemein.favourites/not-favorite.png',
            'title' => $l10n->get('add to favourites'),
            'url' => "{$url_prefix}?net_nemein_favourites_execute=fav&net_nemein_favourites_execute_for={$guid}&net_nemein_favourites_url={$url}/json/fav/{$objectType}/{$guid}/"
        );
        
        if (!$data['can_fav'])
        {
            $fav['url'] = null;
        }
        
        if (!$data['can_bury'])
        {
            $bury['url'] = null;
        }        

        if ($data['has_faved'])
        {
            // User has already favourited the item
            $fav['icon'] = MIDCOM_STATIC_URL . '/net.nemein.favourites/favorite.png';
            $fav['title'] = $l10n->get('favourite');
            $fav['url'] = null;
            $bury['url'] = null;
        }
        
        if ($data['has_buried'])
        {
            // User has already buried the item
            $bury['icon'] = MIDCOM_STATIC_URL . '/net.nemein.favourites/bury.png';
            $bury['title'] = $l10n->get('buried');
            $bury['url'] = null;
            $fav['url'] = null;
        }
        
        $fav_button  = "<span class=\"net_nemein_favourites\">";
        if ($fav['url'])
        {
            $fav_button .= "{$data['favs']} <a href=\"{$fav['url']}\" class=\"net_nemein_favourites_create\"><img src=\"{$fav['icon']}\" style=\"border: none;\" alt=\"{$fav['title']}\" title=\"{$fav['title']}\" /></a>";
        }
        else
        {
            $fav_button .= "{$data['favs']} <img src=\"{$fav['icon']}\" style=\"border: none;\" alt=\"{$fav['title']}\" title=\"{$fav['title']}\" />";
        }
        if ($bury['url'])
        {
            $fav_button .= "{$data['buries']} <a href=\"{$bury['url']}\" class=\"net_nemein_favourites_create\"><img src=\"{$bury['icon']}\" style=\"border: none;\" alt=\"{$bury['title']}\" title=\"{$bury['title']}\" /></a>";
        }
        else
        {
            $fav_button .= "{$data['buries']} <img src=\"{$bury['icon']}\" style=\"border: none;\" alt=\"{$bury['title']}\" title=\"{$bury['title']}\" />";
        }
        $fav_button .= "</span>";
        return $fav_button;
    }
    
    function get_ajax_headers($js_options='', $element_path='.net_nemein_favourites')
    {
        $html = "<script type=\"text/javascript\" src=\"" . MIDCOM_STATIC_URL . "/jQuery/jquery.metadata.js\"></script>\n";
        $html .= "<script type=\"text/javascript\" src=\"" . MIDCOM_STATIC_URL . "/net.nemein.favourites/net_nemein_favourites.js\" ></script>\n";

        $html .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . MIDCOM_STATIC_URL . "/net.nemein.favourites/screen.css\" media=\"screen\" />\n\n";
        
        if ($js_options == '')
        {
            $return_url = substr($_MIDCOM->get_host_prefix(), 0, -1) . $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            $js_options = '{return_to: "' . $return_url . '"}';
        }
        
        $add_html = '';
        if (   isset($_REQUEST['net_nemein_favourites_execute'])
            && isset($_REQUEST['net_nemein_favourites_execute_for'])
            && isset($_REQUEST['net_nemein_favourites_url']))
        {
            $_MIDCOM->auth->require_valid_user();
            $action = $_REQUEST['net_nemein_favourites_execute'];
            $guid = $_REQUEST['net_nemein_favourites_execute_for'];
            
            $add_html .= ".net_nemein_favourites_execute({$js_options}, '{$action}', '{$guid}', '{$_REQUEST['net_nemein_favourites_url']}')\n";
        }
        
        $html .= "<script type=\"text/javascript\">\n";
        $html .= "jQuery(document).ready(function(){\n";
        $html .= "jQuery('{$element_path}').net_nemein_favourites({$js_options}){$add_html};\n";
        $html .= "});\n";
        $html .= "</script>\n\n";
        
        echo $html;
    }
}
?>