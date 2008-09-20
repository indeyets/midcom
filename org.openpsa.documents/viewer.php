<?php
/**
 * @package org.openpsa.documents
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.14 2006/05/10 16:25:51 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.documents site interface class.
 *
 * Document management and WebDAV file share
 *
 * @package org.openpsa.documents
 */
class org_openpsa_documents_viewer extends midcom_baseclasses_components_request
{

    var $_datamanagers = null;

    /**
     * Constructor.
     */
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);

        // Pass topic to handlers
        $this->_request_data['directory'] = new org_openpsa_documents_directory($this->_topic->id);
        $this->_request_data['enable_versioning'] = $this->_config->get('enable_versioning');

        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();

        // Match /document_metadata/new/choosefolder
        $this->_request_switch['metadata_new_choosefolder'] = array
    (
        'handler' => array('org_openpsa_documents_handler_metadata', 'metadata_new'),
            'fixed_args' => Array('document_metadata', 'new', 'choosefolder'),
        );

        // Match /document_metadata/<document GUID>/action
        $this->_request_switch['metadata_action'] = array
        (
        'handler' => array('org_openpsa_documents_handler_metadata', 'metadata_action'),
            'fixed_args' => 'document_metadata',
            'variable_args' => 2,
        );

        // Match /document_metadata/new
        $this->_request_switch['metadata_new'] = array
    (
        'handler' => array('org_openpsa_documents_handler_metadata', 'metadata_new'),
            'fixed_args' => Array('document_metadata', 'new'),
        );

        // Match /document_metadata/<document GUID>
        $this->_request_switch[] = array
    (
        'handler' => array('org_openpsa_documents_handler_metadata', 'metadata'),
            'fixed_args' => 'document_metadata',
            'variable_args' => 1,
        );

        // Match /edit
        $this->_request_switch[] = array
    (
        'handler' => array('org_openpsa_documents_handler_directory', 'directory_edit'),
            'fixed_args' => 'edit',
        );

        // Match /new
        $this->_request_switch[] = array
    (
        'handler' => array('org_openpsa_documents_handler_directory', 'directory_new'),
            'fixed_args' => 'new',
        );

        // Match /search
        $this->_request_switch[] = array
    (
        'handler' => array('org_openpsa_documents_handler_search', 'search'),
            'fixed_args' => 'search',
        );

        /**
        * URL method disabled until MidCOM bug #235 is fixed
        // Match /filename
        $this->_request_switch[] = array(
            'variable_args' => 1,
            'handler' => 'attachment'
        );
        */

        // Match /
        $this->_request_switch[] = array(
        'handler' => array('org_openpsa_documents_handler_directory', 'directory'),
        );

        // This component uses the PEAR HTML_TreeMenu package, include the handler javascripts
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . "/org.openpsa.core/TreeMenu.js");

    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_attachment($handler_id, $args, &$data)
    {
        // This hook is for direct PUT and GET of files
        return false;
    }

}
?>