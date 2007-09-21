<?php

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
        
        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        $qb->add_constraint('objectGuid', '=', $guid);
        $qb->add_constraint('bury', '=', false);
        $total_favs = $qb->count_unchecked();
        
        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        $qb->add_constraint('objectGuid', '=', $guid);
        $qb->add_constraint('bury', '=', true);
        $total_buries = $qb->count_unchecked();
        
        if (   !$_MIDCOM->auth->user
            && !$link_for_anonymous)
        {
            return "<span class=\"net_nemein_favourites\">". sprintf($l10n->get('%d favs'), $total_favs) . "</span>\n";
        }
        
        // Check if user has already favorited this
        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        
        if ($_MIDCOM->auth->user)
        {
            $qb->add_constraint('metadata.creator', '=', $_MIDCOM->auth->user->guid);
        }
        
        $qb->add_constraint('objectGuid', '=', $guid);
        if (   $_MIDCOM->auth->user
            && $qb->count_unchecked() > 0)
        {
            $favs = $qb->execute();
            if ($favs[0]->bury)
            {
                return "<span class=\"net_nemein_favourites\">". sprintf($l10n->get('%d favs'), $total_favs) . " <img src=\"" . MIDCOM_STATIC_URL . "/net.nemein.favourites/bury.png\" alt=\"" . $l10n->get('buried') . "\" title=\"" . $l10n->get('buried') . "\" /></span>\n";
            }
            else
            {
                return "<span class=\"net_nemein_favourites\">". sprintf($l10n->get('%d favs'), $total_favs) . " <img src=\"" . MIDCOM_STATIC_URL . "/net.nemein.favourites/favorite.png\" alt=\"" . $l10n->get('favourite') . "\" title=\"" . $l10n->get('favourite') . "\" /></span>\n";
            }
        }

        $return_url = rawurlencode($_SERVER['REQUEST_URI']);
        $fav_button  = "<span class=\"net_nemein_favourites\">";
        $fav_button .= sprintf($l10n->get('%d favs'), $total_favs);
        $fav_button .= " <a href=\"{$url}create/{$objectType}/{$guid}/?return={$return_url}\" class=\"net_nemein_favourites_create\"><img src=\"" . MIDCOM_STATIC_URL . "/net.nemein.favourites/not-favorite.png\" style=\"border: none;\" alt=\"{$l10n->get('add to favourites')}\" title=\"{$l10n->get('add to favourites')}\" /></a>";
        $fav_button .= " <a href=\"{$url}bury/{$objectType}/{$guid}/?return={$return_url}\" class=\"net_nemein_favourites_create\"><img src=\"" . MIDCOM_STATIC_URL . "/net.nemein.favourites/not-buried.png\" style=\"border: none;\" alt=\"{$l10n->get('bury')}\" title=\"{$l10n->get('bury')}\" /></a>";
        $fav_button .= "</span>";
        return $fav_button;
    }

    function render_add_link($objectType, $guid, $url = '', $link_for_anonymous = true)
    {
        echo net_nemein_favourites_admin::get_add_link($objectType, $guid, $url, $link_for_anonymous);
    }
}

?>
