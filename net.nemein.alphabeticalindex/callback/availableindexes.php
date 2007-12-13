<?php
/**
 * @package net.nehmer.account
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: validation.php 3210 2006-04-06 17:28:02Z torben $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Lists available alphabetical indexes for selection
 *
 * @package net.nemein.alphabeticalindex
 */

class net_nemein_alphabeticalindex_callback_availableindexes extends midcom_baseclasses_components_purecode
{
    /**
     * The array with the data we're working on.
     *
     * @var array
     * @access private
     */
    var $_data = null;
    
    /**
     * The callback class instance, a callback matching the signature required for the DM2 select
     * type callbacks.
     *
     * @var object
     * @access private
     */
    var $_callback = null;
    
    function net_nemein_alphabeticalindex_callback_availableindexes($args)
    {
        $this->_component = 'net.nemein.alphabeticalindex';
        parent::midcom_baseclasses_components_purecode();
        
        $this->_data = array
        (
        );
        
        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('component', '=', 'net.nemein.alphabeticalindex');
        $indexes = $qb->execute();

        if (count($indexes) == 0)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('No indexes found. You have to create at least one net.nemein.alphabeticalindex -topic to use this feature.');
            debug_pop();
        }
        
        $this->_populate_data($indexes);
    }
    
    function _populate_data(&$indexes)
    {
        foreach ($indexes as $index)
        {
            $this->_data[$index->id] = $index->extra;
        }
    }
    
    function get_name_for_key($key)
    {
        return $this->_data[$key];
    }
    
    function key_exists($key)
    {   
        return array_key_exists($key, $this->_data);
    }
    
    function list_all()
    {
        return $this->_data;
    }

    /** Ignored. */
    function set_type(&$type) {}
    
}

?>