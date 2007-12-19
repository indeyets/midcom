<?php
/**
 * @package org.openpsa.contacts
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Person display class
 *
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_handler_person_view extends midcom_baseclasses_components_handler
{
    /**
     * The contact to display
     *
     * @var midcom_db_contact
     * @access private
     */
    var $_contact = null;

    /**
     * Schema to use for contact display
     *
     * @var string
     * @access private
     */
    var $_schema = null;

    /**
     * Simple default constructor.
     */
    function org_openpsa_contacts_handler_person_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['person'] =& $this->_contact;

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "person/edit/{$this->_contact->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_contact->can_do('midgard:update'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            )
        );

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "person/delete/{$this->_contact->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_contact->can_do('midgard:delete'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            )
        );

        $qb = org_openpsa_contacts_buddy::new_query_builder();
        $user = $_MIDCOM->auth->user->get_storage();
        $qb->add_constraint('account', '=', $user->guid);
        $qb->add_constraint('buddy', '=', $this->_request_data['person']->guid);
        $qb->add_constraint('blacklisted', '=', false);
        $buddies = $qb->execute();
        if (count($buddies) > 0)
        {
            // We're buddies, show remove button
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "buddylist/remove/{$this->_request_data['person']->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('remove buddy'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:delete', $buddies[0]),
                )
            );
        }
        else
        {
            // We're not buddies, show add button
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "buddylist/add/{$this->_request_data['person']->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('add buddy'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_person.png',
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:create', $user),
                )
            );
        }

        if ($this->_request_data['person']->username)
        {
            $this->_view_toolbar->add_item
            (
                Array
                (
                    MIDCOM_TOOLBAR_URL => "person/{$this->_request_data['person']->guid}/account_edit.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('edit account'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['person']),
                )
            );
        }
        else
        {
            $this->_view_toolbar->add_item
            (
                Array(
                    MIDCOM_TOOLBAR_URL => "person/{$this->_request_data['person']->guid}/account_create.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('create account'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['person']),
                )
            );
        }
    }

    function _modify_schema()
    {
        if (   isset($GLOBALS['org.openpsa.core:owner_organization_obj'])
            && is_object($GLOBALS['org.openpsa.core:owner_organization_obj']))
        {
            // Figure out if user is from own organization or other org
            $this->_request_data['person_user'] = new midcom_core_user($this->_request_data['person']);

            if (   is_object($this->_request_data['person_user'])
                && method_exists($this->_request_data['person_user'], 'is_in_group')
                && $this->_request_data['person_user']->is_in_group("group:{$GLOBALS['org.openpsa.core:owner_organization_obj']->guid}"))
            {
                $this->_schema = 'employee';
            }
        }

        /*
        foreach ($this->_request_data['schemadb_contact'] as $schema)
        {
            // No need to add components to a component
            if (array_key_exists('components', $schema->fields)
                && (   $this->_contact->orgOpenpsaObtype == ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_COMPONENT
                    || !$this->_config->get('enable_components')
                    )
                )
            {
                unset($schema->fields['components']);
            }
        }
        */
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array();
        /*
        $group = new org_openpsa_contacts_contact_group_dba($this->_contact->contactGroup);
        $parent = $group;
        while ($parent)
        {
            $group = $parent;
            $tmp[] = array
            (
                MIDCOM_NAV_URL => "{$group->guid}/",
                MIDCOM_NAV_NAME => $group->name,
            );
            $parent = $group->get_parent();
        }
        */

        $tmp = array_reverse($tmp);

        $tmp[] = array
        (
            MIDCOM_NAV_URL => "person/{$this->_contact->guid}.html",
            MIDCOM_NAV_NAME => $this->_contact->name,
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Looks up a contact to display.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        $this->_contact = new org_openpsa_contacts_person($args[0]);
        if (!$this->_contact)
        {
            return false;
        }

        $this->_prepare_request_data();
        $this->_modify_schema();

        $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
        $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb_person'];
        $this->_request_data['controller']->set_storage($this->_contact, $this->_schema);
        $this->_request_data['controller']->process_ajax();

        $data['person_rss_url'] = $this->_contact->parameter('net.nemein.rss', 'url');
        if ($data['person_rss_url'])
        {
            // We've autoprobed that this contact has a RSS feed available, link it
            $_MIDCOM->add_link_head
            (
                array(
                    'rel'   => 'alternate',
                    'type'  => 'application/rss+xml',
                    'title' => sprintf($this->_l10n->get('rss feed of person %s'), $this->_contact->name),
                    'href'  => $data['person_rss_url'],
                )
            );
        }

        $_MIDCOM->bind_view_to_object($this->_contact, $this->_request_data['controller']->datamanager->schema->name);
        $this->_update_breadcrumb_line();
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_contact->name}");

        return true;
    }

    /**
     * Shows the loaded contact.
     */
    function _show_view($handler_id, &$data)
    {
        // For AJAX handling it is the controller that renders everything
        $data['view_contact'] = $data['controller']->get_content_html();
        $data['datamanager'] =& $data['controller']->datamanager;

        midcom_show_style('show-person');
    }
}
?>