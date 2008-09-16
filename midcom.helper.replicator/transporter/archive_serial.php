<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/** ignore */
if (!class_exists('midcom_helper_replicator_transporter_archive'))
{
    require('archive.php');
}

/**
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_transporter_archive_serial extends midcom_helper_replicator_transporter_archive
{
    /**
     * @todo figure out a way make do without the subscription (from which configurations are read ATM)
     */
    function __construct($subscription)
    {
         $ret = parent::__construct($subscription);
         if (!$this->_create_tmp_dir())
         {
            $x = false;
            return $x;
         }
         return $ret;
    }

    /**
     * Fake support for queue manager
     *
     * In fact this transporter needs to run with a specialized serial exporter
     */
    function process(&$items)
    {
        $items = array();
        return true;
    }

    /**
     * Adds items to the tmp dir
     */
    function add_items(&$items)
    {
        if (!$this->_dump_items($items))
        {
            $this->error = "Failed to dump items";
            $this->_clean_up(true);
            return false;
        }
        return true;
    }

    /**
     * Creates archive of the tmp dir
     *
     * Call this when you have added all your items with add_items()
     */
    function create_archive()
    {
        if (!$this->_create_archive())
        {
            $this->_clean_up(true);
            return false;
        }
        $this->_clean_up(false);
        return true;
    }
}
?>