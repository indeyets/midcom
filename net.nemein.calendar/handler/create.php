<?php
/**
 * @package net.nemein.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar event creator
 *
 * @package net.nemein.calendar
 */
class net_nemein_calendar_handler_create extends midcom_baseclasses_components_handler
{

    /**
     * The Controller of the article used for creating
     *
     * @var midcom_helper_datamanager2_controller_create
     * @access private
     */
    var $_controller = null;

    /**
     * Simple default constructor.
     */
    function net_nemein_calendar_handler_create()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function & dm2_create_callback (&$controller)
    {
        $this->_request_data['event'] = new net_nemein_calendar_event_dba();
        $this->_request_data['event']->node = $this->_request_data['content_topic']->id;

        if ($this->_request_data['master_event'])
        {
            $this->_request_data['event']->up = $this->_request_data['master_event'];
        }

        if (! $this->_request_data['event']->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_request_data['event']);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new event, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_request_data['event'];
    }

    /**
     * Internal helper, loads the controller for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_request_data['schemadb'];
        $this->_controller->schemaname = $this->_request_data['schema'];
        $this->_controller->defaults = $this->_request_data['defaults'];
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }

    /**
     * Displays an event creation view.
     *
     * The form can be manipulated using query strings like the following:
     *
     * ?defaults[title]=Kaljakellunta&defaults[start]=20070911T123001&defaults[categories]=|foo|
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $data['content_topic']->require_do('midgard:create');
        $data['event'] = null;

        $data['schema'] = $args[0];
        if (!array_key_exists($data['schema'], $data['schemadb']))
        {
            return false;
        }

        $data['defaults'] = Array();

        // Allow setting defaults from query string, useful for things like "create event for today" and chooser
        if (isset($_GET['defaults'])
            && is_array($_GET['defaults']))
        {
            foreach ($_GET['defaults'] as $key => $value)
            {
                if (!isset($data['schemadb'][$data['schema']]->fields[$key]))
                {
                    // No such field in schema
                    continue;
                }

                $data['defaults'][$key] = $value;
            }
        }

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Index the article
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nemein_calendar_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);

                // Generate URL name
                if ($data['event']->name == '')
                {
                    $data['event']->name = midcom_generate_urlname_from_string($data['event']->title);
                    $tries = 0;
                    $maxtries = 999;
                    while(   !$data['event']->update()
                          && $tries < $maxtries)
                    {
                        $data['event']->name = midcom_generate_urlname_from_string($data['event']->title);
                        if ($tries > 0)
                        {
                            // Append an integer if articles with same name exist
                            $data['event']->name .= sprintf("-%03d", $tries);
                        }
                        $tries++;
                    }
                }

                if ($handler_id != 'create_chooser')
                {
                    $_MIDCOM->relocate("{$data['event']->name}/");
                    // This will exit.
                }
                break;

            case 'cancel':
                $data['cancelled'] = true;
                if ($handler_id != 'create_chooser')
                {
                    $_MIDCOM->relocate('');
                    // This will exit.
                }
                break;
        }

        $title = sprintf($this->_l10n_midcom->get('create %s'), $this->_l10n->get($this->_request_data['schemadb'][$this->_request_data['schema']]->description));
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");

        if ($handler_id == 'create_chooser')
        {
            $_MIDCOM->skip_page_style = true;
        }

        // Set the breadcrumb
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "create/{$data['schema']}/",
            MIDCOM_NAV_NAME => sprintf($this->_l10n_midcom->get('create %s'), $this->_l10n->get($data['schemadb'][$data['schema']]->description)),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);

        return true;
    }


    /**
     * Shows the loaded article.
     */
    function _show_create ($handler_id, &$data)
    {
        $data['controller'] =& $this->_controller;

        if ($handler_id == 'create_chooser')
        {
            midcom_show_style('popup_header');

            if (   $data['event']
                || isset($data['cancelled']))
            {
                $data['jsdata'] = $this->_object_to_jsdata($data['event']);
                midcom_show_style('admin_create_after');
            }
            else
            {
                midcom_show_style('admin_create');
            }
            midcom_show_style('popup_footer');

            return;
        }

        midcom_show_style('admin_create');
    }

    function _object_to_jsdata(&$object)
    {
        $id = @$object->id;
        $guid = @$object->guid;

        $jsdata = "{";

        $jsdata .= "id: '{$id}',";
        $jsdata .= "guid: '{$guid}',";
        $jsdata .= "pre_selected: true,";

        $hi_count = count($this->_request_data['schemadb'][$this->_request_data['schema']]->fields);
        $i = 1;
        foreach ($this->_request_data['schemadb'][$this->_request_data['schema']]->fields as $field => $field_data)
        {
            $value = @$object->$field;
            $value = rawurlencode($value);
            $jsdata .= "{$field}: '{$value}'";

            if ($i < $hi_count)
            {
                $jsdata .= ", ";
            }

            $i++;
        }

        $jsdata .= "}";

        return $jsdata;
    }
}

?>
