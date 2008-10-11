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
class org_openpsa_invoices_invoice_hour extends __org_openpsa_invoices_invoice_hour
{
    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    function _on_created()
    {
        parent::_on_created();

        // Cache the information that the hour report has been invoiced to the report itself
        $hour_report = new org_openpsa_projects_hour_report($this->hourReport);
        if (!$hour_report)
        {
            return false;
        }
        $hour_report->invoiced = date('Y-m-d H:i:s', $this->metadata->created);
        $hour_report->invoicer = $this->creator;
        return $hour_report->update();
    }
}
?>