<?php
/**
 * @package fi.protie.host
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 12672 2007-10-05 11:57:32Z adrenalin $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Hosts navigation
 *
 * @package fi.protie.host
 */
class fi_protie_host_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Symlinked content topic
     * 
     * @var midcom_db_topic
     */
    var $_content_topic = null;
    
    /**
     * Simple constuctor. Connect to the parent class and resolve the content topic
     */
    function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get the topic leaves
     * 
     * @access public
     * @return Array containing leaves
     */
    function get_leaves()
    {
        // Get the hosts
        $qb = midcom_db_host::new_query_builder();
        
        if ($_MIDGARD['sitegroup'] !== 0)
        {
            $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        }
        
        $qb->add_order('metadata.score', 'DESC');
        $qb->add_order('name');
        $qb->add_order('prefix');
        
        $results = $qb->execute();
        
        // Get each leaf
        foreach ($results as $host)
        {
            $guid = $host->guid;
            $leaves[$guid] = array
            (
                MIDCOM_NAV_URL => "edit/{$host->name}/",
                MIDCOM_NAV_NAME => "{$host->name}{$host->prefix}",
                MIDCOM_NAV_GUID => $guid,
                MIDCOM_NAV_OBJECT => $host,
                MIDCOM_META_CREATOR => $host->metadata->creator,
                MIDCOM_META_EDITOR => $host->metadata->revisor,
                MIDCOM_META_CREATED => $host->metadata->created,
                MIDCOM_META_EDITED => $host->metadata->published,
            );
        }
        
        return $leaves;
        /* */
    }
}
?>