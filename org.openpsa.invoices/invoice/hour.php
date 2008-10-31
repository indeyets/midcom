<?php
/**
 * @package org.openpsa.invoices
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: invoice_hour.php,v 1.2 2006/06/01 15:28:20 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * MidCOM wrapped base class, keep logic here
 *
 * @package org.openpsa.invoices
 */
class org_openpsa_invoices_invoice_hour_dba extends __org_openpsa_invoices_invoice_hour_dba
{
    function __construct($id = null)
    {
        $this->_use_rcs = false;
        return parent::__construct($id);
    }

    function _on_created()
    {
        parent::_on_created();

        // Cache the information that the hour report has been invoiced to the report itself
        $hour_report = new org_openpsa_projects_hour_report_dba($this->hourReport);
        if (!$hour_report)
        {
            return false;
        }
        $hour_report->invoiced = date('Y-m-d H:i:s', $this->metadata->created);
        $hour_report->invoicer = $this->creator;
        return $hour_report->update();
    }

    /**
     * Marks the hour report as uninvoiced
     */
    function _on_deleted()
    {
        parent::_on_deleted();

        if (! $_MIDCOM->auth->request_sudo('org.openpsa.invoices'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to get SUDO privileges, skipping invoice hour deletion silently.', MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }
        
        $hour_report = new org_openpsa_projects_hour_report_dba($this->hourReport);
        if (!$hour_report)
        {
            return;
        }
        $hour_report->invoiced = '0000-00-00 00:00:00';
        $hour_report->invoicer = 0;
        if (!$hour_report->update())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to mark hour report {$hour_report->id} as uninvoiced, last Midgard error was: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
        }
        
        $_MIDCOM->auth->drop_sudo();
        return;
    }
}
?>