<?php
/**
 * @package org.maemo.devcodes
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 5856 2007-05-04 12:13:52Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Shows the application object view page
 *
 * @package org.maemo.devcodes
 */
class org_maemo_devcodes_handler_application_list extends midcom_baseclasses_components_handler
{
    /**
     * The application to display
     *
     * @var midcom_db_device
     * @access private
     */
    var $_device = null;

    /**
     * The Datamanager of the application to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['application'] =& $this->_device;
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }


    /**
     * Simple default constructor.
     */
    function org_maemo_devcodes_handler_application_list()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $data =& $this->_request_data;
        $data['states_readable'] = array
        (
            ORG_MAEMO_DEVCODES_APPLICATION_PENDING => $this->_l10n->get('application pending'),
            ORG_MAEMO_DEVCODES_APPLICATION_ACCEPTED => $this->_l10n->get('application accepted'),
            ORG_MAEMO_DEVCODES_APPLICATION_REJECTED => $this->_l10n->get('application rejected'),
        );
    }

    /**
     * Render a view of the user's applications
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_my($handler_id, $args, &$data)
    {
        $data['title'] = $this->_l10n->get('my applications');
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "application/list/my",
            MIDCOM_NAV_NAME => $data['title'],
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['title']}");

        if (!$_MIDGARD['user'])
        {
            // Silently ignore if we don't have user
            return true;
        }
        $data['applicable'] = org_maemo_devcodes_device_dba::list_applicable();
        $data['applications'] = org_maemo_devcodes_application_dba::list_applications();

        return true;
    }

    /**
     * Shows the loaded list.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_my($handler_id, &$data)
    {
        if (!$_MIDGARD['user'])
        {
            // Silently ignore if we don't have user
            return true;
        }
        $data['prefix'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        midcom_show_style('view-my-applications');
    }

    /**
     * Handle actual application display
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        $this->_device =& org_maemo_devcodes_device_dba::get_cached($args[0]);
        if (  !$this->_device
            || empty($this->_device->guid))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The device '{$args[0]}' was not found.");
            // This will exit.
        }
        $this->_device->require_do('org.maemo.devcodes:manage');

        $data['applications'] = array();
        $data['device'] =& $this->_device;

        $_MIDCOM->load_library('org.openpsa.qbpager');
        $qb = new org_openpsa_qbpager('org_maemo_devcodes_application_dba', 'applications');
        $qb->add_constraint('device', '=', $this->_device->id);
        $qb->add_order('state', 'ASC');
        $qb->add_order('applicant.lastname', 'ASC');
        $qb->add_order('applicant.firstname', 'ASC');
        $qb->add_order('applicant.username', 'ASC');
        $data['applications'] = $qb->execute();
        if (!is_array($data['applications']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'QB failed fatally, errstr: ' . midgard_errstr());
            // this will exit()
        }
        $data['qb'] =& $qb;

        $data['title'] = sprintf($this->_l10n->get('developer code applications for %s'), $this->_device->title);
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "device/{$this->_device->guid}",
            MIDCOM_NAV_NAME => $this->_device->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "application/list/{$this->_device->guid}",
            MIDCOM_NAV_NAME => $data['title'],
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $this->_prepare_request_data();

        /*
        $_MIDCOM->bind_list_to_object($this->_device, $this->_datamanager->schema->name);
        $_MIDCOM->set_26_request_metadata($this->_device->metadata->revised, $this->_device->guid);
        */
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['title']}");

        return true;
    }

    /**
     * Shows the loaded list.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_list ($handler_id, &$data)
    {
        $data['prefix'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        if (empty($data['applications']))
        {
            midcom_show_style('list-applications-noresults');
            return;
        }
        // TODO: page list
        midcom_show_style('list-applications-header');
        foreach ($data['applications'] as $application)
        {
            $data['application'] =& $application;
            // TODO: DMize ??
            midcom_show_style('list-applications-item');
        }
        midcom_show_style('list-applications-footer');
    }
}

?>