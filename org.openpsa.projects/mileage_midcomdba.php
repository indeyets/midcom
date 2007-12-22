<?php
/**
 * @package org.openpsa.projects
 * @author Nemein Oy, http://www.nemein.com/
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 *  Mileages are just a special case of expense, in fact they might not
 *  need their own object at all...
 *
 * @package org.openpsa.projects
 *
 */
class org_openpsa_projects_mileage extends org_openpsa_projects_expense
{
    function org_openpsa_projects_mileage($identifier=NULL)
    {
        parent::org_openpsa_projects_expense($identifier);
    }
}
?>