<?php
/**
 * @package org.openpsa.directmarketing
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * MidCOM wrapped class for access to stored queries
 *
 * @package org.openpsa.directmarketing
 */
class org_openpsa_directmarketing_query extends org_openpsa_queries_query
{
    function __construct($id = null)
    {
        $stat = parent::__construct($id);
        if (!$this->component)
        {
            $this->component = 'org.openpsa.directmarketing';
        }
        return $stat;
    }
}

?>