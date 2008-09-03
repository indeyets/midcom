<?php
/**
 * @package net.nemein.featured
 */

/**
 * Featured AIS interface class.
 *
 * @package net.nemein.featured
 */
class net_nemein_featured_admin extends midcom_baseclasses_components_request_admin
{
    var $_content_topic = null;

    function net_nemein_featured_admin($topic, $config)
    {
         parent::__construct($topic, $config);
    }

    function _on_initialize()
    {
        $this->_content_topic = $this->_request_data['content_topic'];
        return true;
    }

    function get_groups()
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

    echo $path;
    $url = $node[MIDCOM_NAV_FULLURL];

    echo "<span class=\"net_nemein_featured\">" . "<a href=\"{$url}manage/?featured_path={$path}&type={$objectType}\"
            class=\"net_nemein_featured_manage\">ADD</a></span>";

        return true;
    }

    function get_featured_items($topic_guid, $group_name = '')
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
        $featured_items = net_nemein_featured_admin::get_featured_items($topic_guid, $group_name);

        foreach ($featured_items as $featured)
    {
        $target_object = new midcom_baseclasses_core_dbobject($featured->guid);

        print_r($target_object);
/*
            // TODO:
        if (array_key_exists($target_object->type, $substyle)
        {

        }
        else
        {
                $featured->load_featured_item();
        }*/
        }
    }


}

?>