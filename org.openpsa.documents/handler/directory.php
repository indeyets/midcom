<?php
/**
 * @package org.openpsa.documents
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: directory_handler.php,v 1.10 2006/02/15 14:31:08 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.documents document handler and viewer class.
 *
 * @package org.openpsa.documents
 *
 */
class org_openpsa_documents_handler_directory extends midcom_baseclasses_components_handler
{
    var $_datamanagers = array();

    function __construct()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
        $this->_datamanagers['directory'] = new midcom_helper_datamanager($this->_config->get('schemadb_directory'));
        $this->_datamanagers['document'] = new midcom_helper_datamanager($this->_config->get('schemadb_document'));
    }


    function _creation_dm_callback(&$datamanager)
    {
        // This is what Datamanager calls to actually create a directory
        $result = array 
        (
            "success" => false,
            "storage" => null,
        );

        $topic = new org_openpsa_documents_directory();
        $topic->up = $this->_request_data['directory']->id;
        $topic->component = 'org.openpsa.documents';

        // Set the name by default
        if (array_key_exists('midcom_helper_datamanager_field_extra', $_POST))
        {
            $topic->name = midcom_generate_urlname_from_string($_POST['midcom_helper_datamanager_field_extra']);
        }


        $stat = $topic->create();
        if ($stat)
        {
            $this->_request_data['directory'] = new org_openpsa_documents_directory($topic->id);

            $result["storage"] =& $this->_request_data['directory'];
            $result["success"] = true;
            return $result;
        }
        return null;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['directory']);

        if (!$this->_datamanagers['directory']->init($this->_request_data['directory']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to initialize datamanager for directory.");
            // This will exit
        }

        switch ($this->_datamanagers['directory']->process_form()) 
        {
            case MIDCOM_DATAMGR_EDITING:
                $this->_view = "edit";

                // Add toolbar items
                org_openpsa_helpers_dm_savecancel($this->_toolbars->bottom, $this);

                return true;

            case MIDCOM_DATAMGR_SAVED:
                // TODO: Update the URL name?

                // Update the Index
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanagers['directory']);

                $this->_view = "default";
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
                // This will exit()

            case MIDCOM_DATAMGR_CANCELLED:
                $this->_view = "default";
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
                // This will exit()

            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
        }
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {
        $this->_request_data['directory_dm'] = $this->_datamanagers['directory'];
        midcom_show_style("show-directory-edit");
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_do('midgard:create', $this->_request_data['directory']);

        if (!$this->_datamanagers['directory']->init_creation_mode("default", $this, "_creation_dm_callback"))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to initialize datamanager in creation mode for schema 'default'.");
            // This will exit
        }

        // Add toolbar items
        org_openpsa_helpers_dm_savecancel($this->_toolbars->bottom, $this);

        switch ($this->_datamanagers['directory']->process_form()) 
        {
            case MIDCOM_DATAMGR_CREATING:
                debug_add('First call within creation mode');
                break;

            case MIDCOM_DATAMGR_EDITING:
            case MIDCOM_DATAMGR_SAVED:
                debug_add("First time submit, the DM has created an object");

                // Update the URL name
                // $this->_request_data['directory']->name = midcom_generate_urlname_from_string($this->_request_data['directory']->extra);
                // $this->_request_data['directory']->update();

                // Index the directory
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanagers['directory']);

                // Relocate to the new directory view
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                    . $this->_request_data["directory"]->name. "/");
                break;

            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                debug_add('Cancel without anything being created, redirecting to the welcome screen.');
                $_MIDCOM->relocate('');
                // This will exit

            case MIDCOM_DATAMGR_CANCELLED:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.';
                debug_pop();
                return false;

            case MIDCOM_DATAMGR_FAILED:
            case MIDCOM_DATAMGR_CREATEFAILED:
                debug_add('The DM failed critically, see above.');
                $this->errstr = 'The Datamanager failed to process the request, see the Debug Log for details';
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;

            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method unknown';
                debug_pop();
                return false;

        }

        debug_pop();
        return true;

    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_create($handler_id, &$data)
    {
        $this->_request_data['directory_dm'] = $this->_datamanagers['directory'];
        midcom_show_style("show-directory-new");
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        // Add toolbar items
        if ($_MIDCOM->auth->can_do('midgard:create', $this->_request_data['directory']))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'document/create/',
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('new document'),
                    MIDCOM_TOOLBAR_HELPTEXT => '',
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'create/',
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('new directory'),
                    MIDCOM_TOOLBAR_HELPTEXT => '',
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }
        if ($_MIDCOM->auth->can_do('midgard:update', $this->_request_data['directory']))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'edit/',
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('edit directory'),
                    MIDCOM_TOOLBAR_HELPTEXT => '',
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }
        
        $this->_request_data['prefix'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view($handler_id, &$data)
    {
        midcom_show_style("show-directory-header");
    
        $qb = org_openpsa_documents_document::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_request_data['directory']->id);
        $qb->add_constraint('nextVersion', '=', 0);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_DOCUMENT);

        // Workgroup filtering
        if ($GLOBALS['org_openpsa_core_workgroup_filter'] != 'all')
        {
            debug_push('_show_directory');
            debug_add("Filtering documents by workgroup {$GLOBALS['org_openpsa_core_workgroup_filter']}");
            $qb->add_constraint('orgOpenpsaOwnerWg', '=', $GLOBALS['org_openpsa_core_workgroup_filter']);
            debug_pop();
        }

        $ret = $qb->execute();
        if (   is_array($ret)
            && count($ret) > 0)
        {
            midcom_show_style("show-directory-index-header");
            foreach ($ret as $document)
            {
                $this->_request_data['document'] = $document;
                if ($this->_datamanagers['document']->init($this->_request_data['document']))
                {
                    $this->_request_data['document_dm'] = $this->_datamanagers['document']->get_array();
                    midcom_show_style("show-directory-index-item");
                }
            }
            midcom_show_style("show-directory-index-footer");
        }
        midcom_show_style("show-directory-footer");
    }

}
?>