<?php
/**
 * @package org.openpsa.projects
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: deliverable_midcomdba.php,v 1.1 2005/10/21 14:28:28 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 *
 * @package org.openpsa.projects
 *
 */
class midcom_org_openpsa_deliverable extends __midcom_org_openpsa_deliverable
{
    function __construct($id = null)
    {
        return parent::__midcom_org_openpsa_deliverable($id);
    }
}

/**
 *
 * @package org.openpsa.projects
 *
 */
class org_openpsa_projects_deliverable extends midcom_org_openpsa_deliverable
{

    function __construct($identifier=NULL)
    {
        return parent::__midcom_org_openpsa_deliverable($identifier);
    }
}

?>