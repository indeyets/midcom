<?php
/**
 * @package net.nemein.feedcollector
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapped class for access to stored queries
 *
 * @package net.nemein.feedcollector
 *
 */
class net_nemein_feedcollector_topic_dba extends __net_nemein_feedcollector_topic_dba
{
    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    function get_parent_guid_uncached()
    {
        if (empty($this->node))
        {
            return null;
        }
        $node = new midcom_db_topic($this->node);
        if (   !is_object($node)
            || !isset($node->guid)
            || empty($node->guid))
        {
            return null;
        }
        return $node->guid;
    }

}
?>