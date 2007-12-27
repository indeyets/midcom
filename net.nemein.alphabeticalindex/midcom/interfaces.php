<?php
/**
 * @package net.nemein.alphabeticalindex 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for net.nemein.alphabeticalindex
 * 
 * To include alphabetical index support in schemas
 * add similar field to the schema definition:
'show_in_index' => array
(
    'title' => $_MIDCOM->i18n->get_string('Show in index', 'net.nemein.alphabeticalindex'),
    'storage' => array
    (
        'location' => 'configuration',
        'domain' => 'net.nemein.alphabeticalindex:show_in_list',
        'name' => 'status',
    ),
    'type' => 'select',
    'type_config' => array
    (
        'require_corresponding_option' => false,
        'options' => null,
        'option_callback' => 'net_nemein_alphabeticalindex_callback_availableindexes',
        'allow_multiple' => true,
        'multiple_storagemode' => 'imploded_wrapped',
        'allow_other' => false,
    ),
    'widget' => 'select',
), 
 *
 * @package net.nemein.alphabeticalindex
 */
class net_nemein_alphabeticalindex_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_alphabeticalindex_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'net.nemein.alphabeticalindex';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        ( 
            'viewer.php', 
            'navigation.php',
            'item.php',
        );
        
        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
        );
    }
    
    function _on_watched_dba_create($object, $force_to_list=false)
    {
        // debug_push_class(__CLASS__, __FUNCTION__);
        // debug_print_r("object: ",$object);

        $title = net_nemein_alphabeticalindex_interface::_resolve_object_title($object);
        $show_in_list = $object->get_parameter('net.nemein.alphabeticalindex:show_in_list','status');
        
        if (!is_string($show_in_list))
        {   
            if (! $show_in_list)
            {
                $lists = array();
            }
            else
            {
                $lists = $show_in_list;
            }
        }
        else
        {
            $lists = explode('|', substr($show_in_list, 1, -1));
        }
        
        if ($force_to_list) {
            $lists = array($force_to_list);
        }

        // debug_print_r("lists: ",$lists);
        
        if (! empty($lists))
        {
            foreach ($lists as $list_id)
            {
                $list_id = (int) $list_id;
       	       	if ($list_id == 0) {
       	       	    continue;
       	       	}
       	       	$topic = new midcom_db_topic($list_id);
       	       	
                $item = new net_nemein_alphabeticalindex_item();            
                $item->title = $title;
                $item->url = "{$GLOBALS['midcom_config']['midcom_site_url']}midcom-permalink-{$object->guid}";
                $item->objectGuid = $object->guid;
                $item->node = $list_id;
                if ($item->create())
                {
                    if ($topic) {
                        $topic->update();
                    }
                    
                    $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('net.nemein.alphabeticalindex', 'net.nemein.alphabeticalindex'), sprintf($_MIDCOM->i18n->get_string('item %s has been added to alphabetical index', 'net.nemein.alphabeticalindex'), $item->title), 'ok');
                }                
            }
        }

        // debug_pop();
    }

    function _on_watched_dba_update($object)
    {
        // debug_push_class(__CLASS__, __FUNCTION__);
        // debug_print_r("object: ",$object);
        
        $title = net_nemein_alphabeticalindex_interface::_resolve_object_title($object);
        $show_in_list = $object->get_parameter('net.nemein.alphabeticalindex:show_in_list','status');
        // debug_print_r("show_in_list: ",$show_in_list);

        if (! is_string($show_in_list))
        {
            if (! $show_in_list)
            {
                $lists = array();
            }
            else
            {
                $lists = $show_in_list;
            }
        }
        else
        {
            $lists = explode('|', substr($show_in_list, 1, -1));
        }

        // debug_print_r("lists: ",$lists);

        if (! empty($lists))
        {
            $processed_lists = array();
            
            foreach ($lists as $list_id)
            {
                $list_id = (int) $list_id;
       	       	if ($list_id == 0) {
       	       	    continue;
       	       	}       	       	
       	       	$topic = new midcom_db_topic($list_id);
       	       	
                $qb = net_nemein_alphabeticalindex_item::new_query_builder();
                $qb->add_constraint('objectGuid', '=', $object->guid);
                $qb->add_constraint('node.id', '=', $list_id);
                
                $results = $qb->execute();
                if (count($results) > 0)
                {                
                    $item = $results[0];
                    if (! $item->modified)
                    {
                        $item->title = $title;
                    }
                    $item->node = $list_id;
                    $item->update();
                    
                    if ($topic) {
                        $topic->update();
                    }
                    
                    $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('net.nemein.alphabeticalindex', 'net.nemein.alphabeticalindex'), sprintf($_MIDCOM->i18n->get_string('item %s has been updated in alphabetical index', 'net.nemein.alphabeticalindex'), $item->title), 'ok');
                }
                else
                {
                    net_nemein_alphabeticalindex_interface::_on_watched_dba_create($object, $list_id);
                }
                $processed_lists[] = $list_id;
            }
            
            $qb = net_nemein_alphabeticalindex_item::new_query_builder();
            $qb->add_constraint('objectGuid', '=', $object->guid);
            $results = $qb->execute();
            
            if (count($processed_lists) < count($results))
            {
                foreach ($results as $result)
                {
                    if (! in_array($result->node, $processed_lists))
                    {
                        $result->delete();
                    }
                }
            }
        }
        else
        {
            net_nemein_alphabeticalindex_interface::_on_watched_dba_delete($object, true);
        }
        
        // debug_pop();
    }

    /**
     * The delete handler will drop all entries associated with any record that has been
     * deleted.
     */
    function _on_watched_dba_delete($object, $skip_status=false)
    {
        // debug_push_class(__CLASS__, __FUNCTION__);
        // debug_print_r("object: ",$object);
        
        $show_in_list = true;
        
        if (! $skip_status)
        {
            $show_in_list = $object->get_parameter('net.nemein.alphabeticalindex:show_in_list','status');
            // debug_add("show_in_list: {$show_in_list}");            

            if (! is_string($show_in_list))
            {
                if (! $show_in_list)
                {
                    $lists = array();
                }
                else
                {
                    $lists = $show_in_list;
                }
            }
            else
            {
                $lists = explode('|', substr($show_in_list, 1, -1));
            }
        }
        else
        {
            $lists = array( 'all' );
        }

        // debug_print_r("lists: ",$lists);
        
        if (! empty($lists))
        {
            foreach ($lists as $list_id)
            {
                $list_id = (int) $list_id;
       	       	if ($list_id == 0) {
       	       	    continue;
       	       	}
                $topic = new midcom_db_topic($list_id);
                
                $qb = net_nemein_alphabeticalindex_item::new_query_builder();
                $qb->add_constraint('objectGuid', '=', $object->guid);
                if (! $skip_status)
                {
                    $qb->add_constraint('node', '=', $list_id);                    
                }
                $results = $qb->execute();
                if (count($results) > 0)
                {
                    foreach ($results as $item)
                    {
                        $title = $item->title;
                        if ($item->delete())
                        {
                            $object->set_parameter('net.nemein.alphabeticalindex:show_in_list','status', false);
                            
                            if ($topic) {
                                $topic->update();
                            }
                                                        
                            $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('net.nemein.alphabeticalindex', 'net.nemein.alphabeticalindex'), sprintf($_MIDCOM->i18n->get_string('item %s has been deleted from alphabetical index', 'net.nemein.alphabeticalindex'), $title), 'ok');
                        }
                    }
                }                
            }
        }

        // debug_pop();
    }

    function _resolve_object_title(&$object)
    {
        if ($object->__table__ == 'article')
        {
            $title = $object->title;
        }
        else
        {
            $title = $object->extra;
        }
        
        return $title;
    }

}
?>