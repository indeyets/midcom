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
class org_openpsa_directmarketing_handler_message_message extends midcom_baseclasses_components_handler
{
    /**
     * The message which has been created
     *
     * @var org_openpsa_directmarketing_campaign_message
     * @access private
     */
    var $_message = null;

    /**
     * Simple default constructor.
     */
    function org_openpsa_directmarketing_handler_message_message()
    {
        parent::midcom_baseclasses_components_handler();
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
     * Looks up a message to display.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view ($handler_id, $args, &$data)
    {
        $this->_message = new org_openpsa_directmarketing_campaign_message($args[0]);
        if (!$this->_message)
        {
            return false;
            // This will 404
        }
        $this->_campaign = new org_openpsa_directmarketing_campaign($this->_message->campaign);

        $this->_component_data['active_leaf'] = "campaign_{$this->_campaign->id}";

        $this->_load_datamanager();
        $this->_datamanager->autoset_storage($this->_message);

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "message/{$this->_message->guid}/",
            MIDCOM_NAV_NAME => $this->_message->title,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $data['message'] =& $this->_message;
        $data['campaign'] =& $this->_campaign;
        $data['datamanager'] =& $this->_datamanager;

        // Populate the toolbar
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "message/edit/{$this->_message->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
                MIDCOM_TOOLBAR_ENABLED => $this->_message->can_do('midgard:update')
            )
        );
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "message/delete/{$this->_message->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
                MIDCOM_TOOLBAR_ENABLED => $this->_message->can_do('midgard:delete')
            )
        );
        if (   !empty($_MIDCOM->auth->user)
            && !empty($_MIDCOM->auth->user->guid))
        {
            $preview_url = "message/compose/{$this->_message->guid}/{$_MIDCOM->auth->user->guid}.html";
        }
        else
        {
            $preview_url = "message/compose/{$this->_message->guid}.html";
        }
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => $preview_url,
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('preview message'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'p',
                MIDCOM_TOOLBAR_ENABLED => true,
                MIDCOM_TOOLBAR_OPTIONS => array('target' => '_BLANK'),
            )
        );
        $this->_view_toolbar->add_item
        (
            Array(
                MIDCOM_TOOLBAR_URL => "message/report/{$this->_request_data['message']->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("message report"),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ACCESSKEY => 'r',
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        $this->_view_toolbar->add_item
        (
            Array(
                MIDCOM_TOOLBAR_URL => "message/send_test/{$this->_request_data['message']->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("send message to testers"),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mail-send.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        $this->_view_toolbar->add_item
        (
            Array(
                MIDCOM_TOOLBAR_URL => "message/send/{$this->_request_data['message']->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("send message to whole campaign"),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mail-send.png',
                // TODO: Use some othe privilege ?? (and check that on send handler too)
                MIDCOM_TOOLBAR_ENABLED => $this->_message->can_do('midgard:update'),
                MIDCOM_TOOLBAR_OPTIONS => array(
                        'onClick' => "return confirm('" . $this->_request_data['l10n']->get("are you sure you wish to send this to the whole campaign ?") . "')",
                    ),
            )
        );



        // Populate calendar events for the message
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $_MIDCOM->bind_view_to_object($this->_message, $this->_datamanager->schema->name);
        $_MIDCOM->set_26_request_metadata($this->_message->revised, $this->_message->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_message->title}");

        return true;
    }

    /**
     * Shows the loaded message.
     */
    function _show_view ($handler_id, &$data)
    {
        $data['view_message'] = $this->_datamanager->get_content_html();
        midcom_show_style('show-message');
    }
}

?>