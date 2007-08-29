<?php

/**
 * Forum AIS interface class.
 * 
 * @package net.nemein.favourites
 */
class net_nemein_favourites_admin extends midcom_baseclasses_components_request_admin
{
    var $_content_topic = null;

    function net_nemein_favourites_admin($topic, $config) 
    {
         parent::midcom_baseclasses_components_request_admin($topic, $config);
    }

    function _on_initialize()
    {
        $this->_content_topic = $this->_request_data['content_topic'];
        return true;
    }

    function render_add_link($objectType, $guid, $url = '')
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
        
        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        $qb->add_constraint('objectGuid', '=', $guid);
        $total_favs = $qb->count_unchecked();
        
        if (!$_MIDCOM->auth->user)
        {
            echo "<span class=\"net_nemein_favourites\">". sprintf($l10n->get('%d favs'), $total_favs) . "</span>\n";
            return true;
        }
        
        // Check if user has already favorited this
        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        $qb->add_constraint('metadata.creator', '=', $_MIDCOM->auth->user->guid);
        $qb->add_constraint('objectGuid', '=', $guid);
        if ($qb->count_unchecked() > 0)
        {
            echo "<span class=\"net_nemein_favourites\">". sprintf($l10n->get('%d favs'), $total_favs) . " <img src=\"" . MIDCOM_STATIC_URL . "/net.nemein.favourites/favorite.png\" alt=\"" . $l10n->get('favourite') . "\" /></span>\n";
        }
        else
        {

            echo "<span class=\"net_nemein_favourites\">". sprintf($l10n->get('%d favs'), $total_favs) . "<a href=\"{$url}create/{$objectType}/{$guid}.html\" class=\"net_nemein_favourites_create\"> <img src=\"" . MIDCOM_STATIC_URL . "/net.nemein.favourites/not-favorite.png\" style=\"border: none;\" alt=\"{$l10n->get('add to favourites')}\" /></a></span>";
            
            return true;
        }
    }
}

?>
