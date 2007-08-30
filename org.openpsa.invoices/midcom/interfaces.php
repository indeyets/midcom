<?php
/**
 * @package org.openpsa.invoices
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php,v 1.3 2006/06/01 15:28:21 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Invoice management MidCOM interface class.
 *
 * @package org.openpsa.invoices
 */
class org_openpsa_invoices_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function org_openpsa_invoices_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'org.openpsa.invoices';
        $this->_autoload_files = Array(
            'viewer.php',
            'navigation.php',
            'invoice.php',
            'invoice_hour.php',
        );
        $this->_autoload_libraries = Array(
            'midcom.helper.datamanager',
            'org.openpsa.contactwidget',
            'org.openpsa.relatedto',
        );
    }

    function _on_initialize()
    {

        //We need the contacts person class available.
        $_MIDCOM->componentloader->load_graceful('org.openpsa.contacts');
        $_MIDCOM->componentloader->load_graceful('org.openpsa.projects');
        $_MIDCOM->componentloader->load_graceful('org.openpsa.sales');
        return true;
    }
}

?>