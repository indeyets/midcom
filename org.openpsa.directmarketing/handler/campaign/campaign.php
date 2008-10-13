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
class org_openpsa_directmarketing_handler_campaign_campaign extends midcom_baseclasses_components_handler
{
    /**
     * The campaign which has been created
     *
     * @var org_openpsa_directmarketing_campaign
     * @access private
     */
    var $_campaign = null;

    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Internal helper, loads the datamanager for the current campaign. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb_campaign']);

        if (!$this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for campaigns.");
            // This will exit.
        }
    }

    /**
     * Looks up an campaign to display.
     */
    function _handler_view ($handler_id, $args, &$data)
    {
        $this->_campaign = new org_openpsa_directmarketing_campaign($args[0]);
        if (!$this->_campaign)
        {
            return false;
            // This will 404
        }

        $_MIDCOM->load_library('org.openpsa.qbpager');
        $_MIDCOM->load_library('org.openpsa.contactwidget');

        $this->_load_datamanager();
        $this->_datamanager->autoset_storage($this->_campaign);
        
        $this->_component_data['active_leaf'] = "campaign_{$this->_campaign->id}";

        $this->_request_data['campaign'] =& $this->_campaign;
        $this->_request_data['datamanager'] =& $this->_datamanager;

        // Populate the toolbar
        // TODO: Copy message-related items
        
        if ($this->_campaign->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_CAMPAIGN_SMART)
        {
            //Edit query parameters button in case 1) not in edit mode 2) is smart campaign 3) can edit
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "campaign/edit_query/{$this->_campaign->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit rules'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/repair.png',
                    MIDCOM_TOOLBAR_ENABLED => $this->_campaign->can_do('midgard:update'),
                )
            );
        }
        else
        {
            // Import button if we have permissions to create users
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "campaign/import/{$this->_campaign->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('import subscribers'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_people.png',
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_user_do('midgard:create', null, 'midcom_db_person'),
                )
            );
        }
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "campaign/export/csv/{$this->_campaign->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('export as csv'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_data-edit-table.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );        
        
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "campaign/edit/{$this->_campaign->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
                MIDCOM_TOOLBAR_ENABLED => $this->_campaign->can_do('midgard:update')
            )
        );
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "campaign/delete/{$this->_campaign->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
                MIDCOM_TOOLBAR_ENABLED => $this->_campaign->can_do('midgard:delete')
            )
        );
        foreach ($data['schemadb_message'] as $name => $schema)
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "message/create/{$this->_campaign->guid}/{$name}.html",
                    MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('new %s'), $this->_l10n->get($schema->description)),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/' . org_openpsa_directmarketing_viewer::get_messagetype_icon($schema->customdata['org_openpsa_directmarketing_messagetype']),
                    MIDCOM_TOOLBAR_ENABLED => $this->_campaign->can_do('midgard:create'),
                )
            );
        }
        
        // Populate calendar events for the campaign
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $_MIDCOM->bind_view_to_object($this->_campaign, $this->_datamanager->schema->name);
        $_MIDCOM->set_26_request_metadata($this->_campaign->metadata->revised, $this->_campaign->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_campaign->title}");

        return true;
    }

    /**
     * Shows the loaded campaign.
     */
    function _show_view ($handler_id, &$data)
    {
        $data['view_campaign'] = $this->_datamanager->get_content_html();
        
        // List members of this campaign
        $qb = new org_openpsa_qbpager_direct('midcom_org_openpsa_campaign_member', 'campaign_members');
        $qb->add_constraint('campaign', '=', $data['campaign']->id);
        $qb->add_constraint('orgOpenpsaObtype', '<>', ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER);
        $qb->add_constraint('orgOpenpsaObtype', '<>', ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_UNSUBSCRIBED);
        
        // Set the order
        $qb->add_order('person.lastname', 'ASC');
        $qb->add_order('person.firstname', 'ASC');
        $qb->add_order('person.username', 'ASC');
        $qb->add_order('person.id', 'ASC');
        
        $data['campaign_members_qb'] =& $qb;
        $data['memberships'] = $qb->execute_unchecked();
        $data['campaign_members_count'] =  $qb->count_unchecked();

        $data['campaign_members'] = array();
        if (!empty($data['memberships']))
        {
            foreach ($data['memberships'] as $k => $membership)
            {
                $data['campaign_members'][$k] = new midcom_baseclasses_database_person($membership->person);
            }
        }

        midcom_show_style('show-campaign');
    }
}

?>