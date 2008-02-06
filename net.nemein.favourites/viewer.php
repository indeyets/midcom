<?php
/**
 * @package net.nemein.favourites
 */

/**
 * Favourites
 *
 * @package net.nemein.favourites
 */
class net_nemein_favourites_viewer extends midcom_baseclasses_components_request
{
    var $_content_topic = null;

    function net_nemein_favourites_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    function _on_initialize()
    {
        $this->_request_data['content_topic'] =& $this->_content_topic;

        // Request switches
        $this->_request_switch['create'] = Array
        (
            'handler' => Array('net_nemein_favourites_handler_create', 'create'),
            'fixed_args' => Array('create'),
            'variable_args' => 2,
        );

        $this->_request_switch['bury'] = Array
        (
            'handler' => Array('net_nemein_favourites_handler_create', 'create'),
            'fixed_args' => Array('bury'),
            'variable_args' => 2,
        );
        
        $this->_request_switch['json_fav'] = Array
        (
            'handler' => Array('net_nemein_favourites_handler_create', 'json'),
            'fixed_args' => Array('json', 'fav'),
            'variable_args' => 2,
        );

        $this->_request_switch['json_bury'] = Array
        (
            'handler' => Array('net_nemein_favourites_handler_create', 'json'),
            'fixed_args' => Array('json', 'bury'),
            'variable_args' => 2,
        );

        $this->_request_switch['delete'] = Array
        (
            'handler' => Array('net_nemein_favourites_handler_create', 'delete'),
            'fixed_args' => Array('delete'),
            'variable_args' => 1,
        );

        $this->_request_switch['index'] = Array
        (
            'handler' => Array('net_nemein_favourites_handler_view', 'view'),
        );
    }

    function _on_handle($handler_id, $args)
    {
        if ($this->_config->get('user'))
        {
            $this->_request_data['user'] = $_MIDCOM->auth->get_user('user:' . $this->_config->get('user'));
            if (!$this->_request_data['user'])
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'No user found for this buddy list.');
                // This will exit.
            }
        }
        else
        {
            $_MIDCOM->auth->require_valid_user();
            $this->_request_data['user'] = $_MIDCOM->auth->user;
        }

        $this->_content_topic = new midcom_db_topic($this->_topic->id);

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
        $total_favs = $qb->count_unchecked();

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
            return "<span class=\"net_nemein_favourites\">". sprintf($l10n->get('%d favs'), $total_favs) . " <img src=\"" . MIDCOM_STATIC_URL . "/net.nemein.favourites/favorite.png\" alt=\"" . $l10n->get('favourite') . "\" /></span>\n";
        }

        return "<span class=\"net_nemein_favourites\">". sprintf($l10n->get('%d favs'), $total_favs) . "<a href=\"{$url}create/{$objectType}/{$guid}.html\" class=\"net_nemein_favourites_create\"> <img src=\"" . MIDCOM_STATIC_URL . "/net.nemein.favourites/not-favorite.png\" style=\"border: none;\" alt=\"{$l10n->get('add to favourites')}\" /></a></span>";
    }

    function render_add_link($objectType, $guid, $url = '', $link_for_anonymous = true)
    {
        echo net_nemein_favourites_viewer::get_add_link($objectType, $guid, $url, $link_for_anonymous);
    }
}

?>