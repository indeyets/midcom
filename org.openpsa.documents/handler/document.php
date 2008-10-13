<?php
/**
 * @package org.openpsa.documents
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: document_handler.php,v 1.13 2006/05/10 16:25:51 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.documents document handler and viewer class.
 *
 * @package org.openpsa.documents
 *
 */
class org_openpsa_documents_handler_document extends midcom_baseclasses_components_handler
{
    var $_datamanagers;

    function __construct()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
    $this->_datamanagers['document'] = new midcom_helper_datamanager($this->_config->get('schemadb_document'));
    }

    function _load_document($guid)
    {
        $document = new org_openpsa_documents_document_dba($guid);
        if (!is_object($document))
        {
            return false;
        }
        /*if ($document->topic != $this->_request_data['directory']->id)
        {
            return false;
        }*/
        $this->_datamanagers['document']->init($this->_request_data['document']);

        return $document;
    }

    function _creation_dm_callback(&$datamanager)
    {
        // This is what Datamanager calls to actually create a person
        $result = array (
            "success" => false,
            "storage" => null,
        );

        $document = new org_openpsa_documents_document_dba();
        $document->topic = $this->_request_data['directory']->id;
        $document->orgOpenpsaAccesstype = ORG_OPENPSA_ACCESSTYPE_WGPRIVATE;

        if (! $document->create())
        {
            // Add some logging here?
            return null;
        }

        $this->_request_data['document'] = new org_openpsa_documents_document_dba($document->id);
        $rel_ret = org_openpsa_relatedto_handler::on_created_handle_relatedto($this->_request_data['document'], 'org.openpsa.documents');
        debug_add("org_openpsa_relatedto_handler returned \n===\n" . sprint_r($rel_ret) . "===\n");
        $result["storage"] =& $this->_request_data['document'];
        $result["success"] = true;
        return $result;
    }

    function _find_document_nodes($topic_id, $prefix = '')
    {
        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('up', '=', $topic_id);
        $qb->add_constraint('component', '=', 'org.openpsa.documents');
        $topics = $qb->execute();
        foreach ($topics as $topic)
        {
            $this->_request_data['folders'][$topic->id] = "{$prefix}{$topic->extra}";
            $this->_find_document_nodes($topic->id, "{$prefix}&nbsp;&nbsp;");
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_action($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        // Check if we get the document
        if (!$this->_handler_view($handler_id, $args, &$data, false))
        {
            return false;
        }

        // Check if the action is a valid one
        $this->_request_data['document_action'] = $args[1];
        switch ($args[1])
        {
            case "listview":
                $this->_view = "listview";
                return true;
            case "delete":
                $_MIDCOM->auth->require_do('midgard:delete', $this->_request_data['document']);
                $this->_view = 'delete';

                $this->_request_data['delete_succeeded'] = false;
                if (array_key_exists('org_openpsa_documents_deleteok', $_POST))
                {
                    $this->_request_data['delete_succeeded'] = midcom_helper_purge_object($this->_request_data['document']);
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
                    $indexer->delete($this->_request_data['document']->guid);
                }
                else
                {
                    $this->_view_toolbar->add_item
                    (
                        array
                        (
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
                    $this->_view_toolbar->add_item
                    (
                        array
                        (
                            MIDCOM_TOOLBAR_URL => 'document/'.$this->_request_data['document']->guid.'/',
                            MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("cancel"),
                            MIDCOM_TOOLBAR_HELPTEXT => null,
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/cancel.png',
                            MIDCOM_TOOLBAR_ENABLED => true,
                        )
                    );
                }
                return true;
            case "edit":
                $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['document']);

                // Handle versioning of the attachment
                // TODO: Move this to the DBA wrapper class when DM datatype_blob behaves better
                if (   $this->_request_data['enable_versioning']
                    && array_key_exists('midcom_helper_datamanager__document_delete', $_POST))
                {
                    $this->_request_data['document']->backup_version();
                }

                switch ($this->_datamanagers['document']->process_form()) 
                {
                    case MIDCOM_DATAMGR_EDITING:
                        $this->_view = "edit";

                        // Add toolbar items
                        org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);

                        return true;

                    case MIDCOM_DATAMGR_SAVED:
                        // Update the Index
                        $indexer =& $_MIDCOM->get_service('indexer');
                        $indexer->index($this->_datamanagers['document']);

                        $this->_view = "default";
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "document/" . $this->_request_data["document"]->guid . "/");
                        // This will exit()

                    case MIDCOM_DATAMGR_CANCELLED:
                        $this->_view = "default";
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "document/" . $this->_request_data["document"]->guid . "/");
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

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_action($handler_id, &$data)
    {
        switch($this->_view)
        {
            case 'listview':
                $this->_request_data['document_dm'] = $this->_datamanagers['document']->get_array();
                midcom_show_style("show-document-listview");
                break;
            case 'delete':
                $this->_request_data['document_dm'] = $this->_datamanagers['document'];
                midcom_show_style("show-document-delete");
                break;
            default:
                $this->_request_data['document_dm'] = $this->_datamanagers['document'];
                midcom_show_style("show-document-edit");
                break;
        }
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

        if ($handler_id == 'document-new-choosefolder')
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
            org_openpsa_helpers_schema_modifier($this->_datamanagers['document'], 'topic', 'description', $this->_request_data['l10n']->get('folder'), 'newdocument');
            org_openpsa_helpers_schema_modifier($this->_datamanagers['document'], 'topic', 'location', 'topic', 'newdocument');
            org_openpsa_helpers_schema_modifier($this->_datamanagers['document'], 'topic', 'datatype', 'integer', 'newdocument');
            org_openpsa_helpers_schema_modifier($this->_datamanagers['document'], 'topic', 'widget', 'select', 'newdocument');
            org_openpsa_helpers_schema_modifier($this->_datamanagers['document'], 'topic', 'widget_select_choices', $this->_request_data['folders'], 'newdocument');
        }

        if (!$this->_datamanagers['document']->init_creation_mode("newdocument", $this, "_creation_dm_callback"))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to initialize datamanager in creation mode for schema 'newdocument'.");
            // This will exit
        }

        // Add toolbar items
        org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);

        switch ($this->_datamanagers['document']->process_form()) 
        {
            case MIDCOM_DATAMGR_CREATING:
                debug_add('First call within creation mode');
                break;

            case MIDCOM_DATAMGR_EDITING:
            case MIDCOM_DATAMGR_SAVED:
                debug_add("First time submit, the DM has created an object");
                // Change schema setting
                $this->_request_data['document']->parameter("midcom.helper.datamanager","layout","default");

                // Index the document
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanagers['document']);

                // Relocate to document view
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                    . "document/" . $this->_request_data["document"]->guid. "/");
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
        $this->_request_data['document_dm'] = $this->_datamanagers['document'];
        midcom_show_style("show-document-new");
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view($handler_id, $args, &$data, $add_toolbar = true)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $_MIDCOM->auth->require_valid_user();
        // Get the requested document object
        $this->_request_data['document'] = $this->_load_document($args[0]);
        if (!$this->_request_data['document'])
        {
            debug_pop();
            return false;
        }

        // Add toolbar items
        if (   $add_toolbar
            && $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['document']))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "document/{$this->_request_data['document']->guid}/edit/",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('edit'),
                    MIDCOM_TOOLBAR_HELPTEXT => '',
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }
        if (   $add_toolbar
            && $_MIDCOM->auth->can_do('midgard:delete', $this->_request_data['document']))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "document/{$this->_request_data['document']->guid}/delete/",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('delete'),
                    MIDCOM_TOOLBAR_HELPTEXT => '',
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }

        // Load the document to datamanager
        if (!$this->_datamanagers['document']->init($this->_request_data['document']))
        {
            debug_add('Failed to initialize the datamanager, see debug level log for more information.', MIDCOM_LOG_ERROR);
            debug_print_r('DM instance was:', $this->_datamanagers['document']);
            debug_print_r('Object to be used was:', $this->_request_data['document']);
            debug_pop();
            return false;
        }

        // Get list of older versions
        $this->_request_data['document_versions'] = array();
        $qb = org_openpsa_documents_document_dba::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_request_data['directory']->id);
        $qb->add_constraint('nextVersion', '=', $this->_request_data['document']->id);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_DOCUMENT);
        $ret = $qb->execute();
        if (   is_array($ret)
            && count($ret) > 0)
        {
            foreach ($ret as $document)
            {
                $this->_request_data['document_versions'][$document->guid] = $document;
            }
        }

        $GLOBALS['midcom_component_data']['org.openpsa.documents']['active_leaf'] = $this->_request_data['document']->id;

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/midcom.helper.datamanager/columned..css",
            )
        );

        debug_pop();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view($handler_id, &$data)
    {
        $this->_request_data['document_dm'] = $this->_datamanagers['document'];
        midcom_show_style("show-document");
    }

}
?>