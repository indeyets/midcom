<?php
/**
 * @package org.openpsa.directmarketing
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: index.php 4051 2006-09-12 07:32:51Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Discussion forum index
 *
 * @package org.openpsa.directmarketing
 */
class org_openpsa_directmarketing_handler_message_list extends midcom_baseclasses_components_handler
{
    var $_campaign = false;
    var $_list_type = false;

    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Internal helper, loads the datamanager for the current message. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb_message']);

        if (!$this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for messages.");
            // This will exit.
        }
    }

    /**
     * Looks up an message to display.
     */
    function _handler_list ($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_list_type = $args[0];
        $this->_campaign = new org_openpsa_directmarketing_campaign($args[1]);
        $this->_component_data['active_leaf'] = "campaign_{$this->_campaign->id}";
        
        if (   !is_object($this->_campaign)
            || !$this->_campaign->id)
        {
            // TODO: error reporting
            return false;
        }
        $data['campaign'] =& $this->_campaign;
        $this->_load_datamanager();

        return true;
    }

    /**
     * Shows the loaded message.
     */
    function _show_list ($handler_id, &$data)
    {
        debug_add("Instantiating Query Builder for creating message list");
        //$qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_directmarketing_campaign_message');
        $qb = new org_openpsa_qbpager('org_openpsa_directmarketing_campaign_message', 'campaign_messages');
        $qb->results_per_page = 10;
        $qb->add_order('metadata.created', 'DESC');
        $qb->add_constraint('campaign', '=', $this->_campaign->id);

        debug_add("Executing Query Builder");
        $ret = $qb->execute();
        $data['qbpager'] =& $qb;
        midcom_show_style("show-message-list-header");
        if (count($ret) > 0)
        {
            foreach ($ret as $message)
            {
                $this->_datamanager->autoset_storage($message);
                $data['message'] =& $message;
                $data['message_array'] = $this->_datamanager->get_content_html();
                $data['message_class'] = org_openpsa_directmarketing_viewer::get_messagetype_css_class($message->orgOpenpsaObtype);
                midcom_show_style('show-message-list-item');
            }
        }
        midcom_show_style("show-message-list-footer");
    }
}

?>