<?php
/**
 * @package org.openpsa.projects
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: plugin_noop.php,v 1.1 2005/10/25 17:51:55 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * Baseclass for deliverables plugins
 *
 * @package org.openpsa.projects
 *
 */
class org_openpsa_projects_deliverables_interface_plugin_noop extends org_openpsa_projects_deliverables_interface_plugin
{
    function __construct($identifier=NULL)
    {
        $this->name = 'projects.noop';
        //TODO: Localization ?
        $this->description = 'No-Op deliverable for deliverables outside the scope of OpenPsa';
        return parent::__construct($identifier);
    }

    /**
     * The No-Op plugin always returns true
     *
     * @return boolean
     */
    function status()
    {
        return true;
    }
}

?>