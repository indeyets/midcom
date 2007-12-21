<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapped class for access to stored queries
 */
class org_openpsa_reports_query extends org_openpsa_queries_query
{
    function org_openpsa_reports_query($id = null)
    {
        $stat = parent::__midcom_org_openpsa_query($id);
        if (!$this->orgOpenpsaObtype)
        {
            $this->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_REPORT_TEMPORARY;
        }
        if (!$this->extension)
        {
            $this->extension = '.html';
        }
        if (!$this->mimetype)
        {
            $this->mimetype = 'text/html';
        }
        if (!$this->component)
        {
            $this->component = 'org.openpsa.reports';
        }
        return $stat;
    }
}

?>