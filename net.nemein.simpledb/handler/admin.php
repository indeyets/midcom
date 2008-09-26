<?php
/**
 * @package net.nemein.simpledb
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: create.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * simpledb entry administration
 *
 * @package net.nemein.simpledb
 */
class net_nemein_simpledb_handler_admin extends midcom_baseclasses_components_handler
{
    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Callback for the datamanager create mode.
     *
     * @access protected
     */
    function _dm_create_callback(&$datamanager)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $result = Array
        (
            'success' => true,
            'storage' => null,
        );

        $this->_request_data['entry'] = new midcom_db_article();
        $this->_request_data['entry']->topic = $this->_topic->id;
        $this->_request_data['entry']->author = $_MIDCOM->auth->user;
        if (! $this->_request_data['entry']->create())
        {
            debug_add('Could not create article: ' . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_pop();
            return null;
        }

        $result['storage'] =& $this->_request_data['entry'];
        debug_pop();
        return $result;
    }

    /**
     * Internal helper, creates a valid name for a given article. It calls
     * generate_error on any failure.
     *
     * @param midcom_baseclasses_database_article $entry The article to process, if omitted, the currently selected article is used instead.
     * @return midcom_baseclasses_database_article The updated article.
     * @access private
     */
    function _generate_urlname($entry = null)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (!$entry)
        {
            $entry = $this->_request_data['entry'];
        }

        $updated = false;

        $tries = 0;
        $maxtries = 99;
        while(    ! $updated
              && $tries < $maxtries)
        {
            $entry->name = midcom_generate_urlname_from_string($entry->title);
            if ($tries > 0)
            {
                // Append an integer if articles with same name exist
                $entry->name .= sprintf("-%03d", $tries);
            }
            $updated = $entry->update();
            $tries++;
        }

        if (! $updated)
        {
            debug_print_r('Failed to update the Article with a new URL, last article state:', $entry);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not update the article\'s URL Name: ' . mgd_errstr());
            // This will exit()
        }

        debug_pop();
        return $entry;
    }

    function _load_entry($name)
    {
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_constraint('name', '=', $name);
        $entries = $qb->execute();

        if (count($entries) == 0)
        {
            // Try getting with GUID
            $entry = new midcom_db_article($name);

            if (!$entry)
            {
                return false;
                // This will exit
            }
        }
        else
        {
            $entry = $entries[0];
        }
        return $entry;
    }

    function _populate_toolbar()
    {
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "edit/{$this->_request_data['entry']->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_request_data['entry']->can_do('midgard:update'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            )
        );
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "delete/{$this->_request_data['entry']->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('delete'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_request_data['entry']->can_do('midgard:update'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            )
        );

        $_MIDCOM->bind_view_to_object($this->_request_data['entry'], $this->_request_data['schema_name']);
    }

    /**
     * Displays an event creation view.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:create');

        // Initialize sessioning first
        $session = new midcom_service_session();

        if (! $data['datamanager']->init_creation_mode($data['schema_name'], $this))
        {
            $this->errstr = "Failed to initialize the datamanager in creation mode for schema '{$data['schema_name']}'.";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        // Start up the Datamanager in the usual session driven create loop
        // (create mode if session is empty, otherwise regular edit mode)
        if (! $session->exists('admin_create_id'))
        {
            debug_add('We do not currently have a content object, entering creation mode.');
            $data['entry'] = null;
            $create = true;
        }
        else
        {
            $id = $session->get('admin_create_id');
            debug_add("We have found the article id {$id} in the session, loading object and entering regular edit mode.");

            // Try to load the article and to prepare its datamanager.
            $data['entry'] = new midcom_db_article($id);
            if (!$data['datamanager']->init($data['entry']))
            {
                $session->remove('admin_create_id');
                debug_pop();
                return false;
            }
            $create = false;
        }

        // Ok, we have a go.
        switch ($data['datamanager']->process_form())
        {
            case MIDCOM_DATAMGR_CREATING:
                if (! $create)
                {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = 'Method MIDCOM_DATAMANAGER_CREATING unknown for non-creation mode.';
                    debug_pop();
                    return false;
                }
                else
                {
                    debug_add('First call within creation mode');
                    $this->_view = 'create';
                    break;
                }

            case MIDCOM_DATAMGR_EDITING:
                if ($create)
                {
                    $id = $data['entry']->id;
                    debug_add("First time submit, the DM has created an object, adding ID {$id} to session data");
                    $session->set('admin_create_id', $id);
                }
                else
                {
                    debug_add('Subsequent submit, we already have an id in the session space.');
                }
                $this->_view = 'create';
                break;

            case MIDCOM_DATAMGR_SAVED:
                debug_add('Datamanager has saved, relocating to view.');
                if ($data['entry']->name == '')
                {
                    // Empty URL name or missing index article, generate it
                    $this->_request_data['entry'] = $this->_generate_urlname($this->_request_data['entry']);
                }
                $session->remove('admin_create_id');

                // Reindex the article
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($data['datamanager']);
                
                if ($this->_config->get('autoapprove_created'))
                {
                    $metadata = $data['entry']->get_metadata();
                    $metadata->approve();
                }

                $_MIDCOM->relocate("view/{$this->_request_data['entry']->guid}/");
                // This will exit


            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                if (! $create)
                {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED_NONECREATED unknown for non-creation mode.';
                    debug_pop();
                    return false;
                }
                else
                {
                    debug_add('Cancel without anything being created, redirecting to the welcome screen.');
                    $_MIDCOM->relocate('');
                    // This will exit
                }

            case MIDCOM_DATAMGR_CANCELLED:
                if ($create)
                {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.';
                    debug_pop();
                    return false;
                }
                else
                {
                    debug_add('Cancel with a temporary object, deleting it and redirecting to the welcome screen.');
                    if (   ! mgd_delete_extensions($this->_request_data['entry'])
                        || ! $this->_request_data['entry']->delete())
                    {
                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                            'Failed to remove temporary article or its dependants.');
                        // This will exit
                    }
                    $session->remove('admin_create_id');
                    $_MIDCOM->relocate('');
                    // This will exit
                }

            case MIDCOM_DATAMGR_FAILED:
            case MIDCOM_DATAMGR_CREATEFAILED:
                debug_add('The DM failed critically, see above.');
                $this->errstr = 'The Datamanager failed to process the request, see the debug level log for details.';
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;

        }

        $data['view_title'] = sprintf($this->_request_data['l10n_midcom']->get('create %s'), $this->_request_data['l10n']->get($this->_request_data['datamanager']->_layoutdb[$this->_request_data['schema_name']]['description']));
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'create/',
            MIDCOM_NAV_NAME => $data['view_title'],
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * Shows the creation form
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_create($handler_id, &$data)
    {
        midcom_show_style('admin-create');
    }

    /**
     * Displays entry editing view
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $data['entry'] = $this->_load_entry($args[0]);
        if (!$data['entry'])
        {
            return false;
            // This will exit
        }

        $data['entry']->require_do('midgard:update');

        $data['datamanager']->init($data['entry']);

        // Now launch the datamanager processing loop
        switch ($data['datamanager']->process_form())
        {
            case MIDCOM_DATAMGR_EDITING:
                break;

            case MIDCOM_DATAMGR_SAVED:
                if ($data['entry']->name == '')
                {
                    // Empty URL name or missing index article, generate it and
                    // refresh the DM, so that we can index it.
                    $data['entry'] = $this->_generate_urlname($data['entry']);
                    $data['datamanager']->init($data['entry']);
                }

                // Reindex the article
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($data['datamanager']);

                // Redirect to view page.
                $_MIDCOM->relocate("view/{$data['entry']->guid}/");
                // This will exit()

            case MIDCOM_DATAMGR_CANCELLED:
                // Redirect to view page.
                $_MIDCOM->relocate("view/{$data['entry']->guid}/");
                // This will exit()

            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "The Datamanager failed critically while processing the form, see the debug level log for more details.";
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
        }

        $data['view_title'] = sprintf($data['l10n_midcom']->get('edit %s'), $data['entry']->title);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");

        $this->_populate_toolbar();
        $this->_view_toolbar->hide_item("edit/{$data['entry']->guid}/");

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "view/{$data['entry']->guid}/",
            MIDCOM_NAV_NAME => $data['entry']->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "edit/{$data['entry']->guid}/",
            MIDCOM_NAV_NAME => $data['l10n_midcom']->get('edit'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * Shows the editing form
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {
        midcom_show_style('admin-edit');
    }

    /**
     * Displays entry delete view
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $data['entry'] = $this->_load_entry($args[0]);
        if (!$data['entry'])
        {
            return false;
            // This will exit
        }

        $data['entry']->require_do('midgard:delete');

        if (array_key_exists('net_nemein_simpledb_deleteok', $_POST))
        {
            if ($data['entry']->delete())
            {
                // Update the index
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->delete($data['entry']->guid);

                $_MIDCOM->relocate('');
            }
            else
            {
                // Failure, give a message
                $_MIDCOM->uimessages->add($data['l10n']->get('net.nemein.simpledb'), sprintf($data['l10n']->get('failed to delete entry, reason %s'), mgd_errstr()), 'error');
            }
        }

        $data['view_title'] = sprintf($data['l10n_midcom']->get('delete %s'), $data['entry']->title);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");

        $this->_populate_toolbar();
        $this->_view_toolbar->hide_item("delete/{$data['entry']->guid}/");

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "view/{$data['entry']->guid}/",
            MIDCOM_NAV_NAME => $data['entry']->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "delete/{$data['entry']->guid}/",
            MIDCOM_NAV_NAME => $data['l10n_midcom']->get('delete'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * Shows the delete form
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_delete($handler_id, &$data)
    {
        midcom_show_style('admin-delete');
    }
}

?>