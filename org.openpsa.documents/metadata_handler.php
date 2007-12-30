<?php
/**
 * @package org.openpsa.documents
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: metadata_handler.php,v 1.13 2006/05/10 16:25:51 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.documents metadata handler and viewer class.
 *
 * @package org.openpsa.documents
 *
 */
class org_openpsa_documents_metadata_handler
{
    var $_datamanagers;
    var $_request_data;

    function org_openpsa_documents_metadata_handler(&$datamanagers, &$request_data)
    {
        $this->_datamanagers =& $datamanagers;
        $this->_request_data =& $request_data;
    }

    function _load_metadata($guid)
    {
        $document = new org_openpsa_documents_document($guid);
        if (!is_object($document))
        {
            return false;
        }
        /*if ($document->topic != $this->_request_data['directory']->id)
        {
            return false;
        }*/
        $this->_datamanagers['metadata']->init($this->_request_data['metadata']);

        return $document;
    }

    function _creation_dm_callback(&$datamanager)
    {
        // This is what Datamanager calls to actually create a person
        $result = array (
            "success" => false,
            "storage" => null,
        );

        $document = new org_openpsa_documents_document();
        $document->topic = $this->_request_data['directory']->id;
        $document->orgOpenpsaAccesstype = ORG_OPENPSA_ACCESSTYPE_WGPRIVATE;

        if (! $document->create())
        {
            // Add some logging here?
            return null;
        }

        $this->_request_data['metadata'] = new org_openpsa_documents_document($document->id);
        $rel_ret = org_openpsa_relatedto_handler::on_created_handle_relatedto($this->_request_data['metadata'], 'org.openpsa.documents');
        debug_add("org_openpsa_relatedto_handler returned \n===\n" . sprint_r($rel_ret) . "===\n");
        $result["storage"] =& $this->_request_data['metadata'];
        $result["success"] = true;
        return $result;
    }

    function _find_document_nodes($topic_id, $prefix = '')
    {
        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('up', '=', $topic_id);
        $topics = $qb->execute();
        foreach ($topics as $topic)
        {
            if ($topic->parameter('midcom', 'component') == 'org.openpsa.documents')
            {
                $this->_request_data['folders'][$topic->id] = "{$prefix}{$topic->extra}";

                $this->_find_document_nodes($topic->id, "{$prefix}&nbsp;&nbsp;");
            }
        }
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_metadata_action($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        // Check if we get the document metadata
        if (!$this->_handler_metadata($handler_id, $args, &$data, false))
        {
            return false;
        }

        // Check if the action is a valid one
        $this->_request_data['metadata_action'] = $args[1];
        switch ($args[1])
        {
            case "listview":
                $this->_view = "listview";
                return true;
            case "delete":
                $_MIDCOM->auth->require_do('midgard:delete', $this->_request_data['metadata']);
                $this->_view = 'delete';

                $this->_request_data['delete_succeeded'] = false;
                if (array_key_exists('org_openpsa_documents_deleteok', $_POST))
                {
                    $this->_request_data['delete_succeeded'] = midcom_helper_purge_object($this->_request_data['metadata']);
                    if ($this->_request_data['delete_succeeded'])
                    {
                        // Redirect to the directory
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
                    } else {
                        // Failure, give a message
                        $messagebox = new org_openpsa_helpers_uimessages();
                        $messagebox->addMessage($this->_request_data['l10n']->get("failed to delete document, reason ").mgd_errstr(), 'error');
                    }
                    // Update the index
                    $indexer =& $_MIDCOM->get_service('indexer');
                    $indexer->delete($this->_request_data['metadata']->guid);
                }
                else
                {
                    $this->_view_toolbar->add_item(
                        Array(
                            MIDCOM_TOOLBAR_URL => 'javascript:document.getElementById("org_openpsa_contacts_document_deleteform").submit();',
                            MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("delete"),
                            MIDCOM_TOOLBAR_HELPTEXT => null,
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                            MIDCOM_TOOLBAR_ENABLED => true,
                            MIDCOM_TOOLBAR_OPTIONS  => Array(
                                'rel' => 'directlink',
                            ),
                        )
                    );
                    $this->_view_toolbar->add_item(
                        Array(
                            MIDCOM_TOOLBAR_URL => 'document_metadata/'.$this->_request_data['metadata']->guid.'/',
                            MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("cancel"),
                            MIDCOM_TOOLBAR_HELPTEXT => null,
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/cancel.png',
                            MIDCOM_TOOLBAR_ENABLED => true,
                        )
                    );
                }
                return true;
            case "edit":
                $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['metadata']);

                // Handle versioning of the attachment
                // TODO: Move this to the DBA wrapper class when DM datatype_blob behaves better
                if (   $this->_request_data['enable_versioning']
                    && array_key_exists('midcom_helper_datamanager__document_delete', $_POST))
                {
                    $this->_request_data['metadata']->backup_version();
                }

                switch ($this->_datamanagers['metadata']->process_form()) {
                    case MIDCOM_DATAMGR_EDITING:
                        $this->_view = "edit";

                        // Add toolbar items
                        org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);

                        return true;

                    case MIDCOM_DATAMGR_SAVED:
                        // Update the Index
                        $indexer =& $_MIDCOM->get_service('indexer');
                        $indexer->index($this->_datamanagers['metadata']);

                        $this->_view = "default";
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "document_metadata/" . $this->_request_data["metadata"]->guid. "/");
                        // This will exit()

                    case MIDCOM_DATAMGR_CANCELLED:
                        $this->_view = "default";
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "document_metadata/" . $this->_request_data["metadata"]->guid. "/");
                        // This will exit()

                    case MIDCOM_DATAMGR_FAILED:
                        $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                        $this->errcode = MIDCOM_ERRCRIT;
                        return false;
                }

                return true;
            default:
                return false;
        }
    }

    function _show_metadata_action($handler_id, &$data)
    {
        switch($this->_view)
        {
            case 'listview':
                $this->_request_data['metadata_dm'] = $this->_datamanagers['metadata']->get_array();
                midcom_show_style("show-metadata-listview");
                break;
            case 'delete':
                $this->_request_data['metadata_dm'] = $this->_datamanagers['metadata'];
                midcom_show_style("show-metadata-delete");
                break;
            default:
                $this->_request_data['metadata_dm'] = $this->_datamanagers['metadata'];
                midcom_show_style("show-metadata-edit");
                break;
        }
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_metadata_new($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_do('midgard:create', $this->_request_data['directory']);

        if ($handler_id == 'metadata_new_choosefolder')
        {
            $this->_request_data['folders'] = Array();
            $first_documents_node = midcom_helper_find_node_by_component('org.openpsa.documents');
            if ($first_documents_node)
            {
                $this->_find_document_nodes($first_documents_node[MIDCOM_NAV_OBJECT]->up);
            }
            else
            {
                $this->_find_document_nodes($this->_request_data['directory']->id);
            }
            org_openpsa_helpers_schema_modifier($this->_datamanagers['metadata'], 'topic', 'description', $this->_request_data['l10n']->get('folder'), 'newdocument');
            org_openpsa_helpers_schema_modifier($this->_datamanagers['metadata'], 'topic', 'location', 'topic', 'newdocument');
            org_openpsa_helpers_schema_modifier($this->_datamanagers['metadata'], 'topic', 'datatype', 'integer', 'newdocument');
            org_openpsa_helpers_schema_modifier($this->_datamanagers['metadata'], 'topic', 'widget', 'select', 'newdocument');
            org_openpsa_helpers_schema_modifier($this->_datamanagers['metadata'], 'topic', 'widget_select_choices', $this->_request_data['folders'], 'newdocument');
        }

        if (!$this->_datamanagers['metadata']->init_creation_mode("newdocument",$this,"_creation_dm_callback"))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to initialize datamanager in creation mode for schema 'newdocument'.");
            // This will exit
        }

        // Add toolbar items
        org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);

        switch ($this->_datamanagers['metadata']->process_form()) {
            case MIDCOM_DATAMGR_CREATING:
                debug_add('First call within creation mode');
                break;

            case MIDCOM_DATAMGR_EDITING:
            case MIDCOM_DATAMGR_SAVED:
                debug_add("First time submit, the DM has created an object");
                // Change schema setting
                $this->_request_data['metadata']->parameter("midcom.helper.datamanager","layout","default");

                // Index the document
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanagers['metadata']);

                // Relocate to document view
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                    . "document_metadata/" . $this->_request_data["metadata"]->guid. "/");
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

    function _show_metadata_new($handler_id, &$data)
    {
        $this->_request_data['metadata_dm'] = $this->_datamanagers['metadata'];
        midcom_show_style("show-metadata-new");
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_metadata($handler_id, $args, &$data, $add_toolbar = true)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $_MIDCOM->auth->require_valid_user();
        // Get the requested document metadata object
        $this->_request_data['metadata'] = $this->_load_metadata($args[0]);
        if (!$this->_request_data['metadata'])
        {
            debug_pop();
            return false;
        }

        // Add toolbar items
        if (   $add_toolbar
            && $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['metadata']))
        {
            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "document_metadata/{$this->_request_data['metadata']->guid}/edit.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('edit'),
                    MIDCOM_TOOLBAR_HELPTEXT => '',
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }
        if (   $add_toolbar
            && $_MIDCOM->auth->can_do('midgard:delete', $this->_request_data['metadata']))
        {
            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "document_metadata/{$this->_request_data['metadata']->guid}/delete.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('delete'),
                    MIDCOM_TOOLBAR_HELPTEXT => '',
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }

        // Load the document to datamanager
        if (!$this->_datamanagers['metadata']->init($this->_request_data['metadata']))
        {
            debug_add('Failed to initialize the datamanager, see debug level log for more information.', MIDCOM_LOG_ERROR);
            debug_print_r('DM instance was:', $this->_datamanagers['metadata']);
            debug_print_r('Object to be used was:', $this->_request_data['metadata']);
            debug_pop();
            return false;
        }

        // Get list of older versions
        $this->_request_data['metadata_versions'] = array();
        $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_documents_document');
        $qb->add_constraint('topic', '=', $this->_request_data['directory']->id);
        $qb->add_constraint('nextVersion', '=', $this->_request_data['metadata']->id);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_DOCUMENT);
        $ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
        if (   is_array($ret)
            && count($ret) > 0)
        {
            foreach ($ret as $document)
            {
                $this->_request_data['metadata_versions'][$document->guid] = $document;
            }
        }

        $GLOBALS['midcom_component_data']['org.openpsa.documents']['active_leaf'] = $this->_request_data['metadata']->id;
        debug_pop();
        return true;
    }

    function _show_metadata($handler_id, &$data)
    {
        $this->_request_data['metadata_dm'] = $this->_datamanagers['metadata'];
        midcom_show_style("show-metadata");
    }

}
?>