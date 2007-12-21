<?php
/**
 * @package net.nemein.wiki
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapped class for access to stored queries
 */
class net_nemein_wiki_link_dba extends __net_nemein_wiki_link_dba
{
    function net_nemein_wiki_link_dba($id = null)
    {
        return parent::__net_nemein_wiki_link_dba($id);
    }

    function get_parent_guid_uncached()
    {
        // FIXME: Midgard Core should do this
        if ($this->frompage != 0)
        {
            $parent = new net_nemein_wiki_wikipage($this->frompage);
            return $parent->guid;
        }
        else
        {
            return null;
        }
    }
}
?>
