<?php
/**
 * @package net.nemein.featured
 */

/**
 * Featured
 *
 * @package net.nemein.featured
 */
class net_nemein_featured_viewer extends midcom_baseclasses_components_request
{
    var $_content_topic = null;

    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
    }

    function _on_initialize()
    {
        $this->_request_switch['create'] = Array
        (
            'handler' => Array('net_nemein_featured_handler_featured', 'manage'),
            'fixed_args' => Array('create'),
            'variable_args' => 2,
        );

        $this->_request_data['content_topic'] =& $this->_content_topic;

        $this->_request_switch['manage'] = Array
        (
            'handler' => Array('net_nemein_featured_handler_featured', 'manage'),
            'fixed_args' => Array('manage'),
        );
        $this->_request_switch['edit'] = Array
        (
            'handler' => Array('net_nemein_featured_handler_featured', 'edit'),
            'fixed_args' => Array('edit'),
            'variable_args' => 1,
            );
        $this->_request_switch['delete'] = Array
        (
            'handler' => Array('net_nemein_featured_handler_featured', 'delete'),
            'fixed_args' => Array('delete'),
            'variable_args' => 1,
        );
        $this->_request_switch['move_down'] = Array
        (
            'handler' => Array('net_nemein_featured_handler_featured', 'move_down'),
            'fixed_args' => Array('move_down'),
            'variable_args' => 1,
        );
        $this->_request_switch['move_up'] = Array
        (
            'handler' => Array('net_nemein_featured_handler_featured', 'move_up'),
            'fixed_args' => Array('move_up'),
            'variable_args' => 1,
        );
        $this->_request_switch['index'] = Array
        (
            'handler' => Array('net_nemein_featured_handler_view', 'view'),
        );
        $this->_request_switch['list'] = Array
        (
            'handler' => Array('net_nemein_featured_handler_view', 'view'),
            'fixed_args' => Array('list'),
            'variable_args' => 1,
        );
        $this->_request_switch['list_top'] = Array
        (
            'handler' => Array('net_nemein_featured_handler_view', 'view'),
            'fixed_args' => Array('list'),
            'variable_args' => 2,
        );
    }

    function _populate_node_toolbar()
    {
        if ($this->_content_topic->can_do('midgard:create'))
        {
            if (array_key_exists('schemadb', $this->_request_data))
            {
                $this->_node_toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "manage",
                        MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('manage'),
                        $this->_l10n->get($this->_request_data['schemadb']['default']->description)),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                        MIDCOM_TOOLBAR_ACCESSKEY => 'n',
                    )
                );
            }
        }
    }

    function _on_handle($handler_id, $args)
    {
        $this->_content_topic = new midcom_db_topic($this->_topic->id);

        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $this->_populate_node_toolbar();

        return true;
    }

    function get_groups_callback()
    {
        $data =& $_MIDCOM->get_custom_context_data('request_data');
        $config_groups = $data['config']->get('groups');

        $groups = array();

        foreach($config_groups as $name => $group)
        {
            $groups[$name] = $group['title'];
        }

        return  $groups;  //array('info' => 'Info', 'video' => 'Video');
    }

    function get_groups()
    {
        $data =& $_MIDCOM->get_custom_context_data('request_data');
        $config_groups = $data['config']->get('groups');

        $groups = array();

        foreach($config_groups as $name => $group)
        {
            $groups[$name] = $group;
        }

        return  $groups;
    }

    function render_add_link($objectType, $path, $node = null)
    {
        if (empty($path) || empty($objectType))
        {
            return false;
        }

        $midcom_i18n =& $_MIDCOM->get_service('i18n');
        $l10n =& $midcom_i18n->get_l10n('net.nemein.featured');

        if (!$_MIDCOM->auth->user)
        {
            return true;
        }

        if (is_null($node))
        {
            $node = midcom_helper_find_node_by_component('net.nemein.featured');
        }

        if (empty($node))
        {
            return false;
        }

        $url = $node[MIDCOM_NAV_FULLURL];

        echo "<span class=\"net_nemein_featured\">" . "<a href=\"{$url}manage/?featured_path={$path}&type={$objectType}\"
            class=\"net_nemein_featured_manage\">ADD</a></span>";

        return true;
    }

    function get_items_by_group($topic_guid, $group_name = '')
    {
        $qb = net_nemein_featured_item_dba::new_query_builder();
        $qb->add_constraint('topicGuid', '=', $topic_guid);
        if ($group_name != '')
        {
            $qb->add_constraint('groupName', '=', $group_name);
        }
        $qb->add_order('metadata.score', 'ASC');

        $featured_objects = $qb->execute();

        return $featured_objects;
    }

    function show_featured_items($topic_guid, $group_name = '', $substyle = array())
    {
        $featured_items = net_nemein_featured_viewer::get_featured_items($topic_guid, $group_name);

        foreach ($featured_items as $featured)
        {
            $target_object = new midcom_baseclasses_core_dbobject($featured->guid);

            //print_r($target_object);
            /*
                // TODO:
            if (array_key_exists($target_object->type, $substyle)
            {

            }
            else
            {
                $featured->load_featured_item();
            }
            */
        }
    }
}

?>