<?php
/**
 * @package net.nehmer.account
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: edit.php 11541 2007-08-10 10:02:57Z adrenalin $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Account Management handler class: List users by karma
 *
 * @package net.nehmer.account
 */

class net_nehmer_account_handler_list extends midcom_baseclasses_components_handler
{
    function net_nehmer_account_handler_list()
    {
        parent::midcom_baseclasses_components_handler();

        $_MIDCOM->load_library('org.openpsa.qbpager');
    }

    /**
     * This handler loads the account, validates permissions and starts up the
     * datamanager.
     *
     * This handler is responsible for both admin and user modes, distinguishing it
     * by the handler id (admin_edit vs. edit). In admin mode, admin privileges are
     * required unconditionally, the id/guid of the record to-be-edited is expected
     * in $args[0].
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        if (!$this->_config->get('allow_list'))
        {
            return false;
        }

        $qb = new org_openpsa_qbpager('midcom_db_person', 'net_nehmer_account_list');
        $data['qb'] =& $qb;
        $qb->add_order('metadata.score', 'DESC');
        $data['users'] = $qb->execute();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'list/',
            MIDCOM_NAV_NAME => $this->_l10n->get('user list'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        $this->_view_toolbar->hide_item('list/');

        $_MIDCOM->set_pagetitle($this->_l10n->get('user list'));

        return true;
    }

    /**
     * The rendering code consists of a standard init/loop/end construct.
     */
    function _show_list($handler_id, &$data)
    {
        midcom_show_style('show-list-header');

        foreach ($data['users'] as $user)
        {
            $data['user'] =& $user;
            midcom_show_style('show-list-item');
        }

        midcom_show_style('show-list-footer');
    }
}
?>