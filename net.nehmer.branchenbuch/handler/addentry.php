<?php
/**
 * @package net.nehmer.branchenbuch
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Branchenbuch Add New Entry class.
 *
 * This is a multi-step entry procedure:
 *
 * 1st, welcome page. This will be used later to distinguish basic and power user entries.
 *
 * 2nd, category selection listing.
 *
 * 3rd, Actual data entry, built on the account schemas.
 *
 * 4th, Image upload. Image types and sizes are defined in the component config. At this
 * point we'll have to build a temporary object already, so that we can attach the uploaded
 * files.
 *
 * 5th, entry preview and final submit.
 *
 * 6th, everybody-is-happy-now-page(tm).
 *
 * <i>Implementation notes</i>
 *
 * This class makes quite heavy use of sessioning to interconnect the various requests.
 * Step 2 is responsible for pre-initializing the session to a known, empty state. This is
 * doen in 1 not 2 to allow for deep-links directly to the category listing. Unfortunalety,
 * the class is not yet capable of catching deep-links to step three, which would be
 * possible in theory. (Note, that as of 2006-03-02 the auth system will drop any remaining
 * session data during logout, so the chance for side-effects will be lower in general.)
 *
 * @package net.nehmer.branchenbuch
 */

class net_nehmer_branchenbuch_handler_addentry extends midcom_baseclasses_components_handler
{
    /**
     * The category record encaspulating the root (type) category.
     *
     * @var net_nehmer_branchenbuch_branche
     * @access private
     */
    var $_type = null;

    /**
     * The category we are currently adding to.
     *
     * @var net_nehmer_branchenbuch_branche
     * @access private
     */
    var $_branche = null;

    /**
     * net.nehmer.account Remote management interface class instance.
     *
     * @var net_nehmer_account_remote
     * @access private
     */
    var $_remote = null;

    /**
     * The schema manager class encaspulating all schema operations referencing
     * account schemas.
     *
     * @var net_nehmer_branchenbuch_schemamgr
     * @access private
     */
    var $_schemamgr = null;

    /**
     * This is an array holding the computed category list.
     *
     * The elements are indexed by category GUID and contain the following keys:
     *
     * - string localname
     * - string fullname
     * - net_nehmer_branchenbuch_branche category
     * - int entrycount
     * - string guid
     * - string step2_url
     * - int depth
     *
     * @var Array
     * @access private
     */
    var $_category_list = null;

    /**
     * The controller instance used to render the various forms.
     *
     * @var midcom_helper_datamanager2_controller
     * @access private
     */
    var $_controller = null;

    /**
     * The entry created by the DM2 creation controller callback.
     *
     * @var net_nehmer_branchenbuch_entry
     * @access private
     */
    var $_entry = null;

    function net_nehmer_branchenbuch_handler_addentry()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     *
     * @access private
     */
    function _prepare_request_data()
    {
        $this->_request_data['branche'] =& $this->_branche;
        $this->_request_data['type'] =& $this->_type;
        $this->_request_data['remote'] =& $this->_remote;
        $this->_request_data['category_list'] =& $this->_category_list;
        $this->_request_data['controller'] =& $this->_controller;
    }

    /**
     * Helper function called during startup when a handler requires a n.n.account remote
     * control interface. It builds on the schemamgr helper class.
     *
     * @access private
     */
    function _load_account_remote()
    {
        $this->_schemamgr = new net_nehmer_branchenbuch_schemamgr($this->_topic);
        $this->_remote =& $this->_schemamgr->remote;
    }

    /**
     * Shows a welcome page before the actual account setup. Useful to display help messages
     * and the like.
     */
    function _handler_welcome($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $this->_load_account_remote();
        $type = $this->_remote->get_account_type();
        if (! $type)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'The currently active account is not associated with one of the known account types. Cannot continue.');
            // This will exit.
        }
        else
        {
            $this->_type = net_nehmer_branchenbuch_branche::get_root_category_by_type($type);
        }

        $this->_prepare_request_data();

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $data['step1_url'] = "{$prefix}entry/add/1/{$this->_type->guid}.html";

        if ($this->_config->get('allow_add_to_all'))
        {
            $types = $this->_remote->list_account_types();

            $data['other_category_urls'] = Array();
            foreach ($types as $name => $description)
            {
                if ($name == $this->_type->type)
                {
                    // Skip default type
                    continue;
                }

                $type = net_nehmer_branchenbuch_branche::get_root_category_by_type($name);
                $url = "{$prefix}entry/add/1/{$type->guid}.html";
                $data['other_category_urls'][$url] = $type->name;
            }
        }
        else
        {
            $data['other_category_urls'] = null;
        }

        $_MIDCOM->substyle_append($this->_type->type);
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: " . $this->_l10n->get('add entry') . ": {$this->_type->name}");
        $this->_component_data['active_leaf'] = NET_NEHMER_BRANCHENBUCH_LEAFID_ADDENTRY;

        return true;
    }

    /**
     * Shows the welcome page
     */
    function _show_welcome($handler_id, &$data)
    {
        midcom_show_style('addentry-welcome');
    }

    /**
     * Shows a welcome page before the actual account setup. Useful to display help messages
     * and the like.
     */
    function _handler_categoryselect($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $this->_type = new net_nehmer_branchenbuch_branche($args[0]);
        if (   ! $this->_type
            || $this->_type->parent != '')
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "The root category specified is invalid.";
            return false;
        }

        // Clean up sessioning leftovers from previous login sessions.
        $session = new midcom_service_session();
        $session->remove('entry_data');
        $session->remove('branche');
        $session->remove('type');
        $session->remove('temporary_object');

        // Go over the top level categories
        $this->_category_list = Array();

        $categories = $this->_type->list_childs();
        foreach ($categories as $category)
        {
            $childs = $category->list_childs();
            if ($childs)
            {
                foreach($childs as $child_category)
                {
                    $this->_add_category_to_list($child_category, "{$category->name}: ");
                }
            }
            else
            {
                $this->_add_category_to_list($category);
            }
        }

        $this->_prepare_request_data();
        $_MIDCOM->substyle_append($this->_type->type);
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: " . $this->_l10n->get('add entry') . ": {$this->_type->name}");
        $this->_component_data['active_leaf'] = NET_NEHMER_BRANCHENBUCH_LEAFID_ADDENTRY;

        return true;
    }


    /**
     * Internal Helper encaspulating the index call.
     *
     * @param midcom_helper_datamanager2_datamanager $datamanager The DM2 instance to index.
     */
    function _index(&$datamanager)
    {
        $topic = $this->_config->get('index_to');
        if (! $topic)
        {
            $topic = $this->_topic;
        }

        $indexer =& $_MIDCOM->get_service('indexer');
        net_nehmer_branchenbuch_entry::index($datamanager, $indexer, $topic);
    }

    /**
     * This is a helper which adds the specfied catgory to the _category_list. It computes
     * all members that could be helpful for display.
     *
     * @param net_nehmer_branchenbuch_branche $category The category to add.
     * @param string $parent_prefix The string to use as prefix in front of the name to generate
     *     the full category name. This is faster then using the get_full_name function of the
     *     branchen class. If you need any separators like ': ', you nedd to add them yourself.
     */
    function _add_category_to_list($category, $parent_prefix = '')
    {
        $urlprefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_category_list[$category->guid] = Array
        (
            'localname' => $category->name,
            'fullname' => "{$parent_prefix}{$category->name}",
            'guid' => $category->guid,
            'category' => $category,
            'entrycount' => $category->itemcount,
            'step2_url' => "{$urlprefix}entry/add/2/{$category->guid}.html",
            'depth' => ($parent_prefix == '') ? 0 : 1,
        );
    }


    /**
     * Shows the welcome page
     */
    function _show_categoryselect($handler_id, &$data)
    {
        midcom_show_style('addentry-categoryselect-begin');
        foreach ($this->_category_list as $guid => $category)
        {
            $data['category'] =& $this->_category_list[$guid];
            midcom_show_style('addentry-categoryselect-item');
        }
        midcom_show_style('addentry-categoryselect-end');
    }

    /**
     * This is step three of the adding procedure. It will generate a form based on the schema
     * associated with the user account linked to the category set. The data entered will be
     * put into the session storage area, so that no temporary object is needed at this point.
     */
    function _handler_details($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $this->_load_account_remote();
        $this->_branche = new net_nehmer_branchenbuch_branche($args[0]);
        if (! $this->_branche)
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "The branche specified is invalid.";
            return false;
        }
        $this->_type = $this->_branche->get_root_category();

        $_MIDCOM->auth->require_do('midgard:create', $this->_branche);

        // This will shortcut without creating any datamanager to avoid the possibly
        // expensive creation process.
        switch (midcom_helper_datamanager2_formmanager::get_clicked_button())
        {
            case 'previous':
                $_MIDCOM->relocate("entry/add/1/{$this->_type->guid}.html");
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate('entry/add.html');
                // This will exit.
        }

        // Preapre the controller instance and process the form.
        // This call might redirect away from here to the previous / next step.
        $session = new midcom_service_session();
        if ($session->exists('entry_data'))
        {
            $defaults = $session->get('entry_data');
        }
        else
        {
            $defaults = $this->_get_details_defaults_from_account();
        }
        $this->_prepare_controller($this->_prepare_details_schemadb(), $defaults);
        $this->_process_details_form();

        $this->_prepare_request_data();
        $_MIDCOM->substyle_append($this->_type->type);
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: " . $this->_l10n->get('add entry') . ": {$this->_type->name}");
        $this->_component_data['active_leaf'] = NET_NEHMER_BRANCHENBUCH_LEAFID_ADDENTRY;

        return true;
    }

    /**
     * Uses the field_mapping config option to rename all field defaults which are unknown
     * in the currently selected schema. No further checks on validity of the
     * replacement names are done.
     *
     * @param Array $schemadb The schemadb to use.
     * @param Array $defaults The defaults to use.
     * @return Array The processed defaults
     */
    function _process_default_mapping($schemadb, $defaults)
    {
        $new_defaults = Array();
        $mapping = $this->_config->get('field_mapping');
        foreach ($defaults as $fieldname => $value)
        {
            if (   ! array_key_exists($fieldname, $schemadb[$this->_type->type]->fields)
                && array_key_exists($fieldname, $mapping))
            {
                $fieldname = $mapping[$fieldname];
            }
            $new_defaults[$fieldname] = $value;
        }
        return $new_defaults;
    }

    /**
     * Prepares a nullstorage controller used to manage the entry details. The defaults
     * to use have to be passed to the controller instance, as they can come either from
     * the active session or the currently active account, depending on the registration
     * step.
     *
     * @param Array $schemadb The schemadb to use.
     * @param Array $defaults The defaults to use.
     */
    function _prepare_controller($schemadb, $defaults)
    {
        $this->_controller = midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb = $schemadb;
        $this->_controller->schemaname = $this->_type->type;
        $this->_controller->defaults = $this->_process_default_mapping($schemadb, $defaults);
        $this->_controller->callback_object =& $this;
        $session = new midcom_service_session();
        if ($session->exists('temporary_object'))
        {
            // Hack
            // We have two requests, we need to concatenate. This should be refactored
            // to not use submits anymore, but the sessioning now built into the
            // creation mode controller.
            $_REQUEST[$this->_controller->_tmpid_fieldname] = $session->get('temporary_object');
        }

        $this->_controller->initialize();
    }

    /**
     * Little helper which retrieves the defaults for new entries from the
     * account record, and force-sets the category member accordingly.
     *
     */
    function _get_details_defaults_from_account()
    {
        $defaults = $this->_remote->get_defaults_from_account();
        $defaults['category'] = $this->_branche->guid;
        return $defaults;
    }


    /**
     * This function processes the details form, and redirects accordingly either to the
     * next/previous step or cancels the entry registration entirely. When moving on to
     * the next step, the submitted data is kept in the session, along with all neccessary
     * controlling information.
     */
    function _process_details_form()
    {
        if ($this->_controller->process_form() == 'next')
        {
            // Skip step 4 (index is 3) until image upload works.
            $this->_copy_controller_data_to_session();
            $_MIDCOM->relocate('entry/add/4.html');
            // This will exit.
        }
        // Still editing, so we're fine.
    }

    /**
     * This internal helper function will put the storage representaiton of all information
     * currently in place in the controller into an session based array. This can be used later
     * using the _get_data_defaults_from_session to start up a null controller again.
     *
     * It will set the following session keys:
     *
     * - entry_data holds the data array of the entry.
     * - branche holds the guid of the category we're adding to.
     * - type holds the guid of the type of entry we're adding.
     */
    function _copy_controller_data_to_session()
    {
        $data = Array();
        foreach ($this->_controller->datamanager->types as $name => $type)
        {
            $data[$name] = $type->convert_to_storage();
        }

        $session = new midcom_service_session();
        $session->set('entry_data', $data);
        if ($this->_controller->datamanager->storage->object)
        {
            $session->set('temporary_object', $this->_controller->datamanager->storage->object->id);
        }
        $session->set('branche', $this->_branche->guid);
        $session->set('type', $this->_type->guid);
    }

    /**
     * This helper prepares and returns the schema that should be used to edit the entry
     * to-be-submitted. It will tweak a few settings which differ from the account management
     * tool.
     *
     * @todo Implement an override mechanism for required field settings.
     *
     * @return Array The prepared schema database containing the schema for the account type
     *     we are currently working on (based on $this->_type).
     * @access private
     */
    function _prepare_details_schemadb()
    {
        $schema = $this->_schemamgr->get_account_schema($this->_type->type);

        $schema->operations = Array
        (
            'previous' => '',
            'next' => '',
            'cancel' => '',
        );

        return Array($schema->name => $schema);
    }

    /**
     * Renders the details entry form.
     */
    function _show_details($handler_id, &$data)
    {
        midcom_show_style('addentry-details');
    }

    /**
     * This is the confirm step. It will show the entire entry again, and the user has to
     * acknowledge it.
     */
    function _handler_confirm($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        // Verify session information
        $session = new midcom_service_session();
        if (! (   $session->exists('entry_data')
               && $session->exists('branche')
               && $session->exists('type')))
        {
            $this->errstr = "Session information incomplete, cannot continue.";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }

        $this->_load_account_remote();
        $this->_branche = new net_nehmer_branchenbuch_branche($session->get('branche'));
        if (! $this->_branche)
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "The category contained in the session data is invalid.";
            return false;
        }
        $this->_type = new net_nehmer_branchenbuch_branche($session->get('type'));
        if (! $this->_branche)
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "The type contained in the session data is invalid.";
            return false;
        }

        $_MIDCOM->auth->require_do('midgard:create', $this->_branche);

        // This will shortcut without creating any datamanager to avoid the possibly
        // expensive creation process.
        switch (midcom_helper_datamanager2_formmanager::get_clicked_button())
        {
            case 'previous':
                // Skip step 4 (index is 3) until image upload works.
                $_MIDCOM->relocate("entry/add/2/{$this->_branche->guid}.html");
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate('entry/add.html');
                // This will exit.
        }

        // Preapre the controller instance and process the form.
        // This call might redirect away from here to the previous / next step.
        // If we're still editing, we freeze the form to have a simple confirmation
        // dialog.
        $this->_prepare_controller($this->_prepare_confirm_schemadb(), $session->get('entry_data'));
        $this->_process_confirm_form();
        $this->_controller->formmanager->freeze();

        $this->_prepare_request_data();
        $_MIDCOM->substyle_append($this->_type->type);
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: " . $this->_l10n->get('add entry') . ": {$this->_type->name}");
        $this->_component_data['active_leaf'] = NET_NEHMER_BRANCHENBUCH_LEAFID_ADDENTRY;

        return true;
    }

    /**
     * This function processes the details form, and redirects accordingly either to the
     * next/previous step or cancels the entry registration entirely. When moving on to
     * the next step, the submitted data is kept in the session, along with all neccessary
     * controlling information.
     */
    function _process_confirm_form()
    {
        if ($this->_controller->process_form() == 'save')
        {
            $this->_process_created_entry();
            // This will exit.
        }
        // Still editing, so we're fine.
    }

    /**
     * DM2 creation controller callback. Creates a new entry, initializes it. The reference is stored
     * in the class and then returned to DM2.
     */
    function & dm2_create_callback (&$controller)
    {
        // Create a fresh storage object.
        $this->_entry = new net_nehmer_branchenbuch_entry();
        $this->_entry->branche = $this->_branche->guid;
        $this->_entry->type = $this->_branche->type;
        $this->_entry->account = $_MIDCOM->auth->user->guid;

        if (! $this->_entry->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We tried to create this object:', $this->_entry);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create the entry, see the debug level log for more information, last Midgard Error was:' . mgd_errstr());
            // This will exit.
        }

        return $this->_entry;
    }

    /**
     * This helper processes the entry saved during the DM2 process_form operation.
     *
     * It will relocate to the thank-you page on success.
     */
    function _process_created_entry()
    {
        // Copy everything which is in hidden fields now.
        // TODO do this later, at the end of the call,
        // and use the datamanager interface instead.
        $defaults = $this->_get_details_defaults_from_account();
        $need_save = false;
        foreach ($this->_controller->schemadb[$this->_type->type]->fields as $name => $field)
        {
            if ($field['hidden'])
            {
                if ($field['storage']['location'] == 'parameter')
                {
                    $this->_entry->set_parameter($field['storage']['domain'], $name, $defaults[$name]);
                }
                else
                {
                    $this->_entry->$name = $defaults[$name];
                    $need_save = true;
                }
            }
        }
        if ($need_save)
        {
            $this->_entry->update();
        }

        // Update the index
        $this->_index($this->_controller->datamanager);

        // Clean up remaining session data.
        $session = new midcom_service_session();
        $session->remove('entry_data');
        $session->remove('branche');
        $session->remove('type');
        $session->remove('temporary_object');

        $_MIDCOM->relocate("entry/add/5/{$this->_entry->guid}.html");
    }

    /**
     * This helper prepares and returns the schema that should be used to edit the entry
     * to-be-submitted. It will tweak a few settings which differ from the account management
     * tool.
     *
     * @todo Implement an override mechanism for required field settings.
     *
     * @return Array The prepared schema database containing the schema for the account type
     *     we are currently working on (based on $this->_type).
     * @access private
     */
    function _prepare_confirm_schemadb()
    {
        $schema = $this->_schemamgr->get_account_schema($this->_type->type);

        $schema->operations = Array
        (
            'previous' => '',
            'save' => '',
            'cancel' => '',
        );

        return Array($schema->name => $schema);
    }

    /**
     * Renders the confirm entry page.
     */
    function _show_confirm($handler_id, &$data)
    {
        midcom_show_style('addentry-confirm');
    }

    /**
     * Shows a all-ok page.
     */
    function _handler_thanks($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $this->_load_account_remote();
        $type = $this->_remote->get_account_type();
        if (! $type)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'The currently active account is not associated with one of the known account types. Cannot continue.');
            // This will exit.
        }
        else
        {
            $this->_type = net_nehmer_branchenbuch_branche::get_root_category_by_type($type);
        }

        $entry = new net_nehmer_branchenbuch_entry($args[0]);
        if ($entry->account != $_MIDCOM->auth->user->guid)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'The specified GUID is not associated to the current user, cannot continue.');
            // This will exit.
        }
        $this->_request_data['new_entry'] = $entry;

        $this->_prepare_request_data();
        $_MIDCOM->substyle_append($this->_type->type);
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: " . $this->_l10n->get('add entry') . ": {$this->_type->name}");
        $this->_component_data['active_leaf'] = NET_NEHMER_BRANCHENBUCH_LEAFID_ADDENTRY;

        return true;
    }

    /**
     * Shows a all-ok page.
     */
    function _show_thanks($handler_id, &$data)
    {
        midcom_show_style('addentry-thanks');
    }


}

?>