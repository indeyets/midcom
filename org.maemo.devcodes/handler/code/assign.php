<?php
/**
 * @package org.maemo.devcodes
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 5856 2007-05-04 12:13:52Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Shows the code object view page
 *
 * @package org.maemo.devcodes
 */
class org_maemo_devcodes_handler_code_assign extends midcom_baseclasses_components_handler
{
    /**
     * The code to display
     *
     * @var midcom_db_device
     * @access private
     */
    var $_device = null;

    var $_area = false;

    var $_code_pool = array();

    /**
     * The Datamanager of the code to display.
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
        $this->_request_data['code'] =& $this->_device;
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }

    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
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
        $data['actions'] = array
        (
            'noop' => $this->_l10n->get('do nothing'),
            'assign' => $this->_l10n->get('assign a code'),
            'reject' => $this->_l10n->get('reject application'),
        );
    }

    /**
     * Maps area name to country names
     *
     * @param string $area_name name of area to expand
     * @return array of names
     */
    function _expand_areas($area_name)
    {
        $map = $this->_config->get('area_country_map');
        if (   isset($map[$area_name])
            && is_array($map[$area_name]))
        {
            return $map[$area_name];
        }
        return array($area_name);
    }

    function _process_noop(&$applications)
    {
        return array('ok' => count($applications), 'failed' => 0);
    }

    function _process_reject(&$applications)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $stats = array('ok' => 0, 'failed' => 0);
        foreach ($applications as $guid)
        {
            // Reser time limit counter
            set_time_limit(ini_get('max_execution_time'));
            $application = new org_maemo_devcodes_application_dba($guid);
            if (   !$application
                || empty($application->guid))
            {
                debug_add("Could not instantiate application {$guid}", MIDCOM_LOG_ERROR);
                ++$stats['failed'];
                continue;
            }
            if (!$application->reject())
            {
                ++$stats['failed'];
                debug_add("\$application->reject failed for #{$application->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                continue;
            }
            ++$stats['ok'];
        }
        debug_pop();
        return $stats;
    }

    function _assign_code(&$application)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if ($application->code)
        {
            debug_add("Application #{$application->id} already had code set (to #{$application->code})", MIDCOM_LOG_WARN);
            debug_pop();
            return true;
        }
        if (empty($this->_code_pool))
        {
            debug_add('code pool is empty, can\'t assign any more', MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        $code_id = array_shift($this->_code_pool);
        if (empty($code_id))
        {
            debug_add("Got empty code_id ('{$code_id}') from pool. Can't assign that!", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        $application->code = $code_id;
        debug_add("Assigned code #{$application->code} to application #{$application->id}", MIDCOM_LOG_INFO);
        debug_pop();
        return true;
    }

    function _process_assign(&$applications)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $stats = array('ok' => 0, 'failed' => 0);
        foreach ($applications as $guid)
        {
            // Reser time limit counter
            set_time_limit(ini_get('max_execution_time'));
            $application = new org_maemo_devcodes_application_dba($guid);
            if (   !$application
                || empty($application->guid))
            {
                debug_add("Could not instantiate application {$guid}", MIDCOM_LOG_ERROR);
                ++$stats['failed'];
                continue;
            }

            if (!$this->_assign_code($application))
            {
                ++$stats['failed'];
                debug_add("Could not assign a code for #{$application->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                continue;
            }

            if (!$application->accept())
            {
                ++$stats['failed'];
                debug_add("\$application->accept failed for #{$application->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                continue;
            }
            ++$stats['ok'];
        }
        debug_pop();
        return $stats;
    }

    /**
     * Handle actual code display
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_process($handler_id, $args, &$data)
    {
        // sanity-check _POST
        if (   !isset($_POST['org_maemo_devcodes_assign_device'])
            || !isset($_POST['org_maemo_devcodes_assign_area'])
            || !isset($_POST['org_maemo_devcodes_assign_action'])
            || !is_array($_POST['org_maemo_devcodes_assign_action']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Invalid POST parameters');
            // this will exit
        }

        $this->_device =& org_maemo_devcodes_device_dba::get_cached($_POST['org_maemo_devcodes_assign_device']);
        if (  !$this->_device
            || empty($this->_device->guid))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The device '{$_POST['org_maemo_devcodes_assign_device']}' was not found.");
            // This will exit.
        }
        $this->_device->require_do('org.maemo.devcodes:manage');

        $this->_area = $_POST['org_maemo_devcodes_assign_area'];

        // Get pool of available codes
        $mc = org_maemo_devcodes_code_dba::new_collector('device', $this->_device->id);
        $mc->add_value_property('id');
        $mc->add_constraint('recipient', '=', 0);
        $mc->add_constraint('area', '=', $this->_area);
        $mc->add_order('code');
        $mc->execute();
        $mc_keys = $mc->list_keys();
        if (!is_array($mc_keys))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Collector failed critically when getting free code pool');
            // This will exit()
        }
        foreach ($mc_keys as $code_guid => $empty_copy)
        {
            $this->_code_pool[$code_guid] = $mc->get_subkey($code_guid, 'id');
        }

        // Do some array juggling to get counts etc
        $data['object_actions'] = array();
        $data['action_statictics'] = array();
        foreach ($data['actions'] as $action => $title)
        {
            $data['object_actions'][$action] = array();
            $data['action_statictics'][$action] = array( 'ok' => 0, 'failed' => 0);
        }
        foreach ($_POST['org_maemo_devcodes_assign_action'] as $guid => $action)
        {
            $data['object_actions'][$action][] = $guid;
        }
        foreach ($data['object_actions'] as $action => $applications)
        {
            $method = "_process_{$action}";
            if (!method_exists($this, $method))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Don't know how to process action '{$action}'", MIDCOM_LOG_ERROR);
                debug_pop();
                $data['action_statictics'][$action]['failed'] = count($data['object_actions'][$action]);
                continue;
            }
            $data['action_statictics'][$action] = $this->$method($applications);
        }

        $data['title'] = $this->_l10n->get('processed applications');

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "device/{$this->_device->guid}",
            MIDCOM_NAV_NAME => $this->_device->title,
        );

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "code/assign/{$this->_device->guid}",
            MIDCOM_NAV_NAME => sprintf($this->_l10n->get('assign codes for %s'), $this->_device->title),
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "code/assign/process",
            MIDCOM_NAV_NAME => $this->_l10n->get('process'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $this->_prepare_request_data();

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['title']}");

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_process($handler_id, &$data)
    {
        midcom_show_style('assign-codes-statistics');
    }

    /**
     * Handle actual code display
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
        $data['device'] =& $this->_device;

        $this->_area = $args[1];
        $this->_area_title = $this->_area;
        $data['display_country'] = false;
        if ($this->_area === '__ANY__')
        {
            $this->_area_title = $this->_l10n->get('any area');
            $this->_area = '';
            $data['display_country'] = true;
        }
        $data['area'] =& $this->_area;


        $data['applications'] = array();
        $data['device'] =& $this->_device;

        $qb = org_maemo_devcodes_application_dba::new_query_builder();
        $qb->add_constraint('device', '=', $this->_device->id);
        $qb->add_constraint('code', '=', 0);
        $qb->add_constraint('state', '<>', ORG_MAEMO_DEVCODES_APPLICATION_REJECTED);
        if ($this->_area !== '')
        {
            $countries = $this->_expand_areas($this->_area);
            $qb->add_constraint('applicant.country', 'IN', $countries);
        }
        // Order by applicants karma first and application date second
        /* Creates broken SQL in 1.8.4
        $qb->add_order('applicant.metadata.score', 'DESC');
        $qb->add_order('metadata.created', 'ASC');
        */

        //mgd_debug_start();
        $data['applications'] = $qb->execute();
        // See above on why we can't sort on QB level
        uasort($data['applications'], array($this, 'sort_by_applicant_karma'));
        //mgd_debug_stop();

        $data['title'] = sprintf($this->_l10n->get('assign codes for %s in %s'), $this->_device->title, $this->_area_title);
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "device/{$this->_device->guid}",
            MIDCOM_NAV_NAME => $this->_device->title,
        );

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "code/assign/{$this->_device->guid}",
            MIDCOM_NAV_NAME => sprintf($this->_l10n->get('assign codes for %s'), $this->_device->title),
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "code/assign/{$this->_device->guid}/{$args[1]}",
            MIDCOM_NAV_NAME => sprintf($this->_l10n->get('in %s'), $this->_area_title),
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
     * Used to sort the $data['applications'] array by applicants karma
     *
     * Done this way since we can't sort by linked metadata in 1.8.4
     */
    function sort_by_applicant_karma($a, $b)
    {
        /**
         * We need these persons in any case, and collector has issues
         * with getting values from metadata.xxx
         */
        $a_applicant =& org_openpsa_contacts_person_dba::get_cached($a->applicant);
        $a_karma =& $a_applicant->metadata->score;
        $b_applicant =& org_openpsa_contacts_person_dba::get_cached($b->applicant);
        $b_karma =& $b_applicant->metadata->score;
        // Higher karma comes first
        if ($a_karma > $b_karma)
        {
            return -1;
        }
        if ($a_karma < $b_karma)
        {
            return 1;
        }
        // Equal karma means first come -> first serve
        if ($a->metadata->created > $b->metadata->created)
        {
            return 1;
        }
        if ($a->metadata->created < $b->metadata->created)
        {
            return -1;
        }
        // And last we don't know what to do (unlikely...)
        return 0;
    }

    /**
     * Shows the loaded list.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_list($handler_id, &$data)
    {
        $data['prefix'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        if (empty($data['applications']))
        {
            midcom_show_style('assign-codes-noresults');
            return;
        }
        // TODO: page list
        midcom_show_style('assign-codes-header');
        foreach ($data['applications'] as $application)
        {
            $data['application'] =& $application;
            // TODO: DMize ??
            midcom_show_style('assign-codes-item');
        }
        midcom_show_style('assign-codes-footer');
    }

    /**
     * Handle actual code display
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_select_area($handler_id, $args, &$data)
    {
        $this->_device =& org_maemo_devcodes_device_dba::get_cached($args[0]);
        if (  !$this->_device
            || empty($this->_device->guid))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The device '{$args[0]}' was not found.");
            // This will exit.
        }
        $this->_device->require_do('org.maemo.devcodes:manage');
        $data['device'] =& $this->_device;

        $data['areas'] = array
        (
            '__ANY__' => $this->_l10n->get('any area'),
        );
        $data['codes_available'] = array
        (
            '__ANY__' => 0,
        );

        // Use raw collector to be able to set key property
        $mc = new midgard_collector('org_maemo_devcodes_code', 'device', $this->_device->id);
        $mc->set_key_property('area');
        //$mc->add_constraint('recipient', '=', 0);
        $mc->add_order('area', 'ASC');
        mgd_debug_start();
        $mc->execute();
        $keys = $mc->list_keys();
        mgd_debug_stop();
        foreach ($keys as $area => $dummy)
        {
            $area_trimmed = trim($area);
            if ($area_trimmed === '')
            {
                $area_name = '__ANY__';
            }
            else
            {
                $area_name = $area_trimmed;
            }
            if (!isset($data['areas'][$area_name]))
            {
                $data['areas'][$area_name] = $area_trimmed;
            }
            $qb = org_maemo_devcodes_code_dba::new_query_builder();
            $qb->add_constraint('device', '=', $this->_device->id);
            $qb->add_constraint('recipient', '=', 0);
            $qb->add_constraint('area', '=', $area_trimmed);
            $available = $qb->count_unchecked();
            unset($qb);
            $data['codes_available'][$area_name] = $available;
        }

        $data['title'] = sprintf($this->_l10n->get('assign codes for %s'), $this->_device->title);

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "device/{$this->_device->guid}",
            MIDCOM_NAV_NAME => $this->_device->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "code/assign/{$this->_device->guid}",
            MIDCOM_NAV_NAME => $data['title'],
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $this->_prepare_request_data();

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['title']}");


        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_select_area($handler_id, &$data)
    {
        midcom_show_style('assign-codes-countryselector');
    }
}

?>