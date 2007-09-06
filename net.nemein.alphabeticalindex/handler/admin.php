<?php
/**
 * @package net.nemein.alphabeticalindex
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an admin handler class for net.nemein.alphabeticalindex
 *
 * @package net.nemein.alphabeticalindex
 */
class net_nemein_alphabeticalindex_handler_admin extends midcom_baseclasses_components_handler 
{
    /**
     * The alphabet item
     *
     * @var array
     * @access private
     */
    var $_item = null;
    
    /**
     * Current topic
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_topic = null;
    
    /**
     * Simple default constructor.
     */
    function net_nemein_alphabeticalindex_handler_admin()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        $this->_topic =& $this->_request_data['topic'];
    }
    
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:delete');
        
        $qb = net_nemein_alphabeticalindex_item::new_query_builder();
        $qb->add_constraint('guid', '=', $args[0]);
        $results = $qb->execute();
        
        if (count($results) == 0)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The item {$args[0]} was not found.");
        }
        
        foreach ($results as $item)
        {
            $title = $item->title;
            if ($item->delete())
            {
                if ($item->objectGuid != '')
                {
                    $object = $_MIDCOM->dbfactory->get_object_by_guid($item->objectGuid);
                    if ($object)
                    {
                        $object->set_parameter('net.nemein.alphabeticalindex:show_in_list','status', false);                        
                    }
                }
                
                $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('net.nemein.alphabeticalindex', 'net.nemein.alphabeticalindex'), sprintf($_MIDCOM->i18n->get_string('item %s has been deleted from alphabetical index', 'net.nemein.alphabeticalindex'), $title), 'ok');
            }
        }
        
        return true;
    }
    
    function _show_delete($handler_id, &$data)
    {
        $_MIDCOM->relocate("");
    }
}

?>