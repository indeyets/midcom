<?php

/**
 * @package net.nemein.internalorders
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: viewer.php,v 1.3.2.7 2005/11/07 18:57:45 bergius Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar Viewer interface class.
 *
 * @package net.nemein.internalorders
 */
class net_nemein_internalorders_handler_reports extends midcom_baseclasses_components_handler
{

    /**
     * The root event to use with this topic.
     *
     * @var midcom_baseclasses_database_event
     * @access private
     */

    function __construct()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
        if (is_null($this->_config->get('root_event')))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Component is not properly initialized, root event missing");
        }

        $this->_root_event = mgd_get_object_by_guid($this->_config->get('root_event'));
        if (!$this->_root_event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Root event not found: ".mgd_errstr());
        }

        $this->_is_admin = false;

        $group = mgd_get_object_by_guid($this->_config->get('admin_group'));
        $persons_list = mgd_list_members($group->id);
        while( $persons_list->fetch() )
        {
            $tmp_person = mgd_get_person($persons_list->uid);
            if ($tmp_person->id == $_MIDGARD['user'])
            {
                $this->_is_admin = true;
            }
        }


        // TODO: List user's own orders and incoming orders
        $this->_request_data['open'] = 0;
        $this->_request_data['sent'] = 0;
        $this->_request_data['closed'] = 0;
        $this->_request_data['removed'] = 0;


        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->begin_group('OR');
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT_LOCKED);
        $QB->end_group();
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $this->_request_data['sent'] = $QB->count();

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_NEW);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $this->_request_data['open'] = $QB->count();

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_RECEIVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $this->_request_data['closed'] = $QB->count();

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_REMOVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $this->_request_data['removed'] = $QB->count();

    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report($handler_id, $args, &$data)
    {
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/',
            MIDCOM_NAV_NAME => 'Raportit',
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', array_reverse($tmp));

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report($handler_id, &$data)
    {
        midcom_show_style('show_report');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_places($handler_id, $args, &$data)
    {

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/',
            MIDCOM_NAV_NAME => 'Raportit',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_places/',
            MIDCOM_NAV_NAME => 'Toimipaikoittain',
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $this->_request_data['by_sent'] = array();
        $this->_request_data['by_receive'] = array();

        $this->_request_data['by_sent_not_received'] = array();
        $this->_request_data['by_receive_not_received'] = array();

        $this->_request_data['by_sent_deleted'] = array();
        $this->_request_data['by_receive_deleted'] = array();

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_RECEIVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_order('creator', 'ASC');
        $orders = $QB->execute();

        foreach($orders as $order)
        {
            $order = mgd_get_event($order->id);
            $this->_request_data['by_sent'][$order->creator][] = $order;
            $this->_request_data['by_receive'][$order->extra][] = $order;
        }

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_REMOVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_order('creator', 'ASC');
        $orders = $QB->execute();

        foreach($orders as $order)
        {
            $order = mgd_get_event($order->id);
            $this->_request_data['by_sent_deleted'][$order->creator][] = $order;
            $this->_request_data['by_receive_deleted'][$order->extra][] = $order;
        }


        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->begin_group('OR');
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT_LOCKED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->end_group();
        $QB->add_order('created', 'ASC');
        $orders = $QB->execute();

        foreach($orders as $order)
        {
            $order = mgd_get_event($order->id);
            $this->_request_data['by_sent_not_received'][$order->creator][] = $order;
            $this->_request_data['by_receive_not_received'][$order->extra][] = $order;
        }

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_places($handler_id, &$data)
    {
        midcom_show_style('show_report_by_places');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_places_sent($handler_id, $args, &$data)
    {
        $this->_request_data['detail'] = array();
        $this->_request_data['person'] = $args[0];
        $this->_request_data['sent_receive'] = 0;
        $this->_request_data['link'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."report/by_places/sent_export/".$args[0].".html";
        $person = mgd_get_person($args[0]);

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_RECEIVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_constraint('creator', '=', $person->id);
        $QB->add_order('created', 'ASC');
        $orders = $QB->execute();

        foreach($orders as $order)
        {
            $order = mgd_get_event($order->id);
            $this->_request_data['detail'][] = $order;
        }

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/',
            MIDCOM_NAV_NAME => 'Raportit',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_places/',
            MIDCOM_NAV_NAME => 'Toimipaikoittain',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_places/sent/',
            MIDCOM_NAV_NAME => 'Vastaanotetut, Lähettäjänä '. $person->name,
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_places_sent($handler_id, &$data)
    {
        midcom_show_style('show_report_by_places_detail');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_places_sent_export($handler_id, $args, &$data)
    {


        $headers = array
        (
            'Content-type: application/vnd.ms-excel',
            "Content-disposition: attachment; filename=report_sent.xls",
        );

        foreach ($headers as $header)
        {
            $_MIDCOM->header($header);
        }
        $_MIDCOM->skip_page_style = true;

        $this->_request_data['detail'] = array();
        $this->_request_data['person'] = $args[0];
        $this->_request_data['sent_receive'] = 0;
        $this->_request_data['link'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."report/by_places/sent_export/".$args[0].".html";
        $person = mgd_get_person($args[0]);

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_RECEIVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_constraint('creator', '=', $person->id);
        $QB->add_order('created', 'ASC');
        $orders = $QB->execute();

        foreach($orders as $order)
        {
            $order = mgd_get_event($order->id);
            $this->_request_data['detail'][] = $order;
        }

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_places_sent_export($handler_id, &$data)
    {
        midcom_show_style('show_report_by_places_detail_export');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_places_receive($handler_id, $args, &$data)
    {
        $this->_request_data['detail'] = array();
        $this->_request_data['person'] = $args[0];
        $this->_request_data['sent_receive'] = 1;
        $this->_request_data['link'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."report/by_places/receive_export/".$args[0].".html";
        $person = mgd_get_person($args[0]);

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_RECEIVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_constraint('extra', '=', $person->id);
        $QB->add_order('creator', 'ASC');
        $orders = $QB->execute();

        foreach($orders as $order)
        {
            $order = mgd_get_event($order->id);
            $this->_request_data['detail'][] = $order;
        }

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/',
            MIDCOM_NAV_NAME => 'Raportit',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_places/',
            MIDCOM_NAV_NAME => 'Toimipaikoittain',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_places/receive/',
            MIDCOM_NAV_NAME => 'Vastaanotetut, vastaanottajana '. $person->name,
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);


        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_places_receive($handler_id, &$data)
    {
        midcom_show_style('show_report_by_places_detail');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_places_receive_export($handler_id, $args, &$data)
    {

        $headers = array
        (
            'Content-type: application/vnd.ms-excel',
            "Content-disposition: attachment; filename=report_receive.xls",
        );

        foreach ($headers as $header)
        {
            $_MIDCOM->header($header);
        }
        $_MIDCOM->skip_page_style = true;

        $this->_request_data['detail'] = array();
        $this->_request_data['person'] = $args[0];
        $this->_request_data['sent_receive'] = 1;
        $this->_request_data['link'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."report/by_places/receive_export/".$args[0].".html";
        $person = mgd_get_person($args[0]);

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_RECEIVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_constraint('extra', '=', $person->id);
        $QB->add_order('creator', 'ASC');
        $orders = $QB->execute();

        foreach($orders as $order)
        {
            $order = mgd_get_event($order->id);
            $this->_request_data['detail'][] = $order;
        }


        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_places_receive_export($handler_id, &$data)
    {
        midcom_show_style('show_report_by_places_detail_export');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_places_sent_2($handler_id, $args, &$data)
    {
        $this->_request_data['detail'] = array();
        $this->_request_data['person'] = $args[0];
        $this->_request_data['sent_receive'] = 0;
        $this->_request_data['link'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."report/by_places/sent_2_export/".$args[0].".html";
        $person = mgd_get_person($args[0]);

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->begin_group('OR');
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT_LOCKED);
        $QB->end_group();
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_constraint('creator', '=', $person->id);
        $orders = $QB->execute();
        foreach($orders as $order)
        {
                $order = mgd_get_event($order->id);
                $this->_request_data['detail'][] = $order;
        }

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/',
            MIDCOM_NAV_NAME => 'Raportit',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_places/',
            MIDCOM_NAV_NAME => 'Toimipaikoittain',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_places/sent_2/',
            MIDCOM_NAV_NAME => 'Vastaanottamattomat, lähettäjänä '. $person->name,
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);


        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_places_sent_2($handler_id, &$data)
    {
        midcom_show_style('show_report_by_places_detail');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_places_sent_2_export($handler_id, $args, &$data)
    {
        $this->_request_data['detail'] = array();
        $this->_request_data['person'] = $args[0];
        $this->_request_data['sent_receive'] = 0;
        $person = mgd_get_person($args[0]);


        $headers = array
        (
            'Content-type: application/vnd.ms-excel',
            "Content-disposition: attachment; filename=report_place_sent.xls",
        );
        foreach ($headers as $header)
        {
            $_MIDCOM->header($header);
        }

        $_MIDCOM->skip_page_style = true;

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->begin_group('OR');
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT_LOCKED);
        $QB->end_group();
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_constraint('creator', '=', $person->id);
        $orders = $QB->execute();
        foreach($orders as $order)
        {
                $order = mgd_get_event($order->id);
                $this->_request_data['detail'][] = $order;
        }

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_places_sent_2_export($handler_id, &$data)
    {
        midcom_show_style('show_report_by_places_detail_export');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_places_receive_2($handler_id, $args, &$data)
    {
        $this->_request_data['detail'] = array();
        $this->_request_data['person'] = $args[0];
        $this->_request_data['link'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."report/by_places/receive_2_export/".$args[0].".html";
        $this->_request_data['sent_receive'] = 1;
        $person = mgd_get_person($args[0]);

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->begin_group('OR');
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT_LOCKED);
        $QB->end_group();
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_constraint('extra', '=', $person->id);
        $QB->add_order('creator', 'ASC');
        $orders = $QB->execute();

        foreach($orders as $order)
        {
                $order = mgd_get_event($order->id);
                $this->_request_data['detail'][] = $order;
        }

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/',
            MIDCOM_NAV_NAME => 'Raportit',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_places/',
            MIDCOM_NAV_NAME => 'Toimipaikoittain',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_places/receive_2/',
            MIDCOM_NAV_NAME => 'Vastaanottamattomat, vastaanottajana '. $person->name,
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_places_receive_2($handler_id, &$data)
    {
        midcom_show_style('show_report_by_places_detail');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_places_receive_2_export($handler_id, $args, &$data)
    {
        $this->_request_data['detail'] = array();
        $this->_request_data['person'] = $args[0];
        $this->_request_data['link'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."report/by_places/receive_2_export/".$args[0].".html";
        $this->_request_data['sent_receive'] = 1;
        $person = mgd_get_person($args[0]);

        $headers = array
        (
            'Content-type: application/vnd.ms-excel',
            "Content-disposition: attachment; filename=report_place_receive.xls",
        );

        foreach ($headers as $header)
        {
            $_MIDCOM->header($header);
        }
        $_MIDCOM->skip_page_style = true;

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->begin_group('OR');
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT_LOCKED);
        $QB->end_group();
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_constraint('extra', '=', $person->id);
        $QB->add_order('creator', 'ASC');
        $orders = $QB->execute();

        foreach($orders as $order)
        {
                $order = mgd_get_event($order->id);
                $this->_request_data['detail'][] = $order;
        }


        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_places_receive_2_export($handler_id, &$data)
    {
        midcom_show_style('show_report_by_places_detail_export');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_products($handler_id, $args, &$data)
    {
        $this->_request_data['root_groups'] = array();
        $this->_request_data['sub_groups'] = array();

        $QB_root = org_openpsa_products_product_group_dba::new_query_builder();
        $QB_root->add_constraint('up', '=', 0);
        $QB_root->add_order('code', 'ASC');
        $roots = $QB_root->execute();
        $roots_count = $QB_root->count();


        $this->_request_data['root_groups'] = $roots;
//        print_r($this->_request_data['root_groups']);

        foreach($roots as $root)
        {
            $QB_child = org_openpsa_products_product_group_dba::new_query_builder();
            $QB_child->add_constraint('up', '=', $root->id);
            $QB_child->add_order('code', 'ASC');
            $childs = $QB_child->execute();
            $childs_count = $QB_child->count();
            $this->_request_data['sub_groups'][$root->code] = $childs;
        }


        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/',
            MIDCOM_NAV_NAME => 'Raportit',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_products/',
            MIDCOM_NAV_NAME => 'Tuotteittain',
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_products($handler_id, &$data)
    {
        midcom_show_style('show_report_by_products');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_products_group($handler_id, $args, &$data)
    {
        $this->_request_data['product_group'] = $args[0];
        $this->_request_data['root_group'] = NULL;
        $this->_request_data['sub_group'] = NULL;
        $this->_request_data['products_from_query'] = array();
        if(strlen($this->_request_data['product_group']) == 5)
        {
            $this->_request_data['root_group'] = substr($args[0],0,3);
            $this->_request_data['sub_group'] = substr($args[0],3,5);
        }
        elseif(strlen($this->_request_data['product_group']) == 4)
        {
            $this->_request_data['root_group'] = substr($args[0],0,3);
            $this->_request_data['sub_group'] = substr($args[0],3);
        }
        elseif(strlen($this->_request_data['product_group']) == 3)
        {
            $this->_request_data['root_group'] = substr($args[0],0,3);
        }
        else
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "ERROR, wrong type of product group ");
        }

        $QB_groups = org_openpsa_products_product_group_dba::new_query_builder();
        $QB_groups->add_constraint('up', '=', 0);
        $QB_groups->add_constraint('code', '=', $this->_request_data['root_group']);
        $QB_groups->add_order('code', 'ASC');
        $groups = $QB_groups->execute();
        $groups_count = $QB_groups->count();

        if($groups_count > 0 && $this->_request_data['sub_group'] != NULL)
        {
            $QB_groups2 = org_openpsa_products_product_group_dba::new_query_builder();
            $QB_groups2->add_constraint('up', '=', $groups[0]->id);
            $QB_groups2->add_constraint('code', '=', $this->_request_data['sub_group']);
            $QB_groups2->add_order('code', 'ASC');
            $groups2 = $QB_groups2->execute();
            $groups2_count = $QB_groups2->count();
        }
        if($groups2_count > 0)
        {
            foreach($groups2 as $group2)
            {
                $QB_products = org_openpsa_products_product_dba::new_query_builder();
                $QB_products->add_constraint('productGroup', '=', $group2->id);
                $QB_products->add_order('code', 'ASC');
                $products_QB = $QB_products->execute();
                $products_QB_count = $QB_products->count();

                if($products_QB_count > 0)
                {
                    foreach($products_QB as $product_QB)
                    {
                        $this->_request_data['products_from_query'][$products_QB->code][] = new org_openpsa_products_product_dba($product_QB->id);
                    }
                }
            }
        }

        $this->_request_data['product_known'] = array();
        $this->_request_data['product_unknown'] = array();

        $QB = midcom_db_event::new_query_builder();

        $QB->add_constraint('up', '=', $this->_root_event->id);

        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_RECEIVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_order('creator', 'ASC');
        $products_events = $QB->execute();

        foreach($products_events as $product_event)
        {
            $order = mgd_get_event($product_event->id);
            $QB2 = midcom_db_event::new_query_builder();
            $QB2->add_constraint('up', '=', $product_event->id);
                $QB2->begin_group('OR');
                if($groups2_count > 0)
                {
                    foreach($groups2 as $group2)
                    {
//                        echo $groups[0]->code . $group2->code."<br  />";
                        $QB2->add_constraint('extra', '=',  $groups[0]->code . $group2->code);
                    }
                }

                foreach($this->_request_data['products_from_query'] as $id_not_in_use => $product_from_query)
                {
                    foreach($product_from_query as $id_not_in_use2 => $product_from_query2)
                    {
//                        echo $product_from_query2->code."<br  />";
                        $QB2->add_constraint('extra', '=', $product_from_query2->code);
                    }
                }
                $QB2->end_group();
            $QB2->add_order('creator', 'ASC');
            $products2 = $QB2->execute();

/*            echo "<pre>";
            print_r($products2);
            echo "</pre>";*/

            foreach($products2 as $product2)
            {
                $product3 = mgd_get_event($product2->id);
                if (strlen($product3->extra) == 7)
                    $this->_request_data['product_known'][$product3->extra][] = $product3;
                elseif (strlen($product3->extra) == 5)
                    $this->_request_data['product_unknown'][$product3->extra][] = $product3;
            }
        }

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/',
            MIDCOM_NAV_NAME => 'Raportit',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_products/',
            MIDCOM_NAV_NAME => 'Tuotteittain',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_products_group/',
            MIDCOM_NAV_NAME => 'Ryhmä: '.$args[0],
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_products_group($handler_id, &$data)
    {
        midcom_show_style('show_report_by_products_group');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_products_detail($handler_id, $args, &$data)
    {

        $this->_request_data['link'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."report/by_products/detail_export/".$args[0].".html";

        $this->_request_data['product'] = array();


        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_RECEIVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_order('creator', 'ASC');
        $products = $QB->execute();

        foreach($products as $product)
        {
            $order = mgd_get_event($product->id);
            $QB2 = midcom_db_event::new_query_builder();
            $QB2->add_constraint('up', '=', $product->id);
            $QB2->add_order('creator', 'ASC');
            $products2 = $QB2->execute();
            foreach($products2 as $product2)
            {
                $product3 = mgd_get_event($product2->id);
                if ($args[0] == $product3->extra)
                {
                    if (strlen($product3->extra) == 7)
                    {
                        $this->_request_data['product'][$product3->extra][] = $product3;
                    }
                    elseif (strlen($product3->extra) == 5)
                    {
                        $this->_request_data['product'][$product3->extra][$product->title][] = $product3;
                    }
                }
            }
        }
        $this->_request_data['name'] = $args[0];


        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/',
            MIDCOM_NAV_NAME => 'Raportit',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_products/',
            MIDCOM_NAV_NAME => 'Tuotteittain',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_products/detail/',
            MIDCOM_NAV_NAME => 'Tuote: '. $args[0],
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_products_detail($handler_id, &$data)
    {
        midcom_show_style('show_report_by_products_detail');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_products_detail_export($handler_id, $args, &$data)
    {



        $headers = array
        (
            'Content-type: application/vnd.ms-excel',
            "Content-disposition: attachment; filename=report_products.xls",
        );

        foreach ($headers as $header)
        {
            $_MIDCOM->header($header);
        }
        $_MIDCOM->skip_page_style = true;



        $this->_request_data['product'] = array();


        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_RECEIVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_order('creator', 'ASC');
        $products = $QB->execute();

        foreach($products as $product)
        {
            $order = mgd_get_event($product->id);
            $QB2 = midcom_db_event::new_query_builder();
            $QB2->add_constraint('up', '=', $product->id);
            $QB2->add_order('creator', 'ASC');
            $products2 = $QB2->execute();
            foreach($products2 as $product2)
            {
                $product3 = mgd_get_event($product2->id);
                if ($args[0] == $product3->extra)
                {
                    if (strlen($product3->extra) == 7)
                    {
                        $this->_request_data['product'][$product3->extra][] = $product3;
                    }
                    elseif (strlen($product3->extra) == 5)
                    {
                        $this->_request_data['product'][$product3->extra][$product->title][] = $product3;
                    }
                }
            }
        }
        $this->_request_data['name'] = $args[0];


        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_products_detail_export($handler_id, &$data)
    {
        midcom_show_style('show_report_by_products_detail_export');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_places_sent_3($handler_id, $args, &$data)
    {
        $this->_request_data['detail'] = array();
        $this->_request_data['person'] = $args[0];
        $this->_request_data['sent_receive'] = 0;
        $this->_request_data['link'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."report/by_places/sent_2_export/".$args[0].".html";
        $person = mgd_get_person($args[0]);

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_REMOVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_constraint('creator', '=', $person->id);
        $orders = $QB->execute();
        foreach($orders as $order)
        {
                $order = mgd_get_event($order->id);
                $this->_request_data['detail'][] = $order;
        }

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/',
            MIDCOM_NAV_NAME => 'Raportit',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_places/',
            MIDCOM_NAV_NAME => 'Toimipaikoittain',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_places/sent_3/',
            MIDCOM_NAV_NAME => 'Poistetut, lähettäjänä '. $person->name,
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_places_sent_3($handler_id, &$data)
    {
        midcom_show_style('show_report_by_places_detail');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_places_sent_3_export($handler_id, $args, &$data)
    {
        $this->_request_data['detail'] = array();
        $this->_request_data['person'] = $args[0];
        $this->_request_data['sent_receive'] = 0;
        $person = mgd_get_person($args[0]);

        $headers = array
        (
            'Content-type: application/vnd.ms-excel',
            "Content-disposition: attachment; filename=report_place_sent_removed.xls",
        );

        foreach ($headers as $header)
        {
            $_MIDCOM->header($header);
        }
        $_MIDCOM->skip_page_style = true;

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_REMOVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_constraint('creator', '=', $person->id);
        $orders = $QB->execute();
        foreach($orders as $order)
        {
                $order = mgd_get_event($order->id);
                $this->_request_data['detail'][] = $order;
        }

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_places_sent_3_export($handler_id, &$data)
    {
        midcom_show_style('show_report_by_places_detail_export');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_places_receive_3($handler_id, $args, &$data)
    {
        $this->_request_data['detail'] = array();
        $this->_request_data['person'] = $args[0];
        $this->_request_data['link'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."report/by_places/receive_2_export/".$args[0].".html";
        $this->_request_data['sent_receive'] = 1;
        $person = mgd_get_person($args[0]);

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT_REMOVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_constraint('extra', '=', $person->id);
        $QB->add_order('creator', 'ASC');
        $orders = $QB->execute();

        foreach($orders as $order)
        {
                $order = mgd_get_event($order->id);
                $this->_request_data['detail'][] = $order;
        }

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/',
            MIDCOM_NAV_NAME => 'Raportit',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_places/',
            MIDCOM_NAV_NAME => 'Toimipaikoittain',
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'report/by_places/receive_3/',
            MIDCOM_NAV_NAME => 'Poistetut, vastaanottajana '. $person->name,
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_places_receive_3($handler_id, &$data)
    {
        midcom_show_style('show_report_by_places_detail');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_by_places_receive_3_export($handler_id, $args, &$data)
    {
        $this->_request_data['detail'] = array();
        $this->_request_data['person'] = $args[0];
        $this->_request_data['link'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."report/by_places/receive_2_export/".$args[0].".html";
        $this->_request_data['sent_receive'] = 1;
        $person = mgd_get_person($args[0]);

        $headers = array
        (
            'Content-type: application/vnd.ms-excel',
            "Content-disposition: attachment; filename=report_place_receive.xls",
        );

        foreach ($headers as $header)
        {
            $_MIDCOM->header($header);
        }
        $_MIDCOM->skip_page_style = true;

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT_REMOVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_constraint('extra', '=', $person->id);
        $QB->add_order('creator', 'ASC');
        $orders = $QB->execute();

        foreach($orders as $order)
        {
                $order = mgd_get_event($order->id);
                $this->_request_data['detail'][] = $order;
        }


        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_by_places_receive_3_export($handler_id, &$data)
    {
        midcom_show_style('show_report_by_places_detail_export');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_unclear($handler_id, $args, &$data)
    {
        $this->_request_data['unclear'] = array();
        $this->_request_data['unclear2'] = array();
        $this->_request_data['link'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."report/unclear_export/";


        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_RECEIVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_order('creator', 'ASC');
        $orders = $QB->execute();

        foreach($orders as $order)
        {
            $QB2 = midcom_db_event::new_query_builder();
            $QB2->add_constraint('up', '=', $order->id);
            $QB2->add_order('creator', 'ASC');
            $products = $QB2->execute();

            $is_unclear = false;

            foreach($products as $product)
            {
                if($product->parameter('net.nemein.internalorders', 'quantity') != $product->parameter('net.nemein.internalorders', 'quantity_received'))
                {
                    $is_unclear = true;
                }
            }
            if($is_unclear)
            {
                $order2 = new midcom_db_event($order->id);
                $this->_request_data['unclear'][$order2->creator][] = $order2;
                $this->_request_data['unclear2'][$order2->extra][] = $order2;
            }
        }


        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_unclear($handler_id, &$data)
    {
        midcom_show_style('show_report_unclear');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_unclear_detail($handler_id, $args, &$data)
    {
        $this->_request_data['unclear'] = array();
        $this->_request_data['person'] = $args[0];
        $person = mgd_get_person($args[0]);
        $this->_request_data['link'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."report/unclear/detail_export/".$args[0].".html";


        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_RECEIVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_constraint('creator', '=', $person->id);
        $QB->add_order('creator', 'ASC');
        $orders = $QB->execute();

        foreach($orders as $order)
        {
            $QB2 = midcom_db_event::new_query_builder();
            $QB2->add_constraint('up', '=', $order->id);
            $QB2->add_order('creator', 'ASC');
            $products = $QB2->execute();

            $is_unclear = false;

            foreach($products as $product)
            {
                if($product->parameter('net.nemein.internalorders', 'quantity') != $product->parameter('net.nemein.internalorders', 'quantity_received'))
                {
                    $is_unclear = true;
                }
            }
            if($is_unclear)
            {
                $order2 = new midcom_db_event($order->id);
                $this->_request_data['unclear'][] = $order2;
            }
        }


        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_unclear_detail($handler_id, &$data)
    {
        midcom_show_style('show_report_unclear_detail');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_unclear_detail_export($handler_id, $args, &$data)
    {
        $this->_request_data['unclear'] = array();
        $this->_request_data['person'] = $args[0];
        $person = mgd_get_person($args[0]);
        $this->_request_data['link'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."report/unclear/detail_export/".$args[0].".html";


        $headers = array
        (
            'Content-type: application/octet-stream',
            "Content-disposition: attachment; filename=report_unclear_detail.csv",
        );

        foreach ($headers as $header)
        {
            $_MIDCOM->header($header);
        }
        $_MIDCOM->skip_page_style = true;

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_RECEIVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_constraint('creator', '=', $person->id);
        $QB->add_order('creator', 'ASC');
        $orders = $QB->execute();

        foreach($orders as $order)
        {
            $QB2 = midcom_db_event::new_query_builder();
            $QB2->add_constraint('up', '=', $order->id);
            $QB2->add_order('creator', 'ASC');
            $products = $QB2->execute();

            $is_unclear = false;

            foreach($products as $product)
            {
                if($product->parameter('net.nemein.internalorders', 'quantity') != $product->parameter('net.nemein.internalorders', 'quantity_received'))
                {
                    $is_unclear = true;
                }
            }
            if($is_unclear)
            {
                $order2 = new midcom_db_event($order->id);
                $this->_request_data['unclear'][] = $order2;
            }
        }


        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_unclear_detail_export($handler_id, &$data)
    {
        midcom_show_style('show_report_unclear_detail_export');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_unclear_detail_2($handler_id, $args, &$data)
    {
        $this->_request_data['unclear'] = array();
        $this->_request_data['person'] = $args[0];
        $person = mgd_get_person($args[0]);
        $this->_request_data['link'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."report/unclear/detail_2_export/".$args[0].".html";


        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_RECEIVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_constraint('extra', '=', $person->id);
        $QB->add_order('creator', 'ASC');
        $orders = $QB->execute();

        foreach($orders as $order)
        {
            $QB2 = midcom_db_event::new_query_builder();
            $QB2->add_constraint('up', '=', $order->id);
            $QB2->add_order('creator', 'ASC');
            $products = $QB2->execute();

            $is_unclear = false;

            foreach($products as $product)
            {
                if($product->parameter('net.nemein.internalorders', 'quantity') != $product->parameter('net.nemein.internalorders', 'quantity_received'))
                {
                    $is_unclear = true;
                }
            }
            if($is_unclear)
            {
                $order2 = new midcom_db_event($order->id);
                $this->_request_data['unclear'][] = $order2;
            }
        }


        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_unclear_detail_2($handler_id, &$data)
    {
        midcom_show_style('show_report_unclear_detail');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report_unclear_detail_2_export($handler_id, $args, &$data)
    {
        $this->_request_data['unclear'] = array();
        $this->_request_data['person'] = $args[0];
        $person = mgd_get_person($args[0]);
        $this->_request_data['link'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."report/unclear/detail_2_export/".$args[0].".html";


        $headers = array
        (
            'Content-type: application/octet-stream',
            "Content-disposition: attachment; filename=report_unclear_detail.csv",
        );

        foreach ($headers as $header)
        {
            $_MIDCOM->header($header);
        }
        $_MIDCOM->skip_page_style = true;

        $QB = midcom_db_event::new_query_builder();
        $QB->add_constraint('up', '=', $this->_root_event->id);
        $QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_RECEIVED);
        if(!$this->_is_admin)
        {
            $QB->begin_group('OR');
            $QB->add_constraint('extra', '=', $_MIDGARD['user']);
            $QB->add_constraint('creator', '=', $_MIDGARD['user']);
            $QB->end_group();
        }
        $QB->add_constraint('extra', '=', $person->id);
        $QB->add_order('creator', 'ASC');
        $orders = $QB->execute();

        foreach($orders as $order)
        {
            $QB2 = midcom_db_event::new_query_builder();
            $QB2->add_constraint('up', '=', $order->id);
            $QB2->add_order('creator', 'ASC');
            $products = $QB2->execute();

            $is_unclear = false;

            foreach($products as $product)
            {
                if($product->parameter('net.nemein.internalorders', 'quantity') != $product->parameter('net.nemein.internalorders', 'quantity_received'))
                {
                    $is_unclear = true;
                }
            }
            if($is_unclear)
            {
                $order2 = new midcom_db_event($order->id);
                $this->_request_data['unclear'][] = $order2;
            }
        }


        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report_unclear_detail_2_export($handler_id, &$data)
    {
        midcom_show_style('show_report_unclear_detail_export');
    }

}
?>