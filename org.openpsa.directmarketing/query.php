<?php
/**
 * @package org.openpsa.directmarketing
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * MidCOM wrapped class for access to stored queries
 */
class org_openpsa_directmarketing_query extends org_openpsa_queries_query
{
    function org_openpsa_directmarketing_query($id = null)
    {
        $stat = parent::__midcom_org_openpsa_query($id);
        if (!$this->component)
        {
            $this->component = 'org.openpsa.directmarketing';
        }
        return $stat;
    }
}

?>