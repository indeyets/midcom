<?php
/**
 * @package org.openpsa.reports
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapped class for access to stored queries
 *
 * @package org.openpsa.reports
 */
class org_openpsa_reports_query_dba extends org_openpsa_queries_query_dba
{
    function __construct($id = null)
    {
        $stat = parent::__construct($id);
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