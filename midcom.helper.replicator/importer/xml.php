<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_importer_xml extends midcom_helper_replicator_importer
{
    /**
     * Initializes the class.
     */
    function midcom_helper_replicator_importer_xml()
    {
        parent::__construct();
    }
 
     /**
     * Main entry point for importer, imports given XML content
     *
     * @param string $xml_content Importable Midgard XML content
     * @param boolean $use_the_force Whether to force importing
     * @return boolean Whether importing was successful
     */   
    function import($xml_content, $use_the_force = false)
    {
        $GLOBALS['midcom_helper_replicator_logger']->push_prefix(__CLASS__ . '::' . __FUNCTION__);
        $stat = $this->import_xml($xml_content, $use_the_force);
        if (!$stat)
        {
            $GLOBALS['midcom_helper_replicator_logger']->log('Import failed.', MIDCOM_LOG_ERROR);
            return false;
        }
            $GLOBALS['midcom_helper_replicator_logger']->log('XML imported.', MIDCOM_LOG_INFO);
        $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
        return true;
    }
}
?>