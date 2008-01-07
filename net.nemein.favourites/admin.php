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
        $data = array
        (
            'favs'     => 0,
            'buries'   => 0,
            'has_faved' => false,
            'can_fav'  => true,
            'has_buried' => false,
            'can_bury' => true,
        );
        
        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        $qb->add_constraint('objectGuid', '=', $guid);
        $qb->add_constraint('bury', '=', false);
        $data['favs'] = $qb->count_unchecked();

        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        $qb->add_constraint('objectGuid', '=', $guid);
        $qb->add_constraint('bury', '=', true);
        $data['buries'] = $qb->count_unchecked();
        
        if (   !$_MIDCOM->auth->user
            && !$link_for_anonymous)
        {
            $data['can_fav'] = false;
            $data['can_bury'] = false;
            return $data;
        }

        
        if ($_MIDCOM->auth->user)
        {
            // Check if user has already favorited this
            $qb = net_nemein_favourites_favourite_dba::new_query_builder();
            $qb->add_constraint('metadata.creator', '=', $_MIDCOM->auth->user->guid);
            $qb->add_constraint('objectGuid', '=', $guid);
            $qb->add_constraint('bury', '=', false);
            if ($qb->count_unchecked() > 0)
            {
                $data['can_fav'] = false;
                $data['has_faved'] = true;
            }
            
            // Check if user has already buried this
            $qb = net_nemein_favourites_favourite_dba::new_query_builder();
            $qb->add_constraint('metadata.creator', '=', $_MIDCOM->auth->user->guid);
            $qb->add_constraint('objectGuid', '=', $guid);
            $qb->add_constraint('bury', '=', true);
            if ($qb->count_unchecked() > 0)
            {
                $data['can_bury'] = false;
                $data['has_buried'] = true;
            }
        }
        
        return $data;
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
}
?>
