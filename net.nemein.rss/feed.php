<?php
/**
 * @package net.nemein.rss
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapped class for access to stored queries
 *
 * @package net.nemein.rss
 */
class net_nemein_rss_feed_dba extends __net_nemein_rss_feed_dba
{
    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    function _on_loaded()
    {
        if (   $this->title == ''
            && $this->id)
        {
            $this->title = "Feed #{$this->id}";
        }

        return parent::_on_loaded();
    }
}
?>